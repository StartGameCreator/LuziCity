@props(['type' => 'car'])

@php
    $selectedType = \App\Models\Setting::normalizeVehicleType($type);
    $vehicleBrands = \App\Models\Setting::vehicleBrandLogos($selectedType);
@endphp

<div class="vehicle-brand-strip" aria-label="Principais marcas de veículos">
    @foreach($vehicleBrands as $brand)
        <a
            class="vehicle-brand-logo"
            href="{{ route('vehicles.index', ['vehicle_type' => $selectedType, 'brand' => $brand['name']]) }}"
            aria-label="Ver {{ \App\Models\Setting::vehicleTypeOptions()[$selectedType] }} da marca {{ $brand['name'] }}"
        >
            @if(filled($brand['logo_url'] ?? ''))
                <img src="{{ $brand['logo_url'] }}" alt="{{ $brand['name'] }}" loading="lazy" onerror="this.hidden=true; this.nextElementSibling.hidden=false">
                <span hidden>{{ $brand['name'] }}</span>
            @else
                <span>{{ $brand['name'] }}</span>
            @endif
        </a>
    @endforeach
</div>
