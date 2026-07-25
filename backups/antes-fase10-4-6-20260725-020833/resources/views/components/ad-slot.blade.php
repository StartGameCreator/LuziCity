@props([
    'name',
    'label' => 'Publicidade',
    'variant' => 'banner',
])

@php
    $googleAds = \App\Models\Setting::googleAds();
    $client = trim((string) data_get($googleAds, 'client', ''));
    $slot = trim((string) data_get($googleAds, "slots.$name", ''));
    $adFreeUser = auth()->user()?->hasAdFreeAccess() === true;
    $isSponsor = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($label), 'patrocinador');
    $isConfigured = ! $adFreeUser && filled($client) && filled($slot);
@endphp

@unless($adFreeUser && ! $isSponsor)
    <aside @class([
        'google-ad-slot',
        'google-ad-slot-'.$variant,
        'sponsor-ad-image' => $isSponsor && ! $isConfigured,
    ]) aria-label="{{ $label }}">
        <span @class(['sr-only' => $isSponsor && ! $isConfigured])>{{ $label }}</span>

        @if($isConfigured)
            <ins
                class="adsbygoogle"
                style="display:block"
                data-ad-client="{{ $client }}"
                data-ad-slot="{{ $slot }}"
                data-ad-format="auto"
                data-full-width-responsive="true"
            ></ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        @elseif(! $isSponsor)
            <strong>Google Ads</strong>
            <small>{{ $name }}</small>
        @endif
    </aside>
@endunless
