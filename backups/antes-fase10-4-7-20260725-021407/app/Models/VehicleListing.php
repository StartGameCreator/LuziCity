<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleListing extends Model
{
    public const TYPE_CAR = 'car';
    public const TYPE_MOTORCYCLE = 'motorcycle';
    public const TYPE_NAUTICAL = 'nautical';

    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_SOLD = 'sold';

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'title',
        'brand',
        'model',
        'year',
        'price',
        'mileage',
        'fuel',
        'transmission',
        'city',
        'state',
        'phone',
        'whatsapp',
        'description',
        'photos',
        'video_platform',
        'video_orientation',
        'video_embed_code',
        'status',
        'is_featured',
        'views_count',
        'search_hits',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'search_hits' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function primaryPhotoUrl(): ?string
    {
        $photo = collect($this->photos)->first();

        return $photo ? asset($photo) : null;
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_CAR => 'Carros',
            self::TYPE_MOTORCYCLE => 'Motos',
            self::TYPE_NAUTICAL => 'Embarcações Náuticas',
        ];
    }
}
