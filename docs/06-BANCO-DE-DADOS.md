# Banco de dados

O checkpoint contém 82 migrations. A base atual usa SQLite e foi construída incrementalmente.

## Tabelas identificadas nas migrations

| Tabela | Operações/migrations |
|---|---|
| `ad_campaigns` | create: `2026_06_26_150000_create_luzicity_core_tables.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_25_131302_extend_ad_campaigns_for_phase_13_2.php`; table: `2026_07_25_131302_extend_ad_campaigns_for_phase_13_2.php`; table: `2026_07_25_131302_extend_ad_campaigns_for_phase_13_2.php` |
| `advertiser_addresses` | create: `2026_07_25_131000_extend_advertiser_profiles_for_commercial_management.php` |
| `advertiser_contacts` | create: `2026_07_25_131000_extend_advertiser_profiles_for_commercial_management.php` |
| `advertiser_documents` | create: `2026_07_25_131000_extend_advertiser_profiles_for_commercial_management.php` |
| `advertiser_histories` | create: `2026_07_25_131000_extend_advertiser_profiles_for_commercial_management.php` |
| `advertiser_profiles` | create: `2026_06_26_150000_create_luzicity_core_tables.php`; table: `2026_07_25_131000_extend_advertiser_profiles_for_commercial_management.php` |
| `agency_approval_actions` | create: `2026_07_25_180000_create_agency_approval_actions.php` |
| `ai_agent_runs` | create: `2026_07_25_080000_create_ai_agent_workflow_tables.php` |
| `ai_agent_steps` | create: `2026_07_25_080000_create_ai_agent_workflow_tables.php` |
| `ai_agents` | create: `2026_07_25_080000_create_ai_agent_workflow_tables.php` |
| `ai_audit_events` | create: `2026_07_25_060000_create_ai_audit_events_table.php` |
| `ai_editorial_profiles` | create: `2026_07_25_010000_consolidate_ai_news_editor.php`; table: `2026_07_25_040000_create_ai_editorial_memory_tables.php` |
| `ai_editorial_rules` | create: `2026_07_25_040000_create_ai_editorial_memory_tables.php` |
| `ai_editorial_terms` | create: `2026_07_25_040000_create_ai_editorial_memory_tables.php` |
| `ai_executions` | create: `2026_07_25_000000_create_ai_editorial_foundation_tables.php`; table: `2026_07_25_020000_add_metrics_to_ai_executions.php`; table: `2026_07_25_020000_add_metrics_to_ai_executions.php` |
| `ai_prompt_templates` | create: `2026_07_25_000000_create_ai_editorial_foundation_tables.php`; table: `2026_07_25_030000_create_ai_prompt_versions_table.php`; table: `2026_07_25_030000_create_ai_prompt_versions_table.php` |
| `ai_prompt_versions` | create: `2026_07_25_030000_create_ai_prompt_versions_table.php` |
| `ai_providers` | create: `2026_07_25_000000_create_ai_editorial_foundation_tables.php`; table: `2026_07_25_050000_extend_ai_providers_for_limits.php` |
| `analytics_consents` | create: `2026_07_26_190000_create_analytics_privacy_records.php` |
| `analytics_pageviews` | create: `2026_07_26_180000_create_analytics_pageviews.php`; table: `2026_07_26_200000_link_analytics_to_news.php`; table: `2026_07_26_200000_link_analytics_to_news.php`; table: `2026_07_26_210000_add_editorial_metrics_to_analytics_pageviews.php`; table: `2026_07_26_210000_add_editorial_metrics_to_analytics_pageviews.php` |
| `api_tokens` | create: `2026_07_26_220000_create_api_tokens.php` |
| `audio_ad_plays` | create: `2026_07_25_230000_create_audio_advertising.php` |
| `audio_campaigns` | create: `2026_07_25_230000_create_audio_advertising.php` |
| `audio_spots` | create: `2026_07_25_230000_create_audio_advertising.php` |
| `audio_voice_profiles` | create: `2026_07_25_220000_create_news_narrations.php` |
| `cache` | create: `0001_01_01_000001_create_cache_table.php` |
| `cache_locks` | create: `0001_01_01_000001_create_cache_table.php` |
| `categories` | create: `2026_06_26_150000_create_luzicity_core_tables.php`; table: `2026_06_26_160000_add_parent_and_sort_to_categories_table.php`; table: `2026_06_26_160000_add_parent_and_sort_to_categories_table.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php` |
| `columnist_profiles` | create: `2026_06_26_150000_create_luzicity_core_tables.php` |
| `commercial_invoices` | create: `2026_07_26_110000_create_commercial_finance_tables.php` |
| `commercial_payments` | create: `2026_07_26_110000_create_commercial_finance_tables.php` |
| `commercial_proposal_items` | create: `2026_07_26_100000_create_media_kit_tables.php` |
| `commercial_proposals` | create: `2026_07_26_100000_create_media_kit_tables.php` |
| `database_schema_registry` | create: `2026_07_25_280000_consolidate_database_growth.php` |
| `editorial_calendar_events` | create: `2026_07_25_120000_create_editorial_calendar_events.php` |
| `editorial_pitch_comments` | create: `2026_07_25_070000_create_editorial_pitch_tables.php` |
| `editorial_pitch_sources` | create: `2026_07_25_070000_create_editorial_pitch_tables.php`; table: `2026_07_25_090000_extend_editorial_sources_for_research.php` |
| `editorial_pitch_tasks` | create: `2026_07_25_070000_create_editorial_pitch_tables.php` |
| `editorial_pitches` | create: `2026_07_25_070000_create_editorial_pitch_tables.php` |
| `editorial_source_claims` | create: `2026_07_25_090000_extend_editorial_sources_for_research.php` |
| `editorial_verification_reviews` | create: `2026_07_25_100000_create_editorial_verification_reviews.php` |
| `failed_jobs` | create: `0001_01_01_000002_create_jobs_table.php` |
| `job_batches` | create: `0001_01_01_000002_create_jobs_table.php` |
| `jobs` | create: `0001_01_01_000002_create_jobs_table.php` |
| `journalist_profiles` | create: `2026_06_26_150000_create_luzicity_core_tables.php` |
| `media_assets` | create: `2026_07_25_280000_consolidate_database_growth.php` |
| `media_banners` | create: `2026_06_26_180000_create_media_banners_table.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php` |
| `media_kit_formats` | create: `2026_07_26_100000_create_media_kit_tables.php` |
| `mediables` | create: `2026_07_25_280000_consolidate_database_growth.php` |
| `news_article_tag` | create: `2026_06_26_150000_create_luzicity_core_tables.php` |
| `news_article_versions` | create: `2026_07_25_110000_create_news_editorial_workflow.php` |
| `news_articles` | create: `2026_06_26_150000_create_luzicity_core_tables.php`; table: `2026_07_03_010000_add_carousel_fields_to_news_articles.php`; table: `2026_07_03_010000_add_carousel_fields_to_news_articles.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_25_010000_consolidate_ai_news_editor.php`; table: `2026_07_25_010000_consolidate_ai_news_editor.php`; table: `2026_07_25_010000_consolidate_ai_news_editor.php`; table: `2026_07_25_010000_consolidate_ai_news_editor.php`; table: `2026_07_25_010000_consolidate_ai_news_editor.php`; table: `2026_07_25_010000_consolidate_ai_news_editor.php`; table: `2026_07_25_010000_consolidate_ai_news_editor.php`; table: `2026_07_25_110000_create_news_editorial_workflow.php`; table: `2026_07_26_120000_add_sponsored_content_to_news_articles.php`; table: `2026_07_26_120000_add_sponsored_content_to_news_articles.php`; table: `2026_07_26_260000_create_news_distributions.php`; table: `2026_07_26_260000_create_news_distributions.php` |
| `news_distributions` | create: `2026_07_26_260000_create_news_distributions.php` |
| `news_editorial_reviews` | create: `2026_07_25_110000_create_news_editorial_workflow.php` |
| `news_favorites` | create: `2026_07_26_270000_create_mobile_api_data.php` |
| `news_narrations` | create: `2026_07_25_220000_create_news_narrations.php` |
| `password_reset_tokens` | create: `0001_01_01_000000_create_users_table.php` |
| `payment_webhook_events` | create: `2026_07_26_170000_create_subscription_payments.php` |
| `paywall_accesses` | create: `2026_07_26_140000_create_paywall_rules_and_accesses.php` |
| `paywall_category_rules` | create: `2026_07_26_140000_create_paywall_rules_and_accesses.php` |
| `podcast_episodes` | create: `2026_07_25_210000_create_podcasts.php` |
| `podcast_series` | create: `2026_07_25_210000_create_podcasts.php` |
| `print_edition_items` | create: `2026_07_26_280000_create_print_editions.php` |
| `print_edition_sections` | create: `2026_07_26_280000_create_print_editions.php` |
| `print_editions` | create: `2026_07_26_280000_create_print_editions.php`; table: `2026_07_26_290000_create_print_templates.php`; table: `2026_07_26_290000_create_print_templates.php`; table: `2026_07_26_300000_add_pdf_settings_to_print_editions.php`; table: `2026_07_26_300000_add_pdf_settings_to_print_editions.php`; table: `2026_07_26_310000_add_review_workflow_to_print_editions.php`; table: `2026_07_26_310000_add_review_workflow_to_print_editions.php`; table: `2026_07_26_320000_add_approved_pdf_snapshot_to_print_editions.php`; table: `2026_07_26_320000_add_approved_pdf_snapshot_to_print_editions.php` |
| `print_template_ad_slots` | create: `2026_07_26_290000_create_print_templates.php` |
| `print_templates` | create: `2026_07_26_290000_create_print_templates.php` |
| `privacy_data_requests` | create: `2026_07_26_190000_create_analytics_privacy_records.php` |
| `push_subscriptions` | create: `2026_07_24_210000_create_push_subscriptions_table.php`; table: `2026_07_26_270000_create_mobile_api_data.php`; table: `2026_07_26_270000_create_mobile_api_data.php` |
| `queue_activity_logs` | create: `2026_07_26_340000_create_queue_activity_logs.php` |
| `radio_hosts` | create: `2026_07_25_200000_create_radio_structure.php` |
| `radio_programs` | create: `2026_07_25_200000_create_radio_structure.php` |
| `radio_requests` | create: `2026_06_26_190000_create_radio_requests_table.php`; table: `2026_06_28_030000_add_chat_fields_to_radio_requests_table.php`; table: `2026_06_28_030000_add_chat_fields_to_radio_requests_table.php`; table: `2026_06_28_040000_add_private_chat_fields_to_radio_requests_table.php`; table: `2026_06_28_040000_add_private_chat_fields_to_radio_requests_table.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php` |
| `radio_schedule_slots` | create: `2026_07_25_200000_create_radio_structure.php` |
| `radio_stations` | create: `2026_07_25_200000_create_radio_structure.php` |
| `real_estate_listings` | create: `2026_06_28_010000_create_real_estate_listings_table.php`; table: `2026_07_03_030000_add_video_fields_to_real_estate_listings.php`; table: `2026_07_03_030000_add_video_fields_to_real_estate_listings.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php` |
| `request_metrics` | create: `2026_07_26_350000_create_request_metrics.php` |
| `rss_collection_runs` | create: `2026_07_25_140000_create_rss_collection_runs.php` |
| `rss_feeds` | create: `2026_06_26_170000_create_rss_feeds_table.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_25_130000_extend_rss_feeds_for_assisted_agency.php`; table: `2026_07_25_190000_add_source_policy_to_rss_feeds.php`; table: `2026_07_25_190000_add_source_policy_to_rss_feeds.php` |
| `rss_imported_articles` | create: `2026_07_03_000000_create_rss_imported_articles_table.php`; table: `2026_07_25_140000_create_rss_collection_runs.php`; table: `2026_07_25_140000_create_rss_collection_runs.php`; table: `2026_07_25_150000_add_similarity_to_rss_articles.php`; table: `2026_07_25_150000_add_similarity_to_rss_articles.php` |
| `rss_pre_pitches` | create: `2026_07_25_170000_create_rss_pre_pitches.php` |
| `rss_trend_alerts` | create: `2026_07_25_160000_create_rss_trends_and_alerts.php` |
| `rss_trends` | create: `2026_07_25_160000_create_rss_trends_and_alerts.php` |
| `sessions` | create: `0001_01_01_000000_create_users_table.php` |
| `settings` | create: `2026_06_26_000000_create_settings_table.php` |
| `site_domains` | create: `2026_07_26_240000_create_multisite_structure.php` |
| `site_settings` | create: `2026_07_26_240000_create_multisite_structure.php` |
| `site_user` | create: `2026_07_26_250000_isolate_multisite_data.php` |
| `sites` | create: `2026_07_26_240000_create_multisite_structure.php` |
| `social_accounts` | create: `2026_06_26_150000_create_luzicity_core_tables.php` |
| `subscription_benefit_plan` | create: `2026_07_26_160000_create_subscription_benefits.php` |
| `subscription_benefit_redemptions` | create: `2026_07_26_160000_create_subscription_benefits.php` |
| `subscription_benefits` | create: `2026_07_26_160000_create_subscription_benefits.php` |
| `subscription_histories` | create: `2026_07_26_150000_create_subscription_histories.php` |
| `subscription_payment_refunds` | create: `2026_07_26_170000_create_subscription_payments.php` |
| `subscription_payments` | create: `2026_07_26_170000_create_subscription_payments.php` |
| `subscription_plans` | create: `2026_07_26_130000_create_subscription_plans.php`; table: `2026_07_26_140000_create_paywall_rules_and_accesses.php`; table: `2026_07_26_140000_create_paywall_rules_and_accesses.php` |
| `subscriptions` | create: `2026_06_26_150000_create_luzicity_core_tables.php`; table: `2026_07_26_130000_create_subscription_plans.php`; table: `2026_07_26_130000_create_subscription_plans.php`; table: `2026_07_26_150000_create_subscription_histories.php`; table: `2026_07_26_150000_create_subscription_histories.php` |
| `system_audit_logs` | create: `2026_07_25_280000_consolidate_database_growth.php` |
| `tags` | create: `2026_06_26_150000_create_luzicity_core_tables.php` |
| `tv_broadcasts` | create: `2026_07_25_240000_create_tv_channels_and_broadcasts.php` |
| `tv_channels` | create: `2026_07_25_240000_create_tv_channels_and_broadcasts.php` |
| `users` | create: `0001_01_01_000000_create_users_table.php` |
| `vehicle_listings` | create: `2026_06_27_020000_create_vehicle_listings_table.php`; table: `2026_06_27_030000_add_stats_to_vehicle_listings_table.php`; table: `2026_06_27_030000_add_stats_to_vehicle_listings_table.php`; table: `2026_06_27_040000_add_vehicle_type_to_vehicle_listings_table.php`; table: `2026_06_27_040000_add_vehicle_type_to_vehicle_listings_table.php`; table: `2026_07_03_020000_add_video_fields_to_vehicle_listings.php`; table: `2026_07_03_020000_add_video_fields_to_vehicle_listings.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php`; table: `2026_07_24_110000_add_performance_indexes_to_luzicity_tables.php` |
| `video_categories` | create: `2026_07_25_250000_create_video_library.php` |
| `video_clips` | create: `2026_07_25_270000_create_video_clips.php` |
| `video_playlist_items` | create: `2026_07_25_250000_create_video_library.php` |
| `video_playlists` | create: `2026_07_25_250000_create_video_library.php` |
| `video_scripts` | create: `2026_07_25_260000_create_video_scripts.php` |
| `video_series` | create: `2026_07_25_250000_create_video_library.php` |
| `videos` | create: `2026_07_25_250000_create_video_library.php` |
| `webhook_deliveries` | create: `2026_07_26_230000_create_outgoing_webhooks.php` |
| `webhook_endpoints` | create: `2026_07_26_230000_create_outgoing_webhooks.php` |

