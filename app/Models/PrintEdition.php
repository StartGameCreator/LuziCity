<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCurrentSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintEdition extends Model
{
    use BelongsToCurrentSite;

    protected $fillable = [
        'site_id', 'created_by', 'print_template_id', 'title', 'edition_date',
        'pdf_format', 'bleed_mm', 'high_resolution_images', 'pdf_generated_at',
        'review_status', 'review_notes', 'pdf_page_count', 'approved_by', 'approved_at',
        'approved_pdf_path', 'approved_pdf_sha256',
    ];

    protected function casts(): array
    {
        return [
            'edition_date' => 'date',
            'bleed_mm' => 'decimal:1',
            'high_resolution_images' => 'boolean',
            'pdf_generated_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PrintTemplate::class, 'print_template_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->review_status === 'approved' && $this->approved_at !== null;
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PrintEditionSection::class)->orderBy('position');
    }
}
