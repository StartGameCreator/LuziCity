<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
    ];

    public static function socialLinks(): array
    {
        $links = config('luzicity.social_links', []);

        if (! Schema::hasTable('settings')) {
            return $links;
        }

        $savedLinks = self::query()->where('group', 'social_links')->pluck('value', 'key');

        foreach ($links as $key => $social) {
            if ($savedLinks->has($key)) {
                $links[$key]['url'] = $savedLinks->get($key) ?: '#';
            }
        }

        return $links;
    }

    public static function socialLoginProviders(): array
    {
        $providers = config('luzicity.social_providers', []);

        foreach ($providers as $key => $provider) {
            $service = config("services.$key", []);

            $providers[$key] = array_merge($provider, [
                'enabled' => true,
                'client_id' => $service['client_id'] ?? '',
                'client_secret' => $service['client_secret'] ?? '',
                'redirect' => $service['redirect'] ?: url("/login/{$key}/callback"),
            ]);
        }

        if (! Schema::hasTable('settings')) {
            return $providers;
        }

        $saved = self::query()->where('group', 'social_login')->pluck('value', 'key');

        foreach ($providers as $key => $provider) {
            $providers[$key]['enabled'] = $saved->has("{$key}_enabled")
                ? filter_var($saved->get("{$key}_enabled"), FILTER_VALIDATE_BOOLEAN)
                : (bool) $provider['enabled'];

            foreach (['client_id', 'client_secret', 'redirect'] as $field) {
                if ($saved->has("{$key}_{$field}")) {
                    $providers[$key][$field] = $saved->get("{$key}_{$field}") ?: '';
                }
            }
        }

        return $providers;
    }

    public static function enabledSocialLoginProviders(): array
    {
        return collect(self::socialLoginProviders())
            ->filter(fn (array $provider) => $provider['enabled'])
            ->all();
    }

    public static function socialLoginProvider(string $provider): ?array
    {
        return self::socialLoginProviders()[$provider] ?? null;
    }

    public static function updateSocialLoginProviders(array $providers): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        foreach ($providers as $provider => $values) {
            self::query()->updateOrCreate(
                ['group' => 'social_login', 'key' => "{$provider}_enabled"],
                ['value' => ! empty($values['enabled']) ? '1' : '0']
            );

            foreach (['client_id', 'client_secret', 'redirect'] as $field) {
                self::query()->updateOrCreate(
                    ['group' => 'social_login', 'key' => "{$provider}_{$field}"],
                    ['value' => (string) ($values[$field] ?? '')]
                );
            }
        }
    }

    public static function shopUrl(): string
    {
        $url = config('luzicity.shop.url', '#');

        if (! Schema::hasTable('settings')) {
            return $url ?: '#';
        }

        return self::query()->where('group', 'general')->where('key', 'shop_url')->value('value') ?: ($url ?: '#');
    }

    public static function localCommerceUrl(): string
    {
        if (! Schema::hasTable('settings')) {
            return '#';
        }

        return self::query()->where('group', 'general')->where('key', 'local_commerce_url')->value('value') ?: '#';
    }

    public static function googleAds(): array
    {
        $settings = config('luzicity.google_ads', ['client' => '', 'slots' => []]);

        if (! Schema::hasTable('settings')) {
            return $settings;
        }

        $saved = self::query()->where('group', 'google_ads')->pluck('value', 'key');
        $settings['client'] = $saved->get('client') ?: ($settings['client'] ?? '');

        foreach (($settings['slots'] ?? []) as $key => $value) {
            if ($saved->has("slot_{$key}")) {
                $settings['slots'][$key] = $saved->get("slot_{$key}") ?: $value;
            }
        }

        return $settings;
    }

    public static function radioSettings(): array
    {
        $settings = [
            'tiktok_embed_code' => '',
            'tiktok_url' => '',
            'tiktok_orientation' => 'portrait',
            'audio_stream_url' => '',
            'schedule_text' => 'Ao vivo com mÃºsica, notÃ­cias, entrevistas e participaÃ§Ã£o dos ouvintes.',
            'field_live_enabled' => false,
            'field_live_title' => 'Cobertura externa ao vivo',
            'field_live_description' => 'TransmissÃ£o direto da rua, eventos, campanhas e aÃ§Ãµes especiais da Luzicity.',
            'field_video_embed_code' => '',
            'field_video_url' => '',
            'field_audio_stream_url' => '',
            'field_rtmp_server' => '',
            'field_rtmp_key' => '',
            'field_reporter_whatsapp' => '',
            'field_return_link' => '',
            'field_team_notes' => '',
        ];

        if (! Schema::hasTable('settings')) {
            return $settings;
        }

        $saved = self::query()->where('group', 'radio')->pluck('value', 'key');

        foreach ($settings as $key => $value) {
            if ($saved->has($key)) {
                $settings[$key] = $key === 'field_live_enabled'
                    ? filter_var($saved->get($key), FILTER_VALIDATE_BOOLEAN)
                    : ($saved->get($key) ?: $value);
            }
        }

        return $settings;
    }

    public static function homeLiveBroadcast(): array
    {
        $settings = [
            'enabled' => false,
            'provider' => 'tiktok',
            'orientation' => 'portrait',
            'title' => 'Transmissao especial ao vivo',
            'embed_code' => '',
            'external_url' => '',
        ];

        if (! Schema::hasTable('settings')) {
            return $settings;
        }

        $saved = self::query()->where('group', 'home_live_broadcast')->pluck('value', 'key');

        foreach ($settings as $key => $value) {
            if ($saved->has($key)) {
                $settings[$key] = $key === 'enabled'
                    ? filter_var($saved->get($key), FILTER_VALIDATE_BOOLEAN)
                    : ($saved->get($key) ?: $value);
            }
        }

        $settings['provider'] = in_array($settings['provider'], ['tiktok', 'dlive'], true) ? $settings['provider'] : 'tiktok';
        $settings['orientation'] = $settings['orientation'] === 'landscape' ? 'landscape' : 'portrait';

        return $settings;
    }

    public static function updateHomeLiveBroadcast(array $data): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        foreach ($data as $key => $value) {
            self::query()->updateOrCreate(
                ['group' => 'home_live_broadcast', 'key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
            );
        }
    }

    public static function trackingPixels(): array
    {
        $settings = config('luzicity.tracking_pixels', ['meta_pixel_id' => '', 'tiktok_pixel_id' => '']);

        if (! Schema::hasTable('settings')) {
            return $settings;
        }

        $saved = self::query()->where('group', 'tracking_pixels')->pluck('value', 'key');

        foreach ($settings as $key => $value) {
            if ($saved->has($key)) {
                $settings[$key] = $saved->get($key) ?: $value;
            }
        }

        return $settings;
    }

    public static function companyInfo(): array
    {
        $settings = [
            'copyright' => 'Copyright Â© '.date('Y').' Luzicity. Todos os direitos reservados.',
            'site_name' => 'Luzicity',
            'site_logo' => '',
            'site_favicon' => '',
            'default_share_image' => '',
            'cnpj' => '',
            'phone' => '',
            'whatsapp' => '',
            'whatsapp_secondary' => '',
            'email' => '',
            'email_secondary' => '',
            'email_tertiary' => '',
            'address' => '',
        ];

        if (! Schema::hasTable('settings')) {
            return $settings;
        }

        $saved = self::query()->where('group', 'company_info')->pluck('value', 'key');

        foreach ($settings as $key => $value) {
            if ($saved->has($key)) {
                $settings[$key] = $saved->get($key) ?: $value;
            }
        }

        return $settings;
    }
    public static function siteIdentity(): array
    {
        $company = self::companyInfo();

        return [
            'name' => $company['site_name'] ?: 'Luzicity',
            'logo' => $company['site_logo'] ?: '',
            'favicon' => $company['site_favicon'] ?: ($company['site_logo'] ?: ''),
            'share_image' => $company['default_share_image'] ?: ($company['site_logo'] ?: ''),
        ];
    }

    public static function aboutContent(): string
    {
        $fallback = 'A Luzicity Ã© uma plataforma de notÃ­cias local, criada para aproximar leitores, cidades, jornalistas, colunistas e anunciantes em uma experiÃªncia leve, acessÃ­vel e preparada para crescer.';

        if (! Schema::hasTable('settings')) {
            return $fallback;
        }

        return self::query()->where('group', 'site_content')->where('key', 'about_content')->value('value') ?: $fallback;
    }

    public static function visualBlocks(): array
    {
        $blocks = self::defaultVisualBlocks();

        if (! Schema::hasTable('settings')) {
            return $blocks;
        }

        $saved = self::query()->where('group', 'visual_blocks')->pluck('value', 'key');

        foreach ($blocks as $blockKey => $block) {
            foreach (['image', 'link', 'label'] as $field) {
                $settingKey = "{$blockKey}_{$field}";

                if ($saved->has($settingKey)) {
                    $blocks[$blockKey][$field] = $saved->get($settingKey) ?: $block[$field];
                }
            }
        }

        return $blocks;
    }

    public static function visualBlock(string $block): array
    {
        return self::visualBlocks()[$block] ?? self::defaultVisualBlocks()['events'];
    }

    public static function eventGallery(): array
    {
        $gallery = [
            'title' => 'Fotos de Eventos',
            'subtitle' => 'Coberturas especiais da Luzicity',
            'location' => 'Luziania, Entorno e regiao',
            'date' => 'Galeria em atualizacao',
            'report' => 'Este espaco fica reservado para a reportagem do evento, com bastidores, atracoes, entrevistas, nomes dos organizadores e os melhores momentos registrados pela equipe Luzicity.',
            'photos' => [
                ['title' => 'Palco principal', 'location' => 'Cobertura Luzicity', 'image' => 'images/events/fotos-eventos.png'],
                ['title' => 'Publico e energia', 'location' => 'Momentos do evento', 'image' => 'images/events/fotos-eventos.png'],
                ['title' => 'Bastidores', 'location' => 'Equipe e convidados', 'image' => 'images/events/fotos-eventos.png'],
                ['title' => 'Melhores registros', 'location' => 'Galeria oficial', 'image' => 'images/events/fotos-eventos.png'],
            ],
        ];

        if (! Schema::hasTable('settings')) {
            return $gallery;
        }

        $saved = self::query()->where('group', 'event_gallery')->pluck('value', 'key');

        foreach (['title', 'subtitle', 'location', 'date', 'report'] as $field) {
            if ($saved->has($field)) {
                $gallery[$field] = $saved->get($field) ?: $gallery[$field];
            }
        }

        for ($index = 0; $index < 8; $index++) {
            $fallback = $gallery['photos'][$index] ?? [
                'title' => 'Foto do evento',
                'location' => 'Galeria Luzicity',
                'image' => 'images/events/fotos-eventos.png',
            ];

            $photo = [
                'title' => $saved->get("photo_{$index}_title") ?: $fallback['title'],
                'location' => $saved->get("photo_{$index}_location") ?: $fallback['location'],
                'image' => $saved->get("photo_{$index}_image") ?: $fallback['image'],
            ];

            if (filled($photo['title']) || filled($photo['image'])) {
                $gallery['photos'][$index] = $photo;
            }
        }

        $gallery['photos'] = collect($gallery['photos'])
            ->filter(fn (array $photo) => filled($photo['image']))
            ->values()
            ->all();

        return $gallery;
    }

    public static function updateEventGallery(array $data): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        foreach (['title', 'subtitle', 'location', 'date', 'report'] as $field) {
            self::query()->updateOrCreate(
                ['group' => 'event_gallery', 'key' => $field],
                ['value' => (string) ($data[$field] ?? '')]
            );
        }

        foreach (($data['photos'] ?? []) as $index => $photo) {
            foreach (['title', 'location', 'image'] as $field) {
                self::query()->updateOrCreate(
                    ['group' => 'event_gallery', 'key' => "photo_{$index}_{$field}"],
                    ['value' => (string) ($photo[$field] ?? '')]
                );
            }
        }
    }

    public static function updateVisualBlock(string $block, array $data): void
    {
        if (! Schema::hasTable('settings') || ! array_key_exists($block, self::defaultVisualBlocks())) {
            return;
        }

        foreach (['image', 'link', 'label'] as $field) {
            if (array_key_exists($field, $data)) {
                self::query()->updateOrCreate(
                    ['group' => 'visual_blocks', 'key' => "{$block}_{$field}"],
                    ['value' => (string) ($data[$field] ?? '')]
                );
            }
        }
    }

    private static function defaultVisualBlocks(): array
    {
        return [
            'events' => [
                'label' => 'Fotos Eventos',
                'image' => 'images/events/fotos-eventos.png',
                'link' => '/fotos-eventos',
            ],
            'real_estate' => [
                'label' => 'Imoveis',
                'image' => 'images/real-estate/banner-imoveis.png',
                'link' => '/imoveis',
            ],
            'vehicles' => [
                'label' => 'Classificados de Veiculos',
                'image' => 'images/classificados-veiculos-banner.png',
                'link' => '/classificados-veiculos',
            ],
        ];
    }

    public static function aiSettings(): array
    {
        $settings = [
            'provider' => config('luzicity.ai.provider', 'chatgpt'),
            'openai_api_key' => config('luzicity.ai.openai_api_key', ''),
            'chatgpt_model' => config('luzicity.ai.chatgpt_model', 'gpt-4o-mini'),
            'gemini_api_key' => config('luzicity.ai.gemini_api_key', ''),
            'gemini_model' => config('luzicity.ai.gemini_model', 'gemini-1.5-flash'),
            'copilot_api_key' => config('luzicity.ai.copilot_api_key', ''),
            'copilot_endpoint' => config('luzicity.ai.copilot_endpoint', ''),
        ];

        if (! Schema::hasTable('settings')) {
            return $settings;
        }

        $saved = self::query()->where('group', 'ai')->pluck('value', 'key');

        foreach ($settings as $key => $value) {
            if ($saved->has($key)) {
                $settings[$key] = $saved->get($key) ?: $value;
            }
        }

        return $settings;
    }

    public static function vehicleClassifiedSettings(): array
    {
        $settings = [
            'limit_enabled' => false,
            'max_active_listings' => 5,
        ];

        if (! Schema::hasTable('settings')) {
            return $settings;
        }

        $saved = self::query()->where('group', 'vehicle_classifieds')->pluck('value', 'key');

        if ($saved->has('limit_enabled')) {
            $settings['limit_enabled'] = filter_var($saved->get('limit_enabled'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($saved->has('max_active_listings')) {
            $settings['max_active_listings'] = max(1, (int) $saved->get('max_active_listings'));
        }

        return $settings;
    }

    public static function vehicleTypeOptions(): array
    {
        return [
            'car' => 'Carros',
            'motorcycle' => 'Motos',
            'nautical' => 'EmbarcaÃ§Ãµes NÃ¡uticas',
        ];
    }

    public static function normalizeVehicleType(?string $vehicleType): string
    {
        return array_key_exists($vehicleType, self::vehicleTypeOptions()) ? $vehicleType : 'car';
    }

    public static function vehicleBrandLogosText(string $vehicleType = 'car'): string
    {
        $vehicleType = self::normalizeVehicleType($vehicleType);
        $fallback = collect(self::defaultVehicleBrandLogos($vehicleType))
            ->map(fn (array $brand) => $brand['name'].'|'.($brand['logo_url'] ?? ''))
            ->implode(PHP_EOL);

        if (! Schema::hasTable('settings')) {
            return $fallback;
        }

        return self::query()
            ->where('group', 'vehicle_classifieds')
            ->where('key', self::vehicleBrandLogosKey($vehicleType))
            ->value('value') ?: $fallback;
    }

    public static function vehicleBrandLogos(string $vehicleType = 'car'): array
    {
        return collect(self::parseVehicleBrandLogos(self::vehicleBrandLogosText($vehicleType)))->values()->all();
    }

    public static function normalizeVehicleBrandLogosText(?string $text): string
    {
        return collect(self::parseVehicleBrandLogos($text ?: ''))
            ->map(fn (array $brand) => $brand['name'].'|'.$brand['logo_url'])
            ->implode(PHP_EOL);
    }

    public static function appendVehicleBrandLogoIfMissing(string $brandName, string $vehicleType = 'car'): void
    {
        $brandName = trim($brandName);
        $vehicleType = self::normalizeVehicleType($vehicleType);

        if (! filled($brandName) || ! Schema::hasTable('settings')) {
            return;
        }

        $brands = self::parseVehicleBrandLogos(self::vehicleBrandLogosText($vehicleType));
        $alreadyExists = collect($brands)->contains(
            fn (array $brand) => self::vehicleBrandKey($brand['name']) === self::vehicleBrandKey($brandName)
        );

        if ($alreadyExists) {
            return;
        }

        $brands[] = [
            'name' => $brandName,
            'logo_url' => self::vehicleBrandLogoUrl($brandName),
        ];

        self::query()->updateOrCreate(
            ['group' => 'vehicle_classifieds', 'key' => self::vehicleBrandLogosKey($vehicleType)],
            ['value' => collect($brands)->map(fn (array $brand) => $brand['name'].'|'.$brand['logo_url'])->implode(PHP_EOL)]
        );
    }

    public static function setVehicleBrandLogo(string $brandName, string $logoUrl, string $vehicleType = 'car'): void
    {
        $brandName = trim($brandName);
        $logoUrl = trim($logoUrl);
        $vehicleType = self::normalizeVehicleType($vehicleType);

        if (! filled($brandName) || ! filled($logoUrl) || ! Schema::hasTable('settings')) {
            return;
        }

        $brands = collect(self::parseVehicleBrandLogos(self::vehicleBrandLogosText($vehicleType)))
            ->reject(fn (array $brand) => self::vehicleBrandKey($brand['name']) === self::vehicleBrandKey($brandName))
            ->push([
                'name' => $brandName,
                'logo_url' => $logoUrl,
            ])
            ->sortBy('name')
            ->values();

        self::query()->updateOrCreate(
            ['group' => 'vehicle_classifieds', 'key' => self::vehicleBrandLogosKey($vehicleType)],
            ['value' => $brands->map(fn (array $brand) => $brand['name'].'|'.$brand['logo_url'])->implode(PHP_EOL)]
        );
    }

    private static function vehicleBrandLogosKey(string $vehicleType): string
    {
        return 'brand_logos_'.self::normalizeVehicleType($vehicleType);
    }

    private static function parseVehicleBrandLogos(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(function (string $line) {
                $parts = array_map('trim', explode('|', $line, 2));
                $name = $parts[0] ?? '';

                if (! filled($name)) {
                    return null;
                }

                return [
                    'name' => $name,
                    'logo_url' => filled($parts[1] ?? '') ? $parts[1] : self::vehicleBrandLogoUrl($name),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private static function vehicleBrandLogoUrl(string $brandName): string
    {
        return 'https://cdn.simpleicons.org/'.self::vehicleBrandKey($brandName);
    }

    private static function vehicleBrandKey(string $brandName): string
    {
        $aliases = [
            'vw' => 'volkswagen',
            'volks' => 'volkswagen',
            'citroen' => 'citroen',
            'citroÃ«n' => 'citroen',
            'mercedes' => 'mercedesbenz',
            'mercedes-benz' => 'mercedesbenz',
            'mercedes benz' => 'mercedesbenz',
            'harley davidson' => 'harleydavidson',
            'harley-davidson' => 'harleydavidson',
            'honda marine' => 'honda',
            'caoa chery' => 'chery',
            'bmw' => 'bmw',
        ];

        $normalized = str($brandName)
            ->lower()
            ->ascii()
            ->replace(['&', '+'], ' ')
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();

        return $aliases[$normalized] ?? str_replace(' ', '', $normalized);
    }

    private static function defaultVehicleBrandLogos(string $vehicleType = 'car'): array
    {
        $defaults = [
            'car' => [
                ['name' => 'Fiat', 'logo_url' => 'https://cdn.simpleicons.org/fiat'],
                ['name' => 'Volkswagen', 'logo_url' => 'https://cdn.simpleicons.org/volkswagen'],
                ['name' => 'Chevrolet', 'logo_url' => 'https://cdn.simpleicons.org/chevrolet'],
                ['name' => 'Toyota', 'logo_url' => 'https://cdn.simpleicons.org/toyota'],
                ['name' => 'Honda', 'logo_url' => 'https://cdn.simpleicons.org/honda'],
                ['name' => 'Hyundai', 'logo_url' => 'https://cdn.simpleicons.org/hyundai'],
                ['name' => 'Jeep', 'logo_url' => 'https://cdn.simpleicons.org/jeep'],
                ['name' => 'Renault', 'logo_url' => 'https://cdn.simpleicons.org/renault'],
                ['name' => 'Nissan', 'logo_url' => 'https://cdn.simpleicons.org/nissan'],
                ['name' => 'Ford', 'logo_url' => 'https://cdn.simpleicons.org/ford'],
                ['name' => 'Peugeot', 'logo_url' => 'https://cdn.simpleicons.org/peugeot'],
                ['name' => 'CitroÃ«n', 'logo_url' => 'https://cdn.simpleicons.org/citroen'],
            ],
            'motorcycle' => [
                ['name' => 'Honda', 'logo_url' => 'https://cdn.simpleicons.org/honda'],
                ['name' => 'Yamaha', 'logo_url' => 'https://cdn.simpleicons.org/yamaha'],
                ['name' => 'Suzuki', 'logo_url' => 'https://cdn.simpleicons.org/suzuki'],
                ['name' => 'Kawasaki', 'logo_url' => 'https://cdn.simpleicons.org/kawasaki'],
                ['name' => 'BMW', 'logo_url' => 'https://cdn.simpleicons.org/bmw'],
                ['name' => 'Ducati', 'logo_url' => 'https://cdn.simpleicons.org/ducati'],
                ['name' => 'Harley-Davidson', 'logo_url' => 'https://cdn.simpleicons.org/harleydavidson'],
                ['name' => 'Triumph', 'logo_url' => 'https://cdn.simpleicons.org/triumph'],
            ],
            'nautical' => [
                ['name' => 'Yamaha', 'logo_url' => 'https://cdn.simpleicons.org/yamaha'],
                ['name' => 'Mercury', 'logo_url' => 'https://cdn.simpleicons.org/mercury'],
                ['name' => 'Honda Marine', 'logo_url' => 'https://cdn.simpleicons.org/honda'],
                ['name' => 'Sea-Doo', 'logo_url' => 'https://cdn.simpleicons.org/seadoo'],
                ['name' => 'Bayliner', 'logo_url' => 'https://cdn.simpleicons.org/b'],
                ['name' => 'Ferretti', 'logo_url' => 'https://cdn.simpleicons.org/f'],
            ],
        ];

        return $defaults[self::normalizeVehicleType($vehicleType)];
    }
}

