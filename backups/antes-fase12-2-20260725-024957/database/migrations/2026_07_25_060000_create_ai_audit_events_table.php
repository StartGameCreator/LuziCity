<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('ai_audit_events')) return;
        Schema::create('ai_audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_id')->nullable()->constrained('ai_executions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->foreignId('prompt_template_id')->nullable()->constrained('ai_prompt_templates')->nullOnDelete();
            $table->unsignedBigInteger('article_id')->nullable()->index();
            $table->string('action',120)->index(); $table->string('model',160)->nullable();
            $table->json('safe_parameters')->nullable(); $table->string('result_status',30)->index();
            $table->text('error_message')->nullable(); $table->string('ip_address',45)->nullable();
            $table->string('user_agent',500)->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ai_audit_events'); }
};
