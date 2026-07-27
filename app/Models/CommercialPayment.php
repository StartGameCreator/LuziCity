<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommercialPayment extends Model
{
    protected $fillable = ['recorded_by', 'amount', 'paid_at', 'method', 'reference', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CommercialInvoice::class, 'commercial_invoice_id');
    }
}
