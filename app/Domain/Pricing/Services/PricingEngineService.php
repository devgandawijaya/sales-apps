<?php

namespace App\Domain\Pricing\Services;

use App\Domain\Customer\Models\Customer;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Pricing\Models\PriceListItem;
use App\Domain\Sales\DTO\DiscountCalculationResultDTO;
use App\Domain\Sales\Exceptions\PriceNotFoundException;

class PricingEngineService
{
    /**
     * Resolve unit price and calculate tiered discounts according to GT vs MT channels.
     */
    public function calculateItemPricing(
        Customer $customer,
        Product $product,
        ProductUnit $unit,
        int $qtyOrdered,
        ?float $customUnitPrice = null,
        float $discountPercent1 = 0.0,
        float $discountPercent2 = 0.0,
        float $discountNominal = 0.0
    ): DiscountCalculationResultDTO {
        // 1. Determine Base Unit Price
        $unitPrice = $customUnitPrice;
        if ($unitPrice === null || $unitPrice <= 0) {
            $unitPrice = $this->resolvePriceFromList($customer, $product, $unit, $qtyOrdered);
        }

        $grossAmount = $unitPrice * $qtyOrdered;

        // 2. Channel-based discount rules
        $onInvoiceRate = 0.0;
        $offInvoiceRebateRate = 0.0;

        if ($customer->isModernTrade()) {
            // Modern Trade: Check Modern Trade Agreement
            $agreement = $customer->modernTradeAgreement;
            if ($agreement) {
                $onInvoiceRate = (float) $agreement->on_invoice_discount_rate;
                $offInvoiceRebateRate = (float) $agreement->off_invoice_rebate_rate;
            }
        }

        // Apply item-level stepped discounts: Net = Gross * (1 - d1%) * (1 - d2%) - nominal
        $step1 = $grossAmount * (1 - ($discountPercent1 / 100));
        $step2 = $step1 * (1 - ($discountPercent2 / 100));
        $itemDiscountTotal = ($grossAmount - $step2) + $discountNominal;

        // Apply Modern Trade on-invoice discount if applicable
        if ($onInvoiceRate > 0) {
            $extraMtDiscount = ($step2 - $discountNominal) * ($onInvoiceRate / 100);
            $itemDiscountTotal += $extraMtDiscount;
        }

        $netAmount = max(0, $grossAmount - $itemDiscountTotal);

        return new DiscountCalculationResultDTO(
            unitPrice: $unitPrice,
            grossAmount: $grossAmount,
            itemDiscountTotal: $itemDiscountTotal,
            netAmount: $netAmount,
            onInvoiceDiscountRate: $onInvoiceRate,
            offInvoiceRebateRate: $offInvoiceRebateRate
        );
    }

    /**
     * Resolve the price list item with volume tiering and fallback.
     */
    public function resolvePriceFromList(
        Customer $customer,
        Product $product,
        ProductUnit $unit,
        int $qtyOrdered
    ): float {
        // Priority 1: Customer's dedicated price list
        $priceListId = $customer->price_list_id;

        $query = PriceListItem::where('product_id', $product->id)
            ->where('product_unit_id', $unit->id)
            ->where('min_quantity', '<=', $qtyOrdered)
            ->orderBy('min_quantity', 'desc');

        if ($priceListId) {
            $item = (clone $query)->where('price_list_id', $priceListId)->first();
            if ($item) {
                return (float) $item->unit_price;
            }
        }

        // Priority 2: Channel default price list (GT or MT)
        $channelPriceItem = (clone $query)->whereHas('priceList', function ($q) use ($customer) {
            $q->where('channel_type', $customer->channel)
              ->where('is_active', true)
              ->where('valid_from', '<=', now())
              ->where(function ($w) {
                  $w->whereNull('valid_to')->orWhere('valid_to', '>=', now());
              });
        })->first();

        if ($channelPriceItem) {
            return (float) $channelPriceItem->unit_price;
        }

        // Fallback: If no price found, throw exception
        throw new PriceNotFoundException($product->sku, $unit->unit_name);
    }
}

