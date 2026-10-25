<?php

namespace App\Domain\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domain\Inventory\Models\Stock;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'sku',
        'barcode',
        'name',
        'brand',
        'base_uom',
        'cost_price',
        'weight_gram',
        'volume_cbm',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'weight_gram' => 'decimal:2',
        'volume_cbm' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class, 'product_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'product_id');
    }
}

