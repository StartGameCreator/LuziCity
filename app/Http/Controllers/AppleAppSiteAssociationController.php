<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class AppleAppSiteAssociationController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $appId = trim((string) config('mobile.ios.team_id')).'.'.trim((string) config('mobile.ios.bundle_id'));

        return response()->json([
            'applinks' => [
                'apps' => [],
                'details' => [[
                    'appID' => $appId,
                    'components' => [
                        ['/' => '/noticias/*', 'comment' => 'Notícias LuziCity'],
                        ['/' => '/*', 'comment' => 'Portal LuziCity'],
                    ],
                ]],
            ],
        ])->header('Content-Type', 'application/json')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
