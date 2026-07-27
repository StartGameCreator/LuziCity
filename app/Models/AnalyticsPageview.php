<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsPageview extends Model
{
    protected $fillable = ['event_uuid', 'session_hash', 'user_id', 'news_article_id', 'page_path', 'page_title', 'referrer_host', 'source', 'medium', 'campaign', 'content', 'term', 'device_type', 'reading_time_seconds', 'max_scroll_percent', 'share_count', 'last_shared_at', 'viewed_at', 'last_activity_at'];

    protected function casts(): array
    {
        return [
            'last_shared_at' => 'datetime',
            'viewed_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

}
