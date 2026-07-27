<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    use BelongsToCurrentSite;

    protected $fillable = ['user_id', 'site_id', 'token', 'device_name', 'platform', 'last_seen_at'];

    protected $casts = ['last_seen_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
