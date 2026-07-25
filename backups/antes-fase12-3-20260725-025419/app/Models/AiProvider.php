<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProvider extends Model
{
    protected $fillable = [
        'slug', 'name', 'driver', 'is_enabled', 'model', 'endpoint',
        'monthly_budget_cents', 'daily_request_limit', 'monthly_request_limit', 'metadata',
        'priority', 'timeout_seconds', 'retry_attempts', 'input_cost_per_million',
        'output_cost_per_million', 'last_checked_at', 'health_status', 'last_failure_message',
        'consecutive_failures', 'circuit_open_until', 'fallback_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'monthly_budget_cents' => 'integer',
            'daily_request_limit' => 'integer',
            'metadata' => 'array',
            'fallback_enabled' => 'boolean',
            'last_checked_at' => 'datetime',
            'circuit_open_until' => 'datetime',
            'input_cost_per_million' => 'decimal:6',
            'output_cost_per_million' => 'decimal:6',
        ];
    }

    public function executions(): HasMany
    {
        return $this->hasMany(AiExecution::class, 'provider_id');
    }
}
