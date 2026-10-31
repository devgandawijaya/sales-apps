<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    protected $table = 'stock_opname_items';

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'batch_number',
        'expiry_date',
        'system_qty',
        'physical_qty',
        'discrepancy_qty',
        'unit_cost',
        'discrepancy_cogs_value',
        'reason_code',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'system_qty' => 'integer',
        'physical_qty' => 'integer',
        'discrepancy_qty' => 'integer',
        'unit_cost' => 'decimal:4',
        'discrepancy_cogs_value' => 'decimal:2',
    ];

    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

