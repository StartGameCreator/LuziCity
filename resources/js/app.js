import './bootstrap';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function createToastController() {
    const toast = document.querySelector('[data-install-toast]');
    const title = document.querySelector('[data-install-title]');
    const message = document.querySelector('[data-install-message]');

    return {
        show(nextTitle, nextMessage) {
            if (!toast || !title || !message) {
                window.alert(`${nextTitle}\n\n${nextMessage}`);
                return;
            }

            title.textContent = nextTitle;
            message.textContent = nextMessage;
            toast.hidden = false;
            toast.classList.add('is-open');

            window.clearTimeout(this.timer);
            this.timer = window.setTimeout(() => {
                toast.classList.remove('is-open');
                window.setTimeout(() => {
                    toast.hidden = true;
                }, 180);
            }, 5200);
        },
        timer: null,
    };
}

const toast = createToastController();

async function registerPwa() {
    if (!('serviceWorker' in navigator)) return null;

    const registration = await navigator.serviceWorker.register('/service-worker.js', {
        scope: '/',
        updateViaCache: 'none',
    });

    const banner = document.querySelector('[data-pwa-update-banner]');
    const apply = document.querySelector('[data-pwa-update-apply]');
    const dismiss = document.querySelector('[data-pwa-update-dismiss]');

    const showUpdate = () => {
        if (banner) banner.hidden = false;
    };

    if (registration.waiting) showUpdate();

    registration.addEventListener('updatefound', () => {
        const worker = registration.installing;

        worker?.addEventListener('statechange', () => {
            if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                showUpdate();
            }
        });
    });

    apply?.addEventListener('click', () => {
        registration.waiting?.postMessage({ type: 'SKIP_WAITING' });
    });

    dismiss?.addEventListener('click', () => {
        if (banner) banner.hidden = true;
    });

    let reloading = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (reloading) return;
        reloading = true;
        window.location.reload();
    });

    const checkForUpdate = () => registration.update().catch((error) => {
        console.debug('[Luzicity PWA Update]', error);
    });

    window.setInterval(checkForUpdate, 60 * 60 * 1000);
    window.addEventListener('online', checkForUpdate);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') checkForUpdate();
    });

    return registration;
}

function configureInstallButton() {
    const button = document.querySelector('[data-install-trigger]');
    const label = document.querySelector('[data-install-label]');

    if (!button || !label) return;

    let deferredPrompt = null;

    const isStandalone = () =>
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;

    const markInstalled = () => {
        button.dataset.installed = 'true';
        button.dataset.ready = 'false';
        button.setAttribute('aria-label', 'Luzicity instalado');
        label.textContent = 'App instalado';
    };

    if (isStandalone()) {
        markInstalled();
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        button.dataset.ready = 'true';
        button.dataset.installed = 'false';
        label.textContent = 'Instale o App';
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        markInstalled();
        toast.show('Luzicity instalado', 'O aplicativo foi instalado com sucesso.');
    });

    button.addEventListener('click', async () => {
        if (isStandalone()) {
            markInstalled();
            toast.show('App já instalado', 'A Luzicity já está aberta como aplicativo.');
            return;
        }

        if (deferredPrompt) {
            try {
                await deferredPrompt.prompt();
                const choice = await deferredPrompt.userChoice;
                deferredPrompt = null;
                button.dataset.ready = 'false';

                if (choice?.outcome === 'accepted') {
                    label.textContent = 'Instalando...';
                } else {
                    label.textContent = 'Instale o App';
                    toast.show('Instalação cancelada', 'A instalação foi cancelada pelo usuário.');
                }
            } catch (error) {
                console.error('[Luzicity Install]', error);
                toast.show('Não foi possível instalar', 'Recarregue a página e tente novamente.');
            }

            return;
        }

        const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
        const isEdge = /edg/i.test(navigator.userAgent);

        if (isIos) {
            toast.show(
                'Instalar no iPhone ou iPad',
                'Toque em Compartilhar e depois em “Adicionar à Tela de Início”.'
            );
            return;
        }

        if (isEdge) {
            toast.show(
                'Instalar no Microsoft Edge',
                'Abra o menu ⋯, escolha “Aplicativos” e depois “Instalar Luzicity”. Também procure o ícone de instalação na barra de endereço.'
            );
            return;
        }

        toast.show(
            'Instalar o aplicativo',
            'Use o menu do navegador e escolha “Instalar aplicativo” ou “Adicionar à tela inicial”. O navegador só libera a instalação quando todos os requisitos do PWA são atendidos.'
        );
    });
}

