<?php

namespace App\Services;

use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class NewsEditorialWorkflowService
{
    public const ACTIONS = [
        'submit_review' => 'journalist_review', 'submit_verification' => 'verification',
        'submit_editorial' => 'editorial_review', 'approve' => 'approved',
        'reject' => 'rejected', 'request_changes' => 'changes_requested',
        'schedule' => 'scheduled', 'publish' => 'published',
    ];

    public function snapshot(NewsArticle $article, ?User $user): void
    {
        $last = (int) $article->versions()->max('version');
        $article->versions()->create([
            'created_by' => $user?->id, 'version' => $last + 1,
            'title' => $article->title, 'subtitle' => $article->subtitle,
            'excerpt' => $article->excerpt, 'body' => $article->body,
            'metadata' => ['workflow_status' => $article->workflow_status, 'ai_execution_id' => $article->ai_execution_id],
        ]);
    }

    public function transition(NewsArticle $article, User $user, string $action, ?string $note, ?string $scheduledFor): void
    {
        $to = self::ACTIONS[$action] ?? throw ValidationException::withMessages(['action' => 'Ação inválida.']);
        $from = $article->workflow_status ?: 'draft';
        if (in_array($action, ['approve', 'schedule', 'publish'], true) && ! $user->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403);
        }
        if ($action === 'publish' && ! in_array($from, ['approved', 'scheduled'], true)) {
            throw ValidationException::withMessages(['action' => 'Aprovação humana obrigatória antes da publicação.']);
        }
        if ($action === 'publish' && $article->is_sponsored && ! $article->sponsor_approved_at) {
            throw ValidationException::withMessages(['action' => 'Conteúdo patrocinado exige aprovação comercial antes da publicação.']);
        }
        if ($action === 'schedule' && blank($scheduledFor)) {
            throw ValidationException::withMessages(['scheduled_for' => 'Informe a data do agendamento.']);
        }

        $data = ['workflow_status' => $to, 'status' => 'draft'];
        if ($action === 'approve') {
            $data += ['approved_by' => $user->id, 'approved_at' => now()];
        }
        if ($action === 'schedule') {
            $data['scheduled_for'] = $scheduledFor;
        }
        if ($action === 'publish') {
            $data['status'] = 'published';
            $data['published_by'] = $user->id;
            $data['published_at'] = now();
        }
        $article->update($data);
        $article->editorialReviews()->create([
            'user_id' => $user->id, 'action' => $action,
            'from_status' => $from, 'to_status' => $to, 'note' => $note,
        ]);
    }
}
