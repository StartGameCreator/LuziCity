<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT_URI'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'instagram' => [
        'client_id' => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect' => env('INSTAGRAM_REDIRECT_URI'),
    ],

    'tiktok' => [
        'client_id' => env('TIKTOK_CLIENT_ID'),
        'client_secret' => env('TIKTOK_CLIENT_SECRET'),
        'redirect' => env('TIKTOK_REDIRECT_URI'),
    ],

    'firebase' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
        'vapid_key' => env('FIREBASE_VAPID_KEY'),
        'service_account' => env('FIREBASE_SERVICE_ACCOUNT'),
    ],

    'mercado_pago' => [
        'access_token' => env('MERCADO_PAGO_ACCESS_TOKEN'),
        'webhook_secret' => env('MERCADO_PAGO_WEBHOOK_SECRET'),
        'sandbox' => env('MERCADO_PAGO_SANDBOX', true),
    ],

    'azuracast' => [
        'enabled' => env('AZURACAST_ENABLED', false),
        'base_url' => env('AZURACAST_BASE_URL', 'http://127.0.0.1:8080'),
        'api_key' => env('AZURACAST_API_KEY'),
        'station_id' => env('AZURACAST_STATION_ID'),
        'station_shortcode' => env('AZURACAST_STATION_SHORTCODE'),
        'timeout' => (int) env('AZURACAST_TIMEOUT', 10),
        'verify_ssl' => filter_var(env('AZURACAST_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),
        'cache_seconds' => (int) env('AZURACAST_CACHE_SECONDS', 10),
    ],

];
