<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Rekrutmen Sedang Ditutup - RSI Fatimah Banyuwangi</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-900 bg-slate-50">

    {{-- Simple Top Header --}}
    <header class="bg-white border-b border-slate-200 py-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <x-ui.brand />

            <a
                href="https://rsifatimah.co.id"
                target="_blank"
                rel="noopener noreferrer"
                class="text-xs font-medium text-rsi-700 hover:text-rsi-800 transition-colors flex items-center gap-1.5 focus:outline-hidden focus:underline"
            >
                <span>Website Resmi RSI Fatimah</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>
        </div>
    </header>

    {{-- Centered Information Container --}}
    <main class="flex-1 flex flex-col justify-center items-center px-4 sm:px-6 py-12 lg:py-20 text-center">
        <div class="w-full max-w-lg bg-white rounded-2xl border border-slate-200 p-8 sm:p-10 shadow-sm space-y-6">
            {{-- Hospital Gate / Calendar Icon --}}
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-500 mx-auto flex items-center justify-center ring-8 ring-slate-50">
                <svg class="w-8 h-8 text-rsi-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <div class="space-y-2">
                <x-ui.badge variant="neutral" size="sm">Status Pendaftaran</x-ui.badge>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Rekrutmen Sedang Ditutup
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
                    Saat ini belum terdapat lowongan formasi atau periode pendaftaran rekrutmen SDM yang sedang dibuka untuk masyarakat umum.
                </p>
            </div>

            {{-- Informational Guidance Card --}}
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-600 space-y-2 text-left">
                <p class="font-semibold text-slate-800 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rsi-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Petunjuk Informasi Pembukaan Berikutnya:
                </p>
                <ul class="space-y-1.5 text-[11px] text-slate-500 list-disc list-inside leading-relaxed pl-1">
                    <li>Informasi formasi lowongan baru akan diumumkan secara resmi melalui saluran resmi RSI Fatimah Banyuwangi.</li>
                    <li>Silakan pantau berkala website utama dan media sosial resmi rumah sakit.</li>
                    <li>Bagi pelamar yang telah mengajukan berkas pada periode sebelumnya, proses verifikasi dan seleksi tetap berjalan sesuai jadwal yang telah ditentukan.</li>
                </ul>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a
                    href="https://rsifatimah.co.id"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full sm:w-auto inline-flex items-center justify-center font-medium rounded-lg text-xs px-4 py-2.5 bg-rsi-700 text-white hover:bg-rsi-800 transition-colors shadow-xs"
                >
                    Kunjungi Website Resmi RSI Fatimah
                </a>
            </div>
        </div>
    </main>

    {{-- Simple Footer --}}
    <footer class="py-5 text-center text-xs text-slate-400 border-t border-slate-200/60 bg-white">
        <p>&copy; {{ date('Y') }} RSI Fatimah Banyuwangi &bull; Sistem Rekrutmen SDM</p>
    </footer>

</body>
</html>
