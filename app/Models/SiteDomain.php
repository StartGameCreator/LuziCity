<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteDomain extends Model
{
    protected $fillable = ['site_id', 'domain', 'is_primary'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
