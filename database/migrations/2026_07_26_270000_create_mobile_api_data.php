<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('news_article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'news_article_id', 'site_id']);
            $table->index(['user_id', 'site_id', 'created_at']);
        });
        Schema::table('push_subscriptions', function (Blueprint $table): void {
            $table->foreignId('site_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
        $defaultSiteId = DB::table('sites')->where('is_default', true)->value('id');
        if ($defaultSiteId) {
            DB::table('push_subscriptions')->whereNull('site_id')->update(['site_id' => $defaultSiteId]);
        }
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('site_id');
        });
        Schema::dropIfExists('news_favorites');
    }
};
