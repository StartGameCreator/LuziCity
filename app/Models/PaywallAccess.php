<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaywallAccess extends Model
{
    protected $fillable = ['user_id', 'news_article_id', 'period_month', 'accessed_at'];

    protected function casts(): array
    {
        return ['period_month' => 'date', 'accessed_at' => 'datetime'];
    }
}
