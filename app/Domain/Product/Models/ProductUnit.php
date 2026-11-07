<?php

namespace App\Domain\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    protected $table = 'product_units';

    protected $fillable = [
        'product_id',
        'unit_name',
        'conversion_factor',
        'barcode',
        'is_base_unit',
    ];

    protected $casts = [
        'conversion_factor' => 'integer',
        'is_base_unit' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getIsBaseAttribute(): bool
    {
        return (bool) $this->is_base_unit;
    }
}

