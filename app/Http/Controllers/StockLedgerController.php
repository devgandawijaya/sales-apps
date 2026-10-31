<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\Models\StockLedgerEntry;
use App\Domain\Inventory\Services\InventoryAnalyticsService;
use App\Domain\Organization\Models\Warehouse;
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    public function __construct(
        protected InventoryAnalyticsService $analyticsService
    ) {}

    public function ledger(Request $request)
    {
        $query = StockLedgerEntry::with(['warehouse', 'product', 'creator'])
            ->orderByDesc('id');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('entry_type')) {
            $query->where('entry_type', $request->entry_type);
        }

        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('batch_number', 'like', "%{$request->search}%")
                  ->orWhere('reference_number', 'like', "%{$request->search}%");
            });
        }

        $entries = $query->paginate(20);
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('inventory.ledger.index', compact('entries', 'warehouses'));
    }

    public function expiryAlerts(Request $request)
    {
        $warehouseId = $request->filled('warehouse_id') ? (int) $request->warehouse_id : null;
        $threshold = $request->filled('threshold') ? (int) $request->threshold : 90;

        $alerts = $this->analyticsService->getNearExpiryStock($threshold, $warehouseId);
        $valuation = $this->analyticsService->getInventoryValuationSummary($warehouseId);
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('inventory.alerts.index', compact('alerts', 'valuation', 'warehouses', 'threshold', 'warehouseId'));
    }

    public function exportLedger(Request $request)
    {
        $query = StockLedgerEntry::with(['warehouse', 'product', 'creator'])
            ->orderByDesc('id');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('entry_type')) {
            $query->where('entry_type', $request->entry_type);
        }

        $entries = $query->get();

        $filename = 'Kartu_Stok_Ledger_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Waktu Transaksi',
                'Gudang',
                'SKU Produk',
                'Nama Produk',
                'No. Batch',
                'Tipe Mutasi',
                'Tipe Referensi',
                'No. Referensi',
                'Perubahan Qty',
                'Stok Awal',
                'Stok Akhir',
                'Biaya Satuan (IDR)',
                'Total Nilai (IDR)',
                'Catatan'
            ]);

            foreach ($entries as $e) {
                fputcsv($handle, [
                    $e->created_at?->toDateTimeString(),
                    $e->warehouse?->name ?? '-',
                    $e->product?->sku ?? '-',
                    $e->product?->name ?? '-',
                    $e->batch_number,
                    $e->entry_type,
                    $e->reference_type,
                    $e->reference_number ?? '-',
                    $e->qty_change,
                    $e->qty_before ?? 0,
                    $e->qty_after ?? 0,
                    $e->unit_cost,
                    $e->total_cost,
                    $e->notes ?? '-'
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

