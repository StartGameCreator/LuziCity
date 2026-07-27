<?php

namespace App\Http\Controllers;

use App\Models\AdvertiserProfile;
use App\Models\CommercialProposal;
use App\Models\MediaKitFormat;
use App\Services\SimplePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AdminMediaKitController extends Controller
{
    public function index(): View
    {
        return view('admin.media-kit.index', [
            'formats' => MediaKitFormat::orderBy('display_order')->get(),
            'proposals' => CommercialProposal::with('advertiser')->latest()->paginate(15),
            'advertisers' => AdvertiserProfile::where('is_active', true)->orderBy('company_name')->get(),
        ]);
    }

    public function storeFormat(Request $request): RedirectResponse
    {
        MediaKitFormat::create($this->formatData($request));

        return back()->with('status', 'Formato adicionado ao mídia kit.');
    }

    public function updateFormat(Request $request, MediaKitFormat $format): RedirectResponse
    {
        $format->update($this->formatData($request));

        return back()->with('status', 'Formato atualizado.');
    }

    public function storeProposal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'advertiser_profile_id' => ['required', 'exists:advertiser_profiles,id'],
            'title' => ['required', 'string', 'max:180'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'format_ids' => ['required', 'array', 'min:1'],
            'format_ids.*' => ['exists:media_kit_formats,id'],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['integer', 'min:1', 'max:999'],
        ]);

        $proposal = DB::transaction(function () use ($data): CommercialProposal {
            $proposal = CommercialProposal::create([
                'advertiser_profile_id' => $data['advertiser_profile_id'],
                'created_by' => auth()->id(),
                'number' => 'PROP-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'title' => $data['title'],
                'valid_until' => $data['valid_until'] ?? null,
                'discount' => $data['discount'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);
            $subtotal = 0;
            foreach ($data['format_ids'] as $formatId) {
                $format = MediaKitFormat::findOrFail($formatId);
                $quantity = (int) ($data['quantities'][$formatId] ?? 1);
                $lineTotal = (float) $format->price * $quantity;
                $proposal->items()->create([
                    'media_kit_format_id' => $format->id,
                    'description' => $format->name.' - '.$format->placement,
                    'quantity' => $quantity,
                    'unit_price' => $format->price,
                    'subtotal' => $lineTotal,
                ]);
                $subtotal += $lineTotal;
            }
            $proposal->update(['total' => max(0, $subtotal - (float) $proposal->discount)]);

            return $proposal;
        });

        return redirect()->route('admin.media-kit.proposals.show', $proposal)
            ->with('status', 'Proposta criada.');
    }

    public function showProposal(CommercialProposal $proposal): View
    {
        return view('admin.media-kit.proposal', ['proposal' => $proposal->load('advertiser', 'items')]);
    }

    public function approveProposal(CommercialProposal $proposal): RedirectResponse
    {
        $proposal->update([
            'status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now(),
        ]);

        return back()->with('status', 'Proposta aprovada.');
    }

    public function mediaKitPdf(SimplePdfService $pdf): Response
    {
        $lines = ['Solucoes comerciais e formatos publicitarios', ''];
        foreach (MediaKitFormat::where('is_active', true)->orderBy('display_order')->get() as $format) {
            $lines[] = $format->name.' | '.$format->placement.' | '.$format->dimensions;
            $lines[] = $format->description;
            $lines[] = 'Investimento: R$ '.number_format((float) $format->price, 2, ',', '.').' ('.strtoupper($format->billing_model).')';
            $lines[] = '';
        }

        return $this->pdfResponse($pdf->document('LuziCity - Midia Kit', $lines), 'luzicity-midia-kit.pdf');
    }

    public function proposalPdf(CommercialProposal $proposal, SimplePdfService $pdf): Response
    {
        $proposal->load('advertiser', 'items');
        $lines = [
            'Proposta: '.$proposal->number,
            'Cliente: '.$proposal->advertiser->company_name,
            'Titulo: '.$proposal->title,
            'Validade: '.($proposal->valid_until?->format('d/m/Y') ?: 'A combinar'),
            '',
        ];
        foreach ($proposal->items as $item) {
            $lines[] = "{$item->description} | {$item->quantity} x R$ ".number_format((float) $item->unit_price, 2, ',', '.').' = R$ '.number_format((float) $item->subtotal, 2, ',', '.');
        }
        $lines[] = '';
        $lines[] = 'Desconto: R$ '.number_format((float) $proposal->discount, 2, ',', '.');
        $lines[] = 'TOTAL: R$ '.number_format((float) $proposal->total, 2, ',', '.');
        $lines[] = 'Status: '.strtoupper($proposal->status);
        $lines[] = $proposal->notes;

        return $this->pdfResponse($pdf->document('Proposta Comercial LuziCity', $lines), strtolower($proposal->number).'.pdf');
    }

    private function formatData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'], 'placement' => ['required', 'string', 'max:80'],
            'dimensions' => ['nullable', 'string', 'max:80'], 'description' => ['nullable', 'string', 'max:3000'],
            'price' => ['required', 'numeric', 'min:0'], 'billing_model' => ['required', 'in:fixed,cpm,cpc'],
            'display_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['display_order'] = $data['display_order'] ?? 0;

        return $data;
    }

    private function pdfResponse(string $contents, string $filename): Response
    {
        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
