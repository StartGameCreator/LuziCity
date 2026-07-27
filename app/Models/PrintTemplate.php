<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintTemplate extends Model
{
    use BelongsToCurrentSite;

    protected $fillable = [
        'site_id', 'name', 'cover_style', 'cover_columns', 'internal_style',
        'internal_columns', 'credits', 'show_page_numbers', 'is_default',
    ];

    protected function casts(): array
    {
        return ['show_page_numbers' => 'boolean', 'is_default' => 'boolean'];
    }

    public function adSlots(): HasMany
    {
        return $this->hasMany(PrintTemplateAdSlot::class)->orderBy('position');
    }

    public function editions(): HasMany
    {
        return $this->hasMany(PrintEdition::class);
    }
}
