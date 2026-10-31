<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Organization\Models\Warehouse;
use App\Domain\Tenant\Traits\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use BelongsToTenant;

    protected $table = 'stock_transfers';

    protected $fillable = [
        'tenant_id',
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'transit_warehouse_id',
        'driver_name',
        'vehicle_number',
        'status',
        'approval_level',
        'approved_by',
        'approved_at',
        'sent_date',
        'dispatched_at',
        'received_date',
        'notes',
        'rejection_reason',
        'received_notes',
        'created_by',
        'received_by',
    ];

    protected $casts = [
        'sent_date' => 'datetime',
        'dispatched_at' => 'datetime',
        'received_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function transitWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'transit_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'stock_transfer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isSubmittable(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function isApprovable(): bool
    {
        return in_array($this->status, ['DRAFT', 'SUBMITTED', 'PENDING_APPROVAL']);
    }

    public function isDispatchable(): bool
    {
        return in_array($this->status, ['APPROVED', 'PICKING']);
    }

    public function isReceivable(): bool
    {
        return in_array($this->status, ['DISPATCHED', 'IN_TRANSIT']);
    }
}

