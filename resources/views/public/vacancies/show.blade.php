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

                    {{-- CTA Section --}}
                    <div class="mt-6 pt-4 border-t border-slate-100 space-y-2.5">
                        @auth
                            @if(auth()->user()->isAdmin())
                                <a
                                    href="{{ route('admin.vacancies.edit', $jobVacancy) }}"
                                    class="w-full inline-flex items-center justify-center font-semibold rounded-lg text-sm px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white shadow-xs transition-colors"
                                >
                                    <span>Kelola Lowongan (Admin)</span>
                                </a>
                            @elseif (! $jobVacancy->isCurrentlyOpen())
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
                            @elseif ($existingApplication && ! $existingApplication->isDraft())
                                {{-- Lamaran sudah FINAL / TERKIRIM --}}
                                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 space-y-1.5">
                                    <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        Lamaran Terkunci Resmi
                                    </span>
                                    <p class="text-[11px] leading-relaxed">
                                        Anda telah mengirimkan lamaran untuk formasi <strong>{{ $existingApplication->jobVacancy->title }}</strong> pada periode rekrutmen ini. Pilihan formasi telah terkunci permanen.
                                    </p>
                                </div>
                                <a
                                    href="{{ route('portal.applications.show', $existingApplication) }}"
                                    class="w-full inline-flex items-center justify-center gap-2 font-semibold rounded-lg text-xs px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white shadow-xs transition-colors"
                                >
                                    <span>Lihat Detail Lamaran Anda</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                            @elseif ($existingApplication && $existingApplication->job_vacancy_id === $jobVacancy->id)
                                {{-- Lamaran sedang draft pada formasi yang sama --}}
                                <a
                                    href="{{ route('portal.applications.show', $existingApplication) }}"
                                    class="w-full inline-flex items-center justify-center gap-2 font-semibold rounded-lg text-sm px-4 py-2.5 bg-rsi-700 hover:bg-rsi-800 text-white shadow-xs transition-colors cursor-pointer"
                                >
                                    <span>Lanjutkan Draft Lamaran Ini</span>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                                <p class="text-[11px] text-center text-slate-400">
                                    Anda sedang mengerjakan berkas draft untuk formasi ini.
                                </p>
                            @elseif ($existingApplication && $existingApplication->isDraft() && $existingApplication->job_vacancy_id !== $jobVacancy->id)
                                {{-- KASUS UTAMA: Pelamar memiliki draft di formasi lain, ingin melamar formasi ini --}}
                                <div x-data="{ showSwitchModal: false }">
                                    <button
                                        type="button"
                                        @click="showSwitchModal = true"
                                        class="w-full inline-flex items-center justify-center gap-2 font-semibold rounded-lg text-sm px-4 py-2.5 bg-rsi-700 hover:bg-rsi-800 text-white shadow-xs transition-colors cursor-pointer"
                                    >
                                        <span>Lamar Posisi Ini Sekarang</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </button>
                                    <p class="text-[11px] text-center text-amber-600 mt-2 font-medium">
                                        Draft sebelumnya ({{ $existingApplication->jobVacancy->title }}) akan otomatis diganti.
                                    </p>

                                    {{-- Modal Pop-up: Konfirmasi Ganti Formasi & Hapus Draft Lama --}}
                                    <template x-teleport="body">
                                        <div
                                            x-show="showSwitchModal"
                                            x-cloak
                                            class="fixed inset-0 z-50 overflow-y-auto"
                                            role="dialog"
                                            aria-modal="true"
                                        >
                                            {{-- Backdrop --}}
                                            <div
                                                x-show="showSwitchModal"
                                                x-transition:enter="ease-out duration-200"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="ease-in duration-150"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0"
                                                class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                                                @click="showSwitchModal = false"
                                                aria-hidden="true"
                                            ></div>

                                            {{-- Dialog Wrapper --}}
                                            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 relative z-10 pointer-events-none">
                                                <div
                                                    x-show="showSwitchModal"
                                                    x-transition:enter="ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                    x-transition:leave="ease-in duration-150"
                                                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                    class="relative w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-200 pointer-events-auto space-y-4"
                                                >
                                                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <h3 class="text-base font-bold text-slate-900">Ganti Formasi Lamaran?</h3>
                                                                <p class="text-xs text-slate-500">Konfirmasi pergantian draft lamaran</p>
                                                            </div>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            @click="showSwitchModal = false"
                                                            class="text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer"
                                                        >
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <div class="space-y-2.5 text-xs">
                                                        <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 leading-relaxed">
                                                            <strong class="block font-bold text-amber-950 flex items-center gap-1.5 mb-1 text-xs">
                                                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                </svg>
                                                                Lamaran Sebelumnya Akan Dihapus:
                                                            </strong>
                                                            Anda saat ini memiliki draft lamaran untuk formasi <strong>{{ $existingApplication->jobVacancy->title }}</strong>. Draft tersebut beserta berkas dokumen lamaran draft sebelumnya akan <strong>dihapus</strong> dan otomatis diganti dengan formasi baru: <strong>{{ $jobVacancy->title }}</strong>.
                                                        </div>

                                                        <div class="p-3.5 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-900 leading-relaxed">
                                                            <strong class="block font-bold text-emerald-950 flex items-center gap-1.5 mb-1 text-xs">
                                                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                </svg>
                                                                Profil Anda Tetap Aman Tersimpan:
                                                            </strong>
                                                            Data pribadi, kontak, riwayat pendidikan, STR/lisensi, sertifikasi, dan pengalaman kerja Anda <strong>TIDAK AKAN DIHAPUS</strong>.
                                                        </div>

                                                        <p class="text-slate-500 text-[11px] leading-relaxed">
                                                            Pilihan formasi baru akan terkunci permanen hanya setelah Anda melakukan Final Submit di tahap akhir.
                                                        </p>
                                                    </div>

                                                    <form method="POST" action="{{ route('portal.applications.store', $jobVacancy) }}">
                                                        @csrf
                                                        <input type="hidden" name="job_vacancy_id" value="{{ $jobVacancy->id }}">
                                                        <input type="hidden" name="confirm_switch" value="1">

                                                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                                                            <button
                                                                type="button"
                                                                @click="showSwitchModal = false"
                                                                class="px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                                            >
                                                                Batal
                                                            </button>
                                                            <button
                                                                type="submit"
                                                                class="px-4 py-2 text-xs font-bold text-white bg-rsi-700 hover:bg-rsi-800 rounded-lg shadow-xs transition-colors cursor-pointer"
                                                            >
                                                                Ya, Ganti ke Formasi Ini
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @else
                                <a
                                    href="{{ route('portal.applications.start', $jobVacancy) }}"
                                    class="w-full inline-flex items-center justify-center gap-2 font-semibold rounded-lg text-sm px-4 py-2.5 bg-rsi-700 hover:bg-rsi-800 text-white shadow-xs transition-colors cursor-pointer"
                                >
                                    <span>Lamar Posisi Ini Sekarang</span>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                                <p class="text-[11px] text-center text-slate-400">
                                    Pastikan profil dasar Anda telah lengkap sebelum mendaftar.
                                </p>
                            @endif
                        @else
                            @if ($jobVacancy->isCurrentlyOpen())
                                <a
                                    href="{{ route('register', ['lowongan' => $jobVacancy->id]) }}"
                                    class="w-full inline-flex items-center justify-center gap-2 font-semibold rounded-lg text-sm px-4 py-2.5 bg-rsi-700 hover:bg-rsi-800 text-white shadow-xs transition-colors cursor-pointer"
                                >
                                    <span>Lamar Posisi Ini Sekarang</span>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                                <p class="text-[11px] text-center text-slate-500">
                                    Sudah punya akun? <a href="{{ route('login', ['intended' => route('portal.applications.start', $jobVacancy)]) }}" class="text-rsi-700 font-semibold hover:underline">Masuk untuk Melamar</a>
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
                        @endauth
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
