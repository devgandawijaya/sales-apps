<?php

namespace App\Domain\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;

class GoodsReceiptItem extends Model
{
    protected $table = 'goods_receipt_items';

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'product_id',
        'product_unit_id',
        'warehouse_bin_id',
        'batch_number',
        'expiry_date',
        'qty_expected_base_uom',
        'qty_received_base_uom',
        'qty_discrepancy_base_uom',
        'condition_status',
        'unit_cost',
        'discrepancy_reason',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'qty_expected_base_uom' => 'integer',
        'qty_received_base_uom' => 'integer',
        'qty_discrepancy_base_uom' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function bin()
    {
        return $this->belongsTo(WarehouseBin::class, 'warehouse_bin_id');
    }
}

