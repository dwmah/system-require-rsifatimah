@props([
    'variant' => 'neutral',
    'size' => 'md',
    'dot' => false,
])

@php
    $baseClasses = 'inline-flex items-center font-medium rounded-full whitespace-nowrap';

    $sizeClasses = match($size) {
        'sm' => 'text-[11px] px-2 py-0.5 gap-1',
        'lg' => 'text-sm px-3 py-1 gap-2',
        default => 'text-xs px-2.5 py-0.5 gap-1.5', // md
    };

    $variantClasses = match($variant) {
        'success' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
        'info' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-600/20',
        default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-400/20', // neutral
    };

    $dotClasses = match($variant) {
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-sky-500',
        default => 'bg-slate-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "{$baseClasses} {$sizeClasses} {$variantClasses}"]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClasses }} shrink-0" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
