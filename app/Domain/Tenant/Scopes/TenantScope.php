<?php

namespace App\Domain\Tenant\Scopes;

use App\Domain\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the tenant scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (TenantContext::hasTenant()) {
            $builder->where($model->getTable() . '.tenant_id', '=', TenantContext::getTenantId());
        }
    }
}

