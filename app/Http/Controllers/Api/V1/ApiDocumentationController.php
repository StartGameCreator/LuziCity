<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiDocumentationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'name' => 'LuziCity Public API', 'version' => '1.0.0',
            'openapi' => route('api.v1.docs.openapi'),
            'guide' => route('api.v1.docs.guide'),
        ]);
    }

    public function openapi(): Response
    {
        return response(file_get_contents(base_path('docs/api/openapi.yaml')), 200)
            ->header('Content-Type', 'application/yaml; charset=UTF-8');
    }

    public function guide(): Response
    {
        return response(file_get_contents(base_path('docs/api/README.md')), 200)
            ->header('Content-Type', 'text/markdown; charset=UTF-8');
    }
}
