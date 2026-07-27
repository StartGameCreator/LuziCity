<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->boolean('is_sponsored')->default(false);
            $table->foreignId('sponsor_advertiser_id')->nullable()->constrained('advertiser_profiles')->nullOnDelete();
            $table->foreignId('sponsor_campaign_id')->nullable()->constrained('ad_campaigns')->nullOnDelete();
            $table->string('sponsor_label', 100)->default('Conteúdo patrocinado');
            $table->timestamp('sponsor_starts_at')->nullable();
            $table->timestamp('sponsor_ends_at')->nullable();
            $table->foreignId('sponsor_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sponsor_approved_at')->nullable();
            $table->unsignedBigInteger('sponsored_views_count')->default(0);
            $table->index(['is_sponsored', 'sponsor_starts_at', 'sponsor_ends_at'], 'news_sponsored_period_index');
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->dropIndex('news_sponsored_period_index');
            $table->dropForeign(['sponsor_advertiser_id']);
            $table->dropForeign(['sponsor_campaign_id']);
            $table->dropForeign(['sponsor_approved_by']);
            $table->dropColumn([
                'is_sponsored', 'sponsor_advertiser_id', 'sponsor_campaign_id', 'sponsor_label',
                'sponsor_starts_at', 'sponsor_ends_at', 'sponsor_approved_by',
                'sponsor_approved_at', 'sponsored_views_count',
            ]);
        });
    }
};
