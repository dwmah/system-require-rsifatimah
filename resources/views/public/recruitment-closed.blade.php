<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Lowongan Tidak Tersedia - RSI Fatimah Banyuwangi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-900 bg-slate-50">

    {{-- Simple Top Header --}}
    <header class="bg-white border-b border-slate-200 py-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <x-ui.brand />

            <div class="flex items-center gap-4">
                @auth
                    <a
                        href="{{ route('portal.home') }}"
                        class="text-xs font-semibold text-rsi-700 hover:text-rsi-800 transition-colors flex items-center gap-1"
                    >
                        <span>Portal Saya</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @else
                    
                @endauth

                <a
                    href="https://rsifatimahbanyuwangi.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-xs font-medium text-slate-500 hover:text-slate-700 transition-colors flex items-center gap-1.5 focus:outline-hidden focus:underline"
                >
                    <span>Website Resmi</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </header>

    {{-- Centered Information Container --}}
    <main class="flex-1 flex flex-col justify-center items-center px-4 sm:px-6 py-12 lg:py-20 text-center">
        <div class="w-full max-w-sm bg-white rounded-xl border border-slate-200 py-5 px-6 shadow-sm">
            @if (session('error'))
                <div class="p-3.5 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 text-left flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <h1 class="text-xs sm:text-lg font-bold text-slate-900 tracking-tight">
                Lowongan Tidak Tersedia
            </h1>
        </div>
    </main>

    {{-- Simple Footer --}}
    <footer class="py-5 text-center text-xs text-slate-400 border-t border-slate-200/60 bg-white">
        <p>&copy; {{ date('Y') }} RSI Fatimah Banyuwangi &bull; Sistem Rekrutmen SDM</p>
    </footer>

</body>
</html>
