<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaKitFormat extends Model
{
    protected $fillable = [
        'name', 'placement', 'dimensions', 'description', 'price',
        'billing_model', 'display_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean'];
    }
}
