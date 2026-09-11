@props([
    'title' => null,
    'cardTitle' => null,
    'cardSubtitle' => null,
    'backUrl' => '/',
    'backLabel' => 'Kembali ke Beranda',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}Sistem Rekrutmen SDM RSI Fatimah Banyuwangi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <style>[x-cloak] { display: none !important; }</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-900 bg-slate-50">

    {{-- Minimal Header --}}
    <header class="py-6 px-4 sm:px-6 lg:px-8 border-b border-slate-200/60 bg-white">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <x-ui.brand />

            @if ($backUrl)
                <a
                    href="{{ $backUrl }}"
                    class="text-xs font-medium text-slate-500 hover:text-rsi-700 transition-colors flex items-center gap-1.5 focus:outline-hidden focus:underline"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>{{ $backLabel }}</span>
                </a>
            @endif
        </div>
    </header>

    {{-- Main Centered Card Container --}}
    <main class="flex-1 flex flex-col justify-center items-center px-4 sm:px-6 py-10 lg:py-16">
        <div class="w-full max-w-md">
            {{-- Authentication Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
                @if ($cardTitle)
                    <div class="mb-6 text-center">
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">
                            {{ $cardTitle }}
                        </h2>
                        @if ($cardSubtitle)
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $cardSubtitle }}
                            </p>
                        @endif
                    </div>
                @endif

                {{ $slot }}
            </div>
        </div>
    </main>

    {{-- Minimal Footer --}}
    <footer class="py-5 text-center text-xs text-slate-400 border-t border-slate-200/60 bg-white">
        <p>&copy; {{ date('Y') }} RSI Fatimah Banyuwangi &bull; Sistem Rekrutmen SDM</p>
    </footer>

</body>
</html>
