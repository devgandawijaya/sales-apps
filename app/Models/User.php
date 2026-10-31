<?php

namespace App\Models;

use App\Domain\Organization\Models\Branch;
use App\Domain\Security\Models\AuditLog;
use App\Domain\Security\Models\Role;
use App\Domain\Security\Services\RbacService;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'email',
        'password',
        'phone',
        'role_title',
        'channel_assignment',
        'status',
        'mfa_enabled',
        'mfa_secret',
        'last_login_at',
        'last_active_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'mfa_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if user has a specific role by slug.
     */
    public function hasRole(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles->contains('slug', $slug) || strtoupper($this->role_title) === strtoupper($slug);
    }

    /**
     * Check if user has a specific granular permission.
     */
    public function hasPermission(string $module, string $action): bool
    {
        return app(RbacService::class)->userHasPermission($this, $module, $action);
    }

    /**
     * Check if user is Superadmin.
     */
    public function isSuperAdmin(): bool
    {
        return strtoupper((string)$this->role_title) === 'SUPERADMIN' || $this->roles->contains('slug', 'super-admin');
    }

    /**
     * Check if user is Company Owner.
     */
    public function isCompanyOwner(): bool
    {
        return strtoupper((string)$this->role_title) === 'COMPANY_OWNER' || $this->roles->contains('slug', 'company-owner');
    }
}
