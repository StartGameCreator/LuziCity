<?php

namespace App\Http\Controllers;

use App\Models\AdvertiserProfile;
use App\Models\NewsArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSponsoredContentController extends Controller
{
    public function index(Request $request): View
    {
        $query = NewsArticle::with(['sponsor', 'sponsorCampaign'])->where('is_sponsored', true);
        $query->when($request->filled('advertiser'), fn ($q) => $q->where('sponsor_advertiser_id', $request->integer('advertiser')));
        $query->when($request->filled('status'), function ($q) use ($request): void {
            match ($request->string('status')->toString()) {
                'pending' => $q->whereNull('sponsor_approved_at'),
                'approved' => $q->whereNotNull('sponsor_approved_at')->where('status', '!=', 'published'),
                'published' => $q->where('status', 'published'),
                default => null,
            };
        });

        return view('admin.sponsored-content.index', [
            'articles' => $query->latest()->paginate(20)->withQueryString(),
            'advertisers' => AdvertiserProfile::orderBy('company_name')->get(),
            'metrics' => [
                'total' => NewsArticle::where('is_sponsored', true)->count(),
                'pending' => NewsArticle::where('is_sponsored', true)->whereNull('sponsor_approved_at')->count(),
                'published' => NewsArticle::where('is_sponsored', true)->where('status', 'published')->count(),
                'views' => NewsArticle::where('is_sponsored', true)->sum('sponsored_views_count'),
            ],
        ]);
    }

    public function approve(NewsArticle $article): RedirectResponse
    {
        abort_unless($article->is_sponsored, 422, 'A notícia não é patrocinada.');
        $article->update(['sponsor_approved_by' => auth()->id(), 'sponsor_approved_at' => now()]);

        return back()->with('status', 'Conteúdo patrocinado aprovado comercialmente.');
    }

    public function revoke(NewsArticle $article): RedirectResponse
    {
        abort_if($article->status === 'published', 422, 'Despublique o conteúdo antes de revogar.');
        $article->update(['sponsor_approved_by' => null, 'sponsor_approved_at' => null]);

        return back()->with('status', 'Aprovação comercial revogada.');
    }
}
