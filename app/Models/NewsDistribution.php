<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsDistribution extends Model
{
    protected $fillable = [
        'source_article_id', 'source_site_id', 'target_site_id', 'mode', 'target_article_id', 'distributed_by',
    ];

    public function sourceArticle(): BelongsTo
    {
        return $this->belongsTo(NewsArticle::class, 'source_article_id');
    }

    public function targetArticle(): BelongsTo
    {
        return $this->belongsTo(NewsArticle::class, 'target_article_id');
    }

    public function sourceSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'source_site_id');
    }

    public function targetSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'target_site_id');
    }
}
