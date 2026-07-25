<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_listings', function (Blueprint $table) {
            $table->string('video_platform', 40)->nullable()->after('photos');
            $table->string('video_orientation', 20)->default('landscape')->after('video_platform');
            $table->text('video_embed_code')->nullable()->after('video_orientation');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_listings', function (Blueprint $table) {
            $table->dropColumn([
                'video_platform',
                'video_orientation',
                'video_embed_code',
            ]);
        });
    }
};
