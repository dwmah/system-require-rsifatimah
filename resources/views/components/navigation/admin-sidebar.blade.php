@props(['active' => 'dashboard'])

<aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0 hidden md:flex flex-col justify-between py-6 sticky top-16 h-[calc(100vh-4rem)] overflow-y-auto z-30">
    <div class="space-y-6">
        <!-- Section Menu Utama -->
        <div>
            <div class="px-5 text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">
                Menu Administrasi SDI
            </div>
            <nav class="space-y-1">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center justify-between px-5 py-2.5 text-sm font-medium border-l-4 transition-colors {{ request()->routeIs('admin.dashboard*') ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' : 'border-transparent text-slate-600 hover:text-[#0179B5] hover:bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard*') ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Dashboard</span>
                    </div>
                </a>

                <!-- Lowongan Pekerjaan -->
                <a href="{{ route('admin.vacancies.index') }}"
                   class="flex items-center justify-between px-5 py-2.5 text-sm font-medium border-l-4 transition-colors {{ request()->routeIs('admin.vacancies*') ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' : 'border-transparent text-slate-600 hover:text-[#0179B5] hover:bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.vacancies*') ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>Lowongan SDM</span>
                    </div>
                </a>

                <!-- Lamaran & Verifikasi -->
                <a href="{{ route('admin.applications.index') }}"
                   class="flex items-center justify-between px-5 py-2.5 text-sm font-medium border-l-4 transition-colors {{ request()->routeIs('admin.applications*') ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' : 'border-transparent text-slate-600 hover:text-[#0179B5] hover:bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.applications*') ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Lamaran & Verifikasi</span>
                    </div>
                    @php
                        $pendingCount = \App\Models\JobApplication::where('status', \App\Enums\JobApplicationStatus::SUBMITTED)->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-800 rounded-full">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>

                <!-- Timeline & Jadwal Seleksi -->
                <a href="{{ route('admin.timelines.index') }}"
                   class="flex items-center justify-between px-5 py-2.5 text-sm font-medium border-l-4 transition-colors {{ request()->routeIs('admin.timelines*') ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' : 'border-transparent text-slate-600 hover:text-[#0179B5] hover:bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.timelines*') ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Timeline Seleksi</span>
                    </div>
                </a>

                <!-- Siklus Akun (User Lifecycle) -->
                <a href="{{ route('admin.lifecycle.index') }}"
                   class="flex items-center justify-between px-5 py-2.5 text-sm font-medium border-l-4 transition-colors {{ request()->routeIs('admin.lifecycle*') ? 'bg-[#E6F2F8] text-[#0179B5] font-semibold border-[#0179B5]' : 'border-transparent text-slate-600 hover:text-[#0179B5] hover:bg-slate-50' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.lifecycle*') ? 'text-[#0179B5]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Siklus Akun / Arsip</span>
                    </div>
                </a>
            </nav>
        </div>

        <!-- Section Tautan Publik -->
        <div>
            <div class="px-5 text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">
                Tampilan Publik
            </div>
            <nav class="space-y-1">
                <a href="{{ route('vacancies.index') }}" target="_blank"
                   class="flex items-center justify-between px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-[#0179B5] hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        <span>Portal Publik Lowongan</span>
                    </div>
                </a>
            </nav>
        </div>
    </div>
</aside>
