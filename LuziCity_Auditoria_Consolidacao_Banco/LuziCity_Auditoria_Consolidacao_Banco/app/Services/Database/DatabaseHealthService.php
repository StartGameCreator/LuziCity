<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseHealthService
{
    /** @return array<string, mixed> */
    public function audit(): array
    {
        $requiredTables = [
            'users', 'categories', 'news_articles', 'settings', 'roles', 'permissions',
            'ai_providers', 'ai_prompt_templates', 'ai_executions', 'ai_editorial_profiles',
            'editorial_pitches', 'rss_feeds', 'rss_imported_articles',
            'radio_stations', 'podcast_series', 'tv_channels', 'videos',
            'database_schema_registry', 'media_assets', 'mediables', 'system_audit_logs',
        ];

        $missingTables = array_values(array_filter(
            $requiredTables,
            fn (string $table): bool => ! Schema::hasTable($table)
        ));

        $orphanChecks = [
            ['news_articles', 'author_id', 'users'],
            ['news_articles', 'category_id', 'categories'],
            ['editorial_pitches', 'assignee_id', 'users'],
            ['ai_executions', 'provider_id', 'ai_providers'],
            ['podcast_episodes', 'podcast_series_id', 'podcast_series'],
            ['tv_broadcasts', 'tv_channel_id', 'tv_channels'],
            ['videos', 'video_category_id', 'video_categories'],
        ];

        $orphans = [];
        foreach ($orphanChecks as [$table, $foreignKey, $parent]) {
            if (! Schema::hasTable($table) || ! Schema::hasTable($parent) || ! Schema::hasColumn($table, $foreignKey)) {
                continue;
            }

            $count = DB::table($table.' as child')
                ->leftJoin($parent.' as parent', 'parent.id', '=', 'child.'.$foreignKey)
                ->whereNotNull('child.'.$foreignKey)
                ->whereNull('parent.id')
                ->count();

            if ($count > 0) {
                $orphans[] = compact('table', 'foreignKey', 'parent', 'count');
            }
        }

        $duplicateSlugs = [];
        foreach (['categories', 'news_articles', 'tags', 'podcast_series', 'tv_channels', 'videos'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'slug')) {
                continue;
            }

            $count = DB::table($table)
                ->select('slug')
                ->whereNotNull('slug')
                ->groupBy('slug')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count();

            if ($count > 0) {
                $duplicateSlugs[$table] = $count;
            }
        }

        $foreignKeysEnabled = null;
        if (DB::getDriverName() === 'sqlite') {
            $row = DB::selectOne('PRAGMA foreign_keys');
            $foreignKeysEnabled = (bool) ($row->foreign_keys ?? 0);
        }

        return [
            'driver' => DB::getDriverName(),
            'database' => DB::connection()->getDatabaseName(),
            'foreign_keys_enabled' => $foreignKeysEnabled,
            'missing_tables' => $missingTables,
            'orphans' => $orphans,
            'duplicate_slugs' => $duplicateSlugs,
            'migration_count' => Schema::hasTable('migrations') ? DB::table('migrations')->count() : 0,
            'table_count' => count(Schema::getTableListing()),
            'ok' => $missingTables === [] && $orphans === [] && $duplicateSlugs === [] && $foreignKeysEnabled !== false,
        ];
    }
}
