<x-layouts.admin title="Dashboard SDI">
    <div class="space-y-6">
        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard Rekrutmen SDI</h1>
                <p class="text-sm text-slate-500 mt-0.5">Ringkasan aktivitas seleksi dan status penerimaan karyawan RSI Fatimah Banyuwangi</p>
            </div>
            <div class="flex items-center gap-3">
                @if($activePeriod)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-lg text-xs font-semibold text-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Periode Aktif: {{ $activePeriod->name }}</span>
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-lg text-xs font-semibold text-amber-800">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Tidak Ada Periode Aktif</span>
                    </div>
                @endif
                <a href="{{ route('admin.vacancies.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Buat Lowongan Baru</span>
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Pelamar -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pelamar</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $totalApplicants }}</div>
                    <span class="text-xs text-slate-500 mt-0.5 inline-block">{{ $totalApplications }} Berkas Masuk</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-[#0179B5] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>

            <!-- Menunggu Verifikasi -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-600">Menunggu Verifikasi</span>
                    <div class="text-2xl font-extrabold text-amber-700 mt-1">{{ $pendingVerification }}</div>
                    <a href="{{ route('admin.applications.index', ['status' => 'submitted']) }}" class="text-xs text-amber-600 hover:underline mt-0.5 inline-block font-medium">Periksa Berkas &rarr;</a>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Lolos Administrasi -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Lolos Administrasi</span>
                    <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $passedAdministration }}</div>
                    <span class="text-xs text-emerald-600 mt-0.5 inline-block font-medium">Memenuhi Syarat</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Tidak Lolos -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-rose-600">Tidak Lolos / Ditolak</span>
                    <div class="text-2xl font-extrabold text-rose-700 mt-1">{{ $rejectedCount }}</div>
                    <span class="text-xs text-slate-500 mt-0.5 inline-block">Berkas Tidak Lengkap</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Secondary Metrics Breakdown -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <span class="text-xs text-slate-500">Lowongan Aktif</span>
                <div class="text-xl font-bold text-slate-800 mt-1">{{ $activeVacancies }} / {{ $totalVacancies }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <span class="text-xs text-slate-500">Dalam Tahap Seleksi</span>
                <div class="text-xl font-bold text-sky-700 mt-1">{{ $inSelection }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <span class="text-xs text-slate-500">Diterima / Lolos Akhir</span>
                <div class="text-xl font-bold text-teal-700 mt-1">{{ $acceptedCount }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <span class="text-xs text-slate-500">Draft Pelamar</span>
                <div class="text-xl font-bold text-slate-600 mt-1">{{ $draftCount }}</div>
            </div>
        </div>

        <!-- Main Layout Split: Lowongan & Lamaran Masuk Terbaru -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Lamaran Masuk Terbaru (2 Spans) -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Lamaran Terbaru Masuk</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar berkas pendaftaran yang siap diperiksa oleh tim SDI</p>
                    </div>
                    <a href="{{ route('admin.applications.index') }}" class="text-xs font-semibold text-[#0179B5] hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Pelamar</th>
                                <th class="py-3 px-4">Formasi</th>
                                <th class="py-3 px-4">Tanggal Submit</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentApplications as $app)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-slate-900">{{ $app->user->name }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $app->user->email }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-medium text-slate-700">
                                        {{ $app->jobVacancy->title }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-500">
                                        {{ $app->submitted_at ? $app->submitted_at->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-block px-2.5 py-1 text-[11px] font-semibold rounded-full border {{ $app->status->badgeClass() }}">
                                            {{ $app->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('admin.applications.show', $app) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-sky-50 text-[#0179B5] hover:bg-sky-100 font-semibold rounded text-[11px] transition-colors">
                                            <span>Verifikasi</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        Belum ada lamaran yang disubmit.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kolom Kanan: Ringkasan Lowongan SDM -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                        <h2 class="text-base font-bold text-slate-900">Formasi Lowongan</h2>
                        <a href="{{ route('admin.vacancies.index') }}" class="text-xs font-semibold text-[#0179B5] hover:underline">Kelola &rarr;</a>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($vacanciesWithStats as $vac)
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg hover:border-[#0179B5] transition-colors">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-900">{{ $vac->title }}</h3>
                                        <span class="text-[11px] text-slate-500">{{ $vac->department }}</span>
                                    </div>
                                    @if($vac->is_published)
                                        <span class="px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 rounded-full">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-semibold bg-slate-200 text-slate-700 rounded-full">Tutup</span>
                                    @endif
                                </div>
                                <div class="mt-2 pt-2 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-600">
                                    <span>Kuota: <strong>{{ $vac->quota }}</strong></span>
                                    <span>Pelamar: <strong>{{ $vac->total_applicants }}</strong> ({{ $vac->pending_count }} review)</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">Belum ada formasi lowongan dibuat.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-200">
                    <a href="{{ route('admin.timelines.index') }}" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-lg text-center block transition-colors">
                        Atur Timeline & Jadwal Seleksi
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
