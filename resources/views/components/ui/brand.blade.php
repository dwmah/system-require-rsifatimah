@props([
    'subtitle' => 'Sistem Rekrutmen SDM Rumah Sakit',
    'href' => '/',
    'size' => 'md',
    'inverted' => false,
])

@php
    $iconSize = match($size) {
        'sm' => 'w-8 h-8',
        'lg' => 'w-12 h-12',
        default => 'w-10 h-10',
    };

    $titleSize = match($size) {
        'sm' => 'text-xs sm:text-sm',
        'lg' => 'text-base sm:text-lg',
        default => 'text-sm sm:text-[15px]',
    };

    $subtitleSize = match($size) {
        'sm' => 'text-[10px]',
        'lg' => 'text-xs',
        default => 'text-[11px] sm:text-[11.5px]',
    };

    $logoUrl = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCbN1FpSI8bvRZDh2C7gzdPEHuNzeY7-wAZ_dvdpY9KvouaNm0xlBmPc-HosS8aS7M6QTHLgzzgUVI_Xzkm5ac32uRgoXvu_UEJGq7bMWhAhG0EDxEjTbi-h_JRFFaN0hNhZbyx_gFkNtKOPrDBQDrnCx0COUQmpnjUF2G7pUmbpZ54El9HSzI_DMupYZ2lPVo1pXZKdYm1_4BoEXoGKojoC-mS_ZVUHTOcsbEj2V3nb-FB3-BXgWj375CXoCjeZ0--KDI';

    $targetHref = ($href === '/' && auth()->check())
        ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('portal.home'))
        : $href;
@endphp

<a href="{{ $targetHref }}" {{ $attributes->merge(['class' => 'flex items-center gap-3 group focus:outline-hidden focus:ring-2 focus:ring-white/40 rounded-lg p-1 transition-opacity hover:opacity-95']) }}>
    <img src="{{ $logoUrl }}" alt="Logo RSI Fatimah Banyuwangi" class="{{ $iconSize }} object-contain shrink-0 drop-shadow-xs" />
    <div class="flex flex-col text-left leading-tight">
        <span class="{{ $titleSize }} font-bold {{ $inverted ? 'text-white' : 'text-slate-900' }} tracking-tight">
            RSI Fatimah Banyuwangi
        </span>
        <span class="{{ $subtitleSize }} font-normal {{ $inverted ? 'text-sky-100' : 'text-[#0179B5]' }}">
            {{ $subtitle }}
        </span>
    </div>
</a>
