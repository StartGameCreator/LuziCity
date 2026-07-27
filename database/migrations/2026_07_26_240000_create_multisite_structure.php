<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('city', 120)->nullable();
            $table->char('state', 2)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('theme_primary', 7)->default('#0067c0');
            $table->string('theme_secondary', 7)->default('#004e8c');
            $table->string('theme_background_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
        });
        Schema::create('site_domains', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('domain', 255)->unique();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('key', 120);
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['site_id', 'key']);
        });

        $siteId = DB::table('sites')->insertGetId([
            'name' => 'Luzicity', 'slug' => 'luzicity', 'city' => 'Luziânia', 'state' => 'GO',
            'theme_primary' => '#0067c0', 'theme_secondary' => '#004e8c',
            'is_active' => true, 'is_default' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('site_domains')->insert([
            'site_id' => $siteId, 'domain' => parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost',
            'is_primary' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('site_domains');
        Schema::dropIfExists('sites');
    }
};
