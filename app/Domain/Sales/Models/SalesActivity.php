<?php

namespace App\Domain\Sales\Models;

use App\Domain\Customer\Models\Customer;
use App\Domain\Tenant\Traits\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesActivity extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'customer_id',
        'sales_lead_id',
        'activity_type',
        'activity_date',
        'notes',
        'result_status',
        'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'datetime',
            'follow_up_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(SalesLead::class, 'sales_lead_id');
    }
}

