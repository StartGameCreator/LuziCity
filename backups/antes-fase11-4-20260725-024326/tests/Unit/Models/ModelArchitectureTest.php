<?php

namespace Tests\Unit\Models;

use App\Models\AdCampaign;
use App\Models\MediaBanner;
use App\Models\NewsArticle;
use App\Models\RssFeed;
use App\Models\RssImportedArticle;
use App\Models\SocialAccount;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelArchitectureTest extends TestCase
{
    #[Test]
    public function social_tokens_are_hidden_from_serialization(): void
    {
        $account = new SocialAccount([
            'provider' => 'google',
            'access_token' => 'secret-access',
            'refresh_token' => 'secret-refresh',
        ]);

        $serialized = $account->toArray();

        $this->assertArrayNotHasKey('access_token', $serialized);
        $this->assertArrayNotHasKey('refresh_token', $serialized);
    }

    #[Test]
    public function news_article_exposes_author_and_publisher_relationships(): void
    {
        $article = new NewsArticle();

        $this->assertInstanceOf(BelongsTo::class, $article->author());
        $this->assertInstanceOf(BelongsTo::class, $article->publisher());
    }

    #[Test]
    public function user_exposes_domain_collection_relationships(): void
    {
        $user = new User();

        $this->assertInstanceOf(HasMany::class, $user->authoredArticles());
        $this->assertInstanceOf(HasMany::class, $user->publishedArticles());
        $this->assertInstanceOf(HasMany::class, $user->adCampaigns());
    }

    #[Test]
    public function reusable_scopes_add_expected_filters(): void
    {
        $this->assertStringContainsString('status', NewsArticle::query()->published()->toSql());
        $this->assertStringContainsString('is_active', MediaBanner::query()->active()->toSql());
        $this->assertStringContainsString('url', RssFeed::query()->usable()->toSql());
        $this->assertStringContainsString('is_visible', RssImportedArticle::query()->visible()->toSql());
        $this->assertStringContainsString('is_active', User::query()->active()->toSql());
        $this->assertStringContainsString('starts_at', Subscription::query()->active()->toSql());
    }
}
