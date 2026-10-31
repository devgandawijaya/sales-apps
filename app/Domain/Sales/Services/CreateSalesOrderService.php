<?php

namespace App\Domain\Sales\Services;

use App\Domain\Customer\Models\Customer;
use App\Domain\Inventory\Services\StockReservationService;
use App\Domain\Pricing\Services\PricingEngineService;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\DTO\CreateSalesOrderDTO;
use App\Domain\Sales\DTO\SalesOrderItemDTO;
use App\Domain\Sales\Exceptions\CreditLimitExceededException;
use App\Domain\Sales\Exceptions\CustomerInactiveException;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesOrderItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Enterprise Service for creating Sales Orders across General Trade (GT) and Modern Trade (MT).
 * Encapsulates:
 * 1. Multi-Branch validation & customer status check.
 * 2. Credit limit & outstanding AR validation.
 * 3. Channel-specific pricing & tiered discount calculation (GT vs MT).
 * 4. Pessimistic row locking on inventory (FEFO reservation).
 * 5. Atomic database transaction execution.
 */
class CreateSalesOrderService
{
    public const TAX_RATE = 0.11; // PPN 11%

    public function __construct(
        protected PricingEngineService $pricingEngine,
        protected StockReservationService $stockReservation
    ) {}

    /**
     * Execute Sales Order creation atomically.
     *
     * @param CreateSalesOrderDTO $dto
     * @return SalesOrder
     * @throws CustomerInactiveException
     * @throws CreditLimitExceededException
     * @throws \Throwable
     */
    public function execute(CreateSalesOrderDTO $dto): SalesOrder
    {
        if (empty($dto->items)) {
            throw new InvalidArgumentException("Daftar barang pesanan tidak boleh kosong.");
        }

        return DB::transaction(function () use ($dto) {
            // -----------------------------------------------------------------
            // 1. Lock and validate Customer
            // -----------------------------------------------------------------
            $customer = Customer::where('id', $dto->customerId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$customer->is_active) {
                throw new CustomerInactiveException($customer->code);
            }

            // Modern Trade specific validation
            if ($customer->isModernTrade() && empty($dto->poNumberMt)) {
                throw new InvalidArgumentException("Pesanan Modern Trade harus menyertakan nomor PO dari pelanggan.");
            }

            // -----------------------------------------------------------------
            // 2. Pre-process items & calculate Pricing & Discounts
            // -----------------------------------------------------------------
            $calculatedItems = [];
            $subtotal = 0.0;
            $totalDiscountOnInvoice = 0.0;
            $totalDiscountOffInvoice = 0.0;

            foreach ($dto->items as $itemDto) {
                $product = Product::where('id', $itemDto->productId)
                    ->where('is_active', true)
                    ->firstOrFail();

                $unit = ProductUnit::where('id', $itemDto->productUnitId)
                    ->where('product_id', $product->id)
                    ->firstOrFail();

                // Calculate pricing with channel rules (GT vs MT)
                $pricing = $this->pricingEngine->calculateItemPricing(
                    customer: $customer,
                    product: $product,
                    unit: $unit,
                    qtyOrdered: $itemDto->qtyOrdered,
                    customUnitPrice: $itemDto->customUnitPrice,
                    discountPercent1: $itemDto->discountPercent1,
                    discountPercent2: $itemDto->discountPercent2,
                    discountNominal: $itemDto->discountNominal
                );

                $qtyInBaseUom = $itemDto->qtyOrdered * $unit->conversion_factor;

                // -------------------------------------------------------------
                // 3. Pessimistic Row Lock on Inventory & FEFO Reservation
                // -------------------------------------------------------------
                $this->stockReservation->reserveStock(
                    warehouseId: $dto->warehouseId,
                    product: $product,
                    totalQtyBaseUom: $qtyInBaseUom
                );

                $subtotal += $pricing->grossAmount;
                $totalDiscountOnInvoice += $pricing->itemDiscountTotal;

                // If Modern Trade, calculate off-invoice rebate accrual
                if ($pricing->offInvoiceRebateRate > 0) {
                    $rebateAmount = $pricing->netAmount * ($pricing->offInvoiceRebateRate / 100);
                    $totalDiscountOffInvoice += $rebateAmount;
                }

                $calculatedItems[] = [
                    'dto' => $itemDto,
                    'product' => $product,
                    'unit' => $unit,
                    'qty_in_base_uom' => $qtyInBaseUom,
                    'pricing' => $pricing,
                ];
            }

            // -----------------------------------------------------------------
            // 4. Calculate Tax & Grand Total
            // -----------------------------------------------------------------
            $taxableBase = max(0, $subtotal - $totalDiscountOnInvoice);
            $taxAmount = round($taxableBase * self::TAX_RATE, 2);
            $grandTotal = $taxableBase + $taxAmount;

            // -----------------------------------------------------------------
            // 5. Credit Limit Validation (for Credit Payment Term)
            // -----------------------------------------------------------------
            $paymentTermDays = $customer->term_of_payment;
            $paymentType = $customer->payment_type;

            if (strtoupper($paymentType) === 'CREDIT' || $paymentTermDays > 0) {
                $projectedCreditUsed = (float)$customer->current_credit_used + $grandTotal;
                $creditLimit = (float)$customer->credit_limit;

                if ($projectedCreditUsed > $creditLimit) {
                    throw new CreditLimitExceededException(
                        customerName: $customer->name,
                        currentUsed: (float)$customer->current_credit_used,
                        orderAmount: $grandTotal,
                        creditLimit: $creditLimit
                    );
                }

                // Increment customer's utilized credit
                $customer->increment('current_credit_used', $grandTotal);
            }

            // -----------------------------------------------------------------
            // 6. Generate Sales Order Number & Persist Header
            // -----------------------------------------------------------------
            $soNumber = $this->generateSoNumber($dto->branchId, $customer->channel);

            $salesOrder = SalesOrder::create([
                'branch_id' => $dto->branchId,
                'customer_id' => $customer->id,
                'salesman_id' => $dto->salesmanId,
                'warehouse_id' => $dto->warehouseId,
                'so_number' => $soNumber,
                'po_number_mt' => $dto->poNumberMt,
                'order_date' => $dto->orderDate,
                'channel' => $customer->channel,
                'order_type' => $dto->orderType,
                'status' => 'PENDING_APPROVAL',
                'payment_term_days' => $paymentTermDays,
                'subtotal' => $subtotal,
                'discount_on_invoice' => $totalDiscountOnInvoice,
                'discount_off_invoice' => $totalDiscountOffInvoice,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'approval_level' => 1,
                'notes' => $dto->notes,
            ]);

            // -----------------------------------------------------------------
            // 7. Persist Sales Order Items
            // -----------------------------------------------------------------
            foreach ($calculatedItems as $calc) {
                /** @var SalesOrderItemDTO $itemDto */
                $itemDto = $calc['dto'];
                $pricing = $calc['pricing'];
                $unit = $calc['unit'];

                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $calc['product']->id,
                    'product_unit_id' => $unit->id,
                    'conversion_factor' => $unit->conversion_factor,
                    'qty_ordered' => $itemDto->qtyOrdered,
                    'qty_in_base_uom' => $calc['qty_in_base_uom'],
                    'qty_delivered_base_uom' => 0,
                    'unit_price' => $pricing->unitPrice,
                    'gross_amount' => $pricing->grossAmount,
                    'discount_percent_1' => $itemDto->discountPercent1,
                    'discount_percent_2' => $itemDto->discountPercent2,
                    'discount_nominal' => $itemDto->discountNominal,
                    'net_amount' => $pricing->netAmount,
                ]);
            }

            return $salesOrder->load(['items.product', 'items.productUnit', 'customer', 'branch']);
        }, 5); // 5 retries for deadlock mitigation
    }

    /**
     * Format: SO/{CHANNEL}/{BRANCH_ID}/{YYYYMMDD}/{0000X}
     */
    protected function generateSoNumber(int $branchId, string $channel): string
    {
        $dateStr = date('Ymd');
        $prefix = sprintf("SO-%s-%d-%s", strtoupper($channel), $branchId, $dateStr);

        $latestOrder = SalesOrder::where('so_number', 'LIKE', "{$prefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latestOrder) {
            $parts = explode('-', $latestOrder->so_number);
            $lastNum = (int) end($parts);
            $nextSeq = $lastNum + 1;
        }

        return sprintf("%s-%04d", $prefix, $nextSeq);
    }
}

