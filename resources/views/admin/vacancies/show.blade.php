<x-layouts.admin title="{{ $vacancy->title }}">
    <div class="space-y-6">
        <!-- Breadcrumb & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('admin.vacancies.index') }}" class="hover:text-[#0179B5]">Lowongan SDM</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">{{ $vacancy->title }}</span>
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.vacancies.toggle-publish', $vacancy) }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 {{ $vacancy->is_published ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }} border text-xs font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        {{ $vacancy->is_published ? 'Tutup Pendaftaran' : 'Buka Pendaftaran' }}
                    </button>
                </form>

                <a href="{{ route('admin.vacancies.edit', $vacancy) }}" class="px-3 py-1.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                    Edit Lowongan
                </a>
            </div>
        </div>

        <!-- Header Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-200">
                <div>
                    <span class="text-xs font-bold text-slate-400 font-mono">{{ $vacancy->code }}</span>
                    <h1 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $vacancy->title }}</h1>
                    <div class="flex items-center gap-3 mt-1.5 text-xs text-slate-600">
                        <span>Unit: <strong>{{ $vacancy->department }}</strong></span>
                        <span>&bull;</span>
                        <span>Formasi: <strong>{{ $vacancy->formation_type }}</strong></span>
                        <span>&bull;</span>
                        <span>Periode: <strong>{{ $vacancy->recruitmentPeriod->name ?? '-' }}</strong></span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center min-w-[90px]">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Kuota</span>
                        <div class="text-xl font-extrabold text-slate-900">{{ $vacancy->quota }}</div>
                    </div>
                    <div class="p-3 bg-sky-50 border border-sky-100 rounded-xl text-center min-w-[90px]">
                        <span class="text-[10px] uppercase font-bold text-sky-700">Pelamar</span>
                        <div class="text-xl font-extrabold text-[#0179B5]">{{ $vacancy->jobApplications->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mt-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Deskripsi Pekerjaan</h2>
                <div class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $vacancy->description }}
                </div>
            </div>
        </div>

        <!-- Two Column Grid: Kualifikasi, Dokumen, & Tahapan -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kualifikasi -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Kualifikasi Khusus</h2>
                <ul class="space-y-2 text-xs text-slate-700">
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
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Persyaratan Berkas</h2>
                <ul class="space-y-2 text-xs text-slate-700">
                    @forelse ($vacancy->documentRequirements as $docReq)
                        <li class="flex items-center justify-between py-1 border-b border-slate-100">
                            <span>{{ $docReq->document_name }}</span>
                            @if($docReq->is_mandatory)
                                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-800 rounded">Wajib</span>
                            @else
                                <span class="px-1.5 py-0.5 text-[10px] text-slate-500 bg-slate-100 rounded">Opsional</span>
                            @endif
                        </li>
                    @empty
                        <li class="text-slate-400">Belum ada persyaratan berkas diatur.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Tahapan Seleksi -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Tahapan Seleksi</h2>
                <div class="space-y-2 text-xs">
                    @forelse ($vacancy->selectionStages as $stg)
                        <div class="flex items-center gap-2.5 p-2 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="w-5 h-5 rounded-full bg-[#0179B5] text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $stg->sequence_order }}
                            </span>
                            <span class="font-medium text-slate-800">{{ $stg->stage_name }}</span>
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
                    <p class="text-xs text-slate-500 mt-0.5">Total {{ $vacancy->jobApplications->count() }} pelamar terdaftar</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Nama Pelamar</th>
                            <th class="py-3 px-4">No. Registrasi</th>
                            <th class="py-3 px-4">Tanggal Submit</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($vacancy->jobApplications as $app)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $app->user->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $app->user->email }} &bull; {{ $app->user->phone ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-600">
                                    {{ $app->user->registration_number ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $app->submitted_at ? $app->submitted_at->format('d M Y, H:i') : '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block px-2.5 py-1 text-[11px] font-semibold rounded-full border {{ $app->status->badgeClass() }}">
                                        {{ $app->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('admin.applications.show', $app) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
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
