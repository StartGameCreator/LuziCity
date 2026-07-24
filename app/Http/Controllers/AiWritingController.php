<?php

namespace App\Http\Controllers;

use App\Services\AiWritingAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiWritingController extends Controller
{
    public function store(Request $request, AiWritingAssistant $assistant): JsonResponse
    {
        $data = $request->validate([
            'context' => ['required', 'in:about,news,news_summary,vehicle_ad,real_estate_ad'],
            'provider' => ['nullable', 'in:chatgpt,gemini,copilot'],
            'title' => ['nullable', 'string', 'max:180'],
            'brief' => ['required', 'string', 'max:8000'],
        ]);

        return response()->json($assistant->draftResult(
            $data['context'],
            $data['brief'],
            $data['title'] ?? null,
            $data['provider'] ?? null
        ));
    }
}
