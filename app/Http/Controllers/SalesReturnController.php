<?php

namespace App\Http\Controllers;

use App\Domain\Customer\Models\Customer;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\SalesReturn;
use App\Domain\Sales\Services\ProcessSalesReturnService;
use Illuminate\Http\Request;
use Throwable;

class SalesReturnController extends Controller
{
    public function __construct(
        protected ProcessSalesReturnService $salesReturnService
    ) {}

    public function index(Request $request)
    {
        $query = SalesReturn::with(['customer', 'branch', 'warehouse', 'approver'])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('return_type')) {
            $query->where('return_type', $request->return_type);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', $search)
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', $search);
                  });
            });
        }

        $returns = $query->paginate(15)->withQueryString();

        return view('returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $branches = Branch::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->with('units')->get();
        $invoices = Invoice::orderBy('id', 'desc')->take(50)->get();

        return view('returns.create', compact('branches', 'warehouses', 'customers', 'products', 'invoices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_type' => 'required|in:GOOD_STOCK,BAD_STOCK_EXPIRED,BAD_STOCK_DAMAGED',
            'invoice_id' => 'nullable|exists:invoices,id',
            'product_id' => 'required|exists:products,id',
            'product_unit_id' => 'required|exists:product_units,id',
            'batch_number' => 'required|string|max:50',
            'qty' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $items = [
                [
                    'product_id' => (int) $validated['product_id'],
                    'product_unit_id' => (int) $validated['product_unit_id'],
                    'batch_number' => $validated['batch_number'],
                    'qty' => (int) $validated['qty'],
                    'unit_price' => (float) $validated['unit_price'],
                    'reason' => $validated['reason'] ?? 'Retur barang pelanggan',
                ]
            ];

            $salesReturn = $this->salesReturnService->createReturn(
                branchId: (int) $validated['branch_id'],
                customerId: (int) $validated['customer_id'],
                warehouseId: (int) $validated['warehouse_id'],
                returnType: $validated['return_type'],
                items: $items,
                invoiceId: $validated['invoice_id'] ? (int) $validated['invoice_id'] : null,
                notes: $validated['notes'] ?? null,
                userId: auth()->id()
            );

            return redirect()->route('returns.show', $salesReturn->id)
                ->with('success', "Permintaan retur {$salesReturn->return_number} berhasil dibuat dan menunggu persetujuan (approval).");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Gagal membuat retur penjualan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $salesReturn = SalesReturn::with([
            'customer',
            'branch',
            'warehouse',
            'invoice',
            'approver',
            'items.product',
            'items.productUnit'
        ])->findOrFail($id);

        return view('returns.show', compact('salesReturn'));
    }

    public function approve($id)
    {
        try {
            $salesReturn = $this->salesReturnService->approveReturn(
                returnId: (int) $id,
                approverId: auth()->id() ?? 1
            );

            return redirect()->route('returns.show', $salesReturn->id)
                ->with('success', "Retur {$salesReturn->return_number} telah disetujui. Stok telah masuk ke gudang tujuan dan kredit pelanggan telah disesuaikan.");
        } catch (Throwable $e) {
            return back()->with('error', 'Gagal menyetujui retur: ' . $e->getMessage());
        }
    }
}

