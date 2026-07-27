<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyDataRequest extends Model
{
    protected $fillable = ['user_id', 'session_hash', 'type', 'status', 'completed_at'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }
}
