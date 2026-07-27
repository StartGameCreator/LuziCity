<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_editions', function (Blueprint $table): void {
            $table->string('review_status', 20)->default('draft')->after('pdf_generated_at');
            $table->text('review_notes')->nullable()->after('review_status');
            $table->unsignedInteger('pdf_page_count')->nullable()->after('review_notes');
            $table->foreignId('approved_by')->nullable()->after('pdf_page_count')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->index(['site_id', 'review_status']);
        });
    }

    public function down(): void
    {
        Schema::table('print_editions', function (Blueprint $table): void {
            $table->dropIndex(['site_id', 'review_status']);
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['review_status', 'review_notes', 'pdf_page_count', 'approved_at']);
        });
    }
};
