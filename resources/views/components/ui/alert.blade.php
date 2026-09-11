@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $typeClasses = match($type) {
        'success' => 'bg-emerald-50/80 border-emerald-200 text-emerald-900',
        'warning' => 'bg-amber-50/80 border-amber-200 text-amber-900',
        'danger' => 'bg-red-50/80 border-red-200 text-red-900',
        default => 'bg-sky-50/80 border-sky-200 text-sky-950', // info
    };

    $iconClasses = match($type) {
        'success' => 'text-emerald-600',
        'warning' => 'text-amber-600',
        'danger' => 'text-red-600',
        default => 'text-sky-600',
    };
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    role="alert"
    {{ $attributes->merge(['class' => "rounded-xl border p-4 text-sm flex gap-3.5 items-start {$typeClasses}"]) }}
>
    <div class="shrink-0 mt-0.5 {{ $iconClasses }}" aria-hidden="true">
        @if ($type === 'success')
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif ($type === 'warning')
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        @elseif ($type === 'danger')
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @else
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @endif
    </div>

    <div class="flex-1 min-w-0">
        @if ($title)
            <h4 class="font-semibold mb-1 leading-tight">{{ $title }}</h4>
        @endif
        <div class="leading-relaxed opacity-95">
            {{ $slot }}
        </div>
    </div>

    @if ($dismissible)
        <button
            type="button"
            @click="show = false"
            class="shrink-0 -mr-1 -mt-1 p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-black/5 transition-colors focus:outline-hidden focus:ring-2 focus:ring-slate-400"
            aria-label="Tutup pemberitahuan"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
