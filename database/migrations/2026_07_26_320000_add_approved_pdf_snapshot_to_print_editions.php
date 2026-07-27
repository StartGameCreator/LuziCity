<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_editions', function (Blueprint $table): void {
            $table->string('approved_pdf_path')->nullable()->after('approved_at');
            $table->string('approved_pdf_sha256', 64)->nullable()->after('approved_pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('print_editions', fn (Blueprint $table) => $table->dropColumn([
            'approved_pdf_path', 'approved_pdf_sha256',
        ]));
    }
};
