<x-layouts.admin title="Daftar Lamaran & Verifikasi Berkas">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Lamaran & Verifikasi Berkas</h1>
                    @if(isset($activePeriod) && $activePeriod)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Periode Aktif: {{ $activePeriod->name }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 mt-0.5">Monitoring berkas pendaftaran dan validasi kualifikasi calon karyawan RSI Fatimah</p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                @if($metrics['total'] > 0)
                    <a href="{{ route('admin.applications.export-biodata', request()->only(['search', 'vacancy_id', 'status'])) }}" 
                       class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow-md transition-all flex items-center gap-2 cursor-pointer group"
                       title="Unduh seluruh biodata pelamar yang terdaftar dalam format Excel (.xlsx)">
                        <svg class="w-4 h-4 text-emerald-100 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Unduh Biodata Pelamar (.xlsx)</span>
                    </a>
                @else
                    <button type="button" disabled class="px-4 py-2.5 bg-slate-100 text-slate-400 border border-slate-200 text-xs font-semibold rounded-xl flex items-center gap-2 cursor-not-allowed" title="Belum ada data pelamar untuk diunduh">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Unduh Biodata Pelamar (.xlsx)</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
            <a href="{{ route('admin.applications.index') }}" class="p-3.5 bg-white border border-slate-200 rounded-xl hover:border-[#0179B5] transition-colors {{ !request()->filled('status') ? 'ring-2 ring-[#0179B5]' : '' }}">
                <span class="text-xs uppercase font-bold text-slate-500">Total Berkas</span>
                <div class="text-xl font-extrabold text-slate-900 mt-1">{{ $metrics['total'] }}</div>
            </a>

            <a href="{{ route('admin.applications.index', ['status' => 'submitted']) }}" class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl hover:opacity-90 transition-opacity {{ request('status') === 'submitted' ? 'ring-2 ring-amber-600' : '' }}">
                <span class="text-xs uppercase font-bold text-amber-700">Perlu Verifikasi</span>
                <div class="text-xl font-extrabold text-amber-800 mt-1">{{ $metrics['pending'] }}</div>
            </a>

            <a href="{{ route('admin.applications.index', ['status' => 'passed_administration']) }}" class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl hover:opacity-90 transition-opacity {{ request('status') === 'passed_administration' ? 'ring-2 ring-emerald-600' : '' }}">
                <span class="text-xs uppercase font-bold text-emerald-700">Lolos Administrasi</span>
                <div class="text-xl font-extrabold text-emerald-800 mt-1">{{ $metrics['passed'] }}</div>
            </a>

            <a href="{{ route('admin.applications.index', ['status' => 'in_selection']) }}" class="p-3.5 bg-sky-50 border border-sky-200 rounded-xl hover:opacity-90 transition-opacity {{ request('status') === 'in_selection' ? 'ring-2 ring-[#0179B5]' : '' }}">
                <span class="text-xs uppercase font-bold text-sky-700">Tahap Tes/MCU</span>
                <div class="text-xl font-extrabold text-sky-800 mt-1">{{ $metrics['in_selection'] }}</div>
            </a>

            <a href="{{ route('admin.applications.index', ['status' => 'accepted']) }}" class="p-3.5 bg-teal-50 border border-teal-200 rounded-xl hover:opacity-90 transition-opacity {{ request('status') === 'accepted' ? 'ring-2 ring-teal-700' : '' }}">
                <span class="text-xs uppercase font-bold text-teal-800">Diterima</span>
                <div class="text-xl font-extrabold text-teal-900 mt-1">{{ $metrics['accepted'] }}</div>
            </a>

            <a href="{{ route('admin.applications.index', ['status' => 'rejected']) }}" class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl hover:opacity-90 transition-opacity {{ request('status') === 'rejected' ? 'ring-2 ring-rose-600' : '' }}">
                <span class="text-xs uppercase font-bold text-rose-700">Tidak Lolos</span>
                <div class="text-xl font-extrabold text-rose-800 mt-1">{{ $metrics['rejected'] }}</div>
            </a>
        </div>

        <!-- Filter Form (Auto-Filter on Change) -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('admin.applications.index') }}" class="flex flex-col sm:flex-row gap-3 items-center">
                <div class="flex-1 w-full relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama, email, No. Reg... (Tekan Enter)"
                           class="w-full h-11 pl-10 pr-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white transition-colors">
                </div>

                <div class="w-full sm:w-64">
                    <select name="vacancy_id" onchange="this.form.submit()" class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] cursor-pointer">
                        <option value="">Semua Posisi Formasi</option>
                        @foreach ($vacancies as $vac)
                            <option value="{{ $vac->id }}" {{ request('vacancy_id') == $vac->id ? 'selected' : '' }}>
                                {{ $vac->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-56">
                    <select name="status" onchange="this.form.submit()" class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="passed_administration" {{ request('status') === 'passed_administration' ? 'selected' : '' }}>Lolos Administrasi</option>
                        <option value="in_selection" {{ request('status') === 'in_selection' ? 'selected' : '' }}>Tahap Seleksi</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Diterima</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Tidak Lolos</option>
                    </select>
                </div>

                @if(request()->hasAny(['search', 'vacancy_id', 'status']))
                    <a href="{{ route('admin.applications.index') }}" title="Reset Semua Filter" class="h-11 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg flex items-center justify-center gap-1.5 transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Reset</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Master Applications Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4">Pelamar</th>
                            <th class="py-3.5 px-4">Formasi Lowongan</th>
                            <th class="py-3.5 px-4">No. Pendaftaran</th>
                            <th class="py-3.5 px-4">Tanggal Submit</th>
                            <th class="py-3.5 px-4">Kelengkapan Berkas</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($applications as $app)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 text-[#0179B5] font-bold text-xs flex items-center justify-center border border-slate-200 flex-shrink-0">
                                            {{ strtoupper(substr($app->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm">{{ $app->user->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $app->user->email }} &bull; {{ $app->user->phone ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800 text-sm">{{ $app->jobVacancy->title }}</div>
                                    <span class="text-xs text-slate-500">{{ $app->jobVacancy->department }}</span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-700 text-xs">
                                    {{ $app->application_number ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 text-xs">
                                    {{ $app->submitted_at ? $app->submitted_at->format('d M Y, H:i') : 'Belum Submit' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $uploadedCount = $app->documents->count();
                                        $requiredCount = $app->jobVacancy->documentRequirements->where('is_mandatory', true)->count();
                                    @endphp
                                    <span class="inline-flex items-center gap-1 font-semibold text-xs {{ $uploadedCount >= $requiredCount ? 'text-emerald-700' : 'text-amber-700' }}">
                                        <span>{{ $uploadedCount }} Berkas Terunggah</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full border {{ $app->status->badgeClass() }}">
                                        {{ $app->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('admin.applications.show', $app) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                                        <span>Verifikasi Berkas</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                                    Tidak ada data lamaran pelamar yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
