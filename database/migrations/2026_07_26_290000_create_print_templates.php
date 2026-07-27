<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('cover_style', 30)->default('classic');
            $table->unsignedTinyInteger('cover_columns')->default(3);
            $table->string('internal_style', 30)->default('columns');
            $table->unsignedTinyInteger('internal_columns')->default(3);
            $table->text('credits')->nullable();
            $table->boolean('show_page_numbers')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['site_id', 'name']);
            $table->index(['site_id', 'is_default']);
        });

        Schema::create('print_template_ad_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('print_template_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('page_type', 20);
            $table->string('placement', 30);
            $table->string('size', 30);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['print_template_id', 'page_type', 'position']);
        });

        Schema::table('print_editions', function (Blueprint $table): void {
            $table->foreignId('print_template_id')->nullable()->after('created_by')
                ->constrained('print_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('print_editions', fn (Blueprint $table) => $table->dropConstrainedForeignId('print_template_id'));
        Schema::dropIfExists('print_template_ad_slots');
        Schema::dropIfExists('print_templates');
    }
};
