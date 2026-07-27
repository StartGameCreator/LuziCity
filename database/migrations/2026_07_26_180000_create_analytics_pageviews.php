<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_pageviews', function (Blueprint $table): void {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->string('session_hash',64);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('page_path',2048);
            $table->string('page_title')->nullable();
            $table->string('referrer_host')->nullable();
            $table->string('source',120)->nullable();
            $table->string('medium',120)->nullable();
            $table->string('campaign',180)->nullable();
            $table->string('content',180)->nullable();
            $table->string('term',180)->nullable();
            $table->string('device_type',20);
            $table->unsignedInteger('reading_time_seconds')->default(0);
            $table->unsignedInteger('max_scroll_percent')->default(0);
            $table->timestamp('viewed_at');
            $table->timestamp('last_activity_at');
            $table->timestamps();
            $table->index(['viewed_at','page_path']);
            $table->index(['session_hash','viewed_at']);
            $table->index(['campaign','viewed_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('analytics_pageviews'); }
};
