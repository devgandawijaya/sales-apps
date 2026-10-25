<?php

namespace App\Http\Controllers;

use App\Domain\Customer\Models\Customer;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use App\Domain\Sales\DTO\CreateSalesOrderDTO;
use App\Domain\Sales\DTO\SalesOrderItemDTO;
use App\Domain\Sales\Exceptions\CreditLimitExceededException;
use App\Domain\Sales\Exceptions\CustomerInactiveException;
use App\Domain\Sales\Exceptions\InsufficientStockException;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Services\CreateSalesOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SalesOrderController extends Controller
{
    public function __construct(
        protected CreateSalesOrderService $createSalesOrderService
    ) {}

    public function index(Request $request)
    {
        $query = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->join('branches', 'sales_orders.branch_id', '=', 'branches.id')
            ->select(
                'sales_orders.*',
                'customers.name as customer_name',
                'branches.name as branch_name'
            );

        if ($request->filled('channel') && in_array($request->channel, ['GT', 'MT'])) {
            $query->where('sales_orders.channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('sales_orders.status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('sales_orders.so_number', 'ilike', $search)
                  ->orWhere('customers.name', 'ilike', $search);
            });
        }

        $orders = $query->orderBy('sales_orders.id', 'desc')->paginate(15)->withQueryString();

        return view('sales.index', compact('orders'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->with('units')->get();

        return view('sales.create', compact('branches', 'warehouses', 'customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_type' => 'required|string',
            'po_number_mt' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'product_unit_id' => 'required|exists:product_units,id',
            'qty_ordered' => 'required|numeric|min:1',
            'discount_percent1' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        if ($customer->isModernTrade() && empty($validated['po_number_mt'])) {
            return back()->withInput()->with('error', 'Saluran Modern Trade (MT) wajib mencantumkan Nomor PO Resmi dari pelanggan.');
        }

        try {
            $itemDto = new SalesOrderItemDTO(
                productId: (int) $validated['product_id'],
                productUnitId: (int) $validated['product_unit_id'],
                qtyOrdered: (float) $validated['qty_ordered'],
                discountPercent1: (float) ($validated['discount_percent1'] ?? 0)
            );

            $dto = new CreateSalesOrderDTO(
                branchId: (int) $validated['branch_id'],
                customerId: (int) $validated['customer_id'],
                salesmanId: auth()->id() ?? 1,
                warehouseId: (int) $validated['warehouse_id'],
                orderDate: now()->toDateString(),
                orderType: $validated['order_type'],
                poNumberMt: $validated['po_number_mt'] ?? null,
                items: [$itemDto],
                notes: $validated['notes'] ?? 'Dibuat melalui Web Sales Portal'
            );

            $salesOrder = $this->createSalesOrderService->execute($dto);

            return redirect()->route('sales.show', $salesOrder->id)
                ->with('success', 'Sales Order ' . $salesOrder->so_number . ' berhasil dibuat dengan Grand Total Rp ' . number_format($salesOrder->grand_total, 0, ',', '.') . '!');
        } catch (CreditLimitExceededException $e) {
            return back()->withInput()->with('error', 'Transaksi Ditolak: ' . $e->getMessage());
        } catch (InsufficientStockException $e) {
            return back()->withInput()->with('error', 'Stok Tidak Cukup: ' . $e->getMessage());
        } catch (CustomerInactiveException $e) {
            return back()->withInput()->with('error', 'Pelanggan Tidak Aktif: ' . $e->getMessage());
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $order = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->join('branches', 'sales_orders.branch_id', '=', 'branches.id')
            ->join('warehouses', 'sales_orders.warehouse_id', '=', 'warehouses.id')
            ->where('sales_orders.id', $id)
            ->select(
                'sales_orders.*',
                'customers.name as customer_name',
                'customers.code as customer_code',
                'customers.channel as customer_channel',
                'customers.credit_limit',
                'customers.current_credit_used',
                'customers.term_of_payment',
                'branches.name as branch_name',
                'warehouses.name as warehouse_name'
            )
            ->first();

        if (!$order) {
            abort(404, 'Sales Order tidak ditemukan.');
        }

        $items = DB::table('sales_order_items')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->join('product_units', 'sales_order_items.product_unit_id', '=', 'product_units.id')
            ->where('sales_order_items.sales_order_id', $id)
            ->select(
                'sales_order_items.*',
                'products.sku as product_sku',
                'products.name as product_name',
                'product_units.unit_name'
            )
            ->get();

        return view('sales.show', compact('order', 'items'));
    }
}

