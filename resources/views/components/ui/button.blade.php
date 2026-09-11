@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'disabled' => false,
    'type' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-150 select-none cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-offset-2';

    $sizeClasses = match($size) {
        'sm' => 'text-xs px-3 py-1.5 gap-1.5 min-h-8',
        'lg' => 'text-base px-5 py-3 gap-2.5 min-h-12',
        default => 'text-sm px-4 py-2 gap-2 min-h-10', // md
    };

    $variantClasses = match($variant) {
        'secondary' => 'bg-slate-100 text-slate-800 hover:bg-slate-200 active:bg-slate-300 focus-visible:ring-slate-400 border border-slate-200',
        'outline' => 'bg-transparent text-rsi-700 hover:bg-rsi-50 active:bg-rsi-100 focus-visible:ring-rsi-500 border border-rsi-600',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800 focus-visible:ring-red-500 shadow-xs',
        'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-900 active:bg-slate-200 focus-visible:ring-slate-400',
        default => 'bg-rsi-700 text-white hover:bg-rsi-800 active:bg-rsi-900 focus-visible:ring-rsi-600 shadow-xs', // primary
    };

    $disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none shadow-none' : '';

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses} {$disabledClasses}";
@endphp

@if ($href && ! $disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@elseif ($href && $disabled)
    <span aria-disabled="true" role="link" tabindex="-1" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </span>
@else
    <button
        type="{{ $type }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif
