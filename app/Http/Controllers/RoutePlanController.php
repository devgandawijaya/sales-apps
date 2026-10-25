<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoutePlanController extends Controller
{
    public function index()
    {
        $routes = DB::table('sales_routes')
            ->join('users', 'sales_routes.salesman_id', '=', 'users.id')
            ->join('branches', 'sales_routes.branch_id', '=', 'branches.id')
            ->select(
                'sales_routes.*',
                'users.name as salesman_name',
                'branches.name as branch_name'
            )
            ->get();

        $visits = DB::table('customer_visits')
            ->join('customers', 'customer_visits.customer_id', '=', 'customers.id')
            ->join('users', 'customer_visits.salesman_id', '=', 'users.id')
            ->select(
                'customer_visits.*',
                'customers.name as customer_name',
                'customers.address as customer_address',
                'users.name as salesman_name'
            )
            ->orderBy('customer_visits.check_in_at', 'desc')
            ->limit(10)
            ->get();

        return view('routes.index', compact('routes', 'visits'));
    }
}
