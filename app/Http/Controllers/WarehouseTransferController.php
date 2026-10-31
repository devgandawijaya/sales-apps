<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\Models\StockTransfer;
use App\Domain\Inventory\Services\InterBranchTransferService;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseTransferController extends Controller
{
    public function __construct(
        protected InterBranchTransferService $transferService
    ) {}

    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'creator', 'approver', 'receiver'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('transfer_number', 'like', "%{$request->search}%");
        }

        $transfers = $query->paginate(15);

        return view('inventory.transfers.index', compact('transfers'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->with('units')->get();

        return view('inventory.transfers.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'transit_warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_unit_id' => 'required|exists:product_units,id',
            'items.*.batch_number' => 'required|string|max:50',
            'items.*.expiry_date' => 'required|date',
            'items.*.qty_sent' => 'required|integer|min:1',
        ]);

        $userId = Auth::id() ?? 1;

        $transfer = $this->transferService->createTransfer(
            fromWarehouseId: (int) $validated['from_warehouse_id'],
            toWarehouseId: (int) $validated['to_warehouse_id'],
            items: $validated['items'],
            userId: $userId,
            notes: $validated['notes'] ?? null,
            transitWarehouseId: !empty($validated['transit_warehouse_id']) ? (int) $validated['transit_warehouse_id'] : null
        );

        return redirect()->route('transfers.show', $transfer->id)
            ->with('success', "Transfer stok #{$transfer->transfer_number} berhasil dibuat (Status: DRAFT).");
    }

    public function show(int $id)
    {
        $transfer = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'transitWarehouse', 'items.product', 'items.productUnit', 'creator', 'approver', 'receiver'])
            ->findOrFail($id);

        return view('inventory.transfers.show', compact('transfer'));
    }

    public function submit(int $id)
    {
        $transfer = $this->transferService->submitTransfer($id);

        return redirect()->route('transfers.show', $transfer->id)
            ->with('success', "Transfer #{$transfer->transfer_number} berhasil diajukan untuk persetujuan.");
    }

    public function approve(int $id)
    {
        $userId = Auth::id() ?? 1;
        $transfer = $this->transferService->approveTransfer($id, $userId);

        return redirect()->route('transfers.show', $transfer->id)
            ->with('success', "Transfer #{$transfer->transfer_number} telah disetujui. Siap untuk proses pengiriman.");
    }

    public function dispatch(Request $request, int $id)
    {
        $validated = $request->validate([
            'driver_name' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:30',
        ]);

        $transfer = $this->transferService->dispatchTransfer(
            transferId: $id,
            driverName: $validated['driver_name'] ?? null,
            vehicleNumber: $validated['vehicle_number'] ?? null
        );

        return redirect()->route('transfers.show', $transfer->id)
            ->with('success', "Transfer #{$transfer->transfer_number} berhasil diberangkatkan. Status sekarang: IN_TRANSIT.");
    }

    public function receive(Request $request, int $id)
    {
        $validated = $request->validate([
            'received_notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:stock_transfer_items,id',
            'items.*.qty_received' => 'required|integer|min:0',
            'items.*.condition_status' => 'required|in:GOOD,DAMAGED',
        ]);

        $userId = Auth::id() ?? 1;

        $transfer = $this->transferService->receiveTransfer(
            transferId: $id,
            receiverId: $userId,
            receivedItems: $validated['items'],
            receivedNotes: $validated['received_notes'] ?? null
        );

        return redirect()->route('transfers.show', $transfer->id)
            ->with('success', "Transfer #{$transfer->transfer_number} berhasil diterima di gudang tujuan.");
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $userId = Auth::id() ?? 1;

        $transfer = $this->transferService->rejectTransfer(
            transferId: $id,
            approverId: $userId,
            reason: $validated['reason']
        );

        return redirect()->route('transfers.show', $transfer->id)
            ->with('warning', "Transfer #{$transfer->transfer_number} telah ditolak.");
    }
}

