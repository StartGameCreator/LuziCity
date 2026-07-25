<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_imported_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rss_feed_id')->nullable()->constrained('rss_feeds')->nullOnDelete();
            $table->string('source_name')->nullable();
            $table->string('category')->nullable();
            $table->string('title');
            $table->string('original_url', 2048)->unique();
            $table->string('guid', 2048)->nullable();
            $table->text('excerpt')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['is_visible', 'published_at']);
            $table->index(['rss_feed_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_imported_articles');
    }
};
