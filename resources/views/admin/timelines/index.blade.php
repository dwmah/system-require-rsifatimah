<x-layouts.admin title="Manajemen Timeline & Kalender Seleksi">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Timeline & Jadwal Seleksi</h1>
                <p class="text-sm text-slate-500 mt-0.5">Pengaturan kontrol waktu otomatis (Date-Driven Lock) pendaftaran, upload berkas, dan pengumuman</p>
            </div>
        </div>

        <!-- Info Card: Date-Driven Lock Mechanism -->
        <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 text-xs text-sky-900 flex items-start gap-3">
            <svg class="w-5 h-5 text-[#0179B5] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <span class="font-bold text-sm block mb-1">Mekanisme Kendali Otomatis Berbasis Waktu (Date-Driven Lock)</span>
                <ul class="list-disc list-inside space-y-0.5 text-sky-800">
                    <li><strong>Sebelum Tanggal Mulai:</strong> Pendaftaran dan formulir registrasi terkunci (disabled).</li>
                    <li><strong>Saat Periode Aktif:</strong> Registrasi akun, pemilihan formasi, dan unggah berkas aktif.</li>
                    <li><strong>Setelah Batas Akhir Submit:</strong> Formulir pendaftaran otomatis terkunci (disabled), tombol submit dinonaktifkan.</li>
                </ul>
            </div>
        </div>

        <!-- Periods List -->
        <div class="space-y-6">
            @forelse ($periods as $period)
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
                        <div class="flex items-center gap-3">
                            <h2 class="text-base font-bold text-slate-900">{{ $period->name }}</h2>
                            @if($period->is_active)
                                <span class="px-2.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200">
                                    Periode Aktif
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-600 rounded-full">
                                    Nonaktif / Selesai
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Update Timeline Form -->
                    <form method="POST" action="{{ route('admin.timelines.update', $period) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Periode</label>
                                <input type="text" name="name" value="{{ old('name', $period->name) }}" required
                                       class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Buka Pendaftaran</label>
                                <input type="date" name="start_date" value="{{ old('start_date', $period->start_date ? \Carbon\Carbon::parse($period->start_date)->format('Y-m-d') : '') }}" required
                                       class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Akhir Submit Berkas</label>
                                <input type="date" name="submission_deadline" value="{{ old('submission_deadline', $period->submission_deadline ? \Carbon\Carbon::parse($period->submission_deadline)->format('Y-m-d') : '') }}" required
                                       class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Verifikasi Berkas SDI</label>
                                <input type="date" name="verification_end_date" value="{{ old('verification_end_date', $period->verification_end_date ? \Carbon\Carbon::parse($period->verification_end_date)->format('Y-m-d') : '') }}" required
                                       class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Pengumuman Kelulusan Akhir</label>
                                <input type="date" name="announcement_date" value="{{ old('announcement_date', $period->announcement_date ? \Carbon\Carbon::parse($period->announcement_date)->format('Y-m-d') : '') }}" required
                                       class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Berakhir Seluruh Seleksi</label>
                                <input type="date" name="end_date" value="{{ old('end_date', $period->end_date ? \Carbon\Carbon::parse($period->end_date)->format('Y-m-d') : '') }}" required
                                       class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="active_{{ $period->id }}" value="1" {{ $period->is_active ? 'checked' : '' }}
                                       class="w-4 h-4 text-[#0179B5] rounded border-slate-300 focus:ring-[#0179B5]">
                                <label for="active_{{ $period->id }}" class="text-xs font-semibold text-slate-800">Set sebagai Periode Rekrutmen Aktif</label>
                            </div>

                            <button type="submit" class="px-5 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                                Simpan Jadwal Periode
                            </button>
                        </div>
                    </form>

                    <!-- Vacancy Stage Timelines Overview -->
                    @if ($period->jobVacancies->isNotEmpty())
                        <div class="pt-4 border-t border-slate-100 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Tahapan & Batas Waktu Seleksi Per Formasi Lowongan ({{ $period->jobVacancies->count() }})
                                </h3>
                                <span class="text-[11px] text-slate-400">Klik "Edit Batas Waktu" untuk menyesuaikan tanggal per tahapan</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach ($period->jobVacancies as $vac)
                                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <div class="font-bold text-xs text-slate-900">{{ $vac->title }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $vac->unit }} &bull; {{ $vac->employment_type }}</div>
                                            </div>
                                            <a href="{{ route('admin.vacancies.edit', $vac) }}" class="px-2.5 py-1 text-[11px] font-semibold bg-white border border-slate-300 rounded-lg hover:bg-slate-100 text-[#0179B5] shadow-2xs shrink-0">
                                                Edit Batas Waktu
                                            </a>
                                        </div>

                                        <div class="space-y-1 pt-1.5 border-t border-slate-200/60 text-[11px]">
                                            @forelse ($vac->selectionStages as $stg)
                                                <div class="flex items-center justify-between text-slate-600 py-0.5">
                                                    <span class="truncate pr-2">{{ $loop->iteration }}. {{ $stg->name }}</span>
                                                    <span class="font-mono text-[10px] shrink-0 {{ $stg->end_at && $stg->end_at->isPast() ? 'text-slate-400' : 'text-[#0179B5] font-semibold' }}">
                                                        @if ($stg->end_at)
                                                            Batas: {{ $stg->end_at->format('d M Y') }}
                                                        @else
                                                            <span class="text-slate-400 italic">Belum diatur</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @empty
                                                <div class="text-slate-400 italic text-[11px]">Belum ada tahapan diatur.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white p-12 text-center text-slate-400 rounded-xl border border-slate-200">
                    Belum ada periode rekrutmen dibuat.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
