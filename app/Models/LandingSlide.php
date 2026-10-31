<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingSlide extends Model
{
    use HasFactory;

    protected $table = 'landing_slides';

    protected $fillable = [
        'title',
        'subtitle',
        'badge_text',
        'image_url',
        'cta_text',
        'cta_link',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }
}

