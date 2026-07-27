<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MediaBanner extends Model
{
    use BelongsToCurrentSite;

    public const TYPE_YOUTUBE = 'youtube';

    public const TYPE_FACEBOOK_REEL = 'facebook_reel';

    public const TYPE_VEHICLE_YOUTUBE = 'vehicle_youtube';

    protected $fillable = [
        'site_id',
        'type',
        'title',
        'embed_code',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_YOUTUBE => 'YouTube horizontal',
            self::TYPE_FACEBOOK_REEL => 'Facebook Reels retrato',
            self::TYPE_VEHICLE_YOUTUBE => 'Veículos YouTube horizontal',
        ];
    }
}
