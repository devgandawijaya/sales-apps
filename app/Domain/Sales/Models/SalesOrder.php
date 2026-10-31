<?php

namespace App\Domain\Sales\Models;

use App\Domain\Customer\Models\Customer;
use App\Domain\Organization\Models\Branch;
use App\Domain\Organization\Models\Warehouse;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use BelongsToTenant;

    protected $table = 'sales_orders';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'customer_id',
        'salesman_id',
        'warehouse_id',
        'so_number',
        'po_number_mt',
        'order_date',
        'channel',
        'order_type',
        'status',
        'payment_term_days',
        'subtotal',
        'discount_on_invoice',
        'discount_off_invoice',
        'tax_amount',
        'grand_total',
        'approval_level',
        'approved_by',
        'approved_at',
        'notes',
        'quotation_id',
        'rejection_reason',
    ];

    protected $casts = [
        'order_date' => 'date',
        'payment_term_days' => 'integer',
        'subtotal' => 'decimal:2',
        'discount_on_invoice' => 'decimal:2',
        'discount_off_invoice' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'approval_level' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(SalesQuotation::class, 'quotation_id');
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'sales_order_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'sales_order_id');
    }
}
