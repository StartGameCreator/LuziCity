<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertiserProfile extends Model
{
    protected $fillable = ['user_id', 'company_name', 'document_number', 'contact_phone', 'website'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
