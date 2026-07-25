<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RssCollectionRun extends Model
{
    protected $fillable = [
        'rss_feed_id', 'job_uuid', 'status', 'requested_limit', 'created_count',
        'duplicate_count', 'failed_count', 'message', 'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(RssFeed::class, 'rss_feed_id');
    }
}
