<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiEditorialProfile extends Model
{
    protected $fillable = [
        'name',
        'is_default',
        'language',
        'tone',
        'max_title_length',
        'max_excerpt_length',
        'require_source_attribution',
        'avoid_sensationalism',
        'human_review_required',
        'editorial_rules',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'max_title_length' => 'integer',
            'max_excerpt_length' => 'integer',
            'require_source_attribution' => 'boolean',
            'avoid_sensationalism' => 'boolean',
            'human_review_required' => 'boolean',
        ];
    }
}
