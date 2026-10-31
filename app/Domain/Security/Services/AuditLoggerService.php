<?php

namespace App\Domain\Security\Services;

use App\Domain\Security\Models\AuditLog;
use App\Domain\Tenant\TenantContext;

class AuditLoggerService
{
    /**
     * Record an audit log entry.
     */
    public static function log(
        string $action,
        string $entityType,
        ?string $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $tenantId = null,
        ?int $userId = null
    ): AuditLog {
        return AuditLog::create([
            'tenant_id' => $tenantId ?? TenantContext::getTenantId(),
            'user_id' => $userId ?? auth()->id(),
            'action' => strtoupper($action),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}

