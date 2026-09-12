<x-layouts.applicant title="Lamaran Saya — Rekrutmen RSI Fatimah">
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">
                    Lamaran Saya
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Daftar riwayat dan status lamaran pekerjaan yang telah Anda ajukan di RSI Fatimah Banyuwangi.
                </p>
            </div>

            <div>
                <a
                    href="{{ route('vacancies.index') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-rsi-700 bg-rsi-50 hover:bg-rsi-100 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Jelajahi Lowongan</span>
                </a>
            </div>
        </div>

        {{-- Flash Alerts --}}
        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('info'))
            <x-ui.alert type="info">
                {{ session('info') }}
            </x-ui.alert>
        @endif

        @if (session('warning'))
            <x-ui.alert type="warning">
                {{ session('warning') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert type="error">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        {{-- Applications List --}}
        @if ($applications->isEmpty())
            <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">
                    Belum Ada Lamaran Diajukan
                </h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                    Anda belum memiliki draft atau riwayat berkas lamaran pada sistem seleksi RSI Fatimah. Silakan cek lowongan yang sedang dibuka.
                </p>
                <div class="pt-2">
                    <a
                        href="{{ route('vacancies.index') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg text-xs font-semibold text-white bg-rsi-700 hover:bg-rsi-800 transition-colors shadow-2xs"
                    >
                        <span>Lihat Lowongan Dibuka</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($applications as $app)
                    <div class="bg-white rounded-2xl border border-slate-200 hover:border-slate-300 transition-colors p-5 sm:p-6 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold text-rsi-800 bg-rsi-50 px-2.5 py-0.5 rounded border border-rsi-100">
                                    {{ $app->jobVacancy->code }}
                                </span>
                                <span class="text-xs text-slate-400">&bull;</span>
                                <span class="text-xs font-medium text-slate-500">
                                    {{ $app->recruitmentPeriod->name }}
                                </span>
                            </div>

                            <h2 class="text-base font-bold text-slate-900">
                                {{ $app->jobVacancy->title }}
                            </h2>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                <div>
                                    <span>Nomor Lamaran: </span>
                                    <strong class="text-slate-700 font-mono">
                                        {{ $app->application_number ?? 'Belum diterbitkan' }}
                                    </strong>
                                </div>
                                <div>
                                    <span>Dibuat: </span>
                                    <strong class="text-slate-700">
                                        {{ $app->created_at->translatedFormat('d M Y, H:i') }} WIB
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            {{-- Status Badge --}}
                            @php
                                $statusClass = match ($app->status) {
                                    \App\Enums\JobApplicationStatus::DRAFT => 'bg-amber-50 text-amber-700 border-amber-200',
                                    \App\Enums\JobApplicationStatus::SUBMITTED => 'bg-blue-50 text-blue-700 border-blue-200',
                                    \App\Enums\JobApplicationStatus::IN_PROCESS => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    \App\Enums\JobApplicationStatus::ACCEPTED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    \App\Enums\JobApplicationStatus::REJECTED => 'bg-red-50 text-red-700 border-red-200',
                                };
                            @endphp

                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $statusClass }}">
                                {{ $app->status->label() }}
                            </span>

                            <a
                                href="{{ route('portal.applications.show', $app) }}"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-rsi-700 hover:text-rsi-800 hover:underline"
                            >
                                <span>Detail Lamaran</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.applicant>
