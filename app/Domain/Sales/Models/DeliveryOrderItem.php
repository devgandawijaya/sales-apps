<?php

namespace App\Domain\Sales\Models;

use App\Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrderItem extends Model
{
    protected $table = 'delivery_order_items';

    protected $fillable = [
        'delivery_order_id',
        'sales_order_item_id',
        'product_id',
        'batch_number',
        'expiry_date',
        'qty_shipped',
        'qty_received',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'qty_shipped' => 'integer',
        'qty_received' => 'integer',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class, 'sales_order_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

