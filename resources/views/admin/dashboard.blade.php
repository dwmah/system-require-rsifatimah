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
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pelamar</span>
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
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Menunggu Verifikasi</span>
                    <div class="text-2xl font-extrabold text-amber-700 mt-1">{{ $pendingVerification }}</div>
                    <a href="{{ route('admin.applications.index', ['status' => 'submitted']) }}" class="text-xs text-amber-600 hover:underline mt-0.5 inline-block font-semibold">Periksa Berkas &rarr;</a>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Lolos Administrasi -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Lolos Administrasi</span>
                    <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $passedAdministration }}</div>
                    <span class="text-xs text-emerald-600 mt-0.5 inline-block font-semibold">Memenuhi Syarat</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Tidak Lolos -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Tidak Lolos / Ditolak</span>
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
                <span class="text-xs font-medium text-slate-500">Lowongan Aktif</span>
                <div class="text-xl font-bold text-slate-800 mt-1">{{ $activeVacancies }} / {{ $totalVacancies }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <span class="text-xs font-medium text-slate-500">Dalam Tahap Seleksi</span>
                <div class="text-xl font-bold text-sky-700 mt-1">{{ $inSelection }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <span class="text-xs font-medium text-slate-500">Diterima / Lolos Akhir</span>
                <div class="text-xl font-bold text-teal-700 mt-1">{{ $acceptedCount }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <span class="text-xs font-medium text-slate-500">Draft Pelamar</span>
                <div class="text-xl font-bold text-slate-600 mt-1">{{ $draftCount }}</div>
            </div>
        </div>

        <!-- Main Layout Split: Lowongan & Lamaran Masuk Terbaru -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Lamaran Masuk Terbaru (2 Spans) -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-lg font-bold text-slate-900">Lamaran Terbaru Masuk</h2>
                            @if($selectedVacancy)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#E6F2F8] text-[#0179B5] border border-sky-200 shadow-2xs">
                                    <span>Formasi: {{ $selectedVacancy->title }}</span>
                                    <a href="{{ route('admin.dashboard') }}" title="Hapus Filter" class="hover:text-rose-600 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </a>
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 mt-0.5">
                            @if($selectedVacancy)
                                Menampilkan berkas masuk khusus formasi <strong class="text-slate-700 font-semibold">{{ $selectedVacancy->title }}</strong>
                            @else
                                Daftar berkas pendaftaran yang siap diperiksa oleh tim SDI
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($selectedVacancy)
                            <a href="{{ route('admin.dashboard') }}" class="text-xs font-medium text-slate-600 hover:text-rose-600 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-rose-300 hover:bg-rose-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <span>Batal Filter</span>
                            </a>
                        @endif
                        <a href="{{ route('admin.applications.index', $selectedVacancyId ? ['vacancy_id' => $selectedVacancyId] : []) }}" class="text-sm font-semibold text-[#0179B5] hover:underline whitespace-nowrap">
                            Lihat Semua &rarr;
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
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
                                        <div class="text-xs text-slate-500">{{ $app->user->email }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-medium text-slate-700">
                                        {{ $app->jobVacancy->title }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 text-xs">
                                        {{ $app->submitted_at ? $app->submitted_at->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full border {{ $app->status->badgeClass() }}">
                                            {{ $app->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('admin.applications.show', $app) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-sky-50 text-[#0179B5] hover:bg-sky-100 font-semibold rounded-lg text-xs transition-colors">
                                            <span>Verifikasi</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-slate-400 text-sm">
                                        @if($selectedVacancy)
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                </svg>
                                                <span>Belum ada lamaran masuk untuk formasi <strong>{{ $selectedVacancy->title }}</strong>.</span>
                                                <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-[#0179B5] hover:underline mt-1">
                                                    Tampilkan semua lamaran &rarr;
                                                </a>
                                            </div>
                                        @else
                                            Belum ada lamaran yang disubmit.
                                        @endif
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
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Formasi Lowongan</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Klik kartu formasi untuk menyaring lamaran</p>
                        </div>
                        <a href="{{ route('admin.vacancies.index') }}" class="text-sm font-semibold text-[#0179B5] hover:underline">Kelola &rarr;</a>
                    </div>

                    <div class="mt-4 space-y-2.5">
                        @forelse ($vacanciesWithStats as $vac)
                            @php
                                $isSelected = (string)$selectedVacancyId === (string)$vac->id;
                            @endphp
                            <a href="{{ $isSelected ? route('admin.dashboard') : route('admin.dashboard', ['vacancy_id' => $vac->id]) }}"
                               title="{{ $isSelected ? 'Klik untuk membatalkan filter' : 'Klik untuk menyaring lamaran ' . $vac->title }}"
                               class="group block p-3.5 rounded-xl border transition-all text-left relative {{ $isSelected ? 'bg-sky-50/90 border-[#0179B5] ring-2 ring-[#0179B5]/30 shadow-xs' : 'bg-slate-50/80 border-slate-200 hover:border-[#0179B5]/60 hover:bg-slate-100/60 hover:shadow-2xs' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h3 class="text-sm font-bold truncate transition-colors {{ $isSelected ? 'text-[#0179B5]' : 'text-slate-900 group-hover:text-[#0179B5]' }}">
                                                {{ $vac->title }}
                                            </h3>
                                            @if($isSelected)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#0179B5] text-white">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                    <span>Terfilter</span>
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-slate-500 block truncate mt-0.5">{{ $vac->department }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        @if($vac->is_published)
                                            <span class="px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs font-semibold bg-slate-200 text-slate-700 rounded-full">Tutup</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2.5 pt-2 border-t {{ $isSelected ? 'border-sky-200' : 'border-slate-200' }} flex items-center justify-between text-xs text-slate-600">
                                    <span>Kuota: <strong>{{ $vac->hasQuotaLimit() ? $vac->quota : 'Unlimited' }}</strong></span>
                                    <span>Pelamar: <strong>{{ $vac->total_applicants }}</strong> ({{ $vac->pending_count }} review)</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-slate-400 text-center py-4">Belum ada formasi lowongan dibuat.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-200">
                    <a href="{{ route('admin.timelines.index') }}" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-semibold rounded-lg text-center block transition-colors">
                        Atur Timeline & Jadwal Seleksi
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
