@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'flex items-center text-xs text-slate-500']) }}>
    <ol class="flex items-center flex-wrap gap-1.5 list-none p-0 m-0">
        <li>
            <a href="{{ (auth()->check() && !auth()->user()->isAdmin()) ? route('portal.home') : '/' }}" class="text-slate-400 hover:text-rsi-700 transition-colors flex items-center gap-1 focus:outline-hidden focus:underline">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Beranda</span>
            </a>
        </li>

        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                <svg class="w-3 h-3 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>

                @if (!empty($item['url']) && !$loop->last)
                    <a href="{{ $item['url'] }}" class="text-slate-500 hover:text-rsi-700 transition-colors focus:outline-hidden focus:underline">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-medium text-slate-800" aria-current="{{ $loop->last ? 'page' : 'false' }}">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
