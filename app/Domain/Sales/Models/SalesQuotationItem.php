<?php

namespace App\Domain\Sales\Models;

use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesQuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_quotation_id',
        'product_id',
        'product_unit_id',
        'conversion_factor',
        'qty_ordered',
        'qty_in_base_uom',
        'unit_price',
        'gross_amount',
        'discount_percent_1',
        'discount_percent_2',
        'discount_nominal',
        'net_amount',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'integer',
            'qty_ordered' => 'integer',
            'qty_in_base_uom' => 'integer',
            'unit_price' => 'decimal:4',
            'gross_amount' => 'decimal:2',
            'discount_percent_1' => 'decimal:2',
            'discount_percent_2' => 'decimal:2',
            'discount_nominal' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }
}

