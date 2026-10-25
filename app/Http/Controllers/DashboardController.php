<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSo = DB::table('sales_orders')->count();
        $totalOmset = (float) DB::table('sales_orders')->sum('grand_total');
        $omsetGt = (float) DB::table('sales_orders')->where('channel', 'GT')->sum('grand_total');
        $omsetMt = (float) DB::table('sales_orders')->where('channel', 'MT')->sum('grand_total');

        $kpi = [
            'total_sales_orders' => $totalSo,
            'total_omset' => $totalOmset,
            'channel_breakdown' => [
                'general_trade' => [
                    'omset' => $omsetGt,
                    'share_percent' => $totalOmset > 0 ? round(($omsetGt / $totalOmset) * 100, 2) : 0,
                ],
                'modern_trade' => [
                    'omset' => $omsetMt,
                    'share_percent' => $totalOmset > 0 ? round(($omsetMt / $totalOmset) * 100, 2) : 0,
                ],
            ],
        ];

        // AR Aging
        $invoices = DB::table('invoices')->whereIn('status', ['UNPAID', 'OVERDUE', 'PARTIAL'])->get();
        $agingCurrent = 0;
        $aging31to60 = 0;
        $aging61to90 = 0;
        $agingOver90 = 0;
        $today = now();
        foreach ($invoices as $inv) {
            $dueDate = \Carbon\Carbon::parse($inv->due_date);
            $diffDays = $today->diffInDays($dueDate, false);
            $balance = (float) $inv->balance_due;

            if ($diffDays >= 0 || abs($diffDays) <= 30) {
                $agingCurrent += $balance;
            } elseif (abs($diffDays) <= 60) {
                $aging31to60 += $balance;
            } elseif (abs($diffDays) <= 90) {
                $aging61to90 += $balance;
            } else {
                $agingOver90 += $balance;
            }
        }

        $aging = [
            'current_0_to_30_days' => $agingCurrent,
            'overdue_31_to_60_days' => $aging31to60,
            'overdue_61_to_90_days' => $aging61to90,
            'overdue_over_90_days' => $agingOver90,
            'total_outstanding_ar' => $agingCurrent + $aging31to60 + $aging61to90 + $agingOver90,
        ];

        $recentOrders = DB::table('sales_orders')
            ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
            ->select(
                'sales_orders.*',
                'customers.name as customer_name'
            )
            ->orderBy('sales_orders.id', 'desc')
            ->limit(5)
            ->get();

        $warehouses = DB::table('warehouses')->count();
        $customersCount = DB::table('customers')->count();
        $productsCount = DB::table('products')->count();

        return view('dashboard.index', compact('kpi', 'aging', 'recentOrders', 'warehouses', 'customersCount', 'productsCount'));
    }
}

