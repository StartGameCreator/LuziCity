<?php

namespace Tests\Integration;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_observer_invalidates_cached_api_query_end_to_end(): void
    {
        $this->getJson('/api/v1/categories?per_page=100')
            ->assertOk()
            ->assertJsonMissing(['slug' => 'integration-cache']);

        Category::create([
            'name' => 'Integração Cache',
            'slug' => 'integration-cache',
            'sort_order' => 999,
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/categories?per_page=100')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'integration-cache']);
    }
}
