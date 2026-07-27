<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table): void {
            $table->boolean('can_access_premium')->default(false);
            $table->unsignedInteger('monthly_article_limit')->nullable();
            $table->unsignedInteger('preview_characters')->default(600);
        });

        Schema::create('paywall_category_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('minimum_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('preview_characters')->default(600);
            $table->timestamps();
        });

        Schema::create('paywall_accesses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('news_article_id')->constrained()->cascadeOnDelete();
            $table->date('period_month');
            $table->timestamp('accessed_at');
            $table->timestamps();
            $table->unique(['user_id', 'news_article_id', 'period_month']);
            $table->index(['user_id', 'period_month']);
        });

        \Illuminate\Support\Facades\DB::table('subscription_plans')->whereIn('slug', ['premium','vip','empresarial'])
            ->update(['can_access_premium'=>true,'monthly_article_limit'=>null]);
        \Illuminate\Support\Facades\DB::table('subscription_plans')->where('slug','gratuito')
            ->update(['can_access_premium'=>false,'monthly_article_limit'=>5]);
    }

    public function down(): void
    {
        Schema::dropIfExists('paywall_accesses');
        Schema::dropIfExists('paywall_category_rules');
        Schema::table('subscription_plans', function (Blueprint $table): void {
            $table->dropColumn(['can_access_premium','monthly_article_limit','preview_characters']);
        });
    }
};
