@props(['name'])

@switch($name)
    @case('login')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M10 7V5.75A2.75 2.75 0 0 1 12.75 3h5.5A2.75 2.75 0 0 1 21 5.75v12.5A2.75 2.75 0 0 1 18.25 21h-5.5A2.75 2.75 0 0 1 10 18.25V17" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            <path d="M3 12h11m0 0-3.4-3.4M14 12l-3.4 3.4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('home')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M4 11.4 12 4l8 7.4v7.1A1.5 1.5 0 0 1 18.5 20H15v-5.5H9V20H5.5A1.5 1.5 0 0 1 4 18.5v-7.1Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
        </svg>
        @break
    @case('news')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M5 5.75A1.75 1.75 0 0 1 6.75 4h10.5A1.75 1.75 0 0 1 19 5.75v12.5A1.75 1.75 0 0 1 17.25 20H6.75A1.75 1.75 0 0 1 5 18.25V5.75Z" fill="none" stroke="currentColor" stroke-width="1.7"/>
            <path d="M8 8h8M8 11.5h8M8 15h5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
        @break
    @case('clock')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7"/>
            <path d="M12 7.5V12l3 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('grid')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M5 5h6v6H5V5Zm8 0h6v6h-6V5ZM5 13h6v6H5v-6Zm8 0h6v6h-6v-6Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
        </svg>
        @break
    @case('radio')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M5 9.5h14A2.5 2.5 0 0 1 21.5 12v5A2.5 2.5 0 0 1 19 19.5H5A2.5 2.5 0 0 1 2.5 17v-5A2.5 2.5 0 0 1 5 9.5Z" fill="none" stroke="currentColor" stroke-width="1.7"/>
            <path d="m7 9.5 8.5-5M7.5 14.5h5M16.5 14.5h.01" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
        @break
    @case('user')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <circle cx="12" cy="8" r="3.5" fill="none" stroke="currentColor" stroke-width="1.7"/>
            <path d="M5 20a7 7 0 0 1 14 0" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
        @break
    @case('dashboard')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M4 13.5h6V20H4v-6.5ZM14 4h6v16h-6V4ZM4 4h6v5.5H4V4Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
        </svg>
        @break
    @case('theme')
        <svg class="theme-icon-sun" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <circle cx="12" cy="12" r="3.6" fill="none" stroke="currentColor" stroke-width="1.7"/>
            <path d="M12 3.5v2M12 18.5v2M4.5 12h2M17.5 12h2M6.7 6.7l1.4 1.4M15.9 15.9l1.4 1.4M17.3 6.7l-1.4 1.4M8.1 15.9l-1.4 1.4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
        <svg class="theme-icon-moon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M20 14.8A7.8 7.8 0 0 1 9.2 4a8.2 8.2 0 1 0 10.8 10.8Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
        </svg>
        @break
    @case('pin')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M12 21s6.5-5.9 6.5-11.2A6.5 6.5 0 0 0 5.5 9.8C5.5 15.1 12 21 12 21Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            <circle cx="12" cy="9.8" r="2.2" fill="none" stroke="currentColor" stroke-width="1.7"/>
        </svg>
        @break
    @case('install')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M7 20h10a2 2 0 0 0 2-2v-3.2M5 14.8V18a2 2 0 0 0 2 2M12 4v10m0 0 3.7-3.7M12 14l-3.7-3.7" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7.5 4h9A2.5 2.5 0 0 1 19 6.5v2M5 8.5v-2A2.5 2.5 0 0 1 7.5 4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
        @break
    @case('share')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <circle cx="7" cy="12" r="2.6" fill="none" stroke="currentColor" stroke-width="1.7"/>
            <circle cx="17" cy="6.5" r="2.6" fill="none" stroke="currentColor" stroke-width="1.7"/>
            <circle cx="17" cy="17.5" r="2.6" fill="none" stroke="currentColor" stroke-width="1.7"/>
            <path d="m9.35 10.75 5.3-2.9M9.35 13.25l5.3 2.9" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
        @break
    @case('store')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M5 10.5v8A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5v-8" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            <path d="M4 10.5h16l-1.15-5.1A1.8 1.8 0 0 0 17.1 4H6.9a1.8 1.8 0 0 0-1.75 1.4L4 10.5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            <path d="M8 10.5V4M12 10.5V4M16 10.5V4M9.2 20v-5.2h5.6V20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('car')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M5 12.2 6.7 7.8A2.8 2.8 0 0 1 9.3 6h5.4a2.8 2.8 0 0 1 2.6 1.8l1.7 4.4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            <path d="M4.5 12h15A1.5 1.5 0 0 1 21 13.5V17a1.5 1.5 0 0 1-1.5 1.5H18a2 2 0 0 1-4 0h-4a2 2 0 0 1-4 0H4.5A1.5 1.5 0 0 1 3 17v-3.5A1.5 1.5 0 0 1 4.5 12Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            <path d="M7 15h.01M17 15h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        @break
    @case('info')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="1.7"/>
            <path d="M12 11v5M12 7.8h.01" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
        </svg>
        @break
    @case('edit')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M4.5 19.5h15M6.5 15.7l-.5 3 3-.5 8.8-8.8a2.1 2.1 0 0 0-3-3L6.5 15.7Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('rss')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M5 5.5A13.5 13.5 0 0 1 18.5 19M5 10.2A8.8 8.8 0 0 1 13.8 19" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            <circle cx="6.7" cy="17.3" r="1.7" fill="currentColor"/>
        </svg>
        @break
    @case('video')
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <rect x="3.5" y="6" width="12.5" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/>
            <path d="m16 10.2 4.5-2.6v8.8L16 13.8v-3.6Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            <path d="m8.7 9.3 4.1 2.7-4.1 2.7V9.3Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
        </svg>
        @break
@endswitch
