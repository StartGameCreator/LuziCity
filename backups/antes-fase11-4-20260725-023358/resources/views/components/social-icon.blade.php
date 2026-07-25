@props(['provider'])

@switch($provider)
    @case('microsoft')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <rect x="2" y="2" width="9.5" height="9.5" fill="#f25022"/>
            <rect x="12.5" y="2" width="9.5" height="9.5" fill="#7fba00"/>
            <rect x="2" y="12.5" width="9.5" height="9.5" fill="#00a4ef"/>
            <rect x="12.5" y="12.5" width="9.5" height="9.5" fill="#ffb900"/>
        </svg>
        @break

    @case('apple')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="#111111" d="M16.55 12.55c-.02-2.2 1.8-3.25 1.88-3.3-1.03-1.5-2.62-1.7-3.18-1.72-1.35-.14-2.64.8-3.33.8-.7 0-1.77-.78-2.9-.76-1.5.02-2.88.87-3.65 2.2-1.56 2.7-.4 6.7 1.12 8.9.74 1.07 1.63 2.27 2.8 2.23 1.12-.04 1.55-.72 2.91-.72 1.35 0 1.75.72 2.94.7 1.22-.02 1.99-1.09 2.73-2.16.85-1.24 1.2-2.44 1.22-2.5-.03-.01-2.52-.96-2.54-3.67ZM14.37 6.1c.62-.75 1.04-1.8.93-2.84-.9.04-1.98.6-2.62 1.35-.58.67-1.08 1.74-.94 2.76 1 .08 2.02-.51 2.63-1.27Z"/>
        </svg>
        @break

    @case('google')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="#4285f4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09Z"/>
            <path fill="#34a853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23Z"/>
            <path fill="#fbbc05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84Z"/>
            <path fill="#ea4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06L5.84 9.9C6.71 7.31 9.14 5.38 12 5.38Z"/>
        </svg>
        @break

    @case('facebook')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <circle cx="12" cy="12" r="10.5" fill="#1877f2"/>
            <path fill="#ffffff" d="M14.85 15.04h2.45l.38-2.95h-2.83v-1.88c0-.85.24-1.44 1.46-1.44h1.52V6.12c-.74-.08-1.48-.12-2.23-.12-2.22 0-3.74 1.35-3.74 3.82v2.27H9.35v2.95h2.51V22h2.99v-6.96Z"/>
        </svg>
        @break

    @case('instagram')
        @php($instagramGradientId = 'instagramGradient-'.uniqid())
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <defs>
                <linearGradient id="{{ $instagramGradientId }}" x1="3" y1="21" x2="21" y2="3" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#feda75"/>
                    <stop offset=".28" stop-color="#fa7e1e"/>
                    <stop offset=".55" stop-color="#d62976"/>
                    <stop offset=".78" stop-color="#962fbf"/>
                    <stop offset="1" stop-color="#4f5bd5"/>
                </linearGradient>
            </defs>
            <rect x="3" y="3" width="18" height="18" rx="5" fill="url(#{{ $instagramGradientId }})"/>
            <circle cx="12" cy="12" r="4.1" fill="none" stroke="#fff" stroke-width="1.8"/>
            <circle cx="17.1" cy="6.9" r="1.15" fill="#fff"/>
        </svg>
        @break

    @case('tiktok')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="#25f4ee" d="M10.3 10.15v2.47a4.28 4.28 0 0 0-1.17-.16 4.31 4.31 0 1 0 4.31 4.31V8.29a6.24 6.24 0 0 0 3.57 1.13V6.7a3.57 3.57 0 0 1-3.57-3.57h-2.72v13.64a1.59 1.59 0 1 1-1.59-1.59c.43 0 .82.17 1.11.44v-2.91a4.29 4.29 0 0 0-.94-.1Z"/>
            <path fill="#fe2c55" d="M12.02 10.15v2.47a4.28 4.28 0 0 0-1.17-.16 4.31 4.31 0 1 0 4.31 4.31V8.29a6.24 6.24 0 0 0 3.57 1.13V6.7a3.57 3.57 0 0 1-3.57-3.57h-2.72v13.64a1.59 1.59 0 1 1-1.59-1.59c.43 0 .82.17 1.11.44v-2.91a4.29 4.29 0 0 0-.94-.1Z" opacity=".85"/>
            <path fill="#111111" d="M11.18 10.15v2.47a4.28 4.28 0 0 0-1.17-.16 4.31 4.31 0 1 0 4.31 4.31V8.29a6.24 6.24 0 0 0 3.57 1.13V6.7a3.57 3.57 0 0 1-3.57-3.57H11.6v13.64a1.59 1.59 0 1 1-1.59-1.59c.43 0 .82.17 1.11.44v-2.91a4.29 4.29 0 0 0-.94-.1Z"/>
        </svg>
        @break

    @case('youtube')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <rect x="2.4" y="5.2" width="19.2" height="13.6" rx="4" fill="#ff0000"/>
            <path d="m10 8.8 6 3.2-6 3.2V8.8Z" fill="#ffffff"/>
        </svg>
        @break

    @case('apple_music')
        @php($appleMusicGradientId = 'appleMusicGradient-'.uniqid())
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <defs>
                <linearGradient id="{{ $appleMusicGradientId }}" x1="4" y1="20" x2="20" y2="4" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#fa233b"/>
                    <stop offset=".48" stop-color="#fb5c74"/>
                    <stop offset="1" stop-color="#b33bf6"/>
                </linearGradient>
            </defs>
            <rect x="3" y="3" width="18" height="18" rx="5" fill="url(#{{ $appleMusicGradientId }})"/>
            <path d="M15.8 6.7v8.15a2.55 2.55 0 1 1-1.42-2.28V9.06l-5.05 1.02v5.83a2.55 2.55 0 1 1-1.42-2.28V8.55l7.89-1.85Z" fill="#ffffff"/>
        </svg>
        @break

    @case('deezer')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <rect x="3" y="3" width="18" height="18" rx="5" fill="#111827"/>
            <rect x="5.4" y="13.4" width="2.2" height="4.2" rx=".45" fill="#ff0092"/>
            <rect x="8.4" y="10.6" width="2.2" height="7" rx=".45" fill="#ff6a00"/>
            <rect x="11.4" y="8.4" width="2.2" height="9.2" rx=".45" fill="#ffde00"/>
            <rect x="14.4" y="6.7" width="2.2" height="10.9" rx=".45" fill="#00c7f2"/>
            <rect x="17.4" y="11.8" width="2.2" height="5.8" rx=".45" fill="#8a2be2"/>
        </svg>
        @break

    @case('kwai')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <rect x="3" y="3" width="18" height="18" rx="5" fill="#ff6f00"/>
            <circle cx="9" cy="8.3" r="2.4" fill="#ffffff"/>
            <circle cx="9" cy="15.7" r="2.4" fill="#ffffff"/>
            <rect x="11.6" y="8.4" width="5.4" height="7.2" rx="1.6" fill="#ffffff"/>
            <circle cx="9" cy="8.3" r=".9" fill="#ff6f00"/>
            <circle cx="9" cy="15.7" r=".9" fill="#ff6f00"/>
        </svg>
        @break

    @case('rumble')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <rect x="3" y="3" width="18" height="18" rx="5" fill="#85c742"/>
            <path d="M9 7.2v9.6l7-4.8-7-4.8Z" fill="#ffffff"/>
        </svg>
        @break

    @case('dlive')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <rect x="3" y="3" width="18" height="18" rx="5" fill="#ffd21e"/>
            <path d="M8 7h5.2a4.9 4.9 0 0 1 0 9.8H8V7Zm3 2.7v4.4h2.1a2.2 2.2 0 0 0 0-4.4H11Z" fill="#111111"/>
        </svg>
        @break
@endswitch
