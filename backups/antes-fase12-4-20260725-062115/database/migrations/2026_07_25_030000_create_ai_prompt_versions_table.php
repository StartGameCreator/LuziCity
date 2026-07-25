<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('ai_prompt_templates') && ! Schema::hasColumn('ai_prompt_templates', 'is_default')) {
            Schema::table('ai_prompt_templates', fn (Blueprint $table) => $table->boolean('is_default')->default(false)->index());
        }
        if (! Schema::hasTable('ai_prompt_versions')) {
            Schema::create('ai_prompt_versions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ai_prompt_template_id')->constrained('ai_prompt_templates')->cascadeOnDelete();
                $table->unsignedInteger('version');
                $table->text('system_prompt');
                $table->longText('user_prompt');
                $table->json('variables')->nullable();
                $table->text('change_notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['ai_prompt_template_id', 'version']);
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('ai_prompt_versions');
        if (Schema::hasTable('ai_prompt_templates') && Schema::hasColumn('ai_prompt_templates', 'is_default')) {
            Schema::table('ai_prompt_templates', fn (Blueprint $table) => $table->dropColumn('is_default'));
        }
    }
};
