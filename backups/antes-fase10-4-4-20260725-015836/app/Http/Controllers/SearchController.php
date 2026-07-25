<?php

namespace App\Http\Controllers;

use App\Services\Search\UnifiedSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(private readonly UnifiedSearchService $search)
    {
    }

    public function index(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:all,news,properties,vehicles'],
        ]);

        $term = trim((string) ($data['q'] ?? ''));
        $type = (string) ($data['type'] ?? 'all');
        $results = $this->search->search($term, $type);

        return view('search.index', [
            'term' => $term,
            'type' => $type,
            'results' => $results,
            'total' => collect($results)->sum(fn ($items) => $items->count()),
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);

        return response()->json([
            'data' => $this->search->suggestions((string) $data['q']),
        ])->header('Cache-Control', 'public, max-age=60');
    }
}
