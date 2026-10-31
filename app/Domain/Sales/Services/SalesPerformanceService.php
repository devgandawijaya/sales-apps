<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Sales\Models\SalesQuotation;
use App\Domain\Sales\Models\SalesTarget;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesPerformanceService
{
    /**
     * Get Sales Performance Summary for a given month and year.
     */
    public function getMonthlyPerformance(?string $periodDate = null): array
    {
        $tenantId = TenantContext::getTenantId();
        $date = $periodDate ? Carbon::parse($periodDate) : Carbon::now();
        $startOfMonth = $date->copy()->startOfMonth()->startOfDay()->toDateTimeString();
        $endOfMonth = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();
        $targetPeriodDate = $date->copy()->startOfMonth()->toDateString();

        // 1. Total Actual Omzet this month
        $soQuery = DB::table('sales_orders')
            ->whereBetween('order_date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['APPROVED', 'PROCESSING', 'PARTIAL_DELIVERED', 'DELIVERED', 'COMPLETED']);

        if ($tenantId) {
            $soQuery->where('tenant_id', $tenantId);
        }

        $actualOmzet = (float) $soQuery->sum('grand_total');
        $totalOrders = (int) $soQuery->count();

        // 2. Total Target Omzet this month
        $targetQuery = DB::table('sales_targets')
            ->whereDate('period_date', $targetPeriodDate);

        if ($tenantId) {
            $targetQuery->where('tenant_id', $tenantId);
        }

        $targetOmzet = (float) $targetQuery->sum('target_omset');
        $achievementPercent = $targetOmzet > 0 ? round(($actualOmzet / $targetOmzet) * 100, 2) : 0.0;

        // 3. Sales Leaderboard per Salesman
        $leaderboardQuery = DB::table('sales_orders')
            ->join('users', 'sales_orders.salesman_id', '=', 'users.id')
            ->whereBetween('sales_orders.order_date', [$startOfMonth, $endOfMonth])
            ->whereIn('sales_orders.status', ['APPROVED', 'PROCESSING', 'PARTIAL_DELIVERED', 'DELIVERED', 'COMPLETED'])
            ->select(
                'users.id as salesman_id',
                'users.name as salesman_name',
                DB::raw('COUNT(sales_orders.id) as total_orders'),
                DB::raw('SUM(sales_orders.grand_total) as total_omzet')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_omzet')
            ->take(10);

        if ($tenantId) {
            $leaderboardQuery->where('sales_orders.tenant_id', $tenantId);
        }

        $leaderboard = $leaderboardQuery->get()->map(function ($row) use ($targetPeriodDate, $tenantId) {
            $userTarget = SalesTarget::where('user_id', $row->salesman_id)
                ->whereDate('period_date', $targetPeriodDate)
                ->first();

            $targetVal = $userTarget ? (float)$userTarget->target_omset : 0.0;
            $achieve = $targetVal > 0 ? round(($row->total_omzet / $targetVal) * 100, 1) : 0.0;

            return [
                'salesman_id' => $row->salesman_id,
                'salesman_name' => $row->salesman_name,
                'total_orders' => (int)$row->total_orders,
                'total_omzet' => (float)$row->total_omzet,
                'target_omzet' => $targetVal,
                'achievement_percent' => $achieve,
            ];
        });

        // 4. Pipeline Conversion Metrics (Quotations)
        $quoteQuery = SalesQuotation::whereBetween('quotation_date', [$startOfMonth, $endOfMonth]);
        $totalQuotes = $quoteQuery->count();
        $convertedQuotes = (clone $quoteQuery)->where('status', 'CONVERTED_TO_SO')->count();
        $conversionRate = $totalQuotes > 0 ? round(($convertedQuotes / $totalQuotes) * 100, 1) : 0.0;

        return [
            'period' => $date->format('F Y'),
            'actual_omzet' => $actualOmzet,
            'target_omzet' => $targetOmzet,
            'achievement_percent' => $achievementPercent,
            'total_orders' => $totalOrders,
            'quotation_stats' => [
                'total' => $totalQuotes,
                'converted' => $convertedQuotes,
                'conversion_rate' => $conversionRate,
            ],
            'leaderboard' => $leaderboard,
        ];
    }
}

