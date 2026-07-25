<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('database_schema_registry')) {
            Schema::create('database_schema_registry', function (Blueprint $table) {
                $table->id();
                $table->string('module', 100);
                $table->string('schema_version', 40);
                $table->string('status', 30)->default('active');
                $table->text('notes')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
                $table->unique(['module', 'schema_version'], 'db_schema_registry_module_version_uq');
                $table->index(['status', 'verified_at'], 'db_schema_registry_status_verified_idx');
            });
        }

        if (! Schema::hasTable('media_assets')) {
            Schema::create('media_assets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('disk', 40)->default('public');
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->string('mime_type', 120)->nullable();
                $table->string('extension', 20)->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->unsignedInteger('duration_seconds')->nullable();
                $table->string('checksum_sha256', 64)->nullable();
                $table->string('visibility', 20)->default('public');
                $table->string('status', 30)->default('ready');
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['disk', 'path'], 'media_assets_disk_path_uq');
                $table->index(['status', 'created_at'], 'media_assets_status_created_idx');
                $table->index('checksum_sha256', 'media_assets_checksum_idx');
            });
        }

        if (! Schema::hasTable('mediables')) {
            Schema::create('mediables', function (Blueprint $table) {
                $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
                $table->string('mediable_type');
                $table->unsignedBigInteger('mediable_id');
                $table->string('collection', 60)->default('default');
                $table->unsignedInteger('position')->default(0);
                $table->string('alt_text')->nullable();
                $table->string('caption')->nullable();
                $table->timestamps();
                $table->primary(['media_asset_id', 'mediable_type', 'mediable_id', 'collection'], 'mediables_primary');
                $table->index(['mediable_type', 'mediable_id', 'collection', 'position'], 'mediables_lookup_idx');
            });
        }

        if (! Schema::hasTable('system_audit_logs')) {
            Schema::create('system_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event', 120);
                $table->string('auditable_type')->nullable();
                $table->unsignedBigInteger('auditable_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('request_id', 100)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['event', 'created_at'], 'system_audit_event_created_idx');
                $table->index(['auditable_type', 'auditable_id'], 'system_audit_auditable_idx');
                $table->index(['user_id', 'created_at'], 'system_audit_user_created_idx');
                $table->index('request_id', 'system_audit_request_idx');
            });
        }

        $this->createIndexes();

        DB::table('database_schema_registry')->updateOrInsert(
            ['module' => 'core', 'schema_version' => '2026.07.25.28'],
            ['status' => 'active', 'notes' => 'Consolidação estrutural após crescimento editorial, IA, rádio, podcast e TV.', 'verified_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );
    }

    private function createIndexes(): void
    {
        $indexes = [
            ['news_articles', 'news_articles_category_status_published_idx', ['category_id', 'status', 'published_at']],
            ['news_articles', 'news_articles_author_status_created_idx', ['author_id', 'status', 'created_at']],
            ['news_articles', 'news_articles_workflow_scheduled_idx', ['workflow_status', 'scheduled_for']],
            ['news_articles', 'news_articles_ai_execution_idx', ['ai_execution_id']],
            ['editorial_pitches', 'editorial_pitches_status_priority_due_idx', ['status', 'priority', 'due_at']],
            ['editorial_pitches', 'editorial_pitches_assignee_status_idx', ['assignee_id', 'status']],
            ['editorial_pitch_comments', 'editorial_pitch_comments_pitch_created_idx', ['editorial_pitch_id', 'created_at']],
            ['editorial_pitch_tasks', 'editorial_pitch_tasks_pitch_position_idx', ['editorial_pitch_id', 'position']],
            ['ai_executions', 'ai_executions_status_created_idx', ['status', 'created_at']],
            ['ai_executions', 'ai_executions_provider_created_idx', ['provider_id', 'created_at']],
            ['ai_executions', 'ai_executions_user_created_idx', ['user_id', 'created_at']],
            ['ai_audit_events', 'ai_audit_events_action_created_idx', ['action', 'created_at']],
            ['rss_imported_articles', 'rss_articles_feed_published_idx', ['rss_feed_id', 'published_at']],
            ['rss_imported_articles', 'rss_articles_status_collected_idx', ['collection_status', 'collected_at']],
            ['podcast_episodes', 'podcast_episodes_series_published_idx', ['podcast_series_id', 'is_published', 'published_at']],
            ['radio_schedule_slots', 'radio_schedule_day_time_idx', ['day_of_week', 'starts_at', 'ends_at']],
            ['tv_broadcasts', 'tv_broadcasts_channel_status_start_idx', ['tv_channel_id', 'status', 'starts_at']],
            ['videos', 'videos_published_date_idx', ['is_published', 'published_at']],
            ['video_clips', 'video_clips_status_created_idx', ['status', 'created_at']],
            ['news_narrations', 'news_narrations_status_created_idx', ['status', 'created_at']],
            ['push_subscriptions', 'push_subscriptions_user_platform_idx', ['user_id', 'platform']],
        ];

        foreach ($indexes as [$table, $name, $columns]) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue 2;
                }
            }

            $quotedColumns = implode(', ', array_map(fn (string $column) => '"'.$column.'"', $columns));

            if (DB::getDriverName() === 'sqlite') {
                DB::statement('CREATE INDEX IF NOT EXISTS "'.$name.'" ON "'.$table.'" ('.$quotedColumns.')');
            } else {
                try {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
                } catch (Throwable) {
                    // Índice já existe ou o banco não permite a alteração nesta execução.
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_audit_logs');
        Schema::dropIfExists('mediables');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('database_schema_registry');
    }
};
