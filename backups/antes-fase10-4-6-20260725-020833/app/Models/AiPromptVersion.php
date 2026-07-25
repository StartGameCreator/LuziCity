<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPromptVersion extends Model
{
    protected $fillable = [
        'ai_prompt_template_id', 'version', 'system_prompt', 'user_prompt',
        'variables', 'change_notes', 'created_by',
    ];

    protected function casts(): array
    {
        return ['variables' => 'array', 'version' => 'integer'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AiPromptTemplate::class, 'ai_prompt_template_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
