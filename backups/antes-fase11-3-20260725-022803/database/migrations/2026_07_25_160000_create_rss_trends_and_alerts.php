<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rss_trends', function (Blueprint $table) {
            $table->id();
            $table->string('term')->index();
            $table->string('category')->nullable()->index();
            $table->string('location')->nullable()->index();
            $table->unsignedInteger('mention_count')->default(0);
            $table->unsignedInteger('previous_count')->default(0);
            $table->decimal('growth_percent', 8, 2)->default(0);
            $table->decimal('score', 10, 2)->default(0)->index();
            $table->timestamp('window_started_at');
            $table->timestamp('window_ended_at');
            $table->timestamps();
            $table->unique(['term', 'category', 'location', 'window_ended_at'], 'rss_trends_window_unique');
        });

        Schema::create('rss_trend_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rss_trend_id')->constrained('rss_trends')->cascadeOnDelete();
            $table->string('severity')->default('attention')->index();
            $table->string('title');
            $table->text('pitch_suggestion');
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('detected_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_trend_alerts');
        Schema::dropIfExists('rss_trends');
    }
};
