<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealEstateListing extends Model
{
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_DEAL_DONE = 'deal_done';

    protected $fillable = [
        'user_id',
        'purpose',
        'property_type',
        'title',
        'price',
        'city',
        'state',
        'neighborhood',
        'address',
        'bedrooms',
        'bathrooms',
        'parking_spaces',
        'area_m2',
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
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'views_count' => 'integer',
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

    public static function purposeLabels(): array
    {
        return [
            'sale' => 'Comprar',
            'rent' => 'Alugar',
            'sell' => 'Vender',
        ];
    }

    public static function propertyTypeLabels(): array
    {
        return [
            'house' => 'Casa',
            'apartment' => 'Apartamento',
            'land' => 'Lote/Terreno',
            'commercial' => 'Comercial',
            'farm' => 'Chácara/Fazenda',
        ];
    }
}
