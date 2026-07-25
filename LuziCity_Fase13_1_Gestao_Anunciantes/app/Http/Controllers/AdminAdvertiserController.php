<?php

namespace App\Http\Controllers;

use App\Models\AdvertiserProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAdvertiserController extends Controller
{
    private function guard(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }

    public function index(Request $request): View
    {
        $this->guard();
        $query = AdvertiserProfile::query()->with(['user', 'responsible'])->withCount(['contacts', 'documents']);
        $query->search($request->string('q')->toString());
        $query->when($request->filled('status'), fn ($q) => $q->where('commercial_status', $request->string('status')));

        $metrics = [
            'total' => AdvertiserProfile::count(),
            'active' => AdvertiserProfile::where('commercial_status', 'active')->count(),
            'prospects' => AdvertiserProfile::whereIn('commercial_status', ['prospect','contact','negotiation','proposal'])->count(),
            'expiring' => AdvertiserProfile::whereBetween('contract_ends_at', [today(), today()->addDays(30)])->count(),
            'contracted' => AdvertiserProfile::sum('contracted_revenue'),
            'expected' => AdvertiserProfile::sum('expected_revenue'),
        ];

        return view('admin.advertisers.index', [
            'advertisers' => $query->latest()->paginate(20)->withQueryString(),
            'metrics' => $metrics,
        ]);
    }

    public function create(): View
    {
        $this->guard();
        return view('admin.advertisers.form', ['advertiser' => new AdvertiserProfile(), 'responsibles' => User::active()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->guard();
        $data = $this->validated($request);
        $profile = DB::transaction(function () use ($data): AdvertiserProfile {
            $email = $data['email'];
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $data['trade_name'] ?: $data['legal_name'],
                'password' => Hash::make(Str::random(40)),
                'is_active' => true,
            ]);
            if (! $user->hasRole('Anunciante')) $user->assignRole('Anunciante');
            $data['user_id'] = $user->id;
            $data['company_name'] = $data['trade_name'] ?: $data['legal_name'];
            $profile = AdvertiserProfile::create($data);
            $profile->histories()->create(['user_id'=>auth()->id(),'type'=>'system','title'=>'Anunciante cadastrado','occurred_at'=>now()]);
            return $profile;
        });
        return redirect()->route('admin.advertisers.show', $profile)->with('status', 'Anunciante cadastrado com sucesso.');
    }

    public function show(AdvertiserProfile $advertiser): View
    {
        $this->guard();
        $advertiser->load(['user','responsible','contacts','addresses','documents.uploader','histories.user']);
        return view('admin.advertisers.show', compact('advertiser'));
    }

    public function edit(AdvertiserProfile $advertiser): View
    {
        $this->guard();
        return view('admin.advertisers.form', ['advertiser'=>$advertiser, 'responsibles'=>User::active()->orderBy('name')->get()]);
    }

    public function update(Request $request, AdvertiserProfile $advertiser): RedirectResponse
    {
        $this->guard();
        $data = $this->validated($request, $advertiser);
        $data['company_name'] = $data['trade_name'] ?: $data['legal_name'];
        $advertiser->update($data);
        $advertiser->histories()->create(['user_id'=>auth()->id(),'type'=>'system','title'=>'Cadastro atualizado','occurred_at'=>now()]);
        return redirect()->route('admin.advertisers.show', $advertiser)->with('status', 'Cadastro atualizado.');
    }

    private function validated(Request $request, ?AdvertiserProfile $advertiser = null): array
    {
        return $request->validate([
            'legal_name'=>'required|string|max:180','trade_name'=>'nullable|string|max:180',
            'document_number'=>'nullable|string|max:30','state_registration'=>'nullable|string|max:40',
            'municipal_registration'=>'nullable|string|max:40','segment'=>'nullable|string|max:100',
            'company_size'=>'nullable|in:mei,micro,small,medium,large','commercial_status'=>'required|in:prospect,contact,negotiation,proposal,contracted,active,inactive,cancelled',
            'responsible_user_id'=>'nullable|exists:users,id','contact_phone'=>'nullable|string|max:30',
            'whatsapp'=>'nullable|string|max:30','email'=>'required|email|max:180','website'=>'nullable|url|max:2048',
            'notes'=>'nullable|string|max:10000','contracted_revenue'=>'nullable|numeric|min:0',
            'expected_revenue'=>'nullable|numeric|min:0','contract_starts_at'=>'nullable|date',
            'contract_ends_at'=>'nullable|date|after_or_equal:contract_starts_at','is_active'=>'nullable|boolean',
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
