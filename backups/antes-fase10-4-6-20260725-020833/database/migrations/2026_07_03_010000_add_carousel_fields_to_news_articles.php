<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->boolean('show_in_carousel')->default(false)->after('allow_ads');
            $table->string('carousel_type', 40)->nullable()->after('show_in_carousel');
            $table->text('carousel_embed_code')->nullable()->after('carousel_type');
            $table->string('carousel_image_path')->nullable()->after('carousel_embed_code');
            $table->unsignedInteger('carousel_sort_order')->default(0)->after('carousel_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropColumn([
                'show_in_carousel',
                'carousel_type',
                'carousel_embed_code',
                'carousel_image_path',
                'carousel_sort_order',
            ]);
        });
    }
};
