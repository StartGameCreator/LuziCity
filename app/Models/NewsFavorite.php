<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsFavorite extends Model
{
    protected $fillable = ['user_id', 'news_article_id', 'site_id'];

    public function article(): BelongsTo
    {
        return $this->belongsTo(NewsArticle::class, 'news_article_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
