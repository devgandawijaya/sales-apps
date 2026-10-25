<?php

namespace App\Domain\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Organization\Models\Warehouse;

class WarehouseBin extends Model
{
    use SoftDeletes;

    protected $table = 'warehouse_bins';

    protected $fillable = [
        'warehouse_id',
        'bin_code',
        'zone',
        'aisle',
        'rack',
        'tier',
        'bin',
        'max_weight_kg',
        'max_volume_cbm',
        'is_occupied',
        'is_active',
    ];

    protected $casts = [
        'max_weight_kg' => 'decimal:2',
        'max_volume_cbm' => 'decimal:4',
        'is_occupied' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}

