<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'category_id', 'target_audience', 'priority_region',
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
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function rules(): HasMany { return $this->hasMany(AiEditorialRule::class, 'profile_id'); }
    public function terms(): HasMany { return $this->hasMany(AiEditorialTerm::class, 'profile_id'); }
}
