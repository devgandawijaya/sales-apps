<?php

namespace App\Http\Controllers;

use App\Domain\Organization\Models\Branch;
use App\Domain\Security\Models\Role;
use App\Domain\Security\Services\AuditLoggerService;
use App\Domain\Tenant\Services\QuotaEnforcerService;
use App\Domain\Tenant\TenantContext;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        protected QuotaEnforcerService $quotaEnforcer
    ) {}

    /**
     * List users in current tenant.
     */
    public function index(Request $request)
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()?->tenant;

        $query = User::with(['branch', 'roles']);
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('role_title', 'like', "%{$request->search}%");
            });
        }

        $users = $query->paginate(15);
        $branches = Branch::where('is_active', true)->get();
        $roles = Role::where(function ($q) use ($tenant) {
            $q->whereNull('tenant_id');
            if ($tenant) {
                $q->orWhere('tenant_id', $tenant->id);
            }
        })->get();

        return view('users.index', compact('users', 'branches', 'roles', 'tenant'));
    }

    /**
     * Store a new user with quota validation.
     */
    public function store(Request $request)
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()?->tenant;

        if ($tenant) {
            // Enforce plan quota
            $this->quotaEnforcer->validateCanAddUser($tenant);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:30',
            'branch_id' => 'nullable|exists:branches,id',
            'role_title' => 'required|string|max:50',
            'channel_assignment' => 'required|in:ALL,GT,MT',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $user = User::create([
            'tenant_id' => $tenant?->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role_title' => $validated['role_title'],
            'channel_assignment' => $validated['channel_assignment'],
            'status' => 'active',
            'is_active' => true,
        ]);

        if (!empty($validated['role_id'])) {
            $user->roles()->sync([$validated['role_id']]);
        }

        AuditLoggerService::log(
            action: 'USER_CREATED',
            entityType: 'User',
            entityId: (string) $user->id,
            newValues: ['name' => $user->name, 'email' => $user->email, 'role_title' => $user->role_title]
        );

        return redirect()->back()->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(int $id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->status = $user->is_active ? 'active' : 'inactive';
        $user->save();

        AuditLoggerService::log(
            action: 'USER_STATUS_TOGGLED',
            entityType: 'User',
            entityId: (string) $user->id,
            newValues: ['is_active' => $user->is_active, 'status' => $user->status]
        );

        return redirect()->back()->with('success', "Status pengguna {$user->name} berhasil diperbarui.");
    }
}

