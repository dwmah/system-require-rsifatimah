<x-layouts.applicant title="Konfirmasi Formasi Lamaran — Rekrutmen RSI Fatimah">
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Breadcrumb & Back --}}
        <div class="flex items-center justify-between">
            <nav class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('portal.home') }}" class="hover:text-rsi-700 transition-colors">Portal</a>
                <span>/</span>
                <a href="{{ route('vacancies.show', $jobVacancy) }}" class="hover:text-rsi-700 transition-colors">Lowongan</a>
                <span>/</span>
                <span class="text-slate-900 font-medium">Konfirmasi Formasi</span>
            </nav>

            <a
                href="{{ route('vacancies.show', $jobVacancy) }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-rsi-700 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Detail</span>
            </a>
        </div>

        {{-- Main Confirmation Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            {{-- Header Banner --}}
            <div class="p-6 sm:p-7 border-b border-slate-100 bg-gradient-to-r from-rsi-50/70 via-white to-rsi-50/30">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="font-mono text-xs font-bold text-rsi-800 bg-rsi-100/80 px-2.5 py-0.5 rounded">
                        {{ $jobVacancy->code }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Pendaftaran Terbuka
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">
                    {{ $jobVacancy->title }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    {{ $jobVacancy->unit }} &bull; RSI Fatimah Banyuwangi
                </p>
            </div>

            <div class="p-6 sm:p-7 space-y-6">
                {{-- Detail Formasi Grid --}}
                <div>
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                        Rincian Formasi Pekerjaan
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="block text-[11px] text-slate-400 mb-0.5">Tipe Pekerjaan</span>
                            <span class="text-xs font-bold text-slate-800">{{ $jobVacancy->employment_type }}</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="block text-[11px] text-slate-400 mb-0.5">Kebutuhan Kuota</span>
                            <span class="text-xs font-bold text-slate-800">{{ $jobVacancy->quota }} Orang</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="block text-[11px] text-slate-400 mb-0.5">Penempatan</span>
                            <span class="text-xs font-bold text-slate-800">{{ $jobVacancy->location }}</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="block text-[11px] text-slate-400 mb-0.5">Batas Pendaftaran</span>
                            <span class="text-xs font-bold text-red-600">{{ $jobVacancy->close_at->translatedFormat('d M Y, H:i') }} WIB</span>
                        </div>
                    </div>
                </div>

                {{-- Status Prasyarat Profil --}}
                <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-xs font-bold text-emerald-900">
                            Profil Dasar Anda 100% Lengkap
                        </p>
                        <p class="text-[11px] text-emerald-800 leading-relaxed">
                            Data pribadi, alamat domisili, kontak resmi, dan riwayat pendidikan formal telah terverifikasi siap untuk pembuatan draft lamaran.
                        </p>
                    </div>
                </div>

                {{-- Kualifikasi Utama Lowongan --}}
                @if ($jobVacancy->qualifications->isNotEmpty())
                    <div>
                        <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5">
                            Kualifikasi yang Dipersyaratkan
                        </h2>
                        <div class="divide-y divide-slate-100 bg-slate-50 rounded-xl border border-slate-100 p-4">
                            @foreach ($jobVacancy->qualifications as $qual)
                                <div class="py-2 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-baseline justify-between gap-1 text-xs">
                                    <span class="font-semibold text-slate-600 sm:w-1/3 shrink-0">
                                        {{ $qual->label }}
                                    </span>
                                    <span class="text-slate-900 sm:w-2/3 font-medium">
                                        {{ $qual->value }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Critical Warning Box --}}
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3 text-amber-900">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="space-y-1 text-xs">
                        <p class="font-bold text-amber-950">
                            PENTING: Formasi Terkunci untuk Periode Ini
                        </p>
                        <p class="text-amber-800 leading-relaxed text-[11px]">
                            Setelah Anda membuat draft lamaran ini, pilihan formasi pada periode rekrutmen ini <strong>tidak dapat diganti atau dipindahkan</strong> secara mandiri. Setiap pelamar hanya dapat mengajukan <strong>1 formasi</strong> per periode seleksi.
                        </p>
                    </div>
                </div>

                {{-- Action Form --}}
                <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-[11px] text-slate-400">
                        Dengan menekan tombol di samping, Anda mengonfirmasi pilihan formasi ini.
                    </p>

                    <form method="POST" action="{{ route('portal.applications.store', $jobVacancy) }}">
                        @csrf
                        <div class="flex items-center gap-3">
                            <a
                                href="{{ route('vacancies.show', $jobVacancy) }}"
                                class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors"
                            >
                                Batal
                            </a>
                            <x-ui.button type="submit" variant="primary" size="md">
                                <span>Buat Draft Lamaran</span>
                                <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.applicant>
