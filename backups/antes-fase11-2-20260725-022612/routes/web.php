<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AdminAiSettingsController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminCompanyInfoController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminMediaBannerController;
use App\Http\Controllers\AdminPushNotificationController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminRadioController;
use App\Http\Controllers\AdminRealEstateController;
use App\Http\Controllers\AdminRssFeedController;
use App\Http\Controllers\AdminRssImportController;
use App\Http\Controllers\AdminSiteContentController;
use App\Http\Controllers\AdminSocialLinkController;
use App\Http\Controllers\AdminSocialLoginController;
use App\Http\Controllers\AdminSystemHealthController;
use App\Http\Controllers\AdminTagController;
use App\Http\Controllers\AdminTrackingPixelController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminVehicleClassifiedController;
use App\Http\Controllers\AiWritingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityNewsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventGalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\RadioController;
use App\Http\Controllers\RadioRequestController;
use App\Http\Controllers\RealEstateController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\VehicleClassifiedController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/', HomeController::class)->name('home');
Route::get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');
Route::get('/firebase-messaging-sw.js', [PwaController::class, 'firebaseServiceWorker'])->name('pwa.firebase-sw');
Route::post('/push/subscriptions', [PushSubscriptionController::class, 'store'])->middleware('throttle:20,1')->name('push-subscriptions.store');
Route::delete('/push/subscriptions', [PushSubscriptionController::class, 'destroy'])->middleware('throttle:20,1')->name('push-subscriptions.destroy');
Route::get('/buscar', [SearchController::class, 'index'])->name('search.index');
Route::get('/buscar/sugestoes', [SearchController::class, 'suggestions'])
    ->middleware('throttle:60,1')
    ->name('search.suggestions');
