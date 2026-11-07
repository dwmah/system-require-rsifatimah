@props([
    'title' => null,
    'activeMenu' => null,
])

@php
    $isApplicant = auth()->check() && !auth()->user()->isAdmin();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}Sistem Rekrutmen SDM RSI Fatimah Banyuwangi</title>

    <style>
        [x-cloak] { display: none !important; }
        body, html {
            font-family: 'Tahoma', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
            font-size: 15px;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-900 bg-slate-50" x-data="{ mobileMenuOpen: false }">

    {{-- Topbar Navigation --}}
    <x-navigation.topbar :variant="$isApplicant ? 'applicant' : 'public'" />

    {{-- Mobile Menu Drawer --}}
    <x-navigation.mobile-menu :variant="$isApplicant ? 'applicant' : 'public'" :active-menu="$activeMenu" />

    {{-- Body Shell: Sidebar + Main Content --}}
    <div class="pt-16 flex-1 flex w-full">
        {{-- Desktop Left Sidebar (Fixed with reserved space) --}}
        <div class="hidden lg:block w-56 shrink-0">
            <div class="fixed top-16 left-0 bottom-0 w-56 z-30">
                @if ($isApplicant)
                    <x-navigation.applicant-sidebar :active-menu="$activeMenu" class="h-full" />
                @else
                    <x-navigation.public-sidebar :active-menu="$activeMenu" class="h-full" />
                @endif
            </div>
        </div>

        {{-- Main Page Content Wrapper --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Main Page Content --}}
            <main class="flex-1 min-w-0 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                {{ $slot }}
            </main>

            {{-- Footer --}}
            <footer class="bg-white border-t border-slate-200 mt-auto">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-center sm:text-left">
                        <span class="font-semibold text-slate-700">RSI Fatimah Banyuwangi</span>
                        <span>&bull;</span>
                        <span>Jl. Jember No. 56, Banyuwangi, Jawa Timur</span>
                    </div>
                    <p class="text-slate-400 text-center sm:text-right">
                        &copy; {{ date('Y') }} Sistem Rekrutmen SDM. Hak cipta dilindungi undang-undang.
                    </p>
                </div>
            </footer>
        </div>
    </div>

    {{-- Global Confirmation & Alert Modal --}}
    <x-ui.confirm-modal />

</body>
</html>
