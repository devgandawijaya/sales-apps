<?php

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Domain\Organization\Models\Branch;
use App\Domain\Pricing\Models\PriceList;
use App\Domain\Sales\Models\SalesOrder;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'owner_name',
        'channel',
        'sub_channel',
        'tax_id_npwp',
        'nik',
        'phone',
        'email',
        'address',
        'city',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'credit_limit',
        'current_credit_used',
        'term_of_payment',
        'payment_type',
        'price_list_id',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'geofence_radius_meters' => 'integer',
        'credit_limit' => 'decimal:2',
        'current_credit_used' => 'decimal:2',
        'term_of_payment' => 'integer',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }

    public function modernTradeAgreement(): HasOne
    {
        return $this->hasOne(ModernTradeAgreement::class, 'customer_id')
            ->where('status', 'ACTIVE');
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'customer_id');
    }

    public function isGeneralTrade(): bool
    {
        return strtoupper($this->channel) === 'GT';
    }

    public function isModernTrade(): bool
    {
        return strtoupper($this->channel) === 'MT';
    }

    public function availableCredit(): float
    {
        return max(0, (float)$this->credit_limit - (float)$this->current_credit_used);
    }
}

