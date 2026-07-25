<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rss_imported_articles', function (Blueprint $table) {
            $table->string('source_hash', 64)->nullable()->unique();
            $table->string('source_domain')->nullable()->index();
            $table->string('collection_status')->default('pending_review')->index();
            $table->timestamp('collected_at')->nullable()->index();
        });

        Schema::create('rss_collection_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rss_feed_id')->constrained('rss_feeds')->cascadeOnDelete();
            $table->uuid('job_uuid')->unique();
            $table->string('status')->default('running')->index();
            $table->unsignedSmallInteger('requested_limit')->default(12);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_collection_runs');
        Schema::table('rss_imported_articles', function (Blueprint $table) {
            $table->dropColumn(['source_hash', 'source_domain', 'collection_status', 'collected_at']);
        });
    }
};
