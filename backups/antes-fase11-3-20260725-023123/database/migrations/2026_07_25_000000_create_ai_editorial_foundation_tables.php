<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name', 120);
            $table->string('driver', 60);
            $table->boolean('is_enabled')->default(false)->index();
            $table->string('model', 160)->nullable();
            $table->string('endpoint', 2048)->nullable();
            $table->unsignedBigInteger('monthly_budget_cents')->default(0);
            $table->unsignedInteger('daily_request_limit')->default(100);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_prompt_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('name', 160);
            $table->string('purpose', 120)->index();
            $table->text('system_prompt');
            $table->longText('user_template');
            $table->json('output_schema')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('ai_executions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->foreignId('prompt_template_id')->nullable()->constrained('ai_prompt_templates')->nullOnDelete();
            $table->string('feature', 120)->index();
            $table->string('status', 30)->default('pending')->index();
            $table->string('input_hash', 64)->nullable()->index();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedBigInteger('estimated_cost_micros')->default(0);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        DB::table('ai_providers')->insert([
            [
                'slug' => 'chatgpt',
                'name' => 'OpenAI / ChatGPT',
                'driver' => 'openai',
                'is_enabled' => false,
                'model' => 'gpt-4o-mini',
                'monthly_budget_cents' => 0,
                'daily_request_limit' => 100,
                'metadata' => json_encode(['credential_source' => 'settings.ai.openai_api_key']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'gemini',
                'name' => 'Google Gemini',
                'driver' => 'gemini',
                'is_enabled' => false,
                'model' => 'gemini-1.5-flash',
                'monthly_budget_cents' => 0,
                'daily_request_limit' => 100,
                'metadata' => json_encode(['credential_source' => 'settings.ai.gemini_api_key']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'copilot',
                'name' => 'Microsoft Copilot',
                'driver' => 'copilot',
                'is_enabled' => false,
                'model' => null,
                'monthly_budget_cents' => 0,
                'daily_request_limit' => 100,
                'metadata' => json_encode(['credential_source' => 'settings.ai.copilot_api_key']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('ai_prompt_templates')->insert([
            [
                'key' => 'editorial.news.full',
                'name' => 'Notícia completa',
                'purpose' => 'news',
                'system_prompt' => 'Você é um editor jornalístico brasileiro. Preserve os fatos, diferencie informação confirmada de hipótese e nunca invente dados.',
                'user_template' => "Produza uma notícia em português do Brasil com título, subtítulo, resumo, corpo, SEO e tags.\n\nBriefing:\n{{briefing}}",
                'output_schema' => json_encode([
                    'title' => 'string',
                    'subtitle' => 'string',
                    'summary' => 'string',
                    'body' => 'string',
                    'seo_title' => 'string',
                    'seo_description' => 'string',
                    'tags' => ['string'],
                ]),
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'editorial.news.rewrite',
                'name' => 'Reescrita editorial',
                'purpose' => 'rewrite',
                'system_prompt' => 'Reescreva com linguagem original sem alterar fatos, números, nomes próprios ou atribuições. Não esconda a fonte original.',
                'user_template' => "Reescreva o conteúdo abaixo para publicação editorial, removendo repetições e preservando integralmente os fatos:\n\n{{content}}",
                'output_schema' => json_encode(['body' => 'string', 'summary' => 'string']),
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'editorial.seo',
                'name' => 'SEO automático',
                'purpose' => 'seo',
                'system_prompt' => 'Gere metadados objetivos e fiéis ao conteúdo. Evite clickbait enganoso.',
                'user_template' => "Gere SEO para a notícia:\n\nTítulo: {{title}}\nConteúdo: {{content}}",
                'output_schema' => json_encode([
                    'seo_title' => 'string',
                    'seo_description' => 'string',
                    'keywords' => ['string'],
                    'slug' => 'string',
                ]),
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'editorial.tags',
                'name' => 'Tags automáticas',
                'purpose' => 'tags',
                'system_prompt' => 'Escolha tags curtas, específicas e realmente relacionadas ao texto.',
                'user_template' => "Liste até 8 tags para:\n\n{{content}}",
                'output_schema' => json_encode(['tags' => ['string']]),
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_executions');
        Schema::dropIfExists('ai_prompt_templates');
        Schema::dropIfExists('ai_providers');
    }
};
