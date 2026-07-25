<?php

use App\Models\Setting;
use App\Models\User;
use App\Models\VehicleListing;
use App\Services\RssImportService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('luzicity:create-admin {email} {--name=Administrador Luzicity} {--password=}', function () {
    $password = $this->option('password') ?: $this->secret('Senha do administrador');

    if (! $password || strlen($password) < 8) {
        $this->error('A senha precisa ter pelo menos 8 caracteres.');
        return self::FAILURE;
    }

    $user = User::updateOrCreate(
        ['email' => $this->argument('email')],
        [
            'name' => $this->option('name'),
            'password' => Hash::make($password),
            'is_active' => true,
            'email_verified_at' => now(),
        ]
    );

    $user->syncRoles(['Super Admin', 'Admin']);

    $this->info('Administrador criado ou atualizado com sucesso.');
    return self::SUCCESS;
})->purpose('Create or update the first Luzicity administrator');

Artisan::command('luzicity:sync-vehicle-brand-logos', function () {
    VehicleListing::query()
        ->select('vehicle_type', 'brand')
        ->distinct()
        ->orderBy('vehicle_type')
        ->orderBy('brand')
        ->get()
        ->each(fn (VehicleListing $vehicle) => Setting::appendVehicleBrandLogoIfMissing($vehicle->brand, $vehicle->vehicle_type));

    foreach (array_keys(Setting::vehicleTypeOptions()) as $type) {
        Setting::query()->updateOrCreate(
            ['group' => 'vehicle_classifieds', 'key' => 'brand_logos_'.$type],
            ['value' => Setting::normalizeVehicleBrandLogosText(Setting::vehicleBrandLogosText($type))]
        );
    }

    $this->info('Logos das marcas de veículos sincronizadas por tipo.');
    return self::SUCCESS;
})->purpose('Sincroniza automaticamente as logos das marcas anunciadas nos classificados');

Artisan::command('luzicity:import-rss {--limit=12}', function () {
    $limit = max(1, min(30, (int) $this->option('limit')));
    $summary = app(RssImportService::class)->importAll($limit);

    $this->info("Importacao RSS concluida: {$summary['created']} nova(s), {$summary['updated']} atualizada(s), {$summary['failed']} falha(s).");

    foreach ($summary['messages'] as $message) {
        $this->line($message);
    }

    return self::SUCCESS;
})->purpose('Importa noticias dos feeds RSS ativos para o banco de dados');

use Illuminate\Support\Facades\Schedule;

Schedule::command('luzicity:rss-collect-due --limit=12 --feeds=50')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);

Schedule::command('luzicity:rss-analyze-trends')->hourly()->withoutOverlapping(30);
