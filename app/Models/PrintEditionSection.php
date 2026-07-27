<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintEditionSection extends Model
{
    protected $fillable = ['print_edition_id', 'name', 'position'];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(PrintEdition::class, 'print_edition_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrintEditionItem::class)->orderBy('position');
    }
}
