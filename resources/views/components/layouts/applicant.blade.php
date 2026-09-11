@props([
    'title' => null,
    'activeMenu' => null,
    'userName' => 'Nama Pelamar',
    'userEmail' => 'pelamar@example.com',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}Portal Pelamar - RSI Fatimah Banyuwangi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <style>[x-cloak] { display: none !important; }</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-900 bg-slate-50" x-data="{ mobileMenuOpen: false }">

    {{-- Topbar Navigation for Applicant --}}
    <x-navigation.topbar
        variant="applicant"
        :user-name="$userName"
        :user-email="$userEmail"
    />

    {{-- Mobile Menu Drawer --}}
    <x-navigation.mobile-menu variant="applicant" />

    {{-- Body Shell: Applicant Sidebar + Main Content --}}
    <div class="flex-1 flex w-full max-w-7xl mx-auto">
        {{-- Desktop Left Sidebar --}}
        <div class="hidden lg:block shrink-0">
            <x-navigation.applicant-sidebar :active-menu="$activeMenu" class="h-full sticky top-16" />
        </div>

        {{-- Main Page Content --}}
        <main class="flex-1 min-w-0 px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
            {{ $slot }}
        </main>
    </div>

    {{-- Footer --}}
    <footer class="bg-white border-t border-slate-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-center sm:text-left">
                <span class="font-semibold text-slate-700">RSI Fatimah Banyuwangi</span>
                <span>&bull;</span>
                <span>Portal Rekrutmen SDM</span>
            </div>
            <p class="text-slate-400 text-center sm:text-right">
                &copy; {{ date('Y') }} RSI Fatimah Banyuwangi.
            </p>
        </div>
    </footer>

</body>
</html>
