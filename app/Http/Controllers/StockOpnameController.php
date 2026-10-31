<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\Models\StockOpname;
use App\Domain\Inventory\Services\StockOpnameService;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockOpnameController extends Controller
{
    public function __construct(
        protected StockOpnameService $opnameService
    ) {}

    public function index(Request $request)
    {
        $query = StockOpname::with(['branch', 'warehouse', 'creator', 'approver'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $opnames = $query->paginate(15);
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('inventory.opname.index', compact('opnames', 'warehouses'));
    }

    public function create()
    {
        $branches = Branch::all();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('inventory.opname.create', compact('branches', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'title' => 'required|string|max:150',
            'count_type' => 'required|in:CYCLE_COUNT,WALL_TO_WALL,SPOT_CHECK',
            'blind_count' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id() ?? 1;

        $opname = $this->opnameService->startOpname(
            branchId: (int) $validated['branch_id'],
            warehouseId: (int) $validated['warehouse_id'],
            title: $validated['title'],
            userId: $userId,
            countType: $validated['count_type'],
            blindCount: (bool) ($validated['blind_count'] ?? false),
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('opname.show', $opname->id)
            ->with('success', "Sesi Stock Opname #{$opname->opname_number} berhasil dimulai. Stok sistem telah dibekukan (snapshot).");
    }

    public function show(int $id)
    {
        $opname = StockOpname::with(['branch', 'warehouse', 'items.product', 'creator', 'approver'])
            ->findOrFail($id);

        return view('inventory.opname.show', compact('opname'));
    }

    public function recordCount(Request $request, int $id)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:stock_opname_items,id',
            'items.*.physical_qty' => 'required|integer|min:0',
            'items.*.reason_code' => 'nullable|string|max:50',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        $opname = $this->opnameService->recordCount($id, $validated['items']);

        return redirect()->route('opname.show', $opname->id)
            ->with('success', "Hasil perhitungan fisik berhasil disimpan. Menunggu persetujuan penyesuaian (SUBMITTED).");
    }

    public function postAdjustment(int $id)
    {
        $userId = Auth::id() ?? 1;
        $opname = $this->opnameService->approveAndPost($id, $userId);

        return redirect()->route('opname.show', $opname->id)
            ->with('success', "Stock Opname #{$opname->opname_number} telah disetujui & penyesuaian stok berhasil diposting ke buku besar.");
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $userId = Auth::id() ?? 1;
        $opname = $this->opnameService->rejectOpname($id, $userId, $validated['reason']);

        return redirect()->route('opname.show', $opname->id)
            ->with('warning', "Stock Opname #{$opname->opname_number} telah ditolak.");
    }
}

