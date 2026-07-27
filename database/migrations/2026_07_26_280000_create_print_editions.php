<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_editions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 180);
            $table->date('edition_date');
            $table->timestamps();
            $table->index(['site_id', 'edition_date']);
        });

        Schema::create('print_edition_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('print_edition_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['print_edition_id', 'name']);
            $table->index(['print_edition_id', 'position']);
        });

        Schema::create('print_edition_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('print_edition_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('news_article_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['print_edition_section_id', 'news_article_id']);
            $table->index(['print_edition_section_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_edition_items');
        Schema::dropIfExists('print_edition_sections');
        Schema::dropIfExists('print_editions');
    }
};
