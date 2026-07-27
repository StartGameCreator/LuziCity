<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_pageviews', function (Blueprint $table): void {
            $table->foreignId('news_article_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->index(['news_article_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('analytics_pageviews', function (Blueprint $table): void {
            $table->dropForeign(['news_article_id']);
            $table->dropIndex(['news_article_id', 'viewed_at']);
            $table->dropColumn('news_article_id');
        });
    }
};
