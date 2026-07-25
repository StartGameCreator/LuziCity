<?php

namespace App\Http\Controllers;

use App\Models\AiExecution;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAiLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AiExecution::with(['user','provider','promptTemplate'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('feature'), fn ($q) => $q->where('feature', $request->string('feature')))
            ->latest()->paginate(25)->withQueryString();
        return view('admin.ai.logs.index', compact('logs'));
    }

    public function show(AiExecution $execution): View
    {
        $execution->load(['user','provider','promptTemplate']);
        return view('admin.ai.logs.show', compact('execution'));
    }
}
