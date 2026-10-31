<?php

namespace App\Http\Controllers;

use App\Domain\Security\Models\Permission;
use App\Domain\Security\Models\Role;
use App\Domain\Tenant\TenantContext;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display roles and permissions matrix.
     */
    public function index()
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()?->tenant;

        $roles = Role::with(['permissions', 'users'])
            ->where(function ($q) use ($tenant) {
                $q->whereNull('tenant_id');
                if ($tenant) {
                    $q->orWhere('tenant_id', $tenant->id);
                }
            })
            ->get();

        $permissionsByModule = Permission::all()->groupBy('module');

        return view('roles.index', compact('roles', 'permissionsByModule', 'tenant'));
    }

    /**
     * Show detail of a role and assign permissions.
     */
    public function show(int $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissionsByModule = Permission::all()->groupBy('module');

        return view('roles.show', compact('role', 'permissionsByModule'));
    }

    /**
     * Update permissions assigned to a role.
     */
    public function updatePermissions(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $permissionIds = $request->input('permissions', []);
        $role->permissions()->sync($permissionIds);

        return redirect()->back()->with('success', "Hak akses untuk peran {$role->name} berhasil disimpan.");
    }
}

