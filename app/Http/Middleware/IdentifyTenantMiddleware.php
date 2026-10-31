<?php

namespace App\Http\Middleware;

use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenantMiddleware
{
    /**
     * Handle an incoming request and identify the tenant context.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            // Check if tenant is suspended or expired
            if ($tenant->isSuspended()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Layanan tenant Anda ditangguhkan (Suspended). Silakan hubungi Customer Support.'
                    ], 403);
                }
                abort(403, 'Layanan tenant Anda ditangguhkan (Suspended). Silakan hubungi Customer Support.');
            }

            if ($tenant->isExpired()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Masa langganan atau trial tenant Anda telah berakhir. Silakan perpanjang paket langganan Anda.'
                    ], 402);
                }
            }

            TenantContext::setTenant($tenant);
        }

        $response = $next($request);

        return $response;
    }

    /**
     * Resolve tenant from request parameters.
     */
    protected function resolveTenant(Request $request): ?Tenant
    {
        // 1. Check from header (API / Integration)
        $headerIdentifier = $request->header('X-Tenant-Domain') ?? $request->header('X-Tenant-ID');
        if ($headerIdentifier) {
            $tenant = Tenant::where('subdomain', $headerIdentifier)
                ->orWhere('id', $headerIdentifier)
                ->orWhere('code', $headerIdentifier)
                ->first();

            if ($tenant) {
                return $tenant;
            }
        }

        // 2. Check from Subdomain (e.g. {tenant}.localhost or {tenant}.domain.com)
        $host = $request->getHost();
        $parts = explode('.', $host);
        if (count($parts) > 1 && !in_array($parts[0], ['www', 'app', 'api', 'localhost', '127'])) {
            $subdomain = $parts[0];
            $tenant = Tenant::where('subdomain', $subdomain)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        // 3. Check from authenticated user
        if (auth()->check() && auth()->user()->tenant_id) {
            return auth()->user()->tenant;
        }

        // 4. Default fallback: Pick the first active tenant
        return Tenant::where('status', 'active')->orWhere('status', 'trial')->first();
    }
}

