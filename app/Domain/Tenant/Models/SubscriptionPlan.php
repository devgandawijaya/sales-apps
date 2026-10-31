<?php

namespace App\Domain\Tenant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'tier',
        'price_monthly',
        'price_annual',
        'max_users',
        'max_companies',
        'max_branches',
        'max_warehouses',
        'storage_limit_mb',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_annual' => 'decimal:2',
            'max_users' => 'integer',
            'max_companies' => 'integer',
            'max_branches' => 'integer',
            'max_warehouses' => 'integer',
            'storage_limit_mb' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}

