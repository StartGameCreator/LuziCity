import './bootstrap';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function registerPwa() {
    if (!('serviceWorker' in navigator)) return null;

    const registration = await navigator.serviceWorker.register('/service-worker.js', { scope: '/' });
    const banner = document.querySelector('[data-pwa-update-banner]');
    const apply = document.querySelector('[data-pwa-update-apply]');
    const dismiss = document.querySelector('[data-pwa-update-dismiss]');

    const showUpdate = () => { if (banner) banner.hidden = false; };

    if (registration.waiting) showUpdate();

    registration.addEventListener('updatefound', () => {
        const worker = registration.installing;
        worker?.addEventListener('statechange', () => {
            if (worker.state === 'installed' && navigator.serviceWorker.controller) showUpdate();
        });
    });

    apply?.addEventListener('click', () => registration.waiting?.postMessage({ type: 'SKIP_WAITING' }));
    dismiss?.addEventListener('click', () => { if (banner) banner.hidden = true; });
    navigator.serviceWorker.addEventListener('controllerchange', () => window.location.reload());

    return registration;
}

async function configurePush() {
    const button = document.querySelector('[data-push-trigger]');
    const label = document.querySelector('[data-push-label]');
    const configMeta = document.querySelector('meta[name="luzicity-firebase-config"]');

    if (!button || !label || !configMeta || !('Notification' in window) || !('serviceWorker' in navigator)) return;

    let config;
    try {
        config = JSON.parse(configMeta.content);
    } catch {
        button.hidden = true;
        return;
    }

    if (!config?.apiKey || !config?.projectId || !config?.messagingSenderId || !config?.appId || !config?.vapidKey) {
        button.hidden = true;
        return;
    }

    if (Notification.permission === 'granted') label.textContent = 'Avisos ativos';

    button.addEventListener('click', async () => {
        button.disabled = true;

        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                label.textContent = 'Avisos bloqueados';
                return;
            }

            const [{ initializeApp, getApps }, { getMessaging, getToken }] = await Promise.all([
                import('https://www.gstatic.com/firebasejs/10.14.1/firebase-app.js'),
                import('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging.js'),
            ]);

            // Escopo exclusivo: não substitui o service worker principal do PWA.
            const pushRegistration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', {
                scope: '/firebase-cloud-messaging-push-scope/',
            });

            const firebaseApp = getApps().length ? getApps()[0] : initializeApp(config);
            const token = await getToken(getMessaging(firebaseApp), {
                vapidKey: config.vapidKey,
                serviceWorkerRegistration: pushRegistration,
            });

            if (!token) throw new Error('Firebase não retornou um token.');

            const response = await fetch('/push/subscriptions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    token,
                    device_name: navigator.userAgent,
                    platform: navigator.platform || 'web',
                }),
            });

            if (!response.ok) throw new Error('Falha ao salvar inscrição.');

            localStorage.setItem('luzicity-fcm-token', token);
            label.textContent = 'Avisos ativos';
        } catch (error) {
            console.error('[Luzicity Push]', error);
            label.textContent = 'Tentar novamente';
        } finally {
            button.disabled = false;
        }
    });
}

window.addEventListener('load', () => {
    registerPwa().catch((error) => console.error('[Luzicity PWA]', error));
    configurePush().catch((error) => console.error('[Luzicity Push]', error));
});
