<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\Site;
use App\Services\NewsDistributionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminNewsDistributionController extends Controller
{
    public function store(Request $request, NewsArticle $news, NewsDistributionService $service): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
        $data = $request->validate([
            'target_site_id' => ['required', 'integer', Rule::exists('sites', 'id')->where('is_active', true)],
            'mode' => ['required', Rule::in(['reference', 'copy'])],
        ]);
        $service->distribute($news, Site::findOrFail($data['target_site_id']), $data['mode'], $request->user());

        return back()->with('status', $data['mode'] === 'copy'
            ? 'Cópia criada como rascunho no site de destino.'
            : 'Notícia referenciada no site de destino.');
    }
}
