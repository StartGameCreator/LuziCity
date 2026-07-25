<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PerformanceIndexesTest extends TestCase
{
    use RefreshDatabase;

    public function test_performance_indexes_exist_after_migrations(): void
    {
        $expected = [
            'categories' => ['categories_active_sort_name_idx'],
            'rss_feeds' => ['rss_feeds_active_sort_name_idx'],
            'news_articles' => ['news_carousel_publication_idx'],
            'media_banners' => ['media_banners_active_type_sort_idx'],
            'radio_requests' => ['radio_room_visibility_created_idx', 'radio_status_created_idx'],
            'vehicle_listings' => ['vehicles_public_featured_idx', 'vehicles_public_popular_idx'],
            'real_estate_listings' => ['properties_public_featured_idx'],
            'ad_campaigns' => ['ad_campaigns_delivery_window_idx'],
        ];

        foreach ($expected as $table => $indexes) {
            $this->assertTrue(Schema::hasTable($table), "Tabela ausente: {$table}");

            foreach ($indexes as $index) {
                $this->assertTrue(
                    Schema::hasIndex($table, $index),
                    "Indice ausente: {$index} em {$table}"
                );
            }
        }
    }
}
