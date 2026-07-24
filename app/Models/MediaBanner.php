<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaBanner extends Model
{
    public const TYPE_YOUTUBE = 'youtube';
    public const TYPE_FACEBOOK_REEL = 'facebook_reel';
    public const TYPE_VEHICLE_YOUTUBE = 'vehicle_youtube';

    protected $fillable = [
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

    public static function typeLabels(): array
    {
        return [
            self::TYPE_YOUTUBE => 'YouTube horizontal',
            self::TYPE_FACEBOOK_REEL => 'Facebook Reels retrato',
            self::TYPE_VEHICLE_YOUTUBE => 'Veículos YouTube horizontal',
        ];
    }
}
