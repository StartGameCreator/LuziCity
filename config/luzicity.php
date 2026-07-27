<?php

return [
    'theme' => [
        'background_image' => env('LUZICITY_BACKGROUND_IMAGE', ''),
        'background_opacity' => env('LUZICITY_BACKGROUND_OPACITY', '0.34'),
    ],

    'city_locations' => [
        ['name' => 'Distrito Federal', 'state' => 'DF', 'slug' => 'distrito-federal'],
        ['name' => 'Luziânia', 'state' => 'GO', 'slug' => 'luziania'],
        ['name' => 'Cristalina', 'state' => 'GO', 'slug' => 'cristalina'],
        ['name' => 'Cidade Ocidental', 'state' => 'GO', 'slug' => 'cidade-ocidental'],
        ['name' => 'Valparaíso de Goiás', 'state' => 'GO', 'slug' => 'valparaiso-de-goias'],
        ['name' => 'Novo Gama', 'state' => 'GO', 'slug' => 'novo-gama'],
        ['name' => 'Santo Antônio do Descoberto', 'state' => 'GO', 'slug' => 'santo-antonio-do-descoberto'],
        ['name' => 'Águas Lindas de Goiás', 'state' => 'GO', 'slug' => 'aguas-lindas-de-goias'],
        ['name' => 'Formosa', 'state' => 'GO', 'slug' => 'formosa'],
    ],

    'social_links' => [
        'facebook' => ['label' => 'Facebook', 'url' => env('LUZICITY_FACEBOOK_URL', '#')],
        'instagram' => ['label' => 'Instagram', 'url' => env('LUZICITY_INSTAGRAM_URL', '#')],
        'tiktok' => ['label' => 'TikTok', 'url' => env('LUZICITY_TIKTOK_URL', '#')],
        'youtube' => ['label' => 'YouTube', 'url' => env('LUZICITY_YOUTUBE_URL', '#')],
        'apple_music' => ['label' => 'Apple Music', 'url' => env('LUZICITY_APPLE_MUSIC_URL', '#')],
        'deezer' => ['label' => 'Deezer', 'url' => env('LUZICITY_DEEZER_URL', '#')],
        'kwai' => ['label' => 'Kwai', 'url' => env('LUZICITY_KWAI_URL', '#')],
        'rumble' => ['label' => 'Rumble', 'url' => env('LUZICITY_RUMBLE_URL', '#')],
        'dlive' => ['label' => 'DLive', 'url' => env('LUZICITY_DLIVE_URL', '#')],
    ],

    'shop' => [
        'url' => env('LUZICITY_SHOP_URL', '#'),
    ],

    'google_ads' => [
        'client' => env('LUZICITY_GOOGLE_ADS_CLIENT', ''),
        'slots' => [
            'home_top' => env('LUZICITY_GOOGLE_AD_HOME_TOP', ''),
            'home_after_hero' => env('LUZICITY_GOOGLE_AD_HOME_AFTER_HERO', ''),
            'home_before_latest' => env('LUZICITY_GOOGLE_AD_HOME_BEFORE_LATEST', ''),
            'home_after_latest' => env('LUZICITY_GOOGLE_AD_HOME_AFTER_LATEST', ''),
            'home_before_topics' => env('LUZICITY_GOOGLE_AD_HOME_BEFORE_TOPICS', ''),
            'home_footer' => env('LUZICITY_GOOGLE_AD_HOME_FOOTER', ''),
            'radio_hero_1' => env('LUZICITY_GOOGLE_AD_RADIO_HERO_1', ''),
            'radio_hero_2' => env('LUZICITY_GOOGLE_AD_RADIO_HERO_2', ''),
            'radio_hero_3' => env('LUZICITY_GOOGLE_AD_RADIO_HERO_3', ''),
            'radio_hero_4' => env('LUZICITY_GOOGLE_AD_RADIO_HERO_4', ''),
            'vehicles_top' => env('LUZICITY_GOOGLE_AD_VEHICLES_TOP', ''),
            'vehicles_after_search' => env('LUZICITY_GOOGLE_AD_VEHICLES_AFTER_SEARCH', ''),
            'vehicles_sidebar' => env('LUZICITY_GOOGLE_AD_VEHICLES_SIDEBAR', ''),
            'vehicles_footer' => env('LUZICITY_GOOGLE_AD_VEHICLES_FOOTER', ''),
        ],
    ],

    'tracking_pixels' => [
        'meta_pixel_id' => env('LUZICITY_META_PIXEL_ID', ''),
        'tiktok_pixel_id' => env('LUZICITY_TIKTOK_PIXEL_ID', ''),
    ],

    'ai' => [
        'provider' => env('LUZICITY_AI_PROVIDER', 'chatgpt'),
        'openai_api_key' => env('OPENAI_API_KEY', ''),
        'chatgpt_model' => env('LUZICITY_CHATGPT_MODEL', 'gpt-4o-mini'),
        'gemini_api_key' => env('GEMINI_API_KEY', ''),
        'gemini_model' => env('LUZICITY_GEMINI_MODEL', 'gemini-1.5-flash'),
        'copilot_api_key' => env('COPILOT_API_KEY', ''),
        'copilot_endpoint' => env('LUZICITY_COPILOT_ENDPOINT', ''),
    ],

    'social_providers' => [
        'microsoft' => ['label' => 'Microsoft'],
        'apple' => ['label' => 'Apple'],
        'google' => ['label' => 'Google'],
        'facebook' => ['label' => 'Facebook'],
        'instagram' => ['label' => 'Instagram'],
        'tiktok' => ['label' => 'TikTok'],
    ],

    'home_cache_ttl_minutes' => (int) env('LUZICITY_HOME_CACHE_TTL', 5),
    'public_cache_ttl_seconds' => max(10, (int) env('LUZICITY_PUBLIC_CACHE_TTL', 60)),
    'rss_queue_enabled' => filter_var(env('LUZICITY_RSS_QUEUE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'rss_queue' => env('LUZICITY_RSS_QUEUE', 'rss'),
];
