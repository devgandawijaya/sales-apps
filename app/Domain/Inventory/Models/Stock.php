<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use BelongsToTenant;

    protected $table = 'stocks';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'product_id',
        'batch_number',
        'expiry_date',
        'qty_on_hand',
        'qty_reserved',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'qty_on_hand' => 'integer',
        'qty_reserved' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getQtyAvailableAttribute(): int
    {
        return max(0, $this->qty_on_hand - $this->qty_reserved);
    }
}

