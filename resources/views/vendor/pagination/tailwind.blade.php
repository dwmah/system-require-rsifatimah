@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">
        {{-- Mobile View: Simple Prev / Next --}}
        <div class="flex flex-1 justify-between gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-300 shadow-2xs cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-medium text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-medium text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-300 shadow-2xs cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs text-slate-500">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-semibold text-slate-800">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-semibold text-slate-800">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-semibold text-slate-800">{{ $paginator->count() }}</span>
                    @endif
                    {!! __('of') !!}
                    <span class="font-semibold text-slate-800">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="inline-flex -space-x-px rounded-lg bg-white shadow-2xs border border-slate-200 overflow-hidden">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="inline-flex items-center px-2.5 py-2 text-xs font-medium text-slate-300 bg-white cursor-not-allowed border-r border-slate-200" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-2.5 py-2 text-xs font-medium text-slate-600 bg-white border-r border-slate-200 hover:bg-slate-50 hover:text-slate-900 transition-colors" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-400 bg-white border-r border-slate-200 cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-rsi-700 bg-rsi-50 border-r border-slate-200 cursor-default ring-1 ring-inset ring-rsi-500/30">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border-r border-slate-200 hover:bg-slate-50 hover:text-slate-900 transition-colors" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-2.5 py-2 text-xs font-medium text-slate-600 bg-white hover:bg-slate-50 hover:text-slate-900 transition-colors" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="inline-flex items-center px-2.5 py-2 text-xs font-medium text-slate-300 bg-white cursor-not-allowed" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
