@props([
    'subtitle' => 'Sistem Rekrutmen SDM',
    'href' => '/',
    'size' => 'md',
])

@php
    $iconSize = match($size) {
        'sm' => 'w-8 h-8 text-xs',
        'lg' => 'w-12 h-12 text-base',
        default => 'w-10 h-10 text-sm',
    };

    $titleSize = match($size) {
        'sm' => 'text-sm',
        'lg' => 'text-lg',
        default => 'text-base',
    };

    $subtitleSize = match($size) {
        'sm' => 'text-[11px]',
        'lg' => 'text-xs',
        default => 'text-xs',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center gap-3 group focus:outline-hidden focus:ring-2 focus:ring-rsi-500 rounded-lg p-1']) }}>
    {{-- TODO: Ganti placeholder visual dengan aset logo resmi RSI Fatimah Banyuwangi ketika file resmi tersedia --}}
    <div class="{{ $iconSize }} rounded-lg bg-rsi-700 text-white flex items-center justify-center font-bold tracking-wider shrink-0 shadow-xs ring-1 ring-rsi-800/20">
        <span>RSI</span>
    </div>
    <div class="flex flex-col text-left leading-tight">
        <span class="{{ $titleSize }} font-bold text-slate-900 tracking-tight group-hover:text-rsi-700 transition-colors">
            RSI Fatimah
        </span>
        <span class="{{ $subtitleSize }} font-medium text-rsi-700">
            {{ $subtitle }}
        </span>
    </div>
</a>
