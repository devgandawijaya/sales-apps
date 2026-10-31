<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request and check granular permission.
     *
     * @param  string  $permission  Format: 'module.action' (e.g. 'sales.create', 'inventory.view')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Superadmin bypass
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        [$module, $action] = explode('.', $permission, 2);

        if (!$user->hasPermission($module, $action)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Akses ditolak. Anda tidak memiliki izin untuk '{$action}' pada modul '{$module}'."
                ], 403);
            }

            abort(403, "Akses ditolak. Anda tidak memiliki izin untuk '{$action}' pada modul '{$module}'.");
        }

        return $next($request);
    }
}

