<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ad_campaigns')) {
            return;
        }

        Schema::table('ad_campaigns', function (Blueprint $table): void {
            if (! Schema::hasColumn('ad_campaigns', 'advertiser_profile_id')) {
                $table->foreignId('advertiser_profile_id')
                    ->nullable()
                    ->after('advertiser_id')
                    ->constrained('advertiser_profiles')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('ad_campaigns', 'campaign_type')) {
                $table->string('campaign_type', 40)
                    ->default('banner')
                    ->after('name');
            }

            if (! Schema::hasColumn('ad_campaigns', 'billing_model')) {
                $table->string('billing_model', 20)
                    ->default('fixed')
                    ->after('status');
            }

            if (! Schema::hasColumn('ad_campaigns', 'budget')) {
                $table->decimal('budget', 14, 2)
                    ->default(0)
                    ->after('billing_model');
            }

            if (! Schema::hasColumn('ad_campaigns', 'daily_budget')) {
                $table->decimal('daily_budget', 14, 2)
                    ->nullable()
                    ->after('budget');
            }

            if (! Schema::hasColumn('ad_campaigns', 'price_per_impression')) {
                $table->decimal('price_per_impression', 14, 6)
                    ->nullable()
                    ->after('daily_budget');
            }

            if (! Schema::hasColumn('ad_campaigns', 'price_per_click')) {
                $table->decimal('price_per_click', 14, 6)
                    ->nullable()
                    ->after('price_per_impression');
            }

            if (! Schema::hasColumn('ad_campaigns', 'impression_limit')) {
                $table->unsignedBigInteger('impression_limit')
                    ->nullable()
                    ->after('price_per_click');
            }

            if (! Schema::hasColumn('ad_campaigns', 'click_limit')) {
                $table->unsignedBigInteger('click_limit')
                    ->nullable()
                    ->after('impression_limit');
            }

            if (! Schema::hasColumn('ad_campaigns', 'impressions_count')) {
                $table->unsignedBigInteger('impressions_count')
                    ->default(0)
                    ->after('click_limit');
            }

            if (! Schema::hasColumn('ad_campaigns', 'clicks_count')) {
                $table->unsignedBigInteger('clicks_count')
                    ->default(0)
                    ->after('impressions_count');
            }

            if (! Schema::hasColumn('ad_campaigns', 'target_cities')) {
                $table->text('target_cities')
                    ->nullable()
                    ->after('image_alt');
            }

            if (! Schema::hasColumn('ad_campaigns', 'target_categories')) {
                $table->text('target_categories')
                    ->nullable()
                    ->after('target_cities');
            }

            if (! Schema::hasColumn('ad_campaigns', 'target_devices')) {
                $table->text('target_devices')
                    ->nullable()
                    ->after('target_categories');
            }

            if (! Schema::hasColumn('ad_campaigns', 'weekdays')) {
                $table->text('weekdays')
                    ->nullable()
                    ->after('target_devices');
            }

            if (! Schema::hasColumn('ad_campaigns', 'daily_start_time')) {
                $table->time('daily_start_time')
                    ->nullable()
                    ->after('weekdays');
            }

            if (! Schema::hasColumn('ad_campaigns', 'daily_end_time')) {
                $table->time('daily_end_time')
                    ->nullable()
                    ->after('daily_start_time');
            }

            if (! Schema::hasColumn('ad_campaigns', 'priority')) {
                $table->unsignedSmallInteger('priority')
                    ->default(0)
                    ->after('daily_end_time');
            }

            if (! Schema::hasColumn('ad_campaigns', 'is_active')) {
                $table->boolean('is_active')
                    ->default(true)
                    ->after('priority');
            }

            if (! Schema::hasColumn('ad_campaigns', 'approved_at')) {
                $table->timestamp('approved_at')
                    ->nullable()
                    ->after('ends_at');
            }

            if (! Schema::hasColumn('ad_campaigns', 'approved_by')) {
                $table->foreignId('approved_by')
                    ->nullable()
                    ->after('approved_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('ad_campaigns', 'notes')) {
                $table->text('notes')
                    ->nullable()
                    ->after('approved_by');
            }
        });

        Schema::table('ad_campaigns', function (Blueprint $table): void {
            $table->index(
                ['status', 'is_active', 'starts_at', 'ends_at'],
                'ad_campaigns_delivery_index'
            );

            $table->index(
                ['placement', 'priority'],
                'ad_campaigns_placement_priority_index'
            );

            $table->index(
                ['advertiser_profile_id', 'status'],
                'ad_campaigns_advertiser_status_index'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ad_campaigns')) {
            return;
        }

        Schema::table('ad_campaigns', function (Blueprint $table): void {
            $table->dropIndex('ad_campaigns_delivery_index');
            $table->dropIndex('ad_campaigns_placement_priority_index');
            $table->dropIndex('ad_campaigns_advertiser_status_index');

            $table->dropForeign(['advertiser_profile_id']);
            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'advertiser_profile_id',
                'campaign_type',
                'billing_model',
                'budget',
                'daily_budget',
                'price_per_impression',
                'price_per_click',
                'impression_limit',
                'click_limit',
                'impressions_count',
                'clicks_count',
                'target_cities',
                'target_categories',
                'target_devices',
                'weekdays',
                'daily_start_time',
                'daily_end_time',
                'priority',
                'is_active',
                'approved_at',
                'approved_by',
                'notes',
            ]);
        });
    }
};