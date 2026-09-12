@php
    /** @var \App\Models\JobVacancy $jobVacancy */
    $daysRemaining = now()->diffInDays($jobVacancy->close_at, false);
    $isClosingSoon = $daysRemaining >= 0 && $daysRemaining <= 3;
@endphp

<x-layouts.public :title="$jobVacancy->title" active-menu="lowongan">
    <div class="space-y-6 max-w-5xl">
        {{-- Breadcrumb --}}
        <x-ui.breadcrumb :items="[
            ['label' => 'Lowongan Pekerjaan', 'url' => route('vacancies.index')],
            ['label' => $jobVacancy->title]
        ]" />

        {{-- Header Detail --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">
                        {{ $jobVacancy->code }}
                    </span>
                    @if ($isClosingSoon)
                        <x-ui.badge variant="warning" dot size="sm">
                            Segera Ditutup (Sisa {{ $daysRemaining }} hari)
                        </x-ui.badge>
                    @else
                        <x-ui.badge variant="success" dot size="sm">
                            Pendaftaran Dibuka
                        </x-ui.badge>
                    @endif
                </div>

                <span class="text-xs text-slate-400">
                    Periode: {{ $jobVacancy->recruitmentPeriod?->name }}
                </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-3">
                {{ $jobVacancy->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600">
                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    <svg class="w-4 h-4 text-rsi-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ $jobVacancy->unit }}
                </span>

                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    <svg class="w-4 h-4 text-rsi-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ $jobVacancy->employment_type }}
                </span>

                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    <svg class="w-4 h-4 text-rsi-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Kuota: {{ $jobVacancy->quota }} Orang
                </span>

                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    <svg class="w-4 h-4 text-rsi-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ $jobVacancy->location }}
                </span>
            </div>
        </div>

        {{-- Main Detail Layout: 2 Columns on Desktop --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            {{-- Main Content Column (2/3) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Deskripsi Posisi --}}
                @if ($jobVacancy->description)
                    <x-ui.card title="Deskripsi Pekerjaan">
                        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $jobVacancy->description }}
                        </div>
                    </x-ui.card>
                @endif

                {{-- Tanggung Jawab / Tugas Utama --}}
                @if ($jobVacancy->responsibilities)
                    <x-ui.card title="Tanggung Jawab & Uraian Tugas">
                        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $jobVacancy->responsibilities }}
                        </div>
                    </x-ui.card>
                @endif

                {{-- Kualifikasi Dinamis --}}
                <x-ui.card title="Kualifikasi Pelamar">
                    @if ($jobVacancy->qualifications->isEmpty())
                        <p class="text-xs text-slate-500 italic">Kualifikasi umum berlaku sesuai pedoman SDM.</p>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach ($jobVacancy->qualifications as $qual)
                                <div class="py-2.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-baseline justify-between gap-1 sm:gap-4 text-xs">
                                    <span class="font-semibold text-slate-700 sm:w-1/3 shrink-0">
                                        {{ $qual->label }}
                                    </span>
                                    <span class="text-slate-900 sm:w-2/3">
                                        {{ $qual->value }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card>

                {{-- Persyaratan Berkas / Dokumen --}}
                <x-ui.card title="Persyaratan Dokumen & Berkas">
                    <p class="text-xs text-slate-500 mb-3">
                        Dokumen berikut harus disiapkan dan diunggah secara digital saat pengisian berkas lamaran.
                    </p>

                    @if ($jobVacancy->documentRequirements->isEmpty())
                        <p class="text-xs text-slate-500 italic">Tidak ada dokumen spesifik yang disyaratkan.</p>
                    @else
                        <div class="space-y-2.5">
                            @foreach ($jobVacancy->documentRequirements as $doc)
                                @php
                                    $sizeText = $doc->max_size_kb ? ($doc->max_size_kb >= 1024 ? round($doc->max_size_kb / 1024, 1) . ' MB' : $doc->max_size_kb . ' KB') : '2 MB';
                                    $mimeText = $doc->allowed_mimes ?: 'PDF';
                                @endphp
                                <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/70 flex items-start justify-between gap-3 text-xs">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <span class="font-semibold text-slate-900">{{ $doc->name }}</span>
                                            @if ($doc->is_required)
                                                <x-ui.badge variant="danger" size="sm">Wajib</x-ui.badge>
                                            @else
                                                <x-ui.badge variant="neutral" size="sm">Opsional</x-ui.badge>
                                            @endif
                                        </div>
                                        @if ($doc->description)
                                            <p class="text-[11px] text-slate-500 leading-relaxed">{{ $doc->description }}</p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 text-[11px] text-slate-400 font-mono bg-white px-2 py-0.5 rounded-md border border-slate-200">
                                        {{ $mimeText }} &bull; {{ $sizeText }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card>

                {{-- Tahapan Seleksi Dinamis --}}
                <x-ui.card title="Tahapan Seleksi">
                    <p class="text-xs text-slate-500 mb-4">
                        Kandidat wajib mengikuti seluruh rangkaian evaluasi secara bertahap sesuai jadwal yang ditetapkan.
                    </p>

                    @if ($jobVacancy->selectionStages->isEmpty())
                        <p class="text-xs text-slate-500 italic">Tahapan seleksi mengikuti pengumuman panitia.</p>
                    @else
                        <div class="relative pl-6 space-y-4 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                            @foreach ($jobVacancy->selectionStages as $index => $stage)
                                <div class="relative flex items-start gap-3">
                                    <span class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-rsi-700 text-white font-bold text-[10px] flex items-center justify-center ring-4 ring-white">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-bold text-slate-900 leading-snug">
                                            {{ $stage->name }}
                                        </h4>
                                        @if ($stage->description)
                                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                                {{ $stage->description }}
                                            </p>
                                        @endif
                                        @if ($stage->start_at && $stage->end_at)
                                            <p class="text-[11px] text-rsi-700 font-medium mt-1">
                                                Estimasi: {{ $stage->start_at->translatedFormat('d M Y') }} &ndash; {{ $stage->end_at->translatedFormat('d M Y') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card>
            </div>

            {{-- Summary Sticky Sidebar Column (1/3) --}}
            <div class="space-y-4 sticky top-20">
                <x-ui.card title="Ringkasan Pendaftaran">
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Mulai Pendaftaran:</span>
                            <span class="font-semibold text-slate-900">{{ $jobVacancy->open_at->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Batas Akhir:</span>
                            <span class="font-semibold text-red-600">{{ $jobVacancy->close_at->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Kebutuhan Kuota:</span>
                            <span class="font-semibold text-slate-900">{{ $jobVacancy->quota }} Orang</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-100">
                            <span class="text-slate-500">Status Seleksi:</span>
                            <span class="font-semibold text-emerald-600">Terbuka untuk Umum</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-slate-500">Penempatan:</span>
                            <span class="font-semibold text-slate-900">{{ $jobVacancy->location }}</span>
                        </div>
                    </div>

                    {{-- CTA Section: Mengarah ke portal start application --}}
                    <div class="mt-6 pt-4 border-t border-slate-100 space-y-2">
                        @if ($jobVacancy->isCurrentlyOpen())
                            <a
                                href="{{ route('portal.applications.start', $jobVacancy) }}"
                                class="w-full inline-flex items-center justify-center gap-2 font-semibold rounded-lg text-sm px-4 py-2.5 bg-rsi-700 hover:bg-rsi-800 text-white shadow-xs transition-colors cursor-pointer"
                            >
                                <span>Lamar Sekarang</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                            <p class="text-[11px] text-center text-slate-400">
                                Pastikan profil dasar Anda telah lengkap 100% sebelum mendaftar.
                            </p>
                        @else
                            <button
                                type="button"
                                disabled
                                class="w-full inline-flex items-center justify-center font-medium rounded-lg text-sm px-4 py-2.5 bg-slate-200 text-slate-500 cursor-not-allowed shadow-xs"
                            >
                                <span>Pendaftaran Ditutup</span>
                            </button>
                            <p class="text-[11px] text-center text-slate-400">
                                Masa pendaftaran untuk lowongan ini telah berakhir.
                            </p>
                        @endif
                    </div>
                </x-ui.card>

                {{-- Help Information --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-white text-xs text-slate-600 space-y-2">
                    <p class="font-semibold text-slate-800">Ada Pertanyaan?</p>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Pelajari alur pendaftaran dan persyaratan berkas di menu panduan, atau hubungi panitia rekrutmen SDM RSI Fatimah.
                    </p>
                    <div class="pt-2 flex flex-col gap-1.5">
                        <a href="{{ route('guides.flow') }}" class="text-rsi-700 hover:underline font-medium text-xs flex items-center gap-1">
                            &rarr; Baca Alur Pendaftaran
                        </a>
                        <a href="{{ route('guides.requirements') }}" class="text-rsi-700 hover:underline font-medium text-xs flex items-center gap-1">
                            &rarr; Ketentuan Persyaratan Berkas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>
