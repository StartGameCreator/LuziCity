<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_pageviews', function (Blueprint $table): void {
            $table->unsignedInteger('share_count')->default(0)->after('max_scroll_percent');
            $table->timestamp('last_shared_at')->nullable()->after('share_count');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_pageviews', function (Blueprint $table): void {
            $table->dropColumn(['share_count', 'last_shared_at']);
        });
    }
};
