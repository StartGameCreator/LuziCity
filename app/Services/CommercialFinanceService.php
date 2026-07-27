<?php

namespace App\Services;

use App\Models\CommercialInvoice;
use Illuminate\Support\Facades\DB;

class CommercialFinanceService
{
    public function reconcileOverdue(): int
    {
        return CommercialInvoice::overdue()->where('status', '!=', 'overdue')->update(['status' => 'overdue']);
    }

    public function recordPayment(CommercialInvoice $invoice, array $data): void
    {
        DB::transaction(function () use ($invoice, $data): void {
            $invoice->payments()->create($data);
            $paid = (float) $invoice->payments()->sum('amount');
            $invoice->update([
                'paid_amount' => $paid,
                'status' => $paid >= (float) $invoice->amount ? 'paid' : 'partial',
                'paid_at' => $paid >= (float) $invoice->amount ? ($data['paid_at'] ?? now()) : null,
            ]);
        });
    }

    public function renew(CommercialInvoice $invoice, int $userId): CommercialInvoice
    {
        $nextDue = match ($invoice->recurrence) {
            'quarterly' => $invoice->due_at->addMonths(3),
            'yearly' => $invoice->due_at->addYear(),
            default => $invoice->due_at->addMonth(),
        };

        return CommercialInvoice::create([
            'advertiser_profile_id' => $invoice->advertiser_profile_id,
            'commercial_proposal_id' => $invoice->commercial_proposal_id,
            'created_by' => $userId,
            'number' => 'COB-'.now()->format('YmdHis').'-'.$invoice->id,
            'description' => $invoice->description.' - renovação',
            'amount' => $invoice->amount,
            'issued_at' => today(),
            'due_at' => $nextDue,
            'is_recurring' => true,
            'recurrence' => $invoice->recurrence ?: 'monthly',
            'next_renewal_at' => match ($invoice->recurrence) {
                'quarterly' => $nextDue->copy()->addMonths(3),
                'yearly' => $nextDue->copy()->addYear(),
                default => $nextDue->copy()->addMonth(),
            },
        ]);
    }
}
