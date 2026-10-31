<?php

namespace App\Http\Controllers;

use App\Domain\Security\Services\AuditLoggerService;
use App\Domain\Tenant\Models\SubscriptionPlan;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    /**
     * SaaS Super Admin Dashboard: Overview & Platform Metrics.
     */
    public function dashboard()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $trialTenants = Tenant::where('status', 'trial')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();

        // Calculate MRR (Monthly Recurring Revenue)
        $mrr = (float) Tenant::join('subscriptions', 'tenants.id', '=', 'subscriptions.tenant_id')
            ->where('subscriptions.status', 'active')
            ->sum('subscriptions.amount');
        $arr = $mrr * 12;

        $plans = SubscriptionPlan::withCount('subscriptions')->get();
        $recentTenants = Tenant::with('currentSubscription.plan')->latest()->take(10)->get();

        return view('superadmin.dashboard', compact(
            'totalTenants',
            'activeTenants',
            'trialTenants',
            'suspendedTenants',
            'mrr',
            'arr',
            'plans',
            'recentTenants'
        ));
    }

    /**
     * List all tenants.
     */
    public function tenants(Request $request)
    {
        $query = Tenant::with(['currentSubscription.plan', 'users', 'branches', 'warehouses']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('subdomain', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        $tenants = $query->latest()->paginate(15);

        return view('superadmin.tenants.index', compact('tenants'));
    }

    /**
     * Show detail of a tenant.
     */
    public function showTenant(string $id)
    {
        $tenant = Tenant::with(['companies', 'branches', 'warehouses', 'users', 'subscriptions.plan'])->findOrFail($id);
        $plans = SubscriptionPlan::where('is_active', true)->get();

        return view('superadmin.tenants.show', compact('tenant', 'plans'));
    }

    /**
     * Toggle Tenant Status (Suspend / Activate).
     */
    public function toggleStatus(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $newStatus = $request->input('status', 'suspended');

        $oldStatus = $tenant->status;
        $tenant->update(['status' => $newStatus]);

        AuditLoggerService::log(
            action: 'TENANT_STATUS_CHANGED',
            entityType: 'Tenant',
            entityId: $tenant->id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus],
            tenantId: $tenant->id
        );

        return redirect()->back()->with('success', "Status tenant {$tenant->name} berhasil diubah menjadi {$newStatus}.");
    }

    /**
     * Update Tenant Quota & Plan.
     */
    public function updateQuota(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'max_users' => 'required|integer|min:1',
            'max_companies' => 'required|integer|min:1',
            'max_branches' => 'required|integer|min:1',
            'max_warehouses' => 'required|integer|min:1',
            'storage_limit_mb' => 'required|integer|min:100',
        ]);

        $tenant->update($validated);

        AuditLoggerService::log(
            action: 'TENANT_QUOTA_UPDATED',
            entityType: 'Tenant',
            entityId: $tenant->id,
            newValues: $validated,
            tenantId: $tenant->id
        );

        return redirect()->back()->with('success', "Batas kuota tenant {$tenant->name} berhasil diperbarui.");
    }
}

