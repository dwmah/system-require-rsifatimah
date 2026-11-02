<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Panel Administrasi SDI' }} - RSI Fatimah Banyuwangi</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            background-color: #F6FAFF;
            color: #1E293B;
            font-size: 15px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-[#F6FAFF] antialiased" x-data="{ mobileMenuOpen: false }">
    <!-- ================= TOP NAVBAR (ADMIN) ================= -->
    <header class="fixed top-0 inset-x-0 z-40 bg-[#0179B5] text-white shadow-xs border-b border-[#016699]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Sisi Kiri: Trigger Mobile & Logo RS -->
            <div class="flex items-center gap-3">
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        aria-label="Buka Menu"
                        class="md:hidden p-1.5 rounded-lg text-white hover:bg-[#016699] transition-colors focus:outline-hidden focus:ring-2 focus:ring-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                </button>

                <x-ui.brand inverted subtitle="Panel Administrasi SDM / SDI" href="{{ route('admin.dashboard') }}" />
            </div>

            <!-- Sisi Kanan: Profile & Logout Dropdown -->
            <div class="flex items-center gap-4">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="flex items-center gap-2.5 pl-1 py-1 cursor-pointer rounded-lg hover:bg-white/10 px-2 transition-colors focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-white text-[#0179B5] font-bold text-xs flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="hidden md:flex flex-col text-left leading-tight">
                            <span class="text-sm font-semibold text-white">{{ auth()->user()->name }}</span>
                            <span class="text-xs text-sky-100">Super User / SDI</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-sky-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50 text-slate-700 divide-y divide-slate-100">
                        <div class="px-4 py-2.5">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500 truncate mt-0.5">{{ auth()->user()->email }}</p>
                            <span class="inline-block mt-1.5 px-2 py-0.5 text-xs font-medium bg-sky-50 text-[#0179B5] rounded border border-sky-100">Super User SDI</span>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span>Dashboard SDI</span>
                            </a>
                            <a href="{{ route('vacancies.index') }}" target="_blank" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                <span>Portal Publik</span>
                            </a>
                        </div>
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 transition-colors text-left">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    <span>Keluar Sistem</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- BODY WRAPPER -->
    <div class="pt-16 flex-1 max-w-[1536px] w-full mx-auto flex">
        <!-- Desktop Sidebar -->
        <x-navigation.admin-sidebar />

        <!-- Mobile Drawer Overlay -->
        <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-50 flex md:hidden">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>
            <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white pt-5 pb-4">
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                    <button @click="mobileMenuOpen = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="px-5 pb-4 border-b border-slate-200">
                    <span class="font-bold text-base text-[#0179B5]">RSI Fatimah</span>
                    <p class="text-xs text-slate-500">Panel Administrasi SDI</p>
                </div>
                <div class="mt-4 flex-1 h-0 overflow-y-auto">
                    <nav class="px-2 space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.dashboard*') ? 'bg-[#E6F2F8] text-[#0179B5]' : 'text-slate-700 hover:bg-slate-100' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.vacancies.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.vacancies*') ? 'bg-[#E6F2F8] text-[#0179B5]' : 'text-slate-700 hover:bg-slate-100' }}">
                            Lowongan SDM
                        </a>
                        <a href="{{ route('admin.applications.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.applications*') ? 'bg-[#E6F2F8] text-[#0179B5]' : 'text-slate-700 hover:bg-slate-100' }}">
                            Lamaran & Verifikasi
                        </a>
                        <a href="{{ route('admin.timelines.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.timelines*') ? 'bg-[#E6F2F8] text-[#0179B5]' : 'text-slate-700 hover:bg-slate-100' }}">
                            Timeline Seleksi
                        </a>
                        <a href="{{ route('admin.guides.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.guides*') ? 'bg-[#E6F2F8] text-[#0179B5]' : 'text-slate-700 hover:bg-slate-100' }}">
                            Kelola Panduan
                        </a>
                        <a href="{{ route('admin.lifecycle.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.lifecycle*') ? 'bg-[#E6F2F8] text-[#0179B5]' : 'text-slate-700 hover:bg-slate-100' }}">
                            Siklus Akun / Arsip
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start gap-3 text-emerald-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="flex-1 font-medium">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-3 text-rose-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="flex-1 font-medium">{{ session('error') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-6 p-4 bg-sky-50 border border-sky-200 rounded-xl flex items-start gap-3 text-sky-800 text-sm shadow-sm">
                    <svg class="w-5 h-5 text-sky-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="flex-1 font-medium">{{ session('info') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-sm shadow-sm">
                    <div class="flex items-center gap-2 font-bold mb-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Terdapat kesalahan pada formulir:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    {{-- Global Confirmation & Alert Modal --}}
    <x-ui.confirm-modal />
</body>
</html>
