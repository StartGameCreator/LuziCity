<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->index(['is_active', 'sort_order', 'name'], 'categories_active_sort_name_idx');
        });

        Schema::table('rss_feeds', function (Blueprint $table): void {
            $table->index(['is_active', 'sort_order', 'name'], 'rss_feeds_active_sort_name_idx');
        });

        Schema::table('news_articles', function (Blueprint $table): void {
            $table->index(
                ['status', 'show_in_carousel', 'carousel_type', 'carousel_sort_order', 'published_at'],
                'news_carousel_publication_idx'
            );
        });

        Schema::table('media_banners', function (Blueprint $table): void {
            $table->index(['is_active', 'type', 'sort_order', 'title'], 'media_banners_active_type_sort_idx');
        });

        Schema::table('radio_requests', function (Blueprint $table): void {
            $table->index(['category', 'is_private', 'created_at'], 'radio_room_visibility_created_idx');
            $table->index(['status', 'created_at'], 'radio_status_created_idx');
        });

        Schema::table('vehicle_listings', function (Blueprint $table): void {
            $table->index(
                ['status', 'vehicle_type', 'is_featured', 'published_at'],
                'vehicles_public_featured_idx'
            );
            $table->index(
                ['status', 'vehicle_type', 'views_count', 'published_at'],
                'vehicles_public_popular_idx'
            );
        });

        Schema::table('real_estate_listings', function (Blueprint $table): void {
            $table->index(['status', 'is_featured', 'published_at'], 'properties_public_featured_idx');
        });

        Schema::table('ad_campaigns', function (Blueprint $table): void {
            $table->index(['status', 'placement', 'starts_at', 'ends_at'], 'ad_campaigns_delivery_window_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ad_campaigns', function (Blueprint $table): void {
            $table->dropIndex('ad_campaigns_delivery_window_idx');
        });

        Schema::table('real_estate_listings', function (Blueprint $table): void {
            $table->dropIndex('properties_public_featured_idx');
        });

        Schema::table('vehicle_listings', function (Blueprint $table): void {
            $table->dropIndex('vehicles_public_featured_idx');
            $table->dropIndex('vehicles_public_popular_idx');
        });

        Schema::table('radio_requests', function (Blueprint $table): void {
            $table->dropIndex('radio_room_visibility_created_idx');
            $table->dropIndex('radio_status_created_idx');
        });

        Schema::table('media_banners', function (Blueprint $table): void {
            $table->dropIndex('media_banners_active_type_sort_idx');
        });

        Schema::table('news_articles', function (Blueprint $table): void {
            $table->dropIndex('news_carousel_publication_idx');
        });

        Schema::table('rss_feeds', function (Blueprint $table): void {
            $table->dropIndex('rss_feeds_active_sort_name_idx');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex('categories_active_sort_name_idx');
        });
    }
};
