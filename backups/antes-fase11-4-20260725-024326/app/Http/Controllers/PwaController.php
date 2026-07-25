<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

class PwaController extends Controller
{
    public function offline(): View
    {
        return view('pwa.offline');
    }

    public function firebaseServiceWorker(): Response
    {
        $config = [
            'apiKey' => config('services.firebase.api_key'),
            'authDomain' => config('services.firebase.auth_domain'),
            'projectId' => config('services.firebase.project_id'),
            'storageBucket' => config('services.firebase.storage_bucket'),
            'messagingSenderId' => config('services.firebase.messaging_sender_id'),
            'appId' => config('services.firebase.app_id'),
        ];

        $javascript = "importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');\n"
            ."importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');\n"
            .'firebase.initializeApp('.json_encode($config, JSON_UNESCAPED_SLASHES).');'."\n"
            ."const messaging = firebase.messaging();\n"
            ."messaging.onBackgroundMessage((payload) => {\n"
            ." const n = payload.notification || {};\n"
            ." self.registration.showNotification(n.title || 'Luzicity', { body: n.body || '', icon: '/pwa/icon.svg', badge: '/pwa/icon.svg', data: { url: payload.data?.url || '/' } });\n"
            ."});\n"
            ."self.addEventListener('notificationclick', (event) => { event.notification.close(); event.waitUntil(clients.openWindow(event.notification.data?.url || '/')); });\n";

        return response($javascript, 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'no-store, max-age=0',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}