async function configurePush() {
    const button = document.querySelector('[data-push-trigger]');
    const label = document.querySelector('[data-push-label]');
    const configMeta = document.querySelector('meta[name="luzicity-firebase-config"]');

    if (!button || !label) return;

    if (!configMeta || !('Notification' in window) || !('serviceWorker' in navigator)) {
        button.hidden = true;
        return;
    }

    let config;

    try {
        config = JSON.parse(configMeta.content);
    } catch (error) {
        console.error('[Luzicity Push Config]', error);
        button.hidden = true;
        return;
    }

    const completeConfig =
        config?.apiKey &&
        config?.projectId &&
        config?.messagingSenderId &&
        config?.appId &&
        config?.vapidKey;

    if (!completeConfig) {
        button.hidden = true;
        return;
    }

    const setStatus = (status, text) => {
        button.dataset.status = status;
        label.textContent = text;
    };

    const storedToken = localStorage.getItem('luzicity-fcm-token');

    if (Notification.permission === 'granted' && storedToken) {
        setStatus('active', 'Avisos ativos');
    } else if (Notification.permission === 'denied') {
        setStatus('blocked', 'Avisos bloqueados');
    } else {
        setStatus('idle', 'Ativar avisos');
    }

    button.addEventListener('click', async () => {
        if (Notification.permission === 'denied') {
            setStatus('blocked', 'Avisos bloqueados');
            toast.show(
                'Notificações bloqueadas',
                'Clique no cadeado ao lado do endereço do site, abra as permissões e permita Notificações.'
            );
            return;
        }

        button.disabled = true;

        if (button.dataset.status === 'active') {
            setStatus('loading', 'Desativando...');

            try {
                const token = localStorage.getItem('luzicity-fcm-token');

                if (token) {
                    const response = await fetch('/push/subscriptions', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ token }),
                    });

                    if (!response.ok) throw new Error(`Falha ao remover inscrição (${response.status}).`);
                }

                localStorage.removeItem('luzicity-fcm-token');
                setStatus('idle', 'Ativar avisos');
                toast.show('Avisos desativados', 'Este dispositivo não receberá novas notificações.');
            } catch (error) {
                console.error('[Luzicity Push Disable]', error);
                setStatus('active', 'Avisos ativos');
                toast.show('Falha ao desativar avisos', 'Tente novamente em alguns instantes.');
            } finally {
                button.disabled = false;
            }

            return;
        }

        setStatus('loading', 'Ativando...');

        try {
            const permission = await Notification.requestPermission();

            if (permission !== 'granted') {
                setStatus('blocked', 'Avisos bloqueados');
                toast.show(
                    'Permissão não concedida',
                    'As notificações não foram ativadas. Você poderá mudar essa permissão nas configurações do navegador.'
                );
                return;
            }

            const [{ initializeApp, getApps }, { getMessaging, getToken }] = await Promise.all([
                import('https://www.gstatic.com/firebasejs/10.14.1/firebase-app.js'),
                import('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging.js'),
            ]);

            const pushRegistration = await navigator.serviceWorker.register(
                '/firebase-messaging-sw.js',
                { scope: '/firebase-cloud-messaging-push-scope/', updateViaCache: 'none' }
            );

            await navigator.serviceWorker.ready;

            const firebaseApp = getApps().length ? getApps()[0] : initializeApp(config);
            const token = await getToken(getMessaging(firebaseApp), {
                vapidKey: config.vapidKey,
                serviceWorkerRegistration: pushRegistration,
            });

            if (!token) {
                throw new Error('Firebase não retornou um token de notificação.');
            }

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

            if (!response.ok) {
                const body = await response.text();
                throw new Error(`Falha ao salvar inscrição (${response.status}): ${body}`);
            }

            localStorage.setItem('luzicity-fcm-token', token);
            setStatus('active', 'Avisos ativos');
            toast.show('Avisos ativados', 'Você poderá receber novas notificações da Luzicity.');
        } catch (error) {
            console.error('[Luzicity Push]', error);
            setStatus('error', 'Tentar novamente');
            toast.show(
                'Falha ao ativar avisos',
                'Não foi possível concluir a ativação. Consulte o Console do navegador para ver o motivo técnico.'
            );
        } finally {
            button.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    configureInstallButton();
});

