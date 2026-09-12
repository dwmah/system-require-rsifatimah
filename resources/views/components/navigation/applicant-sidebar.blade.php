@props([
    'activeMenu' => null,
])

@php
<<<<<<< HEAD
    $isDashboardActive = $activeMenu === 'dashboard' || request()->routeIs('portal.home');
    $isProfileActive = $activeMenu === 'profile' || request()->routeIs('portal.profile.*') || request()->routeIs('profile.*');
    $isApplicationsActive = $activeMenu === 'applications' || request()->routeIs('portal.applications.*');
    $isNotificationsActive = $activeMenu === 'notifications' || request()->routeIs('portal.notifications.*');
=======
    $isDashboardActive = $activeMenu === 'dashboard' || request()->routeIs('portal.home') || request()->routeIs('applicant.dashboard');
    $isProfileActive = $activeMenu === 'profile' || request()->routeIs('profile.*') || request()->routeIs('applicant.profile.*');
    $isApplicationsActive = $activeMenu === 'applications' || request()->routeIs('portal.applications.*') || request()->routeIs('applicant.applications.*');
    $isNotificationsActive = $activeMenu === 'notifications' || request()->routeIs('applicant.notifications.*');
>>>>>>> 00c3161e062b5b07902ac4a8b7a5537efbf9b88d

    $isLowonganActive = $activeMenu === 'lowongan' || request()->routeIs('vacancies.*');
    $isPanduanActive = $activeMenu === 'panduan' || request()->routeIs('guides.*');

    $unreadNotificationsCount = auth()->user() ? auth()->user()->recruitmentNotifications()->whereNull('read_at')->count() : 0;
@endphp

<aside {{ $attributes->merge(['class' => 'w-64 shrink-0 bg-white border-r border-slate-200 flex flex-col justify-between py-5 px-3.5 min-h-full']) }}>
    <div class="space-y-6">
        {{-- PORTAL PELAMAR --}}
        <div>
            <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">
                Portal Pelamar
            </p>

            <nav class="space-y-1">
                {{-- Dashboard / Portal Home --}}
                <a href="{{ route('portal.home') }}"
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $isDashboardActive ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-l-4 border-[#0179B5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 shrink-0 {{ $isDashboardActive ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Beranda Portal</span>
                </a>

                {{-- Lamaran Saya (Workspace Dokumen) --}}
                <a href="{{ route('portal.applications.index') }}"
                   class="flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $isApplicationsActive ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-l-4 border-[#0179B5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 {{ $isApplicationsActive ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Lamaran Saya</span>
                    </div>
                </a>

                {{-- Profil Saya --}}
                <a href="{{ route('profile.show') }}"
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $isProfileActive ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-l-4 border-[#0179B5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 shrink-0 {{ $isProfileActive ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil Saya</span>
                </a>

<<<<<<< HEAD
                {{-- Notifikasi --}}
                <a href="{{ route('portal.notifications.index') }}"
                   class="flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $isNotificationsActive ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-l-4 border-[#0179B5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 {{ $isNotificationsActive ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
=======
                {{-- Lamaran Saya --}}
                <a
                    href="{{ route('portal.applications.index') }}"
                    @class([
                        'flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors',
                        'bg-rsi-50 text-rsi-800 font-semibold' => $isApplicationsActive,
                        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $isApplicationsActive,
                    ])
                >
                    <svg class="w-4 h-4 shrink-0 {{ $isApplicationsActive ? 'text-rsi-700' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Lamaran Saya</span>
                </a>

                {{-- Notifikasi (Tahap Mendatang) --}}
                <a
                    href="{{ Route::has('applicant.notifications.index') ? route('applicant.notifications.index') : 'javascript:void(0)' }}"
                    title="Pusat notifikasi akan segera aktif"
                    class="flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg text-slate-400 hover:text-slate-600 transition-colors"
                    aria-disabled="true"
                >
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
>>>>>>> 00c3161e062b5b07902ac4a8b7a5537efbf9b88d
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
            </nav>
        </div>

        {{-- MENU UTAMA --}}
        <div>
            <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">
                Menu Utama
            </p>

            <nav class="space-y-1">
                <a href="{{ route('vacancies.index') }}"
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $isLowonganActive ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Lowongan Terbuka</span>
                </a>

                <a href="{{ route('guides.flow') }}"
                   class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $isPanduanActive ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Panduan & Info</span>
                </a>
            </nav>
        </div>
    </div>

    {{-- Applicant Account Status & Logout in Sidebar --}}
    <div class="space-y-3">
        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-500">
            <p class="font-semibold text-slate-700 mb-0.5">Status Akun</p>
            <div class="flex items-center gap-1.5 text-[11px] text-emerald-700 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Pelamar SDM Aktif</span>
            </div>
        </div>

        @if (Route::has('logout'))
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar Akun</span>
                </button>
            </form>
        @endif
    </div>
</aside>
