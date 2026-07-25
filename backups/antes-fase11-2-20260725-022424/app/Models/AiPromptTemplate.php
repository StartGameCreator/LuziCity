<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiPromptTemplate extends Model
{
    protected $fillable = [
        'key', 'name', 'purpose', 'system_prompt', 'user_template',
        'output_schema', 'version', 'is_active', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'output_schema' => 'array',
            'version' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AiPromptVersion::class, 'ai_prompt_template_id')->orderByDesc('version');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(AiExecution::class, 'prompt_template_id');
    }
}
