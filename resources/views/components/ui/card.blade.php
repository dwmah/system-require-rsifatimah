@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
    'divided' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden']) }}>
    @if (! $divided && ($title || isset($header)))
        <div class="px-5 pt-5 pb-0 flex items-center justify-between gap-4">
            <div>
                @if ($title)
                    <h3 class="text-base font-semibold text-slate-900 leading-tight">
                        {{ $title }}
                    </h3>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            @if (isset($header))
                <div class="flex items-center gap-2">
                    {{ $header }}
                </div>
            @endif
        </div>

        <div @class(['px-5 pb-5 pt-2' => $padding])>
            {{ $slot }}
        </div>
    @else
        @if ($title || isset($header))
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-4">
                <div>
                    @if ($title)
                        <h3 class="text-base font-semibold text-slate-900 leading-tight">
                            {{ $title }}
                        </h3>
                    @endif
                    @if ($subtitle)
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>

                @if (isset($header))
                    <div class="flex items-center gap-2">
                        {{ $header }}
                    </div>
                @endif
            </div>
        @endif

        <div @class(['p-5' => $padding])>
            {{ $slot }}
        </div>
    @endif

    @if (isset($footer))
        <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between gap-3">
            {{ $footer }}
        </div>
    @endif
</div>