Route::get('/quem-somos', AboutController::class)->name('about');
Route::get('/fotos-eventos', EventGalleryController::class)->name('events.gallery');
Route::get('/radio', [RadioController::class, 'index'])->name('radio.index');
Route::post('/radio/pedidos', [RadioRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('radio.requests.store');
Route::get('/cidades/{city}', [CityNewsController::class, 'show'])->name('cities.show');
Route::get('/noticias/{news:slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/classificados-veiculos', [VehicleClassifiedController::class, 'index'])->name('vehicles.index');
Route::get('/classificados-veiculos/{vehicle}', [VehicleClassifiedController::class, 'show'])->name('vehicles.show');
Route::get('/imoveis', [RealEstateController::class, 'index'])->name('real-estate.index');
Route::get('/imoveis/{property}', [RealEstateController::class, 'show'])->name('real-estate.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/login/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/login/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::post('/ai-writing', [AiWritingController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('admin.ai-writing.store');

    Route::get('/classificados-veiculos/anunciar/novo', [VehicleClassifiedController::class, 'create'])->name('vehicles.create');
    Route::post('/classificados-veiculos/anunciar', [VehicleClassifiedController::class, 'store'])->name('vehicles.store');
    Route::get('/imoveis/anunciar/novo', [RealEstateController::class, 'create'])->name('real-estate.create');
    Route::post('/imoveis/anunciar', [RealEstateController::class, 'store'])->name('real-estate.store');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'roles:Super Admin,Admin'])
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('index');
        Route::get('/saude-do-sistema', AdminSystemHealthController::class)->name('system-health.index');
        Route::get('/notificacoes-push', [AdminPushNotificationController::class, 'index'])->name('push-notifications.index');
        Route::post('/notificacoes-push/enviar', [AdminPushNotificationController::class, 'send'])->middleware('throttle:5,1')->name('push-notifications.send');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::get('/social-links', [AdminSocialLinkController::class, 'edit'])->name('social-links.edit');
        Route::put('/social-links', [AdminSocialLinkController::class, 'update'])->name('social-links.update');
        Route::get('/social-login', [AdminSocialLoginController::class, 'edit'])->name('social-login.edit');
        Route::put('/social-login', [AdminSocialLoginController::class, 'update'])->name('social-login.update');
        Route::get('/tracking-pixels', [AdminTrackingPixelController::class, 'edit'])->name('tracking-pixels.edit');
        Route::put('/tracking-pixels', [AdminTrackingPixelController::class, 'update'])->name('tracking-pixels.update');
        Route::get('/company-info', [AdminCompanyInfoController::class, 'edit'])->name('company-info.edit');
        Route::put('/company-info', [AdminCompanyInfoController::class, 'update'])->name('company-info.update');
        Route::get('/upload-diagnostico', function () {
            $uploadTmp = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
            $systemTmp = ini_get('sys_temp_dir') ?: sys_get_temp_dir();

            return response()->json([
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'upload_tmp_dir' => $uploadTmp,
                'upload_tmp_dir_existe' => is_dir($uploadTmp),
                'upload_tmp_dir_gravavel' => is_writable($uploadTmp),
                'sys_temp_dir' => $systemTmp,
                'sys_temp_dir_existe' => is_dir($systemTmp),
                'sys_temp_dir_gravavel' => is_writable($systemTmp),
            ]);
        })->name('upload-diagnostics');
        Route::get('/site-content', [AdminSiteContentController::class, 'edit'])->name('site-content.edit');
        Route::put('/site-content', [AdminSiteContentController::class, 'update'])->name('site-content.update');
        Route::get('/configuracoes/ia', [AdminAiSettingsController::class, 'edit'])->name('ai-settings.edit');
        Route::put('/ia', [AdminAiSettingsController::class, 'update'])->name('ai-settings.update');
        Route::post('/ia/testar', [AdminAiSettingsController::class, 'test'])->middleware('throttle:5,1')->name('ai-settings.test');
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::get('/tags', [AdminTagController::class, 'index'])->name('tags.index');
        Route::post('/tags', [AdminTagController::class, 'store'])->name('tags.store');
        Route::put('/tags/{tag}', [AdminTagController::class, 'update'])->name('tags.update');
        Route::get('/rss-feeds', [AdminRssFeedController::class, 'index'])->name('rss-feeds.index');
        Route::post('/rss-feeds', [AdminRssFeedController::class, 'store'])->name('rss-feeds.store');
        Route::post('/rss-feeds/refresh', [AdminRssFeedController::class, 'refresh'])->middleware('throttle:5,1')->name('rss-feeds.refresh');
        Route::put('/rss-feeds/{rssFeed}', [AdminRssFeedController::class, 'update'])->name('rss-feeds.update');
        Route::get('/importacao-rss', [AdminRssImportController::class, 'index'])->name('rss-imports.index');
        Route::post('/importacao-rss/importar', [AdminRssImportController::class, 'import'])->middleware('throttle:5,1')->name('rss-imports.import');
        Route::put('/importacao-rss/{article}', [AdminRssImportController::class, 'update'])->name('rss-imports.update');
        Route::get('/radio', [AdminRadioController::class, 'edit'])->name('radio.edit');
        Route::put('/radio', [AdminRadioController::class, 'update'])->name('radio.update');
        Route::get('/media-banners', [AdminMediaBannerController::class, 'index'])->name('media-banners.index');
        Route::put('/media-banners/transmissao-home', [AdminMediaBannerController::class, 'updateHomeLiveBroadcast'])->name('media-banners.home-live.update');
        Route::post('/media-banners', [AdminMediaBannerController::class, 'store'])->name('media-banners.store');
        Route::put('/media-banners/{mediaBanner}', [AdminMediaBannerController::class, 'update'])->name('media-banners.update');
        Route::get('/classificados-veiculos', [AdminVehicleClassifiedController::class, 'index'])->name('vehicles.index');
        Route::put('/classificados-veiculos/configuracao', [AdminVehicleClassifiedController::class, 'updateSettings'])->name('vehicles.settings.update');
        Route::post('/classificados-veiculos/logos', [AdminVehicleClassifiedController::class, 'uploadBrandLogo'])->name('vehicles.logos.upload');
        Route::put('/classificados-veiculos/{vehicle}', [AdminVehicleClassifiedController::class, 'updateListing'])->name('vehicles.update');
        Route::get('/imoveis', [AdminRealEstateController::class, 'index'])->name('real-estate.index');
        Route::put('/imoveis/{property}', [AdminRealEstateController::class, 'update'])->name('real-estate.update');
    });

Route::prefix('admin/news')
    ->name('admin.news.')
    ->middleware(['auth', 'roles:Super Admin,Admin,Jornalista,Colunista'])
    ->group(function () {
        Route::get('/', [AdminNewsController::class, 'index'])->name('index');
        Route::get('/create', [AdminNewsController::class, 'create'])->name('create');
        Route::post('/', [AdminNewsController::class, 'store'])->name('store');
        Route::get('/{news}/edit', [AdminNewsController::class, 'edit'])->name('edit');
        Route::put('/{news}', [AdminNewsController::class, 'update'])->name('update');
    });

require __DIR__.'/ai_editorial.php';

require __DIR__.'/ai_dashboard.php';

require __DIR__.'/ai_prompts.php';

require __DIR__.'/ai_memory.php';

require __DIR__.'/ai_providers.php';

require __DIR__.'/ai_costs_logs.php';

require __DIR__.'/editorial_pitches.php';

require __DIR__.'/ai_agents.php';

require __DIR__.'/editorial_sources.php';

require __DIR__.'/editorial_verification.php';

require __DIR__.'/news_workflow.php';

require __DIR__.'/editorial_calendar.php';

require __DIR__.'/editorial_room.php';

require __DIR__.'/ai_news.php';

require __DIR__.'/rss_trends.php';

require __DIR__.'/rss_pre_pitches.php';

require __DIR__.'/agency_approval.php';

require __DIR__.'/agency_dashboard.php';

require __DIR__.'/radio_structure.php';

require __DIR__.'/podcasts.php';
