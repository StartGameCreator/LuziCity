<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->foreignId('origin_article_id')->nullable()->after('site_id')->constrained('news_articles')->nullOnDelete();
            $table->foreignId('origin_site_id')->nullable()->after('origin_article_id')->constrained('sites')->nullOnDelete();
        });
        Schema::create('news_distributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_article_id')->constrained('news_articles')->cascadeOnDelete();
            $table->foreignId('source_site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('target_site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('mode', 20);
            $table->foreignId('target_article_id')->nullable()->constrained('news_articles')->nullOnDelete();
            $table->foreignId('distributed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['source_article_id', 'target_site_id']);
            $table->index(['target_site_id', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_distributions');
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('origin_article_id');
            $table->dropConstrainedForeignId('origin_site_id');
        });
    }
};
