<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radio_requests', function (Blueprint $table) {
            $table->string('region')->nullable()->after('city');
            $table->string('category', 80)->default('geral')->after('region');
            $table->string('attachment_path')->nullable()->after('message');
            $table->string('attachment_type', 20)->nullable()->after('attachment_path');
            $table->string('attachment_original_name')->nullable()->after('attachment_type');
        });
    }

    public function down(): void
    {
        Schema::table('radio_requests', function (Blueprint $table) {
            $table->dropColumn([
                'region',
                'category',
                'attachment_path',
                'attachment_type',
                'attachment_original_name',
            ]);
        });
    }
};
