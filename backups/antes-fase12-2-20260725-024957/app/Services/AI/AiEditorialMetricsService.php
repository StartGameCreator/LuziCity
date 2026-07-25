<?php

namespace App\Services\AI;

use App\Models\AiExecution;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AiEditorialMetricsService
{
    public function dashboard(array $filters, ?int $restrictedUserId = null): array
    {
        [$from, $to] = $this->period($filters);
        $base = $this->query($filters, $restrictedUserId)->whereBetween('ai_executions.created_at', [$from, $to]);
        $completed = (clone $base)->where('status', 'completed');
        $total = (clone $base)->count();
        $inputTokens = (int) (clone $base)->sum('input_tokens');
        $outputTokens = (int) (clone $base)->sum('output_tokens');
        $cost = Schema::hasColumn('ai_executions', 'estimated_cost')
            ? (string) ((clone $base)->selectRaw('COALESCE(SUM(estimated_cost), 0) AS value')->value('value') ?? '0')
            : number_format(((int) (clone $base)->sum('estimated_cost_micros')) / 1_000_000, 6, '.', '');

        $provider = (clone $base)
            ->leftJoin('ai_providers', 'ai_providers.id', '=', 'ai_executions.provider_id')
            ->selectRaw("COALESCE(ai_providers.name, 'Sem provedor') AS provider_name, COUNT(*) AS aggregate")
            ->groupBy('ai_providers.id', 'ai_providers.name')
            ->orderByDesc('aggregate')
            ->first();

        $latest = (clone $base)
            ->with(['provider:id,name,slug', 'promptTemplate:id,name,key', 'user:id,name'])
            ->latest('ai_executions.created_at')
            ->limit(15)
            ->get();

        $errors = (clone $base)
            ->where('status', 'failed')
            ->with(['provider:id,name,slug', 'user:id,name'])
            ->latest('ai_executions.created_at')
            ->limit(10)
            ->get();

        $newsGenerated = 0;
        if (Schema::hasTable('news_articles') && Schema::hasColumn('news_articles', 'ai_execution_id')) {
            $newsGenerated = DB::table('news_articles')
                ->join('ai_executions', 'ai_executions.id', '=', 'news_articles.ai_execution_id')
                ->whereBetween('ai_executions.created_at', [$from, $to])
                ->when($restrictedUserId, fn ($query) => $query->where('ai_executions.user_id', $restrictedUserId))
                ->count();
        }

        return [
            'period' => ['from' => $from, 'to' => $to],
            'summary' => [
                'total' => $total,
                'completed' => (clone $completed)->count(),
                'failed' => (clone $base)->where('status', 'failed')->count(),
                'success_rate' => $total > 0 ? round(((clone $completed)->count() / $total) * 100, 1) : 0,
                'average_ms' => (int) round((float) ((clone $completed)->avg('duration_ms') ?? 0)),
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'total_tokens' => $inputTokens + $outputTokens,
                'estimated_cost' => $cost,
                'provider' => $provider?->provider_name ?? 'Nenhum',
                'news_generated' => $newsGenerated,
            ],
            'today' => (clone $this->query($filters, $restrictedUserId))->whereDate('ai_executions.created_at', today())->count(),
            'week' => (clone $this->query($filters, $restrictedUserId))->whereBetween('ai_executions.created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'month' => (clone $this->query($filters, $restrictedUserId))->whereBetween('ai_executions.created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'latest' => $latest,
            'errors' => $errors,
        ];
    }

    private function query(array $filters, ?int $restrictedUserId): Builder
    {
        return AiExecution::query()
            ->when($restrictedUserId, fn (Builder $query) => $query->where('ai_executions.user_id', $restrictedUserId))
            ->when(!$restrictedUserId && !empty($filters['user_id']), fn (Builder $query) => $query->where('ai_executions.user_id', $filters['user_id']))
            ->when(!empty($filters['provider_id']), fn (Builder $query) => $query->where('ai_executions.provider_id', $filters['provider_id']));
    }

    private function period(array $filters): array
    {
        $period = $filters['period'] ?? 'month';
        $now = CarbonImmutable::now();

        return match ($period) {
            'today' => [$now->startOfDay(), $now->endOfDay()],
            'week' => [$now->startOfWeek(), $now->endOfWeek()],
            'custom' => [
                CarbonImmutable::parse($filters['from'] ?? $now->startOfMonth())->startOfDay(),
                CarbonImmutable::parse($filters['to'] ?? $now)->endOfDay(),
            ],
            default => [$now->startOfMonth(), $now->endOfMonth()],
        };
    }
}
