<?php

namespace App\Domain\Warehouse\Models;

use App\Domain\Organization\Models\Warehouse;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseZone extends Model
{
    use BelongsToTenant;

    protected $table = 'warehouse_zones';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'code',
        'name',
        'zone_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class, 'warehouse_id', 'warehouse_id')
            ->where('zone', $this->code);
    }
}

