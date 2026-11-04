<x-layouts.admin title="Manajemen Timeline & Kalender Seleksi">
    <div class="space-y-8" x-data="{
        showCreateTemplateModal: false,
        editingTemplate: null,
        newTemplate: {
            name: '',
            is_default: false,
            stages: [
                { sequence_order: 1, name: 'Pendaftaran Peserta Rekrutmen', description: 'Pendaftaran online akun peserta dan verifikasi kelayakan biodata data diri pelamar', start_date: '', end_date: '', announcement_date: '' },
                { sequence_order: 2, name: 'Seleksi Administrasi', description: 'Pemeriksaan dan verifikasi keabsahan seluruh berkas dokumen persyaratan (CV, Portofolio, Ijazah, dll) serta pengumuman hasil seleksi berkas', start_date: '', end_date: '', announcement_date: '' },
                { sequence_order: 3, name: 'Seleksi Tes Tertulis, Keterampilan, dan Wawancara', description: 'Ujian teori bidang, tes skill teknis, wawancara kompetensi dan etika islami', start_date: '', end_date: '', announcement_date: '' },
                { sequence_order: 4, name: 'Seleksi Psikotes dan Pemeriksaan Kesehatan (MCU)', description: 'Pemeriksaan psikotes lanjutan dan Medical Check-Up (MCU) kesehatan fisik', start_date: '', end_date: '', announcement_date: '' },
                { sequence_order: 5, name: 'Masa Orientasi & Pembekalan Karyawan Baru', description: 'Pembekalan nilai Al-Islam, orientasi SOP Rumah Sakit, dan magang', start_date: '', end_date: '', announcement_date: '' }
            ]
        },
        addStage(target) {
            target.stages.push({
                sequence_order: target.stages.length + 1,
                name: '',
                description: '',
                start_date: '',
                end_date: '',
                announcement_date: ''
            });
        },
        removeStage(target, index) {
            target.stages.splice(index, 1);
            target.stages.forEach((s, idx) => s.sequence_order = idx + 1);
        }
    }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Timeline & Jadwal Seleksi</h1>
                <p class="text-sm text-slate-500 mt-0.5">Pengaturan switch buka/tutup periode rekrutmen dan standarisasi template tahapan seleksi</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="showCreateTemplateModal = true" class="px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-lg shadow-sm transition-colors inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Buat Template Timeline</span>
                </button>
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
        @endif
        @if ($errors->any())
            <x-ui.alert type="error">
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <!-- Quick Switch Buka/Tutup Periode Cards -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Kendali Status Periode Rekrutmen (Switch Buka / Tutup)</h2>
                    <p class="text-xs text-slate-500">Buka atau tutup periode rekrutmen secara instan dengan switch switch status.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($periods as $p)
                    <div class="p-4 rounded-xl border {{ $p->is_active ? 'border-emerald-300 bg-emerald-50/30 ring-1 ring-emerald-200' : 'border-slate-200 bg-slate-50/50' }} flex flex-col justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-[10px] font-bold text-slate-500 uppercase">{{ $p->code }}</span>
                                @if ($p->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        SEDANG BUKA
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-200 text-slate-700">
                                        DITUTUP
                                    </span>
                                @endif
                            </div>
                            <h3 class="font-bold text-sm text-slate-900">{{ $p->name }}</h3>
                            <p class="text-xs text-slate-500">
                                {{ $p->registration_start_at?->format('d M Y') }} &mdash; {{ $p->registration_end_at?->format('d M Y') }}
                            </p>
                        </div>

                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-700">Status Periode:</span>
                            <form action="{{ route('admin.timelines.toggle-status', $p) }}" method="POST" class="inline flex items-center">
                                @csrf
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" name="status" value="open" {{ $p->is_active ? 'checked' : '' }} onchange="this.form.submit()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                    <span class="ml-2 text-xs font-bold {{ $p->is_active ? 'text-emerald-700' : 'text-slate-500' }}">
                                        {{ $p->is_active ? 'Buka' : 'Tutup' }}
                                    </span>
                                </label>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section Template Tahapan Seleksi (Foto 2) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-200">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Template Timeline Seleksi Reusable</h2>
                    <p class="text-sm text-slate-500">Template tahapan seleksi seragam yang dapat diaplikasikan langsung ke semua formasi lowongan.</p>
                </div>
                <button type="button" @click="showCreateTemplateModal = true" class="px-3.5 py-2 bg-sky-50 text-[#0179B5] hover:bg-sky-100 border border-sky-200 text-xs font-bold rounded-lg transition-colors inline-flex items-center gap-1.5 cursor-pointer self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Template Baru</span>
                </button>
            </div>

            <div class="space-y-4">
                @forelse ($templates as $tpl)
                    <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-bold text-slate-900">{{ $tpl->name }}</h3>
                                @if ($tpl->is_default)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#0179B5] text-white">
                                        Default Template
                                    </span>
                                @endif
                                <span class="text-xs text-slate-400 font-mono">({{ count($tpl->stages_data ?? []) }} Tahapan)</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.timelines.templates.apply-to-vacancies', $tpl) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Terapkan template tahapan seleksi ini ke seluruh lowongan pada periode aktif?')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors shadow-2xs inline-flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        <span>Terapkan ke Semua Lowongan</span>
                                    </button>
                                </form>

                                <button type="button" @click="editingTemplate = JSON.parse(JSON.stringify({{ Js::from($tpl) }}))" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                                    Edit
                                </button>

                                @if (!$tpl->is_default)
                                    <form action="{{ route('admin.timelines.templates.destroy', $tpl) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Hapus template ini?')" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Hapus Template">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Tahapan List (Visual Foto 2) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            @foreach ($tpl->stages_data ?? [] as $stg)
                                <div class="p-3.5 bg-white rounded-xl border border-slate-200 space-y-2 shadow-2xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-sky-100 text-[#0179B5] text-xs font-bold flex items-center justify-center shrink-0">
                                            {{ $stg['sequence_order'] ?? $loop->iteration }}
                                        </span>
                                        <h4 class="font-bold text-xs text-slate-800 line-clamp-2 leading-tight">
                                            {{ $stg['name'] }}
                                        </h4>
                                    </div>

                                    @if (!empty($stg['description']))
                                        <p class="text-[11px] text-slate-500 line-clamp-2">{{ $stg['description'] }}</p>
                                    @endif

                                    <div class="pt-2 border-t border-slate-100 text-[10px] space-y-0.5 text-slate-600 font-medium">
                                        @if (!empty($stg['start_date']) || !empty($stg['end_date']))
                                            <div class="flex items-center justify-between">
                                                <span class="text-slate-400">Jadwal:</span>
                                                <span class="font-mono text-slate-700">{{ $stg['start_date'] ?? '-' }} s/d {{ $stg['end_date'] ?? '-' }}</span>
                                            </div>
                                        @endif
                                        @if (!empty($stg['announcement_date']))
                                            <div class="flex items-center justify-between text-emerald-700 font-semibold">
                                                <span>Pengumuman:</span>
                                                <span class="font-mono">{{ $stg['announcement_date'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-sm">
                        Belum ada template tahapan seleksi dibuat.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Detail Jadwal Periode & Lowongan Aktif -->
        <div class="space-y-6">
            <h2 class="text-lg font-bold text-slate-900 pb-2 border-b border-slate-200">
                Detail Periode & Tahapan Per Lowongan
            </h2>

            @forelse ($periods as $period)
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-bold text-slate-900">{{ $period->name }}</h3>
                            @if($period->is_active)
                                <span class="px-3 py-1 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200">
                                    Periode Aktif
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-medium bg-slate-100 text-slate-600 rounded-full">
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
                                       class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Buka Pendaftaran</label>
                                <input type="date" name="start_date" value="{{ old('start_date', $period->registration_start_at ? $period->registration_start_at->format('Y-m-d') : '') }}" required
                                       class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Akhir Periode Pendaftaran</label>
                                <input type="date" name="registration_end_at" value="{{ old('registration_end_at', old('submission_deadline', $period->registration_end_at ? $period->registration_end_at->format('Y-m-d') : '')) }}" required
                                       class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="active_{{ $period->id }}" value="1" {{ $period->is_active ? 'checked' : '' }}
                                       class="w-4 h-4 text-[#0179B5] rounded border-slate-300 focus:ring-[#0179B5]">
                                <label for="active_{{ $period->id }}" class="text-sm font-semibold text-slate-800">Set sebagai Periode Rekrutmen Aktif</label>
                            </div>

                            <button type="submit" class="px-5 py-2.5 bg-[#0179B5] hover:bg-[#016293] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                                Simpan Jadwal Periode
                            </button>
                        </div>
                    </form>

                    <!-- Vacancy Stage Timelines Overview -->
                    @if ($period->jobVacancies->isNotEmpty())
                        <div class="pt-4 border-t border-slate-100 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Tahapan & Batas Waktu Seleksi Per Formasi Lowongan ({{ $period->jobVacancies->count() }})
                                </h4>
                                <span class="text-xs text-slate-400">Sesuaikan tanggal per tahapan pada lowongan</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach ($period->jobVacancies as $vac)
                                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <div class="font-bold text-sm text-slate-900">{{ $vac->title }}</div>
                                                <div class="text-xs text-slate-500">{{ $vac->unit }} &bull; {{ $vac->employment_type }}</div>
                                            </div>
                                            <a href="{{ route('admin.vacancies.edit', $vac) }}" class="px-3 py-1.5 text-xs font-semibold bg-white border border-slate-300 rounded-lg hover:bg-slate-100 text-[#0179B5] shadow-2xs shrink-0 cursor-pointer">
                                                Edit Tahapan
                                            </a>
                                        </div>

                                        <div class="space-y-1.5 pt-2 border-t border-slate-200/60 text-xs">
                                            @forelse ($vac->selectionStages as $stg)
                                                <div class="flex items-center justify-between text-slate-600 py-0.5">
                                                    <span class="truncate pr-2 font-medium">{{ $loop->iteration }}. {{ $stg->name }}</span>
                                                    <span class="font-mono text-xs shrink-0 {{ $stg->end_at && $stg->end_at->isPast() ? 'text-slate-400' : 'text-[#0179B5] font-semibold' }}">
                                                        @if ($stg->end_at)
                                                            Batas: {{ $stg->end_at->format('d M Y') }}
                                                        @else
                                                            <span class="text-slate-400 italic">Belum diatur</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @empty
                                                <div class="text-slate-400 italic text-xs">Belum ada tahapan diatur.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white p-12 text-center text-slate-400 rounded-xl border border-slate-200 text-sm">
                    Belum ada periode rekrutmen dibuat.
                </div>
            @endforelse
        </div>

        <!-- Modal Buat Template Baru -->
        <div x-show="showCreateTemplateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showCreateTemplateModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden z-10 border border-slate-200">
                <form action="{{ route('admin.timelines.templates.store') }}" method="POST" class="flex flex-col h-full overflow-hidden">
                    @csrf
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between">
                        <h3 class="font-bold text-base text-slate-900">Buat Template Timeline Seleksi Baru</h3>
                        <button type="button" @click="showCreateTemplateModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="p-6 overflow-y-auto space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Template <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="newTemplate.name" required placeholder="Contoh: Template Standar RSI Fatimah (4 Tahap)" class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_default" id="new_is_default" value="1" x-model="newTemplate.is_default" class="w-4 h-4 text-[#0179B5] rounded border-slate-300">
                            <label for="new_is_default" class="text-xs font-semibold text-slate-800">Set sebagai template default utama</label>
                        </div>

                        <div class="pt-2 border-t border-slate-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Daftar Tahapan Template</h4>
                                <button type="button" @click="addStage(newTemplate)" class="text-xs text-[#0179B5] hover:underline font-semibold">+ Tambah Tahapan</button>
                            </div>

                            <template x-for="(stg, idx) in newTemplate.stages" :key="idx">
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                    <input type="hidden" :name="'stages[' + idx + '][sequence_order]'" :value="stg.sequence_order">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 flex-1">
                                            <span class="w-6 h-6 rounded-full bg-[#0179B5] text-white text-xs font-bold flex items-center justify-center shrink-0" x-text="stg.sequence_order"></span>
                                            <input type="text" :name="'stages[' + idx + '][name]'" x-model="stg.name" required placeholder="Nama Tahapan" class="w-full h-9 px-3 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                        </div>
                                        <button type="button" @click="removeStage(newTemplate, idx)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                    <input type="text" :name="'stages[' + idx + '][description]'" x-model="stg.description" placeholder="Deskripsi alur / petunjuk tahapan" class="w-full h-8 px-2 text-xs bg-white border border-slate-200 rounded-lg">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-[10px] text-slate-500">Tanggal Mulai</label>
                                            <input type="date" :name="'stages[' + idx + '][start_date]'" x-model="stg.start_date" class="w-full h-8 px-2 text-xs bg-white border border-slate-200 rounded-lg">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-slate-500">Batas Akhir (Deadline)</label>
                                            <input type="date" :name="'stages[' + idx + '][end_date]'" x-model="stg.end_date" class="w-full h-8 px-2 text-xs bg-white border border-slate-200 rounded-lg">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-slate-500">Pengumuman Kelulusan</label>
                                            <input type="date" :name="'stages[' + idx + '][announcement_date]'" x-model="stg.announcement_date" class="w-full h-8 px-2 text-xs bg-white border border-slate-200 rounded-lg">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end gap-2">
                        <button type="button" @click="showCreateTemplateModal = false" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-lg shadow-sm">Simpan Template</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Template -->
        <div x-show="editingTemplate !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="editingTemplate = null"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden z-10 border border-slate-200" x-if="editingTemplate">
                <form :action="'{{ url('admin/timelines/templates') }}/' + editingTemplate.id" method="POST" class="flex flex-col h-full overflow-hidden">
                    @csrf
                    @method('PUT')
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between">
                        <h3 class="font-bold text-base text-slate-900">Edit Template Timeline Seleksi</h3>
                        <button type="button" @click="editingTemplate = null" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="p-6 overflow-y-auto space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Template <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="editingTemplate.name" required class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_default" id="edit_is_default" value="1" x-model="editingTemplate.is_default" class="w-4 h-4 text-[#0179B5] rounded border-slate-300">
                            <label for="edit_is_default" class="text-xs font-semibold text-slate-800">Set sebagai template default utama</label>
                        </div>

                        <div class="pt-2 border-t border-slate-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Daftar Tahapan Template</h4>
                                <button type="button" @click="addStage(editingTemplate)" class="text-xs text-[#0179B5] hover:underline font-semibold">+ Tambah Tahapan</button>
                            </div>

                            <template x-for="(stg, idx) in (editingTemplate.stages_data || [])" :key="idx">
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                    <input type="hidden" :name="'stages[' + idx + '][sequence_order]'" :value="stg.sequence_order">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 flex-1">
                                            <span class="w-6 h-6 rounded-full bg-[#0179B5] text-white text-xs font-bold flex items-center justify-center shrink-0" x-text="stg.sequence_order"></span>
                                            <input type="text" :name="'stages[' + idx + '][name]'" x-model="stg.name" required placeholder="Nama Tahapan" class="w-full h-9 px-3 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                        </div>
                                        <button type="button" @click="editingTemplate.stages_data.splice(idx, 1); editingTemplate.stages_data.forEach((s, i) => s.sequence_order = i + 1)" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                    <input type="text" :name="'stages[' + idx + '][description]'" x-model="stg.description" placeholder="Deskripsi alur / petunjuk tahapan" class="w-full h-8 px-2 text-xs bg-white border border-slate-200 rounded-lg">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-[10px] text-slate-500">Tanggal Mulai</label>
                                            <input type="date" :name="'stages[' + idx + '][start_date]'" x-model="stg.start_date" class="w-full h-8 px-2 text-xs bg-white border border-slate-200 rounded-lg">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-slate-500">Batas Akhir (Deadline)</label>
                                            <input type="date" :name="'stages[' + idx + '][end_date]'" x-model="stg.end_date" class="w-full h-8 px-2 text-xs bg-white border border-slate-200 rounded-lg">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-slate-500">Pengumuman Kelulusan</label>
                                            <input type="date" :name="'stages[' + idx + '][announcement_date]'" x-model="stg.announcement_date" class="w-full h-8 px-2 text-xs bg-white border border-slate-200 rounded-lg">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end gap-2">
                        <button type="button" @click="editingTemplate = null" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-lg shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
