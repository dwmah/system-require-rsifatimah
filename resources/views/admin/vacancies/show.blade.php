<x-layouts.admin title="{{ $vacancy->title }}">
    <div class="space-y-6">
        <!-- Breadcrumb & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('admin.vacancies.index') }}" class="hover:text-[#0179B5]">Lowongan SDM</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">{{ $vacancy->title }}</span>
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.vacancies.toggle-publish', $vacancy) }}">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 {{ $vacancy->is_published ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }} border text-sm font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        {{ $vacancy->is_published ? 'Tutup Pendaftaran' : 'Buka Pendaftaran' }}
                    </button>
                </form>

                <a href="{{ route('admin.vacancies.edit', $vacancy) }}" class="px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                    Edit Lowongan
                </a>
            </div>
        </div>

        <!-- Header Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-200">
                <div>
                    <span class="text-sm font-bold text-slate-400 font-mono">{{ $vacancy->code }}</span>
                    <h1 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $vacancy->title }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-slate-600">
                        <span>Unit: <strong class="text-slate-800">{{ $vacancy->department }}</strong></span>
                        <span>&bull;</span>
                        <span>Formasi: <strong class="text-slate-800">{{ $vacancy->formation_type }}</strong></span>
                        <span>&bull;</span>
                        <span>Periode: <strong class="text-slate-800">{{ $vacancy->recruitmentPeriod->name ?? '-' }}</strong></span>
                        <span>&bull;</span>
                        <span>Pendaftaran: <strong class="text-slate-800">{{ $vacancy->open_at?->translatedFormat('d M Y') ?? '-' }} &mdash; {{ $vacancy->close_at?->translatedFormat('d M Y') ?? '-' }}</strong></span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center min-w-[100px]">
                        <span class="text-xs uppercase font-bold text-slate-400">Kuota</span>
                        <div class="text-2xl font-extrabold text-slate-900">{{ $vacancy->quota }}</div>
                    </div>
                    <div class="p-3 bg-sky-50 border border-sky-100 rounded-xl text-center min-w-[100px]">
                        <span class="text-xs uppercase font-bold text-sky-700">Pelamar</span>
                        <div class="text-2xl font-extrabold text-[#0179B5]">{{ $vacancy->jobApplications->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mt-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">Deskripsi Pekerjaan</h2>
                <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $vacancy->description }}
                </div>
            </div>
        </div>

        <!-- Two Column Grid: Kualifikasi, Dokumen, & Tahapan -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kualifikasi -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#0179B5]">Kualifikasi Khusus</h2>
                <ul class="space-y-2.5 text-sm text-slate-700">
                    @forelse ($vacancy->qualifications as $qual)
                        <li class="flex items-start gap-2">
                            <span class="text-[#0179B5] font-bold">&check;</span>
                            <span>{{ $qual->description }}</span>
                        </li>
                    @empty
                        <li class="text-slate-400">Belum ada kualifikasi khusus diatur.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Persyaratan Dokumen -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#0179B5]">Persyaratan Berkas ({{ $vacancy->documentRequirements->count() }})</h2>
                <ul class="space-y-2.5 text-sm text-slate-700">
                    @forelse ($vacancy->documentRequirements as $docReq)
                        <li class="p-3 bg-slate-50 rounded-lg border border-slate-100 space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-semibold text-slate-900 text-sm">{{ $docReq->name }}</span>
                                @if($docReq->is_required)
                                    <span class="px-2 py-0.5 text-xs font-bold bg-rose-100 text-rose-800 rounded">Wajib</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs text-slate-500 bg-slate-200 rounded">Opsional</span>
                                @endif
                            </div>
                            @if ($docReq->description)
                                <p class="text-xs text-slate-500">{{ $docReq->description }}</p>
                            @endif
                            <div class="text-xs text-slate-400 font-mono">
                                Maks {{ round(($docReq->max_size_kb ?: 5120) / 1024, 1) }} MB
                            </div>
                        </li>
                    @empty
                        <li class="text-slate-400">Belum ada persyaratan berkas diatur.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Tahapan Seleksi -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#0179B5]">Tahapan Seleksi ({{ $vacancy->selectionStages->count() }})</h2>
                <div class="space-y-2.5 text-sm">
                    @forelse ($vacancy->selectionStages as $stg)
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#0179B5] text-white text-xs font-bold flex items-center justify-center shrink-0">
                                    {{ $loop->iteration }}
                                </span>
                                <span class="font-bold text-slate-900 text-sm">{{ $stg->name }}</span>
                            </div>
                            @if ($stg->description)
                                <p class="text-xs text-slate-500 pl-8">{{ $stg->description }}</p>
                            @endif
                            @if ($stg->start_at || $stg->end_at)
                                <div class="pl-8 text-xs text-[#0179B5] font-semibold flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span>
                                        @if ($stg->start_at && $stg->end_at)
                                            {{ $stg->start_at->translatedFormat('d M Y') }} &mdash; {{ $stg->end_at->translatedFormat('d M Y') }}
                                        @elseif ($stg->end_at)
                                            Batas Akhir: {{ $stg->end_at->translatedFormat('d M Y') }}
                                        @elseif ($stg->start_at)
                                            Mulai: {{ $stg->start_at->translatedFormat('d M Y') }}
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-slate-400">Belum ada tahapan seleksi.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Daftar Pelamar Pada Lowongan Ini -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Pelamar Formasi Ini</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Total {{ $vacancy->jobApplications->count() }} pelamar terdaftar</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-xs">
                        <tr>
                            <th class="py-3 px-4">Nama Pelamar</th>
                            <th class="py-3 px-4">No. Registrasi</th>
                            <th class="py-3 px-4">Tanggal Submit</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($vacancy->jobApplications as $app)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $app->user->name }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $app->user->email }} &bull; {{ $app->user->phone ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-600">
                                    {{ $app->user->registration_number ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $app->submitted_at ? $app->submitted_at->format('d M Y, H:i') : '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full border {{ $app->status->badgeClass() }}">
                                        {{ $app->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('admin.applications.show', $app) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                                        <span>Periksa Berkas</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    Belum ada pelamar yang mendaftar pada formasi lowongan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
