<?php

namespace App\Domain\Tenant;

use App\Domain\Tenant\Models\Tenant;

class TenantContext
{
    protected static ?Tenant $currentTenant = null;

    /**
     * Set the currently active tenant.
     */
    public static function setTenant(?Tenant $tenant): void
    {
        static::$currentTenant = $tenant;
    }

    /**
     * Get the currently active tenant.
     */
    public static function getTenant(): ?Tenant
    {
        return static::$currentTenant;
    }

    /**
     * Get the currently active tenant ID.
     */
    public static function getTenantId(): ?string
    {
        return static::$currentTenant?->id;
    }

    /**
     * Check if a tenant context is currently active.
     */
    public static function hasTenant(): bool
    {
        return static::$currentTenant !== null;
    }

    /**
     * Clear the tenant context.
     */
    public static function clear(): void
    {
        static::$currentTenant = null;
    }
}

