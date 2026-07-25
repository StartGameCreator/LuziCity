<?php

namespace App\Http\Controllers;

use App\Models\AiExecution;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAiCostController extends Controller
{
    public function __invoke(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();
        $base = AiExecution::query()->whereBetween('created_at', [$from, $to]);
        $summary = (clone $base)->selectRaw('COUNT(*) total, COALESCE(SUM(total_tokens),0) tokens, COALESCE(SUM(estimated_cost_micros),0) cost_micros, COALESCE(AVG(duration_ms),0) average_ms, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) failures', ['failed'])->first();
        $groups = fn (string $column) => (clone $base)->selectRaw("$column label, COUNT(*) executions, COALESCE(SUM(total_tokens),0) tokens, COALESCE(SUM(estimated_cost_micros),0) cost_micros")->groupBy($column)->orderByDesc('cost_micros')->get();
        return view('admin.ai.costs.index', compact('from','to','summary') + ['byFeature' => $groups('feature'), 'byProvider' => $groups('provider_id'), 'byUser' => $groups('user_id')]);
    }
}
