<?php

namespace App\Domain\Customer\Models;

use App\Domain\Organization\Models\Branch;
use App\Domain\Pricing\Models\PriceList;
use App\Domain\Sales\Models\SalesOrder;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use BelongsToTenant;

    protected $table = 'customers';

    protected $fillable = [
        'tenant_id',
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
        return $this->belongsTo(Branch::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function modernTradeAgreement(): HasOne
    {
        return $this->hasOne(ModernTradeAgreement::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function isModernTrade(): bool
    {
        return $this->channel === 'MT';
    }

    public function isGeneralTrade(): bool
    {
        return $this->channel === 'GT';
    }

    public function availableCredit(): float
    {
        return (float) max(0, $this->credit_limit - $this->current_credit_used);
    }

    public function hasAvailableCredit(float $requiredAmount): bool
    {
        // If credit limit is 0, consider as strictly cash-only
        if ($this->credit_limit <= 0) {
            return false;
        }

        return ($this->current_credit_used + $requiredAmount) <= $this->credit_limit;
    }
}