## Regras de evolução

- Nunca editar migration já aplicada em ambientes compartilhados.
- Criar migrations defensivas para evoluções.
- Fazer backup antes de mudanças de esquema.
- Preservar compatibilidade com SQLite.
- Validar foreign keys, índices e exclusões em cascata.
- Não incluir o arquivo SQLite no repositório público.

## Modelos Eloquent

- `AdCampaign`
- `AdvertiserAddress`
- `AdvertiserContact`
- `AdvertiserDocument`
- `AdvertiserHistory`
- `AdvertiserProfile`
- `AgencyApprovalAction`
- `AiAgent`
- `AiAgentRun`
- `AiAgentStep`
- `AiAuditEvent`
- `AiEditorialProfile`
- `AiEditorialRule`
- `AiEditorialTerm`
- `AiExecution`
- `AiPromptTemplate`
- `AiPromptVersion`
- `AiProvider`
- `AnalyticsConsent`
- `AnalyticsPageview`
- `ApiToken`
- `AudioAdPlay`
- `AudioCampaign`
- `AudioSpot`
- `AudioVoiceProfile`
- `Category`
- `ColumnistProfile`
- `CommercialInvoice`
- `CommercialPayment`
- `CommercialProposal`
- `CommercialProposalItem`
- `EditorialCalendarEvent`
- `EditorialPitch`
- `EditorialPitchComment`
- `EditorialPitchSource`
- `EditorialPitchTask`
- `EditorialSourceClaim`
- `EditorialVerificationReview`
- `JournalistProfile`
- `MediaBanner`
- `MediaKitFormat`
- `NewsArticle`
- `NewsArticleVersion`
- `NewsDistribution`
- `NewsEditorialReview`
- `NewsFavorite`
- `NewsNarration`
- `PaymentWebhookEvent`
- `PaywallAccess`
- `PaywallCategoryRule`
- `PodcastEpisode`
- `PodcastSeries`
- `PrintEdition`
- `PrintEditionItem`
- `PrintEditionSection`
- `PrintTemplate`
- `PrintTemplateAdSlot`
- `PrivacyDataRequest`
- `PushSubscription`
- `RadioHost`
- `RadioProgram`
- `RadioRequest`
- `RadioScheduleSlot`
- `RadioStation`
- `RealEstateListing`
- `RssCollectionRun`
- `RssFeed`
- `RssImportedArticle`
- `RssPrePitch`
- `RssTrend`
- `RssTrendAlert`
- `Setting`
- `Site`
- `SiteDomain`
- `SiteSetting`
- `SocialAccount`
- `Subscription`
- `SubscriptionBenefit`
- `SubscriptionBenefitRedemption`
- `SubscriptionHistory`
- `SubscriptionPayment`
- `SubscriptionPaymentRefund`
- `SubscriptionPlan`
- `SystemAuditLog`
- `Tag`
- `TvBroadcast`
- `TvChannel`
- `User`
- `VehicleListing`
- `Video`
- `VideoCategory`
- `VideoClip`
- `VideoPlaylist`
- `VideoScript`
- `VideoSeries`
- `WebhookDelivery`
- `WebhookEndpoint`
