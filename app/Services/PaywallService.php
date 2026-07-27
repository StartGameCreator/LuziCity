<?php

namespace App\Services;

use App\Models\NewsArticle;
use App\Models\PaywallAccess;
use App\Models\PaywallCategoryRule;
use App\Models\User;

class PaywallService
{
    public function evaluate(NewsArticle $article, ?User $user): array
    {
        $rule = $article->category_id
            ? PaywallCategoryRule::with('minimumPlan')->where('category_id', $article->category_id)->where('is_enabled', true)->first()
            : null;
        $protected = $article->is_premium || $rule !== null;
        if (! $protected) {
            return ['allowed' => true, 'protected' => false, 'preview' => null, 'remaining' => null];
        }

        $plan = $user?->subscription?->isActive() ? $user->subscription->plan : null;
        $previewLength = $rule?->preview_characters ?? $plan?->preview_characters ?? 600;
        if (! $user || ! $plan || ! $plan->can_access_premium) {
            return ['allowed' => false, 'protected' => true, 'preview' => str($article->body)->limit($previewLength)->toString(), 'remaining' => 0];
        }

        if ($rule?->minimumPlan && $plan->display_order < $rule->minimumPlan->display_order) {
            return ['allowed' => false, 'protected' => true, 'preview' => str($article->body)->limit($previewLength)->toString(), 'remaining' => 0];
        }

        $limit = $plan->monthly_article_limit;
        $period = today()->startOfMonth()->toDateString();
        $used = PaywallAccess::where('user_id', $user->id)->whereDate('period_month', $period)->count();
        $alreadyRead = PaywallAccess::where(['user_id' => $user->id, 'news_article_id' => $article->id, 'period_month' => $period])->exists();
        if ($limit !== null && $used >= $limit && ! $alreadyRead) {
            return ['allowed' => false, 'protected' => true, 'preview' => str($article->body)->limit($previewLength)->toString(), 'remaining' => 0];
        }
        PaywallAccess::firstOrCreate(
            ['user_id' => $user->id, 'news_article_id' => $article->id, 'period_month' => $period],
            ['accessed_at' => now()]
        );

        return ['allowed' => true, 'protected' => true, 'preview' => null, 'remaining' => $limit === null ? null : max(0, $limit - $used - ($alreadyRead ? 0 : 1))];
    }
}
