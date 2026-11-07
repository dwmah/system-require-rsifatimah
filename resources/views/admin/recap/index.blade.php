<x-layouts.admin title="Rekap Data Rekrutmen per Periode">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Rekap Data Rekrutmen per Periode</h1>
                <p class="text-sm text-slate-500 mt-0.5">Arsip data, metrik pelamar, dan riwayat seleksi per gelombang/periode rekrutmen</p>
            </div>
        </div>

        <!-- Periode List Cards -->
        <div class="grid grid-cols-1 gap-5">
            @forelse($periods as $period)
                @php
                    $isOpen = $period->status === \App\Enums\RecruitmentPeriodStatus::OPEN;
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md transition-all overflow-hidden p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h2 class="text-lg font-bold text-slate-900">{{ $period->name }}</h2>
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $isOpen ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    {{ $period->status->label() }}
                                </span>
                                <span class="text-xs text-slate-400 font-mono">{{ $period->code }}</span>
                            </div>
                            <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Rentang Pendaftaran: <strong>{{ $period->registration_start_at?->format('d M Y') ?? '-' }}</strong> s/d <strong>{{ $period->registration_end_at?->format('d M Y') ?? '-' }}</strong></span>
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2.5 shrink-0">
                            <a href="{{ route('admin.recap.export', $period) }}" 
                               class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow-md transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Unduh Rekap Excel
                            </a>
                            <a href="{{ route('admin.recap.show', $period) }}" 
                               class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                                Lihat Detail & Pelamar
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Metrics Stats -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5 pt-5 border-t border-slate-100">
                        <div class="bg-slate-50 rounded-xl p-3">
                            <span class="text-xs text-slate-500 font-medium block">Total Formasi</span>
                            <span class="text-lg font-bold text-slate-900">{{ number_format($period->total_vacancies) }} Lowongan</span>
                        </div>
                        <div class="bg-blue-50/50 rounded-xl p-3">
                            <span class="text-xs text-blue-600 font-medium block">Total Lamaran Masuk</span>
                            <span class="text-lg font-bold text-blue-900">{{ number_format($period->total_applications) }} Berkas</span>
                        </div>
                        <div class="bg-emerald-50/50 rounded-xl p-3">
                            <span class="text-xs text-emerald-600 font-medium block">Lolos Seleksi Akhir</span>
                            <span class="text-lg font-bold text-emerald-900">{{ number_format($period->accepted_count) }} Orang</span>
                        </div>
                        <div class="bg-rose-50/50 rounded-xl p-3">
                            <span class="text-xs text-rose-600 font-medium block">Tidak Lolos (Talent Pool)</span>
                            <span class="text-lg font-bold text-rose-900">{{ number_format($period->rejected_count) }} Orang</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <p class="text-slate-500 font-medium">Belum ada riwayat periode rekrutmen.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $periods->links() }}
            </div>
        </div>
    </div>
</x-layouts.admin>
