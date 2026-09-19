@props([
    'activeMenu' => null,
])

@php
    $isDashboardActive = $activeMenu === 'dashboard' || request()->routeIs('portal.home') || request()->routeIs('applicant.dashboard');
    $isProfileActive = $activeMenu === 'profile' || request()->routeIs('portal.profile.*') || request()->routeIs('profile.*') || request()->routeIs('applicant.profile.*');
    $isApplicationsActive = $activeMenu === 'applications' || request()->routeIs('portal.applications.*') || request()->routeIs('applicant.applications.*');
    $isNotificationsActive = $activeMenu === 'notifications' || request()->routeIs('portal.notifications.*') || request()->routeIs('applicant.notifications.*');

    $isAlurActive = $activeMenu === 'alur'
        || request()->routeIs('guides.flow');

    $isSyaratActive = $activeMenu === 'persyaratan'
        || request()->routeIs('guides.requirements');

    $isPanduanActive = $activeMenu === 'panduan' || $isAlurActive || $isSyaratActive || request()->routeIs('guides.*');

    $unreadNotificationsCount = auth()->user() ? auth()->user()->recruitmentNotifications()->whereNull('read_at')->count() : 0;
@endphp

<aside {{ $attributes->merge(['class' => 'w-64 shrink-0 bg-white border-r border-slate-200 flex flex-col justify-between py-4 px-3.5 h-full overflow-hidden']) }}>
    <div class="flex-1 overflow-y-auto space-y-3 pr-1 -mr-1 py-1">
        {{-- PORTAL PELAMAR --}}
        <div>
            <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">
                Portal Pelamar
            </p>

            <nav class="space-y-1">
                {{-- Dashboard / Portal Home --}}
                <a href="{{ route('portal.home') }}"
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg border-l-4 transition-colors {{ $isDashboardActive ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 shrink-0 {{ $isDashboardActive ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Beranda Portal</span>
                </a>

                {{-- Lamaran Saya (Workspace Dokumen) --}}
                <a href="{{ route('portal.applications.index') }}"
                   class="flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg border-l-4 transition-colors {{ $isApplicationsActive ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 {{ $isApplicationsActive ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Lamaran Saya</span>
                    </div>
                </a>

                {{-- Profil Saya --}}
                <a href="{{ route('profile.show') }}"
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg border-l-4 transition-colors {{ $isProfileActive ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 shrink-0 {{ $isProfileActive ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil Saya</span>
                </a>

                {{-- Notifikasi --}}
                <a href="{{ Route::has('portal.notifications.index') ? route('portal.notifications.index') : (Route::has('applicant.notifications.index') ? route('applicant.notifications.index') : 'javascript:void(0)') }}"
                   class="flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg border-l-4 transition-colors {{ $isNotificationsActive ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 {{ $isNotificationsActive ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span>Notifikasi</span>
                    </div>
                    @if($unreadNotificationsCount > 0)
                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-[#0179B5] text-white rounded-full">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                {{-- Panduan & Info (Collapsible Tree with Alpine.js) --}}
                <div x-data="{ open: {{ $isPanduanActive ? 'true' : 'false' }} }" class="space-y-1 pt-1">
                    <button
                        type="button"
                        @click="open = !open"
                        @class([
                            'w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg border-l-4 transition-colors cursor-pointer',
                            'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' => $isPanduanActive,
                            'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $isPanduanActive,
                        ])
                    >
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0 {{ $isPanduanActive ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Panduan &amp; Info</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="pl-8 space-y-1 pr-1">
                        <a
                            href="{{ route('guides.flow') }}"
                            @class([
                                'block px-2.5 py-1.5 text-xs rounded-lg border-l-4 transition-colors',
                                'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' => $isAlurActive,
                                'border-transparent text-slate-500 hover:text-slate-900 hover:bg-slate-50' => ! $isAlurActive,
                            ])
                        >
                            Alur Pendaftaran
                        </a>

                        <a
                            href="{{ route('guides.requirements') }}"
                            @class([
                                'block px-2.5 py-1.5 text-xs rounded-lg border-l-4 transition-colors',
                                'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' => $isSyaratActive,
                                'border-transparent text-slate-500 hover:text-slate-900 hover:bg-slate-50' => ! $isSyaratActive,
                            ])
                        >
                            Persyaratan Umum
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    {{-- Contact / Help Widget (Melayang di posisi paling bawah) --}}
    <div class="pt-3 shrink-0 sticky bottom-0 bg-white z-10">
        <div class="p-3.5 bg-white rounded-2xl border border-slate-200/90 shadow-md hover:shadow-lg transition-all text-xs text-slate-500">
            <p class="font-bold text-slate-800 mb-0.5 flex items-center justify-between">
                <span>Pusat Informasi SDM</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-50 text-[#0179B5] border border-sky-200">Bantuan</span>
            </p>
            <p class="text-[11px] leading-relaxed text-slate-500 mb-2">
                Senin - Jumat, 08.00 - 15.00 WIB
            </p>
            <a href="tel:0333421118" class="inline-flex items-center gap-1.5 text-[#0179B5] hover:text-[#015f8e] font-bold text-xs transition-colors">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>(0333) 421118</span>
            </a>
        </div>
    </div>
</aside>
