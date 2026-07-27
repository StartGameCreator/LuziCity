<?php

namespace Tests\Feature;

use App\Models\AdvertiserProfile;
use App\Models\NewsArticle;
use App\Models\User;
use App\Services\NewsEditorialWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SponsoredContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_sponsored_content_requires_commercial_approval_before_publication(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        $advertiserUser = User::factory()->create();
        $advertiser = AdvertiserProfile::create([
            'user_id' => $advertiserUser->id, 'company_name' => 'Patrocinador', 'legal_name' => 'Patrocinador Ltda',
            'email' => 'patrocinador@example.com', 'commercial_status' => 'active', 'is_active' => true,
        ]);
        $article = NewsArticle::create([
            'author_id' => $admin->id, 'title' => 'Informe publicitário', 'slug' => 'informe-publicitario',
            'body' => 'Conteúdo da campanha.', 'status' => 'draft', 'workflow_status' => 'approved',
            'is_sponsored' => true, 'sponsor_advertiser_id' => $advertiser->id,
            'sponsor_label' => 'Publicidade', 'sponsor_starts_at' => now()->subHour(), 'sponsor_ends_at' => now()->addDay(),
        ]);

        $this->expectException(ValidationException::class);
        app(NewsEditorialWorkflowService::class)->transition($article, $admin, 'publish', null, null);
    }

    public function test_approved_sponsored_content_is_labeled_and_counted(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        $advertiserUser = User::factory()->create();
        $advertiser = AdvertiserProfile::create([
            'user_id' => $advertiserUser->id, 'company_name' => 'Marca Local', 'legal_name' => 'Marca Local Ltda',
            'email' => 'marca@example.com', 'commercial_status' => 'active', 'is_active' => true,
        ]);
        $article = NewsArticle::create([
            'author_id' => $admin->id, 'title' => 'Especial da marca', 'slug' => 'especial-marca', 'body' => 'Texto.',
            'status' => 'published', 'workflow_status' => 'published', 'is_sponsored' => true,
            'sponsor_advertiser_id' => $advertiser->id, 'sponsor_label' => 'Conteúdo patrocinado',
            'sponsor_approved_by' => $admin->id, 'sponsor_approved_at' => now(),
        ]);

        $this->get(route('news.show', $article))->assertOk()->assertSee('Conteúdo patrocinado')->assertSee('Marca Local');
        $this->assertSame(1, $article->fresh()->sponsored_views_count);
    }
}
