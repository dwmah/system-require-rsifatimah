@props([
    'variant' => 'public', // 'public' or 'applicant'
    'userName' => null,
    'userEmail' => null,
])

@php
    $currentUser = auth()->user();
    $name = $userName ?? $currentUser?->name ?? 'Pengguna';
    $email = $userEmail ?? $currentUser?->email ?? '';
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
                    {{-- Profile Pill & Quick Dashboard/Logout --}}
                    <div class="flex items-center gap-3">
                        <a href="{{ $currentUser->isAdmin() ? route('admin.dashboard') : route('profile.show') }}" class="flex items-center gap-2 text-white hover:opacity-90 transition-opacity" title="{{ $currentUser->isAdmin() ? 'Buka Dashboard Admin' : 'Buka Profil Saya' }}">
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
