<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\RoutePlanController;

// =============================================================================
// AUTHENTICATION ROUTES (LOGIN / LOGOUT / QUICK DEMO LOGIN)
// =============================================================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/login/quick/{id}', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// =============================================================================
// WEB APPLICATION ROUTES (DASHBOARD, SALES, CUSTOMERS, INVENTORY, FINANCE, ROUTES)
// =============================================================================
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Sales Orders (Pesanan Penjualan)
Route::get('/sales-orders', [SalesOrderController::class, 'index'])->name('sales.index');
Route::get('/sales-orders/create', [SalesOrderController::class, 'create'])->name('sales.create');
Route::post('/sales-orders', [SalesOrderController::class, 'store'])->name('sales.store');
Route::get('/sales-orders/{id}', [SalesOrderController::class, 'show'])->name('sales.show');

// Pelanggan & Plafon Kredit
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

// Gudang & Stok FEFO
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

// Keuangan, Faktur & AR Aging
Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');

// Rute Salesman GT & Kunjungan Toko
Route::get('/routes', [RoutePlanController::class, 'index'])->name('routes.index');

// =============================================================================
// REST API ENDPOINTS (DATA & INTEGRATION)
// =============================================================================
Route::get('/api/dashboard', function () {
    $totalSo = DB::table('sales_orders')->count();
    $totalOmset = (float) DB::table('sales_orders')->sum('grand_total');
    $omsetGt = (float) DB::table('sales_orders')->where('channel', 'GT')->sum('grand_total');
    $omsetMt = (float) DB::table('sales_orders')->where('channel', 'MT')->sum('grand_total');

    // AR Aging Summary
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

    return response()->json([
        'kpi_summary' => [
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
        ],
        'ar_aging_schedule' => [
            'current_0_to_30_days' => $agingCurrent,
            'overdue_31_to_60_days' => $aging31to60,
            'overdue_61_to_90_days' => $aging61to90,
            'overdue_over_90_days' => $agingOver90,
            'total_outstanding_ar' => $agingCurrent + $aging31to60 + $aging61to90 + $agingOver90,
        ],
        'server_time' => now()->toDateTimeString(),
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

Route::get('/api/sales-orders', function () {
    $orders = DB::table('sales_orders')
        ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
        ->select(
            'sales_orders.id',
            'sales_orders.so_number',
            'sales_orders.order_date',
            'sales_orders.channel',
            'sales_orders.status',
            'customers.name as customer_name',
            'sales_orders.subtotal',
            'sales_orders.discount_on_invoice',
            'sales_orders.tax_amount',
            'sales_orders.grand_total'
        )
        ->orderBy('sales_orders.id', 'desc')
        ->get();

    return response()->json($orders, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

Route::get('/api/customers', function () {
    $customers = DB::table('customers')
        ->select('id', 'code', 'name', 'channel', 'sub_channel', 'credit_limit', 'current_credit_used', 'term_of_payment', 'is_active')
        ->get();

    return response()->json($customers, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

Route::get('/api/stocks', function () {
    $stocks = DB::table('stocks')
        ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
        ->join('products', 'stocks.product_id', '=', 'products.id')
        ->select(
            'warehouses.name as warehouse_name',
            'products.sku',
            'products.name as product_name',
            'stocks.batch_number',
            'stocks.expiry_date',
            'stocks.qty_on_hand',
            'stocks.qty_reserved'
        )
        ->orderBy('stocks.expiry_date', 'asc')
        ->get();

    return response()->json($stocks, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});
