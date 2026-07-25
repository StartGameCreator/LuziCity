<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiExecution extends Model
{
    protected $fillable = [
        'user_id', 'provider_id', 'prompt_template_id', 'feature', 'status',
        'input_hash', 'input_payload', 'output_payload', 'input_tokens',
        'output_tokens', 'estimated_cost_micros', 'duration_ms',
        'error_message', 'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'input_payload' => 'array',
            'output_payload' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class);
    }

    public function promptTemplate(): BelongsTo
    {
        return $this->belongsTo(AiPromptTemplate::class);
    }
}
