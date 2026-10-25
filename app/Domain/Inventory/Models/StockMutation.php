<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;

class StockMutation extends Model
{
    public $timestamps = false;
    protected $table = 'stock_mutations';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'batch_number',
        'transaction_type',
        'reference_type',
        'reference_id',
        'qty_in',
        'qty_out',
        'balance_after',
        'notes',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'qty_in' => 'integer',
        'qty_out' => 'integer',
        'balance_after' => 'integer',
        'created_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