window.addEventListener('load', () => {
    registerPwa().catch((error) => {
        console.error('[Luzicity PWA]', error);
        toast.show('PWA indisponível', 'O Service Worker não pôde ser registrado.');
    });

    configurePush().catch((error) => {
        console.error('[Luzicity Push]', error);
    });
});

const configureFirstPartyAnalytics = () => {
    const endpoint = '/analytics/coletar';
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!token || !window.crypto?.randomUUID || !document.cookie.split('; ').includes('luzicity_analytics_consent=accepted')) return;

    const eventUuid = window.crypto.randomUUID();
    const params = new URLSearchParams(window.location.search);
    const startedAt = Date.now();
    let visibleSince = document.visibilityState === 'visible' ? Date.now() : null;
    let engagedMilliseconds = 0;
    let maxScroll = 0;

    const payload = (event) => ({
        event_uuid: eventUuid,
        event,
        page_path: `${window.location.pathname}${window.location.search}`,
        page_title: document.title,
        news_article_id: Number(document.querySelector('meta[name="analytics-news-id"]')?.content) || null,
        referrer: document.referrer || null,
        source: params.get('utm_source'),
        medium: params.get('utm_medium'),
        campaign: params.get('utm_campaign'),
        content: params.get('utm_content'),
        term: params.get('utm_term'),
        reading_time_seconds: Math.min(86400, Math.round((engagedMilliseconds + (visibleSince ? Date.now() - visibleSince : 0)) / 1000)),
        max_scroll_percent: maxScroll,
    });

    const send = (event) => fetch(endpoint, {
        method: 'POST',
        keepalive: true,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Analytics-Consent': 'accepted'},
        body: JSON.stringify(payload(event)),
    }).catch(() => {});

    const updateScroll = () => {
        const available = document.documentElement.scrollHeight - window.innerHeight;
        maxScroll = Math.max(maxScroll, available > 0 ? Math.min(100, Math.round((window.scrollY / available) * 100)) : 100);
    };
    window.addEventListener('scroll', updateScroll, {passive: true});
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden' && visibleSince) {
            engagedMilliseconds += Date.now() - visibleSince;
            visibleSince = null;
            send('engagement');
        } else if (document.visibilityState === 'visible') {
            visibleSince = Date.now();
        }
    });
    window.addEventListener('pagehide', () => send('engagement'));
    document.querySelectorAll('[data-analytics-share]').forEach((link) => {
        link.addEventListener('click', () => send('share'));
    });
    send('page_view');
};

document.addEventListener('DOMContentLoaded', configureFirstPartyAnalytics);

const configureNativeRadio = () => {
    const root = document.querySelector('[data-radio-native-state]');
    if (!root) return;

    const endpoint = root.dataset.stateUrl;
    const title = root.querySelector('[data-radio-native-title]');
    const artist = root.querySelector('[data-radio-native-artist]');
    const station = root.querySelector('[data-radio-native-station]');
    const listeners = root.querySelector('[data-radio-native-listeners]');
    let failures = 0;
    let timer;

    const update = async () => {
        try {
            const response = await fetch(endpoint, {headers: {Accept: 'application/json'}});
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const state = await response.json();
            failures = 0;

            if (title) title.textContent = state.title || 'Rádio Web Luzicity';
            if (artist) artist.textContent = state.artist || (state.is_online ? 'Transmissão ao vivo' : 'Offline');
            if (station) station.textContent = state.station || 'Rádio Web Luzicity';
            if (listeners) listeners.textContent = `${state.listeners || 0} ouvintes`;

            document.querySelectorAll('[data-radio-audio]').forEach((audio) => {
                if (state.stream_url && audio.src !== state.stream_url) {
                    const wasPlaying = !audio.paused;
                    audio.src = state.stream_url;
                    if (wasPlaying) audio.play().catch(() => {});
                }
            });

            if ('mediaSession' in navigator) {
                navigator.mediaSession.metadata = new MediaMetadata({
                    title: state.title || 'Rádio Web Luzicity',
                    artist: state.artist || state.station || 'Luzicity',
                    album: state.album || 'Ao vivo',
                    artwork: state.art ? [{src: state.art}] : [],
                });
            }
        } catch (error) {
            failures += 1;
            console.warn('[Luzicity Rádio]', error);
        } finally {
            const delay = Math.min(60000, 15000 * Math.max(1, failures));
            timer = window.setTimeout(update, delay);
        }
    };

    update();
    window.addEventListener('pagehide', () => window.clearTimeout(timer), {once: true});
};

document.addEventListener('DOMContentLoaded', configureNativeRadio);
