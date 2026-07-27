<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintTemplateAdSlot extends Model
{
    protected $fillable = ['print_template_id', 'name', 'page_type', 'placement', 'size', 'position'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(PrintTemplate::class, 'print_template_id');
    }
}
