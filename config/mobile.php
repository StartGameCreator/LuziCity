<?php

return [
    'android' => [
        'package' => env('ANDROID_APP_PACKAGE', 'com.luzicity.app'),
        'sha256_fingerprints' => array_values(array_filter(array_map(
            'trim', explode(',', (string) env('ANDROID_SHA256_FINGERPRINTS', ''))
        ))),
    ],
    'ios' => [
        'team_id' => env('IOS_TEAM_ID', ''),
        'bundle_id' => env('IOS_BUNDLE_ID', 'com.luzicity.app'),
    ],
];
