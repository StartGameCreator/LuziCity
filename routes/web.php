<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AdminAiSettingsController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminCompanyInfoController;
use App\Http\Controllers\AdminMediaBannerController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminRadioController;
use App\Http\Controllers\AdminRealEstateController;
use App\Http\Controllers\AdminRssImportController;
use App\Http\Controllers\AdminRssFeedController;
use App\Http\Controllers\AdminSocialLoginController;
use App\Http\Controllers\AdminSiteContentController;
use App\Http\Controllers\AdminSystemHealthController;
use App\Http\Controllers\AdminTagController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminSocialLinkController;
use App\Http\Controllers\AdminTrackingPixelController;
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
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\VehicleClassifiedController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/quem-somos', AboutController::class)->name('about');
Route::get('/fotos-eventos', EventGalleryController::class)->name('events.gallery');
Route::get('/radio', [RadioController::class, 'index'])->name('radio.index');
Route::post('/radio/pedidos', [RadioRequestController::class, 'store'])->name('radio.requests.store');
Route::get('/cidades/{city}', [CityNewsController::class, 'show'])->name('cities.show');
Route::get('/noticias/{news:slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/classificados-veiculos', [VehicleClassifiedController::class, 'index'])->name('vehicles.index');
Route::get('/imoveis', [RealEstateController::class, 'index'])->name('real-estate.index');
Route::post('/ai-writing', [AiWritingController::class, 'store'])->name('admin.ai-writing.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/login/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/admin', AdminDashboardController::class)->name('admin.index');
    Route::get('/admin/saude-do-sistema', AdminSystemHealthController::class)->name('admin.system-health.index');
    Route::get('/classificados-veiculos/anunciar/novo', [VehicleClassifiedController::class, 'create'])->name('vehicles.create');
    Route::post('/classificados-veiculos/anunciar', [VehicleClassifiedController::class, 'store'])->name('vehicles.store');
    Route::get('/imoveis/anunciar/novo', [RealEstateController::class, 'create'])->name('real-estate.create');
    Route::post('/imoveis/anunciar', [RealEstateController::class, 'store'])->name('real-estate.store');
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::get('/admin/social-links', [AdminSocialLinkController::class, 'edit'])->name('admin.social-links.edit');
    Route::put('/admin/social-links', [AdminSocialLinkController::class, 'update'])->name('admin.social-links.update');
    Route::get('/admin/social-login', [AdminSocialLoginController::class, 'edit'])->name('admin.social-login.edit');
    Route::put('/admin/social-login', [AdminSocialLoginController::class, 'update'])->name('admin.social-login.update');
    Route::get('/admin/tracking-pixels', [AdminTrackingPixelController::class, 'edit'])->name('admin.tracking-pixels.edit');
    Route::put('/admin/tracking-pixels', [AdminTrackingPixelController::class, 'update'])->name('admin.tracking-pixels.update');
    Route::get('/admin/company-info', [AdminCompanyInfoController::class, 'edit'])->name('admin.company-info.edit');
    Route::put('/admin/company-info', [AdminCompanyInfoController::class, 'update'])->name('admin.company-info.update');
    Route::get('/admin/upload-diagnostico', function () {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);

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
    })->name('admin.upload-diagnostics');
    Route::get('/admin/site-content', [AdminSiteContentController::class, 'edit'])->name('admin.site-content.edit');
    Route::put('/admin/site-content', [AdminSiteContentController::class, 'update'])->name('admin.site-content.update');
    Route::get('/admin/ia', [AdminAiSettingsController::class, 'edit'])->name('admin.ai-settings.edit');
    Route::put('/admin/ia', [AdminAiSettingsController::class, 'update'])->name('admin.ai-settings.update');
    Route::post('/admin/ia/testar', [AdminAiSettingsController::class, 'test'])->name('admin.ai-settings.test');
    Route::get('/admin/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/admin/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/admin/categories/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
    Route::get('/admin/tags', [AdminTagController::class, 'index'])->name('admin.tags.index');
    Route::post('/admin/tags', [AdminTagController::class, 'store'])->name('admin.tags.store');
    Route::put('/admin/tags/{tag}', [AdminTagController::class, 'update'])->name('admin.tags.update');
    Route::get('/admin/rss-feeds', [AdminRssFeedController::class, 'index'])->name('admin.rss-feeds.index');
    Route::post('/admin/rss-feeds', [AdminRssFeedController::class, 'store'])->name('admin.rss-feeds.store');
    Route::post('/admin/rss-feeds/refresh', [AdminRssFeedController::class, 'refresh'])->name('admin.rss-feeds.refresh');
    Route::put('/admin/rss-feeds/{rssFeed}', [AdminRssFeedController::class, 'update'])->name('admin.rss-feeds.update');
    Route::get('/admin/importacao-rss', [AdminRssImportController::class, 'index'])->name('admin.rss-imports.index');
    Route::post('/admin/importacao-rss/importar', [AdminRssImportController::class, 'import'])->name('admin.rss-imports.import');
    Route::put('/admin/importacao-rss/{article}', [AdminRssImportController::class, 'update'])->name('admin.rss-imports.update');
    Route::get('/admin/radio', [AdminRadioController::class, 'edit'])->name('admin.radio.edit');
    Route::put('/admin/radio', [AdminRadioController::class, 'update'])->name('admin.radio.update');
    Route::get('/admin/media-banners', [AdminMediaBannerController::class, 'index'])->name('admin.media-banners.index');
    Route::put('/admin/media-banners/transmissao-home', [AdminMediaBannerController::class, 'updateHomeLiveBroadcast'])->name('admin.media-banners.home-live.update');
    Route::post('/admin/media-banners', [AdminMediaBannerController::class, 'store'])->name('admin.media-banners.store');
    Route::put('/admin/media-banners/{mediaBanner}', [AdminMediaBannerController::class, 'update'])->name('admin.media-banners.update');
    Route::get('/admin/classificados-veiculos', [AdminVehicleClassifiedController::class, 'index'])->name('admin.vehicles.index');
    Route::put('/admin/classificados-veiculos/configuracao', [AdminVehicleClassifiedController::class, 'updateSettings'])->name('admin.vehicles.settings.update');
    Route::post('/admin/classificados-veiculos/logos', [AdminVehicleClassifiedController::class, 'uploadBrandLogo'])->name('admin.vehicles.logos.upload');
    Route::put('/admin/classificados-veiculos/{vehicle}', [AdminVehicleClassifiedController::class, 'updateListing'])->name('admin.vehicles.update');
    Route::get('/admin/imoveis', [AdminRealEstateController::class, 'index'])->name('admin.real-estate.index');
    Route::put('/admin/imoveis/{property}', [AdminRealEstateController::class, 'update'])->name('admin.real-estate.update');
    Route::resource('/admin/news', AdminNewsController::class)->except(['show', 'destroy'])->names('admin.news');
});

Route::get('/classificados-veiculos/{vehicle}', [VehicleClassifiedController::class, 'show'])->name('vehicles.show');
Route::get('/imoveis/{property}', [RealEstateController::class, 'show'])->name('real-estate.show');
