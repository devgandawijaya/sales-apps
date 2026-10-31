<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Organization\Models\Warehouse;
use App\Domain\Product\Models\Product;
use App\Domain\Tenant\Traits\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Immutable Double-Entry Stock Movement Ledger.
 * Every physical inventory mutation MUST generate a record here.
 * Once created, a ledger entry cannot be edited or deleted.
 */
class StockLedgerEntry extends Model
{
    use BelongsToTenant;

    public $timestamps = false;
    protected $table = 'stock_ledger_entries';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'product_id',
        'batch_number',
        'entry_type',
        'reference_type',
        'reference_id',
        'reference_number',
        'qty_change',
        'qty_before',
        'qty_after',
        'unit_cost',
        'total_cost',
        'notes',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'qty_change' => 'integer',
        'qty_before' => 'integer',
        'qty_after' => 'integer',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException("Catatan mutasi stok bersifat permanen dan tidak dapat diubah.");
        });

        static::deleting(function () {
            throw new RuntimeException("Catatan mutasi stok bersifat permanen dan tidak dapat dihapus.");
        });
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

