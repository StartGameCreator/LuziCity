<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_editorial_profiles')) {
            Schema::create('ai_editorial_profiles', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 120);
                $table->boolean('is_default')->default(false)->index();
                $table->string('language', 20)->default('pt-BR');
                $table->string('tone', 180)->default('jornalístico profissional');
                $table->unsignedSmallInteger('max_title_length')->default(70);
                $table->unsignedSmallInteger('max_excerpt_length')->default(180);
                $table->boolean('require_source_attribution')->default(true);
                $table->boolean('avoid_sensationalism')->default(true);
                $table->boolean('human_review_required')->default(true);
                $table->text('editorial_rules')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('news_articles', 'subtitle')) {
            Schema::table('news_articles', function (Blueprint $table): void {
                $table->string('subtitle', 240)->nullable()->after('title');
            });
        }

        if (! Schema::hasColumn('news_articles', 'seo_title')) {
            Schema::table('news_articles', function (Blueprint $table): void {
                $table->string('seo_title', 180)->nullable()->after('excerpt');
            });
        }

        if (! Schema::hasColumn('news_articles', 'seo_description')) {
            Schema::table('news_articles', function (Blueprint $table): void {
                $table->string('seo_description', 320)->nullable()->after('seo_title');
            });
        }

        if (! Schema::hasColumn('news_articles', 'ai_metadata')) {
            Schema::table('news_articles', function (Blueprint $table): void {
                $table->json('ai_metadata')->nullable()->after('seo_description');
            });
        }

        if (! Schema::hasColumn('news_articles', 'ai_execution_id')) {
            Schema::table('news_articles', function (Blueprint $table): void {
                $table->foreignId('ai_execution_id')->nullable()->after('ai_metadata')
                    ->constrained('ai_executions')->nullOnDelete();
            });
        }

        if (Schema::hasTable('ai_editorial_profiles')
            && ! DB::table('ai_editorial_profiles')->where('is_default', true)->exists()) {
            DB::table('ai_editorial_profiles')->insert([
                'name' => 'Padrão LuziCity',
                'is_default' => true,
                'language' => 'pt-BR',
                'tone' => 'jornalístico profissional, claro e acessível',
                'max_title_length' => 70,
                'max_excerpt_length' => 180,
                'require_source_attribution' => true,
                'avoid_sensationalism' => true,
                'human_review_required' => true,
                'editorial_rules' => implode("\n", [
                    'Nunca inventar fatos, números, nomes, locais, falas ou fontes.',
                    'Diferenciar fatos confirmados de hipóteses, alegações e opiniões.',
                    'Priorizar relevância para Luziânia, Entorno do Distrito Federal e Goiás.',
                    'Evitar linguagem sensacionalista, discriminatória ou acusatória.',
                    'Sempre preservar e mencionar a fonte original quando houver.',
                    'Usar português brasileiro e parágrafos curtos.',
                    'Submeter todo conteúdo gerado à revisão humana antes da publicação.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('news_articles', 'ai_execution_id')) {
            Schema::table('news_articles', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('ai_execution_id');
            });
        }

        $columns = collect(['subtitle', 'seo_title', 'seo_description', 'ai_metadata'])
            ->filter(fn (string $column): bool => Schema::hasColumn('news_articles', $column))
            ->all();

        if ($columns !== []) {
            Schema::table('news_articles', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }

        Schema::dropIfExists('ai_editorial_profiles');
    }
};
