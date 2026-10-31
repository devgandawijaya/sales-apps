<?php

namespace App\Http\Controllers;

use App\Domain\Customer\Models\Customer;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Pricing\Services\PricingEngineService;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\Models\SalesQuotation;
use App\Domain\Sales\Models\SalesQuotationItem;
use App\Domain\Sales\Services\ConvertQuotationToSalesOrderService;
use App\Domain\Security\Services\AuditLoggerService;
use App\Domain\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesQuotationController extends Controller
{
    public function __construct(
        protected PricingEngineService $pricingEngine,
        protected ConvertQuotationToSalesOrderService $convertService
    ) {}

    /**
     * List all Sales Quotations.
     */
    public function index(Request $request)
    {
        $query = SalesQuotation::with(['customer', 'salesman', 'branch']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('quotation_number', 'like', "%{$request->search}%")
                    ->orWhereHas('customer', function ($cq) use ($request) {
                        $cq->where('name', 'like', "%{$request->search}%");
                    });
            });
        }

        $quotations = $query->latest('quotation_date')->paginate(15);

        return view('sales.quotations.index', compact('quotations'));
    }

    /**
     * Show form to create a new quotation.
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $products = Product::with('units')->where('is_active', true)->get();

        return view('sales.quotations.create', compact('customers', 'branches', 'products'));
    }

    /**
     * Store new quotation with price snapshotting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:quotation_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_unit_id' => 'required|exists:product_units,id',
            'items.*.qty_ordered' => 'required|integer|min:1',
            'items.*.custom_unit_price' => 'nullable|numeric|min:0',
            'items.*.discount_percent_1' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_percent_2' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_nominal' => 'nullable|numeric|min:0',
        ]);

        $quotation = DB::transaction(function () use ($validated) {
            $customer = Customer::findOrFail($validated['customer_id']);
            $branch = Branch::findOrFail($validated['branch_id']);
            $salesmanId = auth()->id() ?? 1;

            $quotationNumber = 'QUO-' . date('Ym') . '-' . strtoupper(Str::random(5));

            $subtotal = 0.0;
            $totalDiscount = 0.0;
            $itemsToCreate = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unit = ProductUnit::findOrFail($item['product_unit_id']);

                $pricing = $this->pricingEngine->calculateItemPricing(
                    customer: $customer,
                    product: $product,
                    unit: $unit,
                    qtyOrdered: (int) $item['qty_ordered'],
                    customUnitPrice: !empty($item['custom_unit_price']) ? (float)$item['custom_unit_price'] : null,
                    discountPercent1: (float) ($item['discount_percent_1'] ?? 0),
                    discountPercent2: (float) ($item['discount_percent_2'] ?? 0),
                    discountNominal: (float) ($item['discount_nominal'] ?? 0)
                );

                $qtyInBase = (int) $item['qty_ordered'] * $unit->conversion_factor;
                $subtotal += $pricing->grossAmount;
                $totalDiscount += $pricing->itemDiscountTotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_unit_id' => $unit->id,
                    'conversion_factor' => $unit->conversion_factor,
                    'qty_ordered' => (int) $item['qty_ordered'],
                    'qty_in_base_uom' => $qtyInBase,
                    'unit_price' => $pricing->unitPrice,
                    'gross_amount' => $pricing->grossAmount,
                    'discount_percent_1' => (float) ($item['discount_percent_1'] ?? 0),
                    'discount_percent_2' => (float) ($item['discount_percent_2'] ?? 0),
                    'discount_nominal' => (float) ($item['discount_nominal'] ?? 0),
                    'net_amount' => $pricing->netAmount,
                ];
            }

            $taxableBase = max(0, $subtotal - $totalDiscount);
            $taxAmount = round($taxableBase * 0.11, 2); // PPN 11%
            $grandTotal = $taxableBase + $taxAmount;

            $quotation = SalesQuotation::create([
                'tenant_id' => TenantContext::getTenantId(),
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'salesman_id' => $salesmanId,
                'quotation_number' => $quotationNumber,
                'quotation_date' => $validated['quotation_date'],
                'expiry_date' => $validated['expiry_date'],
                'channel' => $customer->channel,
                'status' => 'SENT',
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsToCreate as $itemData) {
                $quotation->items()->create($itemData);
            }

            AuditLoggerService::log(
                action: 'QUOTATION_CREATED',
                entityType: 'SalesQuotation',
                entityId: (string) $quotation->id,
                newValues: ['quotation_number' => $quotation->quotation_number, 'grand_total' => $grandTotal]
            );

            return $quotation;
        });

        return redirect()->route('quotations.show', $quotation->id)
            ->with('success', "Penawaran harga #{$quotation->quotation_number} berhasil dibuat.");
    }

    /**
     * Show quotation details.
     */
    public function show(int $id)
    {
        $quotation = SalesQuotation::with(['customer', 'salesman', 'branch', 'items.product', 'items.productUnit', 'convertedSalesOrder'])
            ->findOrFail($id);

        $warehouses = Warehouse::where('branch_id', $quotation->branch_id)->where('is_active', true)->get();

        return view('sales.quotations.show', compact('quotation', 'warehouses'));
    }

    /**
     * Convert quotation to Sales Order.
     */
    public function convertToSalesOrder(Request $request, int $id)
    {
        $quotation = SalesQuotation::with('items')->findOrFail($id);

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'po_number_mt' => 'nullable|string|max:100',
        ]);

        try {
            $salesOrder = $this->convertService->execute(
                quotation: $quotation,
                warehouseId: (int) $validated['warehouse_id'],
                poNumberMt: $validated['po_number_mt'] ?? null
            );

            return redirect()->route('sales.show', $salesOrder->id)
                ->with('success', "Penawaran #{$quotation->quotation_number} berhasil diproses menjadi Pesanan #{$salesOrder->so_number}.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', \App\Support\HumanError::translate($e, 'Pemrosesan pesanan dari penawaran belum berhasil'));
        }
    }
}

