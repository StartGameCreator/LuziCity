@php
    $googleAdsClient = trim((string) data_get(\App\Models\Setting::googleAds(), 'client', ''));
    $shouldShowGoogleAds = ! auth()->user()?->hasAdFreeAccess();
    $trackingPixels = \App\Models\Setting::trackingPixels();
    $metaPixelId = trim((string) data_get($trackingPixels, 'meta_pixel_id', ''));
    $tiktokPixelId = trim((string) data_get($trackingPixels, 'tiktok_pixel_id', ''));
    $companyInfo = \App\Models\Setting::companyInfo();
    $siteIdentity = \App\Models\Setting::siteIdentity();
    $siteName = data_get($siteIdentity, 'name', 'Luzicity');
    $siteLogo = data_get($siteIdentity, 'logo');
    $siteFavicon = data_get($siteIdentity, 'favicon') ?: 'pwa/icon.svg';
    $defaultShareImage = data_get($siteIdentity, 'share_image') ?: $siteLogo;
    $pageTitle = $title ?? $siteName;
    $metaData = $meta ?? [];
    $metaTitle = data_get($metaData, 'title', $pageTitle);
    $metaDescription = data_get($metaData, 'description', 'Noticias, radio web, classificados, imoveis e comercio local na Luzicity.');
    $metaImage = data_get($metaData, 'image', $defaultShareImage);
    $metaType = data_get($metaData, 'type', 'website');
    $metaUrl = data_get($metaData, 'url', url()->current());
    $metaRobots = data_get($metaData, 'robots', 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1');
    $metaPublishedTime = data_get($metaData, 'published_time');
    $metaModifiedTime = data_get($metaData, 'modified_time');
    $jsonLd = data_get($metaData, 'json_ld');
    $metaImageUrl = filled($metaImage) ? (\Illuminate\Support\Str::startsWith($metaImage, ['http://', 'https://']) ? $metaImage : asset($metaImage)) : null;
    $faviconUrl = \Illuminate\Support\Str::startsWith($siteFavicon, ['http://', 'https://']) ? $siteFavicon : asset($siteFavicon);
    $themeBackground = trim((string) config('luzicity.theme.background_image'));
    $themeOpacity = trim((string) config('luzicity.theme.background_opacity', '0.34'));
    $firebaseConfig = [
        'apiKey' => config('services.firebase.api_key'),
        'authDomain' => config('services.firebase.auth_domain'),
        'projectId' => config('services.firebase.project_id'),
        'storageBucket' => config('services.firebase.storage_bucket'),
        'messagingSenderId' => config('services.firebase.messaging_sender_id'),
        'appId' => config('services.firebase.app_id'),
        'vapidKey' => config('services.firebase.vapid_key'),
    ];
@endphp
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="luzicity-firebase-config" content='@json($firebaseConfig)'>
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#f3f6fb" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#111827" media="(prefers-color-scheme: dark)">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="{{ $metaRobots }}">
    <link rel="canonical" href="{{ $metaUrl }}">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $metaType }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $metaUrl }}">
    @if($metaPublishedTime)<meta property="article:published_time" content="{{ $metaPublishedTime }}">@endif
    @if($metaModifiedTime)<meta property="article:modified_time" content="{{ $metaModifiedTime }}">@endif
    @if($metaImageUrl)
        <meta property="og:image" content="{{ $metaImageUrl }}">
        <meta property="og:image:secure_url" content="{{ $metaImageUrl }}">
        <meta name="twitter:image" content="{{ $metaImageUrl }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('sitemap') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if($jsonLd)
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>
    @endif
    @if($shouldShowGoogleAds && filled($googleAdsClient))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $googleAdsClient }}" crossorigin="anonymous"></script>
    @endif
    @if(filled($metaPixelId))
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
            (window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($metaPixelId));
            fbq('track', 'PageView');
        </script>
    @endif
    @if(filled($tiktokPixelId))
        <script>
            !function (w, d, t) {
                w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];
                ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
                ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};
                ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};
                var a=document.createElement("script");a.type="text/javascript",a.async=!0,a.src=i+"?sdkid="+e+"&lib="+t;
                var r=document.getElementsByTagName("script")[0];r.parentNode.insertBefore(a,r)};
                ttq.load(@json($tiktokPixelId));
                ttq.page();
            }(window, document, 'ttq');
        </script>
    @endif
    <title>{{ $pageTitle }}</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('luzicity-theme');
            if (savedTheme === 'light' || savedTheme === 'dark') {
                document.documentElement.dataset.theme = savedTheme;
            }
        })();
    </script>
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
    @endif
