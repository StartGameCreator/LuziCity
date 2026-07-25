<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radio_requests', function (Blueprint $table) {
            $table->boolean('is_private')->default(false)->after('category');
            $table->string('recipient_name')->nullable()->after('is_private');
            $table->string('private_contact')->nullable()->after('recipient_name');
        });
    }

    public function down(): void
    {
        Schema::table('radio_requests', function (Blueprint $table) {
            $table->dropColumn([
                'is_private',
                'recipient_name',
                'private_contact',
            ]);
        });
    }
};
