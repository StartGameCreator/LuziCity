<?php

namespace App\Http\Controllers;

use App\Models\AdvertiserProfile;
use App\Models\CommercialInvoice;
use App\Models\CommercialProposal;
use App\Services\CommercialFinanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCommercialFinanceController extends Controller
{
    public function index(Request $request, CommercialFinanceService $finance): View
    {
        $finance->reconcileOverdue();
        $query = CommercialInvoice::with('advertiser');
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));
        $query->when($request->filled('advertiser'), fn ($q) => $q->where('advertiser_profile_id', $request->integer('advertiser')));

        return view('admin.commercial-finance.index', [
            'invoices' => $query->orderBy('due_at')->paginate(20)->withQueryString(),
            'advertisers' => AdvertiserProfile::where('is_active', true)->orderBy('company_name')->get(),
            'proposals' => CommercialProposal::where('status', 'approved')->latest()->get(),
            'metrics' => [
                'receivable' => CommercialInvoice::whereIn('status', ['pending', 'partial', 'overdue'])->sum('amount')
                    - CommercialInvoice::whereIn('status', ['pending', 'partial', 'overdue'])->sum('paid_amount'),
                'received' => CommercialInvoice::sum('paid_amount'),
                'overdue' => CommercialInvoice::overdue()->sum('amount') - CommercialInvoice::overdue()->sum('paid_amount'),
                'dueSoon' => CommercialInvoice::whereIn('status', ['pending', 'partial'])
                    ->whereBetween('due_at', [today(), today()->addDays(7)])->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'advertiser_profile_id' => ['required', 'exists:advertiser_profiles,id'],
            'commercial_proposal_id' => ['nullable', 'exists:commercial_proposals,id'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'issued_at' => ['required', 'date'],
            'due_at' => ['required', 'date', 'after_or_equal:issued_at'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence' => ['nullable', 'required_if:is_recurring,1', 'in:monthly,quarterly,yearly'],
            'next_renewal_at' => ['nullable', 'date', 'after_or_equal:due_at'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ]);
        $data['created_by'] = auth()->id();
        $data['number'] = 'COB-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        $data['is_recurring'] = $request->boolean('is_recurring');
        CommercialInvoice::create($data);

        return back()->with('status', 'Cobrança criada.');
    }

    public function show(CommercialInvoice $invoice): View
    {
        return view('admin.commercial-finance.show', [
            'invoice' => $invoice->load('advertiser', 'proposal', 'payments'),
        ]);
    }

    public function payment(Request $request, CommercialInvoice $invoice, CommercialFinanceService $finance): RedirectResponse
    {
        abort_if(in_array($invoice->status, ['paid', 'cancelled'], true), 422, 'Cobrança encerrada.');
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$invoice->balance],
            'paid_at' => ['required', 'date'], 'method' => ['required', 'in:pix,boleto,card,transfer,cash,other'],
            'reference' => ['nullable', 'string', 'max:120'], 'notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $finance->recordPayment($invoice, $data + ['recorded_by' => auth()->id()]);

        return back()->with('status', 'Pagamento registrado.');
    }

    public function renew(CommercialInvoice $invoice, CommercialFinanceService $finance): RedirectResponse
    {
        abort_unless($invoice->is_recurring, 422, 'Cobrança não recorrente.');
        $renewal = $finance->renew($invoice, auth()->id());
        $invoice->update(['next_renewal_at' => $renewal->next_renewal_at]);

        return redirect()->route('admin.commercial-finance.show', $renewal)->with('status', 'Renovação criada.');
    }

    public function cancel(CommercialInvoice $invoice): RedirectResponse
    {
        abort_if($invoice->status === 'paid', 422, 'Pagamento já concluído.');
        $invoice->update(['status' => 'cancelled']);

        return back()->with('status', 'Cobrança cancelada.');
    }
}
