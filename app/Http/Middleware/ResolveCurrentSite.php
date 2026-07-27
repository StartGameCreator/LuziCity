<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentSite
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->forgetInstance('currentSite');
        if (Schema::hasTable('sites')) {
            $host = strtolower($request->getHost());
            $site = Site::query()->where('is_active', true)->with(['domains', 'settings'])
                ->whereHas('domains', fn ($query) => $query->where('domain', $host))
                ->first()
                ?? Site::query()->where('is_active', true)->where('is_default', true)
                    ->with(['domains', 'settings'])->first();

            if ($site) {
                app()->instance('currentSite', $site);
                $request->attributes->set('site', $site);
                view()->share('currentSite', $site);
                if ($request->is('admin', 'admin/*') && $request->user() && ! $request->user()->canAccessSite($site)) {
                    abort(403, 'Usuário sem acesso ao site atual.');
                }
            }
        }

        return $next($request);
    }
}
