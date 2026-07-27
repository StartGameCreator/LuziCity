<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsConsent extends Model
{
    protected $fillable = ['user_id', 'session_hash', 'choice', 'policy_version', 'consented_at'];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime'];
    }
}
