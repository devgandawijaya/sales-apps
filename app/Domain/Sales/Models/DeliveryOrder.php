<?php

namespace App\Domain\Sales\Models;

use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryOrder extends Model
{
    use BelongsToTenant;

    protected $table = 'delivery_orders';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'warehouse_id',
        'sales_order_id',
        'do_number',
        'delivery_date',
        'delivery_type',
        'status',
        'driver_name',
        'vehicle_plate_number',
        'recipient_name',
        'proof_of_delivery_path',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryOrderItem::class, 'delivery_order_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'delivery_order_id');
    }
}

