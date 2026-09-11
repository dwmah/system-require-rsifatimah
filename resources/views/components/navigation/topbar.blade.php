@props([
    'variant' => 'public', // 'public' or 'applicant'
    'userName' => 'Nama Pelamar',
    'userEmail' => 'pelamar@example.com',
])

<header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-2xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Left: Brand and Mobile Hamburger Toggle --}}
            <div class="flex items-center gap-3">
                {{-- Hamburger Button (Mobile Only) --}}
                <button
                    type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors focus:outline-hidden focus:ring-2 focus:ring-rsi-500"
                    aria-label="Buka menu navigasi"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <x-ui.brand />
            </div>

            {{-- Right: Public Actions or Applicant Profile --}}
            <div class="flex items-center gap-3">
                @if ($variant === 'applicant')
                    {{-- Notifications Link / Icon --}}
                    <a
                        href="{{ Route::has('applicant.notifications.index') ? route('applicant.notifications.index') : '/applicant/notifikasi' }}"
                        class="p-2 text-slate-400 hover:text-rsi-700 hover:bg-slate-50 rounded-lg transition-colors relative focus:outline-hidden focus:ring-2 focus:ring-rsi-500"
                        aria-label="Notifikasi akun"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </a>

                    {{-- Applicant Profile Pill --}}
                    <div class="flex items-center gap-3 pl-2 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-rsi-100 text-rsi-800 font-semibold text-xs flex items-center justify-center ring-1 ring-rsi-200">
                            {{ strtoupper(substr($userName, 0, 2)) }}
                        </div>
                        <div class="hidden sm:block text-left leading-tight">
                            <p class="text-xs font-semibold text-slate-900 truncate max-w-[140px]">{{ $userName }}</p>
                            <p class="text-[11px] text-slate-500 truncate max-w-[140px]">{{ $userEmail }}</p>
                        </div>
                    </div>
                @else
                    {{-- Public Mode: Masuk & Daftar Buttons --}}
                    <div class="flex items-center gap-2">
                        <x-ui.button
                            :href="Route::has('login') ? route('login') : '/login'"
                            variant="ghost"
                            size="sm"
                        >
                            Masuk
                        </x-ui.button>

                        <x-ui.button
                            :href="Route::has('register') ? route('register') : '/register'"
                            variant="primary"
                            size="sm"
                        >
                            Daftar Akun
                        </x-ui.button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>
