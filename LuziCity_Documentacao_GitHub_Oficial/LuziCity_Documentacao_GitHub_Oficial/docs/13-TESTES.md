# Testes

O checkpoint contém 80 arquivos de teste distribuídos entre Unit, Feature, Integration, Smoke e Authorization.

## Cobertura funcional observada

- IA e redação.
- RSS e agência assistida.
- rádio, podcasts e áudio.
- AzuraCast.
- TV, vídeo e clips.
- comercial e campanhas.
- assinaturas, paywall e pagamentos.
- analytics e privacidade.
- API, webhooks, multisite e mobile.
- segurança, cache, observabilidade, backup e deploy.

## Execução

```powershell
php artisan test
```

Para um arquivo:

```powershell
php artisan test tests/Feature/AzuraCastNativeIntegrationTest.php
```

## Inventário

- `Authorization/OperationalAccessTest.php`
- `Feature/AI/AiCentralConsolidationTest.php`
- `Feature/AI/AiCostsLogsTest.php`
- `Feature/AI/AiEditorialDashboardTest.php`
- `Feature/AI/AiEditorialMemoryTest.php`
- `Feature/AI/AiPromptLibraryTest.php`
- `Feature/AI/AiProviderManagerTest.php`
- `Feature/AdCampaignManagementTest.php`
- `Feature/AgencyApprovalTest.php`
- `Feature/AgencyConsolidationTest.php`
- `Feature/AiAgentWorkflowTest.php`
- `Feature/AnalyticsCollectionTest.php`
- `Feature/AnalyticsDashboardTest.php`
- `Feature/AnalyticsPrivacyTest.php`
- `Feature/AndroidIntegrationTest.php`
- `Feature/ApiDocumentationTest.php`
- `Feature/ApiTokenAuthenticationTest.php`
- `Feature/AudioAdvertisingTest.php`
- `Feature/AzuraCastNativeIntegrationTest.php`
- `Feature/BackupOperationsTest.php`
- `Feature/CommercialFinanceManagementTest.php`
- `Feature/Database/PerformanceIndexesTest.php`
- `Feature/DeployReadinessTest.php`
- `Feature/EditorialAnalyticsTest.php`
- `Feature/EditorialCalendarTest.php`
- `Feature/EditorialPitchBoardTest.php`
- `Feature/EditorialRoomConsolidationTest.php`
- `Feature/EditorialSourceResearchTest.php`
- `Feature/EditorialVerificationTest.php`
- `Feature/ExampleTest.php`
- `Feature/GlobalAdministrationTest.php`
- `Feature/IosIntegrationTest.php`
- `Feature/MediaKitManagementTest.php`
- `Feature/MobileApiTest.php`
- `Feature/MultisiteIsolationTest.php`
- `Feature/MultisiteStructureTest.php`
- `Feature/NewsEditorialWorkflowTest.php`
- `Feature/NewsNarrationTest.php`
- `Feature/ObservabilityTest.php`
- `Feature/OutgoingWebhookTest.php`
- `Feature/PaywallManagementTest.php`
- `Feature/Performance/HomeCacheVersionTest.php`
- `Feature/Performance/PublicContentCacheTest.php`
- `Feature/PodcastTest.php`
- `Feature/PrintEditionManagementTest.php`
- `Feature/PrintEditionPdfTest.php`
- `Feature/PrintEditionReviewTest.php`
- `Feature/PrintTemplateManagementTest.php`
- `Feature/PublicApiV1Test.php`
- `Feature/Pwa/PwaAdvancedTest.php`
- `Feature/QueueMonitoringTest.php`
- `Feature/RadioConsolidationTest.php`
- `Feature/RadioStructureTest.php`
- `Feature/RssCollectorTest.php`
- `Feature/RssFeedManagementTest.php`
- `Feature/RssPrePitchTest.php`
- `Feature/RssSimilarityTest.php`
- `Feature/RssTrendTest.php`
- `Feature/Search/UnifiedSearchTest.php`
- `Feature/SecurityHardeningTest.php`
- `Feature/Seo/SitemapTest.php`
- `Feature/SharedNewsContentTest.php`
- `Feature/SponsoredContentManagementTest.php`
- `Feature/SubscriberManagementTest.php`
- `Feature/SubscriptionBenefitManagementTest.php`
- `Feature/SubscriptionPaymentManagementTest.php`
- `Feature/SubscriptionPlanManagementTest.php`
- `Feature/TvChannelTest.php`
- `Feature/TvConsolidationTest.php`
- `Feature/VideoClipTest.php`
- `Feature/VideoLibraryTest.php`
- `Feature/VideoScriptTest.php`
- `Integration/PublicContentIntegrationTest.php`
- `Smoke/OperationalSmokeTest.php`
- `TestCase.php`
- `Unit/Cache/PublicContentCacheUnitTest.php`
- `Unit/ExampleTest.php`
- `Unit/Models/ModelArchitectureTest.php`
- `Unit/Security/EmbedCodeSanitizerTest.php`
- `Unit/Security/PublicUrlGuardTest.php`

## Regra de integração externa

Testes automatizados não devem depender de serviços reais. Use `Http::fake()`, storage temporário, filas fake e bancos de teste isolados.
