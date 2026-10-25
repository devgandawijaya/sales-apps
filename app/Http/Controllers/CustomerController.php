<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('customers')
            ->join('branches', 'customers.branch_id', '=', 'branches.id')
            ->select(
                'customers.*',
                'branches.name as branch_name'
            );

        if ($request->filled('channel') && in_array($request->channel, ['GT', 'MT'])) {
            $query->where('customers.channel', $request->channel);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'ilike', $search)
                  ->orWhere('customers.code', 'ilike', $search)
                  ->orWhere('customers.city', 'ilike', $search);
            });
        }

        $customers = $query->orderBy('customers.id', 'asc')->paginate(10)->withQueryString();

        $stats = [
            'total' => DB::table('customers')->count(),
            'gt_count' => DB::table('customers')->where('channel', 'GT')->count(),
            'mt_count' => DB::table('customers')->where('channel', 'MT')->count(),
            'total_credit_limit' => (float) DB::table('customers')->sum('credit_limit'),
            'total_credit_used' => (float) DB::table('customers')->sum('current_credit_used'),
        ];

        return view('customers.index', compact('customers', 'stats'));
    }
}

