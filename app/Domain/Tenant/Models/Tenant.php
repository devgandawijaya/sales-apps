<?php

namespace App\Domain\Tenant\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Domain\Organization\Models\Company;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Models\User;

class Tenant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'subdomain',
        'custom_domain',
        'status',
        'trial_ends_at',
        'plan_tier',
        'max_users',
        'max_companies',
        'max_branches',
        'max_warehouses',
        'storage_limit_mb',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'max_users' => 'integer',
            'max_companies' => 'integer',
            'max_branches' => 'integer',
            'max_warehouses' => 'integer',
            'storage_limit_mb' => 'integer',
            'settings' => 'array',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest('created_at');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isActive(): bool
    {
        if ($this->status === 'active') {
            return true;
        }

        if ($this->status === 'trial') {
            return $this->trial_ends_at ? $this->trial_ends_at->isFuture() : true;
        }

        return false;
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired' || $this->status === 'canceled') {
            return true;
        }

        if ($this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return true;
        }

        return false;
    }
}

