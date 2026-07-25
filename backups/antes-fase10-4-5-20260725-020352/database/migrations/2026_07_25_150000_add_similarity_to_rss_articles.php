<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rss_imported_articles', function (Blueprint $table) {
            $table->string('title_hash', 64)->nullable()->index();
            $table->uuid('topic_group_id')->nullable()->index();
            $table->boolean('is_topic_primary')->default(true)->index();
            $table->decimal('similarity_score', 5, 4)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rss_imported_articles', fn (Blueprint $table) =>
            $table->dropColumn(['title_hash', 'topic_group_id', 'is_topic_primary', 'similarity_score'])
        );
    }
};
