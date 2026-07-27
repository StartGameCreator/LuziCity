<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultSiteId = DB::table('sites')->where('is_default', true)->value('id');
        Schema::create('site_user', function (Blueprint $table): void {
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->primary(['site_id', 'user_id']);
        });
        if ($defaultSiteId) {
            DB::table('users')->orderBy('id')->each(fn ($user) => DB::table('site_user')->insert([
                'site_id' => $defaultSiteId, 'user_id' => $user->id, 'permissions' => json_encode([]),
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]));
        }
        foreach (['news_articles', 'media_banners', 'ad_campaigns'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->foreignId('site_id')->nullable()->after('id')->constrained()->nullOnDelete();
                $table->index(['site_id', $tableName === 'news_articles' ? 'status' : 'is_active']);
            });
            if ($defaultSiteId) {
                DB::table($tableName)->whereNull('site_id')->update(['site_id' => $defaultSiteId]);
            }
        }
    }

    public function down(): void
    {
        foreach (['news_articles', 'media_banners', 'ad_campaigns'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('site_id');
            });
        }
        Schema::dropIfExists('site_user');
    }
};
