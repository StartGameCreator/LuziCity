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

    if (Notification.permission === 'granted') {
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
