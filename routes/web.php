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
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SalesQuotationController;
use App\Http\Controllers\SalesOrderApprovalController;
use App\Http\Controllers\SalesTargetController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\SalesReturnController;
use App\Domain\Tenant\TenantContext;

// =============================================================================
// AUTHENTICATION & ONBOARDING ROUTES
// =============================================================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/login/quick/{id}', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// SaaS Self-Onboarding
Route::get('/register-tenant', [TenantController::class, 'showRegistrationForm'])->name('tenant.register');
Route::post('/register-tenant', [TenantController::class, 'register'])->name('tenant.register.post');

// =============================================================================
// WEB APPLICATION CORE ROUTES
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
Route::get('/sales-orders-export', [SalesOrderController::class, 'exportOrders'])->name('sales.export');
Route::get('/sales-orders/{id}', [SalesOrderController::class, 'show'])->name('sales.show');

// Phase 4: Delivery Orders / Surat Jalan
Route::get('/delivery-orders', [DeliveryOrderController::class, 'index'])->name('delivery-orders.index');
Route::get('/delivery-orders/create', [DeliveryOrderController::class, 'create'])->name('delivery-orders.create');
Route::post('/delivery-orders', [DeliveryOrderController::class, 'store'])->name('delivery-orders.store');
Route::get('/delivery-orders/{id}', [DeliveryOrderController::class, 'show'])->name('delivery-orders.show');
Route::post('/delivery-orders/{id}/dispatch', [DeliveryOrderController::class, 'dispatchOrder'])->name('delivery-orders.dispatch');
Route::post('/delivery-orders/{id}/deliver', [DeliveryOrderController::class, 'deliverOrder'])->name('delivery-orders.deliver');
Route::get('/delivery-orders/{id}/print', [DeliveryOrderController::class, 'printOrder'])->name('delivery-orders.print');

// Phase 2: Sales Quotations (Penawaran Harga)
Route::get('/quotations', [SalesQuotationController::class, 'index'])->name('quotations.index');
Route::get('/quotations/create', [SalesQuotationController::class, 'create'])->name('quotations.create');
Route::post('/quotations', [SalesQuotationController::class, 'store'])->name('quotations.store');
Route::get('/quotations/{id}', [SalesQuotationController::class, 'show'])->name('quotations.show');
Route::post('/quotations/{id}/convert', [SalesQuotationController::class, 'convertToSalesOrder'])->name('quotations.convert');

// Phase 2: Sales Order Approval Workflow
Route::get('/sales-approvals', [SalesOrderApprovalController::class, 'index'])->name('sales.approvals.index');
Route::post('/sales-orders/{id}/approve', [SalesOrderApprovalController::class, 'approve'])->name('sales.approvals.approve');
Route::post('/sales-orders/{id}/reject', [SalesOrderApprovalController::class, 'reject'])->name('sales.approvals.reject');

// Phase 2: Sales Targets & Leaderboard
Route::get('/sales-targets', [SalesTargetController::class, 'index'])->name('sales.targets.index');
Route::post('/sales-targets', [SalesTargetController::class, 'store'])->name('sales.targets.store');

// Pelanggan & Plafon Kredit (Full CRUD)
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

// Gudang & Stok FEFO
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

// Phase 3: Multi-Warehouse Transfers (Inter-Branch Workflow)
Route::get('/warehouse-transfers', [WarehouseTransferController::class, 'index'])->name('transfers.index');
Route::get('/warehouse-transfers/create', [WarehouseTransferController::class, 'create'])->name('transfers.create');
Route::post('/warehouse-transfers', [WarehouseTransferController::class, 'store'])->name('transfers.store');
Route::get('/warehouse-transfers/{id}', [WarehouseTransferController::class, 'show'])->name('transfers.show');
Route::post('/warehouse-transfers/{id}/submit', [WarehouseTransferController::class, 'submit'])->name('transfers.submit');
Route::post('/warehouse-transfers/{id}/approve', [WarehouseTransferController::class, 'approve'])->name('transfers.approve');
Route::post('/warehouse-transfers/{id}/dispatch', [WarehouseTransferController::class, 'dispatch'])->name('transfers.dispatch');
Route::post('/warehouse-transfers/{id}/receive', [WarehouseTransferController::class, 'receive'])->name('transfers.receive');
Route::post('/warehouse-transfers/{id}/reject', [WarehouseTransferController::class, 'reject'])->name('transfers.reject');

