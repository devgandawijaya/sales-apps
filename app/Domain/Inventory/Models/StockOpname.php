<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Tenant\Traits\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    use BelongsToTenant;

    protected $table = 'stock_opnames';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'warehouse_id',
        'opname_number',
        'title',
        'period_date',
        'status',
        'count_type',
        'blind_count',
        'total_items_counted',
        'total_discrepancy_qty',
        'total_cogs_variance',
        'created_by',
        'approved_by',
        'approved_at',
        'posted_at',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'period_date' => 'date',
        'blind_count' => 'boolean',
        'total_items_counted' => 'integer',
        'total_discrepancy_qty' => 'integer',
        'total_cogs_variance' => 'decimal:2',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class, 'stock_opname_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['DRAFT', 'IN_PROGRESS']);
    }

    public function isPostable(): bool
    {
        return in_array($this->status, ['SUBMITTED', 'APPROVED']);
    }
}

