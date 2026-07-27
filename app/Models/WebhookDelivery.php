<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'event_id', 'webhook_endpoint_id', 'event', 'payload', 'status', 'attempts',
        'response_status', 'response_body', 'error', 'delivered_at',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array', 'delivered_at' => 'datetime'];
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }
}
