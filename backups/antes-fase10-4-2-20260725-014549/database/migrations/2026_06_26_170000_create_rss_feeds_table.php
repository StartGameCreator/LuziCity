<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url', 2048);
            $table->string('category')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach ([
            ['name' => 'Últimas Notícias', 'category' => 'Geral'],
            ['name' => 'Brasil', 'category' => 'Nacional'],
            ['name' => 'Mundo', 'category' => 'Internacional'],
            ['name' => 'Economia', 'category' => 'Economia'],
            ['name' => 'Esportes', 'category' => 'Esportes'],
            ['name' => 'Tecnologia', 'category' => 'Tecnologia'],
        ] as $index => $feed) {
            DB::table('rss_feeds')->insert([
                'name' => $feed['name'],
                'url' => '#',
                'category' => $feed['category'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_feeds');
    }
};
