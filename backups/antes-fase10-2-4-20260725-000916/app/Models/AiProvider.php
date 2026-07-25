<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProvider extends Model
{
    protected $fillable = [
        'slug', 'name', 'driver', 'is_enabled', 'model', 'endpoint',
        'monthly_budget_cents', 'daily_request_limit', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'monthly_budget_cents' => 'integer',
            'daily_request_limit' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function executions(): HasMany
    {
        return $this->hasMany(AiExecution::class, 'provider_id');
    }
}
