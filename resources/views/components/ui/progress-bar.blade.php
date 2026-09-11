@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
    'showPercent' => true,
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $percentage = $max > 0 ? min(100, max(0, round(($value / $max) * 100))) : 0;

    $heightClass = match($size) {
        'sm' => 'h-1.5',
        'lg' => 'h-3',
        default => 'h-2', // md
    };

    $barColor = match($variant) {
        'success' => 'bg-emerald-600',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-600',
        default => 'bg-rsi-700', // primary
    };
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($label || $showPercent)
        <div class="flex justify-between items-center text-xs text-slate-600 mb-1.5 font-medium">
            @if ($label)
                <span>{{ $label }}</span>
            @else
                <span></span>
            @endif

            @if ($showPercent)
                <span class="text-slate-900 font-semibold">{{ $percentage }}%</span>
            @endif
        </div>
    @endif

    <div
        class="w-full bg-slate-100 rounded-full overflow-hidden {{ $heightClass }} ring-1 ring-slate-200/50"
        role="progressbar"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="{{ $max }}"
    >
        <div
            class="{{ $heightClass }} {{ $barColor }} rounded-full transition-all duration-300"
            style="width: {{ $percentage }}%"
        ></div>
    </div>
</div>
