<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class AndroidAppLinkController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => config('mobile.android.package'),
                'sha256_cert_fingerprints' => config('mobile.android.sha256_fingerprints'),
            ],
        ]])->header('Cache-Control', 'public, max-age=3600');
    }
}
