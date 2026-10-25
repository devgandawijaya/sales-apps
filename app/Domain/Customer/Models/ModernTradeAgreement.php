<?php

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModernTradeAgreement extends Model
{
    protected $table = 'modern_trade_agreements';

    protected $fillable = [
        'customer_id',
        'contract_number',
        'start_date',
        'end_date',
        'listing_fee_total',
        'slotting_fee_total',
        'on_invoice_discount_rate',
        'off_invoice_rebate_rate',
        'payment_terms_days',
        'penalty_terms',
        'document_attachment_path',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'listing_fee_total' => 'decimal:2',
        'slotting_fee_total' => 'decimal:2',
        'on_invoice_discount_rate' => 'decimal:2',
        'off_invoice_rebate_rate' => 'decimal:2',
        'payment_terms_days' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

