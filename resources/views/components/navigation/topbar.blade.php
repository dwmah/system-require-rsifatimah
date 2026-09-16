@props([
    'variant' => 'public', // 'public' or 'applicant'
    'userName' => null,
    'userEmail' => null,
])

@php
    $currentUser = auth()->user();
    $name = $userName ?? $currentUser?->name ?? 'Pengguna';
    $email = $userEmail ?? $currentUser?->email ?? '';
    $unreadCount = $currentUser ? $currentUser->recruitmentNotifications()->whereNull('read_at')->count() : 0;
@endphp

<header class="bg-[#0179B5] border-b border-[#016699] fixed top-0 inset-x-0 z-40 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Left: Brand and Mobile Hamburger Toggle --}}
            <div class="flex items-center gap-3">
                {{-- Hamburger Button (Mobile Only) --}}
                <button
                    type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="lg:hidden p-1.5 rounded-lg text-white hover:bg-[#016699] transition-colors focus:outline-hidden focus:ring-2 focus:ring-white"
                    aria-label="Buka menu navigasi"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <x-ui.brand inverted />
            </div>

            {{-- Right: Public Actions or Authenticated Profile --}}
            <div class="flex items-center gap-3">
                @if ($currentUser)
                    {{-- Notifications Link / Icon --}}
                    @if(!$currentUser->isAdmin())
                        <a
                            href="{{ route('portal.notifications.index') }}"
                            title="Pusat Notifikasi"
                            class="p-2 text-white hover:text-sky-100 hover:bg-[#016699] rounded-lg transition-colors relative focus:outline-hidden focus:ring-2 focus:ring-white"
                            aria-label="Notifikasi akun"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if($unreadCount > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-400 ring-2 ring-[#0179B5]"></span>
                            @endif
                        </a>
                    @endif

                    {{-- Profile Pill & Quick Dashboard/Logout --}}
                    <div class="flex items-center gap-3 pl-2 border-l border-white/20">
                        <a href="{{ $currentUser->isAdmin() ? route('admin.dashboard') : route('portal.home') }}" class="flex items-center gap-2 text-white hover:opacity-90 transition-opacity" title="Buka Dashboard">
                            @if(!$currentUser->isAdmin() && $currentUser->profile_photo_url)
                                <img src="{{ $currentUser->profile_photo_url }}" alt="{{ $name }}" class="w-8 h-8 rounded-full object-cover shadow-xs border border-white/40" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; max-width: 32px; max-height: 32px; object-fit: cover; border-radius: 9999px;">
                            @else
                                <div class="w-8 h-8 rounded-full bg-white text-[#0179B5] font-bold text-xs flex items-center justify-center shadow-xs">
                                    {{ strtoupper(substr($name, 0, 2)) }}
                                </div>
                            @endif
                            <div class="hidden sm:block text-left leading-tight">
                                <p class="text-xs font-bold text-white truncate max-w-[140px]">{{ $name }}</p>
                                <p class="text-[11px] text-sky-100 truncate max-w-[140px]">{{ $currentUser->isAdmin() ? 'Admin SDI' : $email }}</p>
                            </div>
                        </a>
                        @if (Route::has('logout'))
                            <form method="POST" action="{{ route('logout') }}" class="inline ml-1">
                                @csrf
                                <button
                                    type="submit"
                                    title="Keluar Akun"
                                    class="p-1.5 text-sky-100 hover:text-white hover:bg-[#016699] rounded-lg transition-colors focus:outline-hidden focus:ring-2 focus:ring-white cursor-pointer"
                                    aria-label="Keluar dari akun"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    {{-- Public Mode: Masuk Button --}}
                    <div class="flex items-center gap-2.5">
                        <a
                            href="{{ Route::has('login') ? route('login') : '/masuk' }}"
                            class="px-4 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 text-white border border-white/30 text-xs font-bold shadow-2xs transition-all"
                        >
                            Masuk
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>
