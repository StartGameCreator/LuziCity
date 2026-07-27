<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialProposalItem extends Model
{
    protected $fillable = [
        'media_kit_format_id', 'description', 'quantity', 'unit_price', 'subtotal',
    ];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(CommercialProposal::class, 'commercial_proposal_id');
    }
}
