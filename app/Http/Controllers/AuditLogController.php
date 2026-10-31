<?php

namespace App\Http\Controllers;

use App\Domain\Security\Models\AuditLog;
use App\Domain\Tenant\TenantContext;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display audit trail.
     */
    public function index(Request $request)
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()?->tenant;

        $query = AuditLog::with('user');
        if ($tenant) {
            $query->where('tenant_id', $tenant->id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('entity_id', 'like', "%{$request->search}%")
                    ->orWhere('ip_address', 'like', "%{$request->search}%");
            });
        }

        $logs = $query->latest('created_at')->paginate(25);

        return view('audit.index', compact('logs', 'tenant'));
    }
}

