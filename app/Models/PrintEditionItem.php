<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintEditionItem extends Model
{
    protected $fillable = ['print_edition_section_id', 'news_article_id', 'position'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(PrintEditionSection::class, 'print_edition_section_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(NewsArticle::class, 'news_article_id');
    }
}
