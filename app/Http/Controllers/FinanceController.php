<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->select(
                'invoices.*',
                'customers.name as customer_name',
                'customers.channel as customer_channel',
                'customers.term_of_payment'
            )
            ->orderBy('invoices.id', 'desc')
            ->paginate(10);

        // Calculate AR Aging
        $allPending = DB::table('invoices')->whereIn('status', ['UNPAID', 'OVERDUE', 'PARTIAL'])->get();
        $agingCurrent = 0;
        $aging31to60 = 0;
        $aging61to90 = 0;
        $agingOver90 = 0;
        $today = now();

        foreach ($allPending as $inv) {
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

        $payments = DB::table('payments')
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->select(
                'payments.*',
                'customers.name as customer_name'
            )
            ->orderBy('payments.id', 'desc')
            ->limit(5)
            ->get();

        return view('finance.index', compact('invoices', 'aging', 'payments'));
    }
}

