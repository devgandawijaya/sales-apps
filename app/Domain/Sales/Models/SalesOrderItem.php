<?php

namespace App\Domain\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;

class SalesOrderItem extends Model
{
    protected $table = 'sales_order_items';

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'product_unit_id',
        'conversion_factor',
        'qty_ordered',
        'qty_in_base_uom',
        'qty_delivered_base_uom',
        'unit_price',
        'gross_amount',
        'discount_percent_1',
        'discount_percent_2',
        'discount_nominal',
        'net_amount',
    ];

    protected $casts = [
        'conversion_factor' => 'integer',
        'qty_ordered' => 'integer',
        'qty_in_base_uom' => 'integer',
        'qty_delivered_base_uom' => 'integer',
        'unit_price' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'discount_percent_1' => 'decimal:2',
        'discount_percent_2' => 'decimal:2',
        'discount_nominal' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}

