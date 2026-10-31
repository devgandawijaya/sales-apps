<?php

namespace App\Http\Controllers;

use App\Domain\Sales\Models\DeliveryOrder;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Services\CreateDeliveryOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeliveryOrderController extends Controller
{
    public function __construct(
        protected CreateDeliveryOrderService $createDeliveryOrderService
    ) {}

    public function index(Request $request)
    {
        $query = DeliveryOrder::with(['salesOrder.customer', 'branch', 'warehouse'])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(do_number) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(driver_name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(vehicle_plate_number) LIKE ?', [$search])
                  ->orWhereHas('salesOrder', function ($sq) use ($search) {
                      $sq->whereRaw('LOWER(so_number) LIKE ?', [$search])
                        ->orWhereHas('customer', function ($cq) use ($search) {
                            $cq->whereRaw('LOWER(name) LIKE ?', [$search]);
                        });
                  });
            });
        }

        $deliveryOrders = $query->paginate(15)->withQueryString();

        return view('delivery_orders.index', compact('deliveryOrders'));
    }

    public function create(Request $request)
    {
        // Get approved sales orders that are not yet delivered
        $approvedOrders = SalesOrder::with(['customer', 'branch', 'warehouse', 'items.product'])
            ->whereIn('status', ['APPROVED', 'PROCESSING'])
            ->orderBy('id', 'desc')
            ->get();

        $selectedOrderId = $request->query('sales_order_id');
        $selectedOrder = $selectedOrderId ? $approvedOrders->firstWhere('id', $selectedOrderId) : null;

        return view('delivery_orders.create', compact('approvedOrders', 'selectedOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'driver_name' => 'required|string|max:100',
            'vehicle_plate_number' => 'required|string|max:30',
            'delivery_type' => 'required|in:DIRECT_STORE,CENTRAL_DC,CANVASS_DIRECT',
            'notes' => 'nullable|string',
        ]);

        try {
            $do = $this->createDeliveryOrderService->execute(
                salesOrderId: (int) $validated['sales_order_id'],
                driverName: $validated['driver_name'],
                vehiclePlateNumber: $validated['vehicle_plate_number'],
                deliveryType: $validated['delivery_type'],
                notes: $validated['notes'],
                userId: auth()->id()
            );

            return redirect()->route('delivery-orders.show', $do->id)
                ->with('success', "Surat Jalan {$do->do_number} berhasil dibuat dan barang siap dikirim.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', \App\Support\HumanError::translate($e, 'Gagal membuat Surat Jalan'));
        }
    }

    public function show($id)
    {
        $deliveryOrder = DeliveryOrder::with([
            'salesOrder.customer',
            'salesOrder.items.product',
            'branch',
            'warehouse',
            'items.product',
            'invoice'
        ])->findOrFail($id);

        return view('delivery_orders.show', compact('deliveryOrder'));
    }

    public function dispatchOrder($id)
    {
        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $deliveryOrder->update(['status' => 'DISPATCHED']);

        return back()->with('success', "Surat Jalan {$deliveryOrder->do_number} berhasil diberangkatkan.");
    }

    public function deliverOrder(Request $request, $id)
    {
        $validated = $request->validate([
            'recipient_name' => 'required|string|max:100',
        ]);

        $deliveryOrder = DeliveryOrder::findOrFail($id);
        $deliveryOrder->update([
            'status' => 'DELIVERED',
            'recipient_name' => $validated['recipient_name'],
            'delivered_at' => now(),
        ]);

        // Mark items received
        foreach ($deliveryOrder->items as $item) {
            $item->update(['qty_received' => $item->qty_shipped]);
        }

        return back()->with('success', "Surat Jalan {$deliveryOrder->do_number} telah diterima oleh {$validated['recipient_name']}.");
    }

    public function printOrder($id)
    {
        $deliveryOrder = DeliveryOrder::with([
            'salesOrder.customer',
            'branch',
            'warehouse',
            'items.product'
        ])->findOrFail($id);

        return view('delivery_orders.print', compact('deliveryOrder'));
    }
}

