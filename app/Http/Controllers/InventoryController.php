<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('stocks')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->select(
                'stocks.*',
                'warehouses.name as warehouse_name',
                'warehouses.code as warehouse_code',
                'warehouses.type as warehouse_type',
                'products.sku as product_sku',
                'products.name as product_name',
                'products.base_uom'
            );

        if ($tenantId = \App\Domain\Tenant\TenantContext::getTenantId()) {
            $query->where('stocks.tenant_id', $tenantId);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('stocks.warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(products.name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(products.sku) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(stocks.batch_number) LIKE ?', [$search]);
            });
        }

        $stocks = $query->orderBy('stocks.expiry_date', 'asc')->paginate(10)->withQueryString();

        $warehouses = DB::table('warehouses')->orderBy('id')->get();

        $transfers = DB::table('stock_transfers')
            ->join('warehouses as from_wh', 'stock_transfers.from_warehouse_id', '=', 'from_wh.id')
            ->join('warehouses as to_wh', 'stock_transfers.to_warehouse_id', '=', 'to_wh.id')
            ->select(
                'stock_transfers.*',
                'from_wh.name as from_warehouse_name',
                'to_wh.name as to_warehouse_name'
            )
            ->orderBy('stock_transfers.id', 'desc')
            ->limit(5)
            ->get();

        return view('inventory.index', compact('stocks', 'warehouses', 'transfers'));
    }
}

