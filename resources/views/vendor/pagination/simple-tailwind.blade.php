@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-300 bg-white border border-slate-200 cursor-not-allowed rounded-lg shadow-2xs">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-2xs hover:bg-slate-50 transition-colors">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-2xs hover:bg-slate-50 transition-colors">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-300 bg-white border border-slate-200 cursor-not-allowed rounded-lg shadow-2xs">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