// Phase 3: Stock Opname & Cycle Counting
Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('opname.index');
Route::get('/stock-opname/create', [StockOpnameController::class, 'create'])->name('opname.create');
Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('opname.store');
Route::get('/stock-opname/{id}', [StockOpnameController::class, 'show'])->name('opname.show');
Route::post('/stock-opname/{id}/count', [StockOpnameController::class, 'recordCount'])->name('opname.count');
Route::post('/stock-opname/{id}/post', [StockOpnameController::class, 'postAdjustment'])->name('opname.post');
Route::post('/stock-opname/{id}/reject', [StockOpnameController::class, 'reject'])->name('opname.reject');

// Phase 3: Immutable Stock Ledger & Expiry Alerts
Route::get('/inventory/ledger', [StockLedgerController::class, 'ledger'])->name('inventory.ledger');
Route::get('/inventory/ledger/export', [StockLedgerController::class, 'exportLedger'])->name('inventory.ledger.export');
Route::get('/inventory/expiry-alerts', [StockLedgerController::class, 'expiryAlerts'])->name('inventory.expiry-alerts');

// Phase 4: Keuangan, Faktur Pajak, Pembayaran & AR Aging
Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
Route::get('/finance/invoices/create', [FinanceController::class, 'createInvoice'])->name('finance.invoices.create');
Route::post('/finance/invoices', [FinanceController::class, 'storeInvoice'])->name('finance.invoices.store');
Route::get('/finance/invoices/{id}', [FinanceController::class, 'showInvoice'])->name('finance.invoices.show');
Route::get('/finance/invoices/{id}/print', [FinanceController::class, 'printInvoice'])->name('finance.invoices.print');
Route::get('/finance/payments/create', [FinanceController::class, 'createPayment'])->name('finance.payments.create');
Route::post('/finance/payments', [FinanceController::class, 'storePayment'])->name('finance.payments.store');
Route::get('/finance/ar-aging/export', [FinanceController::class, 'exportArAging'])->name('finance.ar-aging.export');

// Phase 4: Manajemen Retur Penjualan (Good vs Bad Stock)
Route::get('/sales-returns', [SalesReturnController::class, 'index'])->name('returns.index');
Route::get('/sales-returns/create', [SalesReturnController::class, 'create'])->name('returns.create');
Route::post('/sales-returns', [SalesReturnController::class, 'store'])->name('returns.store');
Route::get('/sales-returns/{id}', [SalesReturnController::class, 'show'])->name('returns.show');
Route::post('/sales-returns/{id}/approve', [SalesReturnController::class, 'approve'])->name('returns.approve');

// Rute Salesman GT & Kunjungan Toko
Route::get('/routes', [RoutePlanController::class, 'index'])->name('routes.index');

// =============================================================================
// PHASE 1: SAAS TENANT, USER & RBAC MANAGEMENT
// =============================================================================
// Tenant Workspace Settings
Route::get('/tenant/settings', [TenantController::class, 'settings'])->name('tenant.settings');

// User Management
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

// Role & Permission Matrix
Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
Route::post('/roles/{id}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

// Audit Trail
Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

// Super Admin SaaS Platform Control
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/tenants', [SuperAdminController::class, 'tenants'])->name('tenants.index');
    Route::get('/tenants/{id}', [SuperAdminController::class, 'showTenant'])->name('tenants.show');
    Route::post('/tenants/{id}/toggle-status', [SuperAdminController::class, 'toggleStatus'])->name('tenants.toggle-status');
    Route::post('/tenants/{id}/quota', [SuperAdminController::class, 'updateQuota'])->name('tenants.update-quota');
});

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
        'tenant' => TenantContext::getTenant()?->name ?? 'Default Global',
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

// SaaS Tenant API
Route::get('/api/v1/tenant/current', function () {
    $tenant = TenantContext::getTenant();
    if (!$tenant) {
        return response()->json(['success' => false, 'message' => 'No active tenant context'], 404);
    }
    return response()->json([
        'success' => true,
        'tenant' => [
            'id' => $tenant->id,
            'code' => $tenant->code,
            'name' => $tenant->name,
            'subdomain' => $tenant->subdomain,
            'status' => $tenant->status,
            'plan_tier' => $tenant->plan_tier,
            'quota' => [
                'max_users' => $tenant->max_users,
                'max_branches' => $tenant->max_branches,
                'max_warehouses' => $tenant->max_warehouses,
            ],
        ]
    ]);
});
