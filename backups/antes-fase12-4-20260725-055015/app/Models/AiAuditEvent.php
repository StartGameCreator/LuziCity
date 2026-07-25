<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAuditEvent extends Model
{
    protected $fillable = ['execution_id', 'user_id', 'provider_id', 'prompt_template_id', 'article_id', 'action', 'model', 'safe_parameters', 'result_status', 'error_message', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['safe_parameters' => 'array'];
    }

    public function execution(): BelongsTo { return $this->belongsTo(AiExecution::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function provider(): BelongsTo { return $this->belongsTo(AiProvider::class); }
    public function promptTemplate(): BelongsTo { return $this->belongsTo(AiPromptTemplate::class); }
}
