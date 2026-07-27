<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_editions', function (Blueprint $table): void {
            $table->string('pdf_format', 20)->default('a4')->after('edition_date');
            $table->decimal('bleed_mm', 4, 1)->default(3)->after('pdf_format');
            $table->boolean('high_resolution_images')->default(true)->after('bleed_mm');
            $table->timestamp('pdf_generated_at')->nullable()->after('high_resolution_images');
        });
    }

    public function down(): void
    {
        Schema::table('print_editions', function (Blueprint $table): void {
            $table->dropColumn(['pdf_format', 'bleed_mm', 'high_resolution_images', 'pdf_generated_at']);
        });
    }
};
