<?php

namespace App\Domain\Product\Models;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant;

    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
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

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class, 'product_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'product_id');
    }

    public function priceListItems(): HasMany
    {
        return $this->hasMany(\App\Domain\Pricing\Models\PriceListItem::class, 'product_id');
    }

    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(\App\Domain\Sales\Models\SalesOrderItem::class, 'product_id');
    }
}
