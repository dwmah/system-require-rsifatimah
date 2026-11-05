<x-layouts.admin title="Detail Rekap: {{ $period->name }}">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.recap.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detail Rekap: {{ $period->name }}</h1>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">Kode: {{ $period->code }} | Rentang: {{ $period->registration_start_at?->format('d M Y') }} - {{ $period->registration_end_at?->format('d M Y') }}</p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.recap.export', array_merge(['period' => $period->id], request()->only(['search', 'vacancy_id', 'status']))) }}" 
                   class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow-md transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Ekspor Data Periode Ini (.xlsx)
                </a>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-medium text-slate-500">Total Pelamar Terdaftar</span>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($metrics['total']) }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-medium text-blue-600">Sedang Proses Seleksi</span>
                <p class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($metrics['in_selection']) }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-medium text-emerald-600">Diterima / Lolos Akhir</span>
                <p class="text-2xl font-bold text-emerald-900 mt-1">{{ number_format($metrics['accepted']) }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-xs font-medium text-rose-600">Tidak Lolos (Talent Pool)</span>
                <p class="text-2xl font-bold text-rose-900 mt-1">{{ number_format($metrics['rejected']) }}</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <form method="GET" action="{{ route('admin.recap.show', $period) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, nomor lamaran, email..." 
                       class="px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-hidden">
                <select name="vacancy_id" class="px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-hidden">
                    <option value="">Semua Formasi Lowongan</option>
                    @foreach($vacancies as $vac)
                        <option value="{{ $vac->id }}" {{ request('vacancy_id') == $vac->id ? 'selected' : '' }}>
                            {{ $vac->title }} ({{ $vac->job_applications_count }} pelamar)
                        </option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2">
                    <select name="status" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-hidden">
                        <option value="">Semua Status Seleksi</option>
                        @foreach(\App\Enums\JobApplicationStatus::cases() as $st)
                            <option value="{{ $st->value }}" {{ request('status') == $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-all">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Table Applications -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">Nomor & Pelamar</th>
                            <th class="py-3 px-4">Formasi Lowongan</th>
                            <th class="py-3 px-4">Status & Tahap</th>
                            <th class="py-3 px-4">Tanggal Masuk</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($applications as $app)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $app->user?->name ?? 'User Terhapus' }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $app->application_number }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800">{{ $app->jobVacancy?->title ?? '-' }}</div>
                                    <div class="text-xs text-slate-400">{{ $app->jobVacancy?->unit ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $app->status->badgeClass() }}">
                                        {{ $app->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-500">
                                    {{ $app->submitted_at?->format('d M Y H:i') ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('admin.applications.show', $app) }}" 
                                       class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg transition-colors inline-block">
                                        Lihat Berkas
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-sm">
                                    Tidak ada data pelamar pada kriteria filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</x-layouts.admin>
