<?php

namespace App\Http\Controllers;

use App\Domain\Tenant\Models\SubscriptionPlan;
use App\Domain\Tenant\Services\QuotaEnforcerService;
use App\Domain\Tenant\Services\TenantManagerService;
use App\Domain\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function __construct(
        protected TenantManagerService $tenantManager,
        protected QuotaEnforcerService $quotaEnforcer
    ) {}

    /**
     * Show registration / onboarding form for a new tenant.
     */
    public function showRegistrationForm()
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();
        return view('auth.register-tenant', compact('plans'));
    }

    /**
     * Handle tenant onboarding submission.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:150',
            'subdomain' => 'required|string|min:3|max:50|alpha_dash|unique:tenants,subdomain',
            'tax_id_npwp' => 'nullable|string|max:50',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'admin_name' => 'required|string|max:100',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
            'admin_phone' => 'nullable|string|max:30',
            'plan_code' => 'nullable|string|exists:subscription_plans,code',
        ]);

        $result = $this->tenantManager->registerTenant($validated);

        // Auto-login newly created tenant owner
        Auth::login($result['user']);

        return redirect()->route('dashboard')->with('success', "Selamat datang di platform Sales & Distribution! Perusahaan {$result['tenant']->name} berhasil diinisialisasi.");
    }

    /**
     * View tenant settings and quota usage.
     */
    public function settings()
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()?->tenant;

        if (!$tenant) {
            abort(404, 'Data tenant tidak ditemukan.');
        }

        $usage = $this->quotaEnforcer->getUsageSummary($tenant);
        $subscription = $tenant->currentSubscription;

        return view('tenant.settings', compact('tenant', 'usage', 'subscription'));
    }
}

