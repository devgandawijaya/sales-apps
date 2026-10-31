<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryAnalyticsService
{
    /**
     * Get batches approaching expiration within a given day threshold.
     */
    public function getNearExpiryStock(int $daysThreshold = 90, ?int $warehouseId = null): Collection
    {
        $tenantId = TenantContext::getTenantId();
        $cutoffDate = Carbon::today()->addDays($daysThreshold)->toDateString();

        $query = Stock::with(['product', 'warehouse'])
            ->where('qty_on_hand', '>', 0)
            ->where('expiry_date', '<=', $cutoffDate)
            ->orderBy('expiry_date', 'asc');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $today = Carbon::today();

        return $query->get()->map(function (Stock $stock) use ($today) {
            $expiry = Carbon::parse($stock->expiry_date);
            $daysLeft = $today->diffInDays($expiry, false);

            if ($daysLeft <= 0) {
                $severity = 'EXPIRED';
                $badgeClass = 'bg-red-600 text-white';
            } elseif ($daysLeft <= 30) {
                $severity = 'CRITICAL';
                $badgeClass = 'bg-red-500 text-white';
            } elseif ($daysLeft <= 60) {
                $severity = 'WARNING';
                $badgeClass = 'bg-amber-500 text-white';
            } else {
                $severity = 'NOTICE';
                $badgeClass = 'bg-blue-500 text-white';
            }

            $costPrice = (float) ($stock->product->cost_price ?? 0);
            $stockValue = round($stock->qty_on_hand * $costPrice, 2);

            return [
                'stock_id' => $stock->id,
                'warehouse_name' => $stock->warehouse->name ?? "Gudang #{$stock->warehouse_id}",
                'product_name' => $stock->product->name ?? "Produk #{$stock->product_id}",
                'product_sku' => $stock->product->sku ?? '-',
                'batch_number' => $stock->batch_number,
                'expiry_date' => $expiry->format('d M Y'),
                'days_left' => $daysLeft,
                'severity' => $severity,
                'badge_class' => $badgeClass,
                'qty_on_hand' => $stock->qty_on_hand,
                'qty_reserved' => $stock->qty_reserved,
                'qty_available' => $stock->qty_available,
                'stock_value' => $stockValue,
            ];
        });
    }

    /**
     * Get total valuation and quantity breakdown across warehouses.
     */
    public function getInventoryValuationSummary(?int $warehouseId = null): array
    {
        $tenantId = TenantContext::getTenantId();

        $query = DB::table('stocks')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->select(
                DB::raw('SUM(stocks.qty_on_hand) as total_qty_on_hand'),
                DB::raw('SUM(stocks.qty_reserved) as total_qty_reserved'),
                DB::raw('SUM(stocks.qty_on_hand * products.cost_price) as total_valuation')
            );

        if ($warehouseId) {
            $query->where('stocks.warehouse_id', $warehouseId);
        }

        if ($tenantId) {
            $query->where('stocks.tenant_id', $tenantId);
        }

        $row = $query->first();

        return [
            'total_qty_on_hand' => (int) ($row?->total_qty_on_hand ?? 0),
            'total_qty_reserved' => (int) ($row?->total_qty_reserved ?? 0),
            'total_qty_available' => max(0, (int) (($row?->total_qty_on_hand ?? 0) - ($row?->total_qty_reserved ?? 0))),
            'total_valuation' => (float) ($row?->total_valuation ?? 0.0),
        ];
    }
}

