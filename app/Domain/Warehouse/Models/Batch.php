<?php

namespace App\Domain\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Product\Models\Product;

class Batch extends Model
{
    use SoftDeletes;

    protected $table = 'batches';

    protected $fillable = [
        'product_id',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'qc_status',
        'notes',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