</head>
<body @class(['has-theme-photo' => filled($themeBackground)]) style="{{ filled($themeBackground) ? '--theme-photo: url('.asset($themeBackground).'); --theme-photo-opacity: '.$themeOpacity.';' : '' }}">
    <div class="app-background" aria-hidden="true"></div>
    <a class="skip-link" href="#conteudo">Pular para o conteudo</a>

    <header class="site-header">
        <nav class="account-menu" aria-label="Menu de conta">
            @auth
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                    <a href="{{ route('admin.index') }}"><x-app-icon name="dashboard" /> Backend</a>
                @else
                    <a href="{{ route('dashboard') }}"><x-app-icon name="dashboard" /> Painel</a>
                @endif
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                    <a href="{{ route('admin.advertisers.index') }}"><x-app-icon name="user" /> Anunciantes</a>
                    <a href="{{ route('admin.system-health.index') }}"><x-app-icon name="dashboard" /> Saúde</a>
                    <a href="{{ route('admin.users.index') }}"><x-app-icon name="user" /> Usuarios</a>
                    <a href="{{ route('admin.social-login.edit') }}"><x-app-icon name="login" /> Login Social</a>
                    <a href="{{ route('admin.social-links.edit') }}"><x-app-icon name="store" /> Links do Site</a>
                    <a href="{{ route('admin.push-notifications.index') }}"><x-app-icon name="news" /> Push</a>
                    <a href="{{ route('admin.tracking-pixels.edit') }}"><x-app-icon name="dashboard" /> Pixels</a>
                    <a href="{{ route('admin.company-info.edit') }}"><x-app-icon name="info" /> Empresa</a>
                    <a href="{{ route('admin.site-content.edit') }}"><x-app-icon name="edit" /> Conteúdo</a>
                    <a href="{{ route('admin.ai-settings.edit') }}"><x-app-icon name="dashboard" /> IA Config</a>
                    <a href="{{ route('admin.ai.dashboard') }}"><x-app-icon name="dashboard" /> Central Editorial IA</a>
                    <a href="{{ route('admin.categories.index') }}"><x-app-icon name="grid" /> Editorias</a>
                    <a href="{{ route('admin.tags.index') }}"><x-app-icon name="grid" /> Tags</a>
                    <a href="{{ route('admin.rss-feeds.index') }}"><x-app-icon name="rss" /> RSS</a>
                    <a href="{{ route('admin.rss-imports.index') }}"><x-app-icon name="rss" /> Importação RSS</a>
                    <a href="{{ route('admin.radio.edit') }}"><x-app-icon name="radio" /> Rádio</a>
                    <a href="{{ route('admin.media-banners.index') }}"><x-app-icon name="video" /> Banners</a>
                    <a href="{{ route('admin.vehicles.index') }}"><x-app-icon name="car" /> Veículos</a>
                    <a href="{{ route('admin.real-estate.index') }}"><x-app-icon name="home" /> Imóveis</a>
                @endif
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Jornalista', 'Colunista']))
                    <a href="{{ route('admin.news.index') }}"><x-app-icon name="news" /> Notícias</a>
                    <a href="{{ route('admin.editorial-room.dashboard') }}"><x-app-icon name="edit" /> Sala de Redação</a>
                @endif
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sair</button>
                </form>
            @else
                <button class="start-login-trigger" type="button" aria-expanded="false" aria-controls="start-login-menu" data-start-login-trigger>
                    <x-app-icon name="login" /> Entrar
                </button>
            @endauth
        </nav>

        <div class="brand-cluster">
            <a class="brand" href="{{ route('home') }}" aria-label="{{ $siteName }}">
                @if(filled($siteLogo))
                    <img class="brand-logo" src="{{ \Illuminate\Support\Str::startsWith($siteLogo, ['http://', 'https://']) ? $siteLogo : asset($siteLogo) }}" alt="{{ $siteName }}">
                @else
                    {{ $siteName }}
                @endif
            </a>
            <a class="radio-link" href="{{ route('radio.index') }}" aria-label="Abrir Radio Web">
                <x-app-icon name="radio" />
                Radio Web
            </a>
            <button class="theme-toggle" type="button" aria-label="Alternar modo claro e escuro" data-theme-toggle>
                <x-app-icon name="theme" />
                <span data-theme-toggle-label>Modo</span>
            </button>
            <button class="city-menu-trigger" type="button" aria-expanded="false" aria-controls="city-menu" data-city-menu-trigger>
                <x-app-icon name="pin" />
                Cidades
            </button>
            <button class="install-trigger" type="button" aria-describedby="install-help" data-install-trigger>
                <x-app-icon name="install" />
                <span data-install-label>Instale o App</span>
            </button>
            <button class="push-trigger" type="button" data-push-trigger>
                <x-app-icon name="news" />
                <span data-push-label>Ativar avisos</span>
            </button>
            <button class="social-links-trigger" type="button" aria-expanded="false" aria-controls="social-links-menu" data-social-links-trigger>
                <x-app-icon name="share" />
                Nossas Redes Sociais
            </button>
            @php
                $shopUrl = \App\Models\Setting::shopUrl();
                $hasShopUrl = filled($shopUrl) && $shopUrl !== '#';
                $localCommerceUrl = \App\Models\Setting::localCommerceUrl();
                $hasLocalCommerceUrl = filled($localCommerceUrl) && $localCommerceUrl !== '#';
            @endphp
            <a class="shop-link" href="{{ $hasShopUrl ? $shopUrl : route('home') }}" @if($hasShopUrl) target="_blank" rel="noopener noreferrer" @endif>
                <x-app-icon name="store" />
                Conheça Nossa Loja
            </a>
            <a class="shop-link" href="{{ route('about') }}">
                <x-app-icon name="info" />
                Quem somos
            </a>
            <a class="shop-link" href="{{ $hasLocalCommerceUrl ? $localCommerceUrl : route('home') }}" @if($hasLocalCommerceUrl) target="_blank" rel="noopener noreferrer" @endif>
                <x-app-icon name="store" />
                Comercio Local
            </a>
        </div>
    </header>

    <main id="conteudo" class="page-shell">
        @if(session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="notice notice-error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer" aria-label="Dados da empresa">
        <div>
            <strong>Luzicity</strong>
            <p>{{ data_get($companyInfo, 'copyright') }}</p>
        </div>

        <address class="company-footer-info">
            @if(filled(data_get($companyInfo, 'cnpj')))
                <span>CNPJ: {{ data_get($companyInfo, 'cnpj') }}</span>
            @endif

            @if(filled(data_get($companyInfo, 'phone')))
                <a href="tel:{{ preg_replace('/\D+/', '', data_get($companyInfo, 'phone')) }}">Telefone: {{ data_get($companyInfo, 'phone') }}</a>
            @endif

            @foreach(['whatsapp', 'whatsapp_secondary'] as $whatsappField)
                @if(filled(data_get($companyInfo, $whatsappField)))
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', data_get($companyInfo, $whatsappField)) }}" target="_blank" rel="noopener noreferrer">WhatsApp: {{ data_get($companyInfo, $whatsappField) }}</a>
                @endif
            @endforeach

            @foreach(['email', 'email_secondary', 'email_tertiary'] as $emailField)
                @if(filled(data_get($companyInfo, $emailField)))
                    <a href="mailto:{{ data_get($companyInfo, $emailField) }}">E-mail: {{ data_get($companyInfo, $emailField) }}</a>
                @endif
            @endforeach

            @if(filled(data_get($companyInfo, 'address')))
                <span>{{ data_get($companyInfo, 'address') }}</span>
            @endif
        </address>
    </footer>

    @guest
        <div class="start-menu-backdrop" data-start-login-backdrop hidden></div>
        <section class="start-login-menu" id="start-login-menu" aria-label="Menu de entrada" data-start-login-menu hidden>
            <div class="start-menu-head">
                <div class="start-avatar" aria-hidden="true">
                    <x-app-icon name="login" />
                </div>
                <div>
                    <p class="eyebrow">Conta Luzicity</p>
                    <h2>Entrar ou cadastrar</h2>
                </div>
                <button class="start-menu-close" type="button" aria-label="Fechar menu de entrada" data-start-login-close>&times;</button>
            </div>

            <p class="start-social-note">No primeiro acesso social, sua conta é criada automaticamente.</p>

            <div class="start-social-grid" aria-label="Opções para entrar ou cadastrar com login social">
                @foreach(\App\Models\Setting::enabledSocialLoginProviders() as $key => $provider)
                    <a class="start-social-tile" href="{{ route('social.redirect', $key) }}">
                        <span class="social-icon" aria-hidden="true">
                            <x-social-icon :provider="$key" />
                        </span>
                        <span>{{ $provider['label'] }}</span>
                    </a>
                @endforeach
            </div>

            <form method="post" action="{{ route('login.store') }}" class="start-login-form">
                @csrf
                <label>
                    E-mail
                    <input type="email" name="email" autocomplete="email" required>
                </label>
                <label>
                    Senha
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <button class="primary-action" type="submit">Entrar com e-mail</button>
            </form>

            <div class="start-menu-footer">
                <a href="{{ route('login') }}">Abrir página completa de login e cadastro</a>
            </div>
        </section>
    @endguest

    <div class="city-menu-backdrop" data-city-menu-backdrop hidden></div>
    <section class="city-menu-panel" id="city-menu" aria-label="Menu de cidades" data-city-menu hidden>
        <div class="city-menu-head">
            <div class="start-avatar" aria-hidden="true">
                <x-app-icon name="pin" />
            </div>
            <div>
                <p class="eyebrow">Noticias da sua cidade</p>
                <h2>Cidades</h2>
            </div>
            <button class="start-menu-close" type="button" aria-label="Fechar menu de cidades" data-city-menu-close>&times;</button>
        </div>

        <div class="city-menu-list">
            @foreach((config('luzicity.city_locations') ?? []) as $city)
                <a href="{{ route('cities.show', $city['slug']) }}">
                    <span>{{ $city['name'] }}</span>
                    <small>{{ $city['state'] }}</small>
                </a>
            @endforeach
        </div>

    </section>

    <div class="social-links-backdrop" data-social-links-backdrop hidden></div>
    <section class="social-links-panel" id="social-links-menu" aria-label="Menu de redes sociais" data-social-links-menu hidden>
        <div class="social-links-head">
            <div class="start-avatar" aria-hidden="true">
                <x-app-icon name="share" />
            </div>
            <div>
                <p class="eyebrow">Acompanhe a Luzicity</p>
                <h2>Nossas Redes Sociais</h2>
            </div>
            <button class="start-menu-close" type="button" aria-label="Fechar menu de redes sociais" data-social-links-close>&times;</button>
        </div>

        <div class="social-links-list">
            @foreach(\App\Models\Setting::socialLinks() as $key => $social)
                @php
                    $socialUrl = trim((string) ($social['url'] ?? '#'));
                    $hasSocialUrl = filled($socialUrl) && $socialUrl !== '#';
                @endphp
                <a href="{{ $hasSocialUrl ? $socialUrl : '#' }}" @if($hasSocialUrl) target="_blank" rel="noopener noreferrer" @endif>
                    <span class="social-icon" aria-hidden="true">
                        <x-social-icon :provider="$key" />
                    </span>
                    <span>{{ $social['label'] }}</span>
                </a>
            @endforeach
        </div>

    </section>

    <div class="install-toast" id="install-help" role="status" data-install-toast hidden>
        <strong data-install-title>Instalar Luzicity</strong>
        <span data-install-message>Quando o navegador liberar, este botão instala o site como aplicativo.</span>
    </div>

    <div class="pwa-update-banner" data-pwa-update-banner hidden>
        <span>Uma nova versão da Luzicity está disponível.</span>
        <button type="button" data-pwa-update-apply>Atualizar agora</button>
        <button type="button" data-pwa-update-dismiss aria-label="Fechar">×</button>
    </div>

    <script>
        (() => {
            const trigger = document.querySelector('[data-start-login-trigger]');
            const menu = document.querySelector('[data-start-login-menu]');
            const backdrop = document.querySelector('[data-start-login-backdrop]');
            const closeButton = document.querySelector('[data-start-login-close]');

            if (!trigger || !menu || !backdrop || !closeButton) {
                return;
            }

            const openMenu = () => {
                menu.hidden = false;
                backdrop.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                requestAnimationFrame(() => {
                    menu.classList.add('is-open');
                    backdrop.classList.add('is-open');
                });
            };

            const closeMenu = () => {
                menu.classList.remove('is-open');
                backdrop.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
                window.setTimeout(() => {
                    menu.hidden = true;
                    backdrop.hidden = true;
                }, 160);
                trigger.focus();
            };

            trigger.addEventListener('click', () => {
                if (menu.hidden) {
                    openMenu();
                } else {
                    closeMenu();
                }
            });

            closeButton.addEventListener('click', closeMenu);
            backdrop.addEventListener('click', closeMenu);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !menu.hidden) {
                    closeMenu();
                }
            });
        })();

        (() => {
            const button = document.querySelector('[data-theme-toggle]');
            const label = document.querySelector('[data-theme-toggle-label]');
            const query = window.matchMedia('(prefers-color-scheme: dark)');

            if (!button || !label) {
                return;
            }

            const activeTheme = () => {
                const savedTheme = localStorage.getItem('luzicity-theme');
                if (savedTheme === 'light' || savedTheme === 'dark') {
                    return savedTheme;
                }

                return query.matches ? 'dark' : 'light';
            };

            const updateButton = () => {
                const theme = activeTheme();
                button.dataset.activeTheme = theme;
                button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
                label.textContent = theme === 'dark' ? 'Escuro' : 'Claro';
            };

            button.addEventListener('click', () => {
                const nextTheme = activeTheme() === 'dark' ? 'light' : 'dark';
                localStorage.setItem('luzicity-theme', nextTheme);
                document.documentElement.dataset.theme = nextTheme;
                updateButton();
            });

            query.addEventListener('change', () => {
                if (!localStorage.getItem('luzicity-theme')) {
                    updateButton();
                }
            });

            updateButton();
        })();

        (() => {
            const trigger = document.querySelector('[data-city-menu-trigger]');
            const menu = document.querySelector('[data-city-menu]');
            const backdrop = document.querySelector('[data-city-menu-backdrop]');
            const closeButton = document.querySelector('[data-city-menu-close]');

            if (!trigger || !menu || !backdrop || !closeButton) {
                return;
            }

            const openMenu = () => {
                menu.hidden = false;
                backdrop.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                requestAnimationFrame(() => {
                    menu.classList.add('is-open');
                    backdrop.classList.add('is-open');
                });
            };

            const closeMenu = () => {
                menu.classList.remove('is-open');
                backdrop.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
                window.setTimeout(() => {
                    menu.hidden = true;
                    backdrop.hidden = true;
                }, 160);
                trigger.focus();
            };

            trigger.addEventListener('click', () => {
                if (menu.hidden) {
                    openMenu();
                } else {
                    closeMenu();
                }
            });

            closeButton.addEventListener('click', closeMenu);
            backdrop.addEventListener('click', closeMenu);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !menu.hidden) {
                    closeMenu();
                }
            });
        })();

        (() => {
            const trigger = document.querySelector('[data-social-links-trigger]');
            const menu = document.querySelector('[data-social-links-menu]');
            const backdrop = document.querySelector('[data-social-links-backdrop]');
            const closeButton = document.querySelector('[data-social-links-close]');

            if (!trigger || !menu || !backdrop || !closeButton) {
                return;
            }

            const openMenu = () => {
                menu.hidden = false;
                backdrop.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                requestAnimationFrame(() => {
                    menu.classList.add('is-open');
                    backdrop.classList.add('is-open');
                });
            };

            const closeMenu = () => {
                menu.classList.remove('is-open');
                backdrop.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
                window.setTimeout(() => {
                    menu.hidden = true;
                    backdrop.hidden = true;
                }, 160);
                trigger.focus();
            };

            trigger.addEventListener('click', () => {
                if (menu.hidden) {
                    openMenu();
                } else {
                    closeMenu();
                }
            });

            closeButton.addEventListener('click', closeMenu);
            backdrop.addEventListener('click', closeMenu);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !menu.hidden) {
                    closeMenu();
                }
            });
        })();

        (() => {
            const buttons = document.querySelectorAll('[data-ai-generate]');

            if (!buttons.length) {
                return;
            }

            buttons.forEach((button) => {
                button.addEventListener('click', async () => {
                    const toolbar = button.closest('[data-ai-toolbar]');
                    const provider = toolbar?.querySelector('[data-ai-provider]')?.value;
                    let status = toolbar?.querySelector('[data-ai-status]');
                    const target = document.getElementById(button.dataset.aiTarget);
                    const brief = document.getElementById(button.dataset.aiBrief);
                    const title = button.dataset.aiTitle ? document.getElementById(button.dataset.aiTitle) : null;

                    if (toolbar && !status) {
                        status = document.createElement('p');
                        status.className = 'ai-status';
                        status.setAttribute('data-ai-status', '');
                        status.setAttribute('role', 'status');
                        toolbar.appendChild(status);
                    }

                    if (!target || !brief || !brief.value.trim()) {
                        alert('Escreva primeiro o briefing para a IA.');
                        return;
                    }

                    const originalText = button.textContent;
                    button.disabled = true;
                    button.textContent = 'Gerando...';
                    if (status) {
                        status.textContent = 'Conectando ao assistente de IA...';
                        status.dataset.state = 'loading';
                    }

                    try {
                        const response = await fetch('{{ route('admin.ai-writing.store') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                context: button.dataset.aiContext,
                                provider,
                                title: title?.value ?? '',
                                brief: brief.value,
                            }),
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            const message = data.message || 'Nao foi possivel gerar com IA. Verifique a configuracao da API.';
                            if (status) {
                                status.textContent = message;
                                status.dataset.state = 'error';
                            }
                            alert(message);
                            return;
                        }

                        if (data.text) {
                            target.value = data.text;
                        }
                        if (data.source === 'local') {
                            const message = data.message || 'A API nao respondeu. Foi gerado um rascunho local para revisao.';
                            if (status) {
                                status.textContent = message;
                                status.dataset.state = 'warning';
                            }
                            alert(message);
                        } else if (status) {
                            status.textContent = data.message || 'Texto gerado com sucesso.';
                            status.dataset.state = 'success';
                        }
                    } catch (error) {
                        const message = 'Nao foi possivel conectar ao assistente de IA agora. Verifique a internet, a chave da API e tente novamente.';
                        if (status) {
                            status.textContent = message;
                            status.dataset.state = 'error';
                        }
                        alert(message);
                    } finally {
                        button.disabled = false;
                        button.textContent = originalText;
                    }
                });
            });
        })();

        (() => {
            const buttons = document.querySelectorAll('[data-ai-open]');

            if (!buttons.length) {
                return;
            }

            const urls = {
                chatgpt: 'https://chatgpt.com/',
                gemini: 'https://gemini.google.com/app',
                copilot: 'https://copilot.microsoft.com/',
            };

            const labels = {
                chatgpt: 'ChatGPT',
                gemini: 'Gemini',
                copilot: 'Copilot',
            };

            const buildPrompt = (button) => {
                const title = button.dataset.aiTitle ? document.getElementById(button.dataset.aiTitle)?.value?.trim() : '';
                const brief = document.getElementById(button.dataset.aiBrief)?.value?.trim();
                const contextLabels = {
                    about: 'Quem somos',
                    news_summary: 'chamada curta de noticia',
                    vehicle_ad: 'anuncio de veiculo',
                    real_estate_ad: 'anuncio de imovel',
                    news: 'noticia',
                };
                const context = contextLabels[button.dataset.aiContext] || 'noticia';

                if (!brief) {
                    return '';
                }

                return [
                    `Voce e assistente editorial e comercial da Luzicity. Produza uma redacao para ${context}.`,
                    title ? `Titulo/base: ${title}` : '',
                    `Briefing: ${brief}`,
                    button.dataset.aiContext === 'news_summary'
                        ? 'Crie uma chamada curta, forte e clara para aparecer no card da home, sem exagero e sem inventar fatos.'
                        : ['vehicle_ad', 'real_estate_ad'].includes(button.dataset.aiContext)
                        ? 'Use uma copy atrativa, objetiva e honesta, destacando diferenciais, estado do produto, contato e chamada para acao.'
                        : 'Use linguagem clara, acessivel, com leitura confortavel em celular.',
                    'Entregue um texto pronto para revisao humana antes da publicacao.',
                ].filter(Boolean).join('\n\n');
            };
            const copyPrompt = async (prompt) => {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(prompt);
                    return true;
                }

                const area = document.createElement('textarea');
                area.value = prompt;
                area.style.position = 'fixed';
                area.style.left = '-9999px';
                document.body.appendChild(area);
                area.focus();
                area.select();
                const copied = document.execCommand('copy');
                area.remove();

                return copied;
            };

            buttons.forEach((button) => {
                button.addEventListener('click', async () => {
                    const provider = button.dataset.aiOpen;
                    const prompt = buildPrompt(button);

                    if (!prompt) {
                        alert('Escreva primeiro o briefing para a IA.');
                        return;
                    }

                    const originalText = button.textContent;
                    button.disabled = true;
                    button.textContent = 'Copiando...';

                    try {
                        await copyPrompt(prompt);
                        window.open(urls[provider] ?? urls.chatgpt, '_blank', 'noopener,noreferrer');
                        button.textContent = 'Prompt copiado';
                        window.setTimeout(() => {
                            button.textContent = originalText;
                        }, 1800);
                    } catch (error) {
                        alert(`Não foi possível copiar o prompt. Copie manualmente e abra ${labels[provider] ?? 'a IA'}.`);
                        button.textContent = originalText;
                    } finally {
                        window.setTimeout(() => {
                            button.disabled = false;
                        }, 300);
                    }
                });
            });
        })();

        (() => {
            const carousels = document.querySelectorAll('[data-carousel]');

            carousels.forEach((carousel) => {
                const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));

                if (slides.length < 2) {
                    return;
                }

                let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));

                if (activeIndex < 0) {
                    activeIndex = 0;
                    slides[0].classList.add('is-active');
                }

                window.setInterval(() => {
                    slides[activeIndex].classList.remove('is-active');
                    activeIndex = (activeIndex + 1) % slides.length;
                    slides[activeIndex].classList.add('is-active');
                }, 7000);
            });
        })();

        (() => {
            const radios = document.querySelectorAll('[data-radio-live]');

            radios.forEach((radio) => {
                const audio = radio.querySelector('[data-radio-audio]');
                const videoFrame = radio.querySelector('[data-radio-video-frame]');
                const status = radio.querySelector('[data-radio-status]');
                const buttons = Array.from(radio.querySelectorAll('[data-radio-mode]'));
                const iframes = videoFrame ? Array.from(videoFrame.querySelectorAll('iframe')) : [];

                const setStatus = (message) => {
                    if (status) {
                        status.textContent = message;
                    }
                };

                const unloadVideo = () => {
                    if (! videoFrame) {
                        return;
                    }

                    iframes.forEach((iframe) => {
                        if (! iframe.dataset.originalSrc && iframe.getAttribute('src')) {
                            iframe.dataset.originalSrc = iframe.getAttribute('src');
                        }

                        if (! iframe.dataset.originalSrcdoc && iframe.getAttribute('srcdoc')) {
                            iframe.dataset.originalSrcdoc = iframe.getAttribute('srcdoc');
                        }

                        iframe.removeAttribute('srcdoc');
                        iframe.setAttribute('src', 'about:blank');
                    });

                    videoFrame.classList.add('is-video-off');
                };

                const restoreVideo = () => {
                    if (! videoFrame) {
                        return;
                    }

                    iframes.forEach((iframe) => {
                        if (iframe.dataset.originalSrcdoc) {
                            iframe.setAttribute('srcdoc', iframe.dataset.originalSrcdoc);
                        }

                        if (iframe.dataset.originalSrc) {
                            iframe.setAttribute('src', iframe.dataset.originalSrc);
                        }
                    });

                    videoFrame.classList.remove('is-video-off');
                };

                const selectMode = (mode) => {
                    buttons.forEach((button) => {
                        button.setAttribute('aria-pressed', button.dataset.radioMode === mode ? 'true' : 'false');
                    });

                    if (mode === 'video') {
                        audio?.pause();
                        restoreVideo();
                        setStatus('Modo vídeo ativo: o player da rádio fica pausado para evitar som duplicado.');
                        return;
                    }

                    if (mode === 'audio') {
                        unloadVideo();
                        audio?.play().catch(() => {});
                        setStatus('Modo só áudio ativo: o TikTok foi pausado para economizar dados.');
                        return;
                    }

                    audio?.pause();
                    unloadVideo();
                    setStatus('Transmissões pausadas. Escolha vídeo ou áudio quando quiser voltar.');
                };

                buttons.forEach((button) => {
                    button.addEventListener('click', () => selectMode(button.dataset.radioMode));
                });
            });
        })();

        (() => {
            const chat = document.querySelector('[data-radio-chat-room][data-radio-chat-nickname]');

            if (! chat) {
                return;
            }

            const nickname = chat.dataset.radioChatNickname || '';
            const room = chat.dataset.radioChatRoom || 'geral';
            const storageKey = `luzicity-radio-chat-last-${room}-${nickname}`.toLowerCase();
            const normalize = (value) => String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .trim()
                .toLowerCase();

            const AudioContext = window.AudioContext || window.webkitAudioContext;
            let beepContext;

            const getBeepContext = () => {
                if (! AudioContext) {
                    return null;
                }

                if (! beepContext) {
                    beepContext = new AudioContext();
                }

                return beepContext;
            };

            const unlockBeep = () => {
                getBeepContext()?.resume().catch(() => {});
            };

            window.addEventListener('pointerdown', unlockBeep, { once: true });
            window.addEventListener('keydown', unlockBeep, { once: true });

            const playMentionBeep = () => {
                const context = getBeepContext();

                if (! context) {
                    return;
                }

                context.resume().catch(() => {});

                const oscillator = context.createOscillator();
                const gain = context.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, context.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(660, context.currentTime + 0.12);
                gain.gain.setValueAtTime(0.0001, context.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.12, context.currentTime + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.18);

                oscillator.connect(gain);
                gain.connect(context.destination);
                oscillator.start();
                oscillator.stop(context.currentTime + 0.2);
            };

            const readMentionState = () => {
                const normalizedNickname = normalize(nickname);
                const messages = Array.from(chat.querySelectorAll('[data-chat-message-id]'));
                let maxMessageId = 0;
                let maxMentionId = 0;

                messages.forEach((message) => {
                    const messageId = Number(message.dataset.chatMessageId || 0);
                    const recipient = normalize(message.dataset.chatRecipient);
                    const author = normalize(message.dataset.chatAuthor);

                    maxMessageId = Math.max(maxMessageId, messageId);

                    if (messageId > 0 && recipient === normalizedNickname && author !== normalizedNickname) {
                        maxMentionId = Math.max(maxMentionId, messageId);
                    }
                });

                return { maxMessageId, maxMentionId };
            };

            const notifyMentions = (allowSound) => {
                const lastNotifiedId = Number(localStorage.getItem(storageKey) || 0);
                const { maxMessageId, maxMentionId } = readMentionState();

                if (maxMentionId > lastNotifiedId && allowSound) {
                    playMentionBeep();
                }

                if (maxMessageId > lastNotifiedId) {
                    localStorage.setItem(storageKey, String(maxMessageId));
                }
            };

            notifyMentions(false);

            const refreshChat = async () => {
                const messages = chat.querySelector('.radio-chat-messages');

                if (! messages) {
                    return;
                }

                try {
                    const response = await fetch(window.location.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const html = await response.text();
                    const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                    const nextMessages = nextDocument.querySelector('.radio-chat-messages');
                    const nextPeople = nextDocument.querySelector('.radio-room-people');
                    const people = chat.querySelector('.radio-room-people');

                    if (nextMessages) {
                        messages.replaceWith(nextMessages);
                    }

                    if (nextPeople && people) {
                        people.replaceWith(nextPeople);
                    }

                    notifyMentions(true);
                } catch (error) {
                    // Mantem o chat atual se a conexao oscilar.
                }
            };

            window.setInterval(refreshChat, 5000);
        })();
    </script>
</body>
</html>