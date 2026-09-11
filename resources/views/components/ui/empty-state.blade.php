@props([
    'title' => 'Belum ada data',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12 px-4 rounded-xl border border-dashed border-slate-200 bg-white/50 flex flex-col items-center justify-center']) }}>
    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3.5 ring-1 ring-slate-200/50" aria-hidden="true">
        @if (isset($icon))
            {{ $icon }}
        @else
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        @endif
    </div>

    <h4 class="text-sm font-semibold text-slate-900 mb-1">
        {{ $title }}
    </h4>

    @if ($description)
        <p class="text-xs text-slate-500 max-w-sm mb-4 leading-relaxed">
            {{ $description }}
        </p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-2">
            {{ $slot }}
        </div>
    @endif
</div>
