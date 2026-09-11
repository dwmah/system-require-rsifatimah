@props([
    'activeMenu' => null,
])

@php
    $isLowonganActive = $activeMenu === 'lowongan'
        || request()->routeIs('vacancies.*')
        || request()->routeIs('recruitment.index');

    $isAlurActive = $activeMenu === 'alur'
        || request()->routeIs('guides.flow');

    $isSyaratActive = $activeMenu === 'persyaratan'
        || request()->routeIs('guides.requirements');

    $isPanduanActive = $isAlurActive || $isSyaratActive || request()->routeIs('guides.*');
@endphp

<aside {{ $attributes->merge(['class' => 'w-64 shrink-0 bg-white border-r border-slate-200 flex flex-col justify-between py-5 px-3.5 min-h-full']) }}>
    <div class="space-y-6">
        <div>
            <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">
                Menu Utama
            </p>

            <nav class="space-y-1">
                {{-- Lowongan Pekerjaan --}}
                <a
                    href="{{ route('vacancies.index') }}"
                    @class([
                        'flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors',
                        'bg-rsi-50 text-rsi-800 font-semibold' => $isLowonganActive,
                        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $isLowonganActive,
                    ])
                >
                    <svg class="w-4 h-4 shrink-0 {{ $isLowonganActive ? 'text-rsi-700' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Lowongan Pekerjaan</span>
                </a>

                {{-- Panduan (Collapsible Tree with Alpine.js) --}}
                <div x-data="{ open: {{ $isPanduanActive ? 'true' : 'false' }} }" class="space-y-1">
                    <button
                        type="button"
                        @click="open = !open"
                        @class([
                            'w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-colors cursor-pointer',
                            'bg-slate-50 text-slate-900 font-semibold' => $isPanduanActive,
                            'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $isPanduanActive,
                        ])
                    >
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 {{ $isPanduanActive ? 'text-rsi-700' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Panduan</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="pl-8 space-y-1 pr-1">
                        <a
                            href="{{ route('guides.flow') }}"
                            @class([
                                'block px-2.5 py-1.5 text-xs rounded-md transition-colors',
                                'text-rsi-700 font-semibold bg-rsi-50' => $isAlurActive,
                                'text-slate-500 hover:text-slate-900 hover:bg-slate-50' => ! $isAlurActive,
                            ])
                        >
                            Alur Pendaftaran
                        </a>

                        <a
                            href="{{ route('guides.requirements') }}"
                            @class([
                                'block px-2.5 py-1.5 text-xs rounded-md transition-colors',
                                'text-rsi-700 font-semibold bg-rsi-50' => $isSyaratActive,
                                'text-slate-500 hover:text-slate-900 hover:bg-slate-50' => ! $isSyaratActive,
                            ])
                        >
                            Persyaratan Umum
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    {{-- Contact / Help Widget --}}
    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-500">
        <p class="font-semibold text-slate-700 mb-0.5">Pusat Informasi SDM</p>
        <p class="text-[11px] leading-relaxed text-slate-500 mb-2">
            Senin - Jumat, 08.00 - 15.00 WIB
        </p>
        <span class="inline-flex items-center gap-1.5 text-rsi-700 font-medium text-[11px]">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            (0333) 421118
        </span>
    </div>
</aside>
