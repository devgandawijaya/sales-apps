<?php

namespace App\Domain\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Models\User;

class GoodsReceipt extends Model
{
    use SoftDeletes;

    protected $table = 'goods_receipts';

    protected $fillable = [
        'branch_id',
        'warehouse_id',
        'purchase_order_id',
        'grn_number',
        'supplier_delivery_note',
        'receive_date',
        'receipt_type',
        'status',
        'driver_name',
        'vehicle_plate_number',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'receive_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }
}

