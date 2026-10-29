<x-layouts.admin title="Edit Lowongan: {{ $vacancy->title }}">
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.vacancies.index') }}" class="hover:text-[#0179B5]">Lowongan SDM</a>
            <span>/</span>
            <a href="{{ route('admin.vacancies.show', $vacancy) }}" class="hover:text-[#0179B5]">{{ $vacancy->title }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Edit Lowongan & Opsi Berkas</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="pb-4 border-b border-slate-200 mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Edit Lowongan SDM</h1>
                    <p class="text-sm text-slate-500 mt-1">Kode: <span class="font-mono font-semibold">{{ $vacancy->code }}</span> &bull; Periode: {{ $vacancy->recruitmentPeriod->name ?? '-' }}</p>
                </div>
            </div>

            @php
                $qualData = $vacancy->qualifications->map(fn($q) => $q->description)->values();
                if ($qualData->isEmpty()) {
                    $qualData = collect(['Pendidikan minimal D3/S1 sesuai bidang keahlian', 'Sehat jasmani dan rohani']);
                }

                $docData = $vacancy->documentRequirements->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'desc' => $d->description ?? '',
                    'mandatory' => $d->is_required ? 1 : 0,
                    'mimes' => $d->allowed_mimes ?? 'application/pdf',
                    'max_size' => $d->max_size_kb ?? 5120,
                ])->values();
                if ($docData->isEmpty()) {
                    $docData = collect([
                        ['id' => null, 'name' => 'Surat Lamaran Kerja', 'desc' => 'Ditujukan ke Direktur RSI Fatimah', 'mandatory' => 1, 'mimes' => 'application/pdf', 'max_size' => 5120],
                        ['id' => null, 'name' => 'Ijazah Terakhir Legalisir', 'desc' => 'Scan ijazah asli legalisir', 'mandatory' => 1, 'mimes' => 'application/pdf', 'max_size' => 5120],
                    ]);
                }

                $stageData = $vacancy->selectionStages->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'desc' => $s->description ?? '',
                    'start_at' => $s->start_at ? $s->start_at->format('Y-m-d') : '',
                    'end_at' => $s->end_at ? $s->end_at->format('Y-m-d') : '',
                ])->values();
                if ($stageData->isEmpty()) {
                    $stageData = collect([
                        ['id' => null, 'name' => 'Pendaftaran Peserta Rekrutmen', 'desc' => 'Pendaftaran akun peserta rekrutmen dan verifikasi kelayakan biodata data diri pelamar', 'start_at' => now()->format('Y-m-d'), 'end_at' => now()->addDays(5)->format('Y-m-d')],
                        ['id' => null, 'name' => 'Seleksi Administrasi', 'desc' => 'Pemeriksaan dan verifikasi keabsahan seluruh berkas dokumen persyaratan (CV, Portofolio, Ijazah, dll) serta pengumuman hasil seleksi berkas', 'start_at' => now()->addDays(6)->format('Y-m-d'), 'end_at' => now()->addDays(10)->format('Y-m-d')],
                        ['id' => null, 'name' => 'Seleksi test tulis, test skill, Test Wawancara', 'desc' => 'Tes kemampuan teknis, skill tindakan medis/teknis, wawancara direksi dan pengumuman lolos tahap 3', 'start_at' => now()->addDays(11)->format('Y-m-d'), 'end_at' => now()->addDays(16)->format('Y-m-d')],
                        ['id' => null, 'name' => 'Seleksi test psikologi dan test kesehatan', 'desc' => 'Psikotes dan tes kesehatan/MCU fisik di RS serta pengumuman lolos tahap 4', 'start_at' => now()->addDays(17)->format('Y-m-d'), 'end_at' => now()->addDays(22)->format('Y-m-d')],
                        ['id' => null, 'name' => 'Masa Orientasi Magang Dokter Umum Baru (atau Karyawan Baru)', 'desc' => 'Pembekalan nilai Al-Islam, orientasi mutu dan keselamatan pasien serta penugasan orientasi kerja', 'start_at' => now()->addDays(23)->format('Y-m-d'), 'end_at' => now()->addDays(30)->format('Y-m-d')],
                    ]);
                }
            @endphp

            <form method="POST" action="{{ route('admin.vacancies.update', $vacancy) }}" class="space-y-8"
                  x-data="{
                      // Section 2: Kualifikasi
                      qualifications: {{ Js::from($qualData) }},
                      addQual() { this.qualifications.push(''); },
                      removeQual(index) { this.qualifications.splice(index, 1); },

                      // Section 3: Persyaratan Dokumen
                      documents: {{ Js::from($docData) }},
                      addDoc() {
                          this.documents.push({ id: null, name: '', desc: '', mandatory: 1, mimes: 'application/pdf', max_size: 5120 });
                      },
                      removeDoc(index) {
                          this.documents.splice(index, 1);
                      },
                      applyDocPreset(type) {
                          if (type === 'medis') {
                              this.documents = [
                                  { id: null, name: 'Surat Lamaran Kerja', desc: 'Ditujukan kepada Direktur RSI Fatimah', mandatory: 1, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'Curriculum Vitae (CV)', desc: 'Riwayat hidup lengkap dan nomor kontak aktif', mandatory: 1, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'Portofolio Klinis / Pengalaman', desc: 'Portofolio praktik klinis / karya', mandatory: 1, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'Ijazah Profesi / Terakhir', desc: 'Scan ijazah asli legalisir', mandatory: 1, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'Transkrip Nilai Akademik', desc: 'Scan transkrip nilai akademik legalisir', mandatory: 1, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'STR (Surat Tanda Registrasi) Aktif', desc: 'Wajib masih aktif dari MTKI / KKI', mandatory: 1, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'Sertifikat ACLS / BTCLS / BCLS', desc: 'Sertifikat pelatihan kegawatdaruratan', mandatory: 0, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'SKCK Kepolisian', desc: 'Surat keterangan catatan kepolisian', mandatory: 0, mimes: 'application/pdf', max_size: 5120 }
                              ];
                          } else if (type === 'umum') {
                              this.documents = [
                                  { id: null, name: 'Surat Lamaran Kerja', desc: 'Ditujukan kepada Direktur RSI Fatimah', mandatory: 1, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'Curriculum Vitae (CV) & Portofolio', desc: 'Riwayat hidup dan bukti hasil karya / proyek', mandatory: 1, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'Ijazah & Transkrip Terakhir', desc: 'Scan legalisir ijazah dan transkrip nilai', mandatory: 1, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'KTP & Kartu Keluarga', desc: 'Scan identitas kependudukan', mandatory: 1, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'Sertifikat Keahlian / Kompetensi', desc: 'Sertifikat sertifikasi IT / akuntansi / administrasi', mandatory: 0, mimes: 'application/pdf', max_size: 5120 },
                                  { id: null, name: 'SKCK Kepolisian', desc: 'Surat catatan kepolisian aktif', mandatory: 0, mimes: 'application/pdf', max_size: 5120 }
                              ];
                          } else if (type === 'kosong') {
                              this.documents = [
                                  { id: null, name: 'Surat Lamaran Kerja', desc: '', mandatory: 1, mimes: 'application/pdf', max_size: 5120 }
                              ];
                          }
                      },

                      // Section 4: Tahapan Seleksi & Batas Waktu
                      stageTemplates: {{ Js::from($stageTemplates) }},
                      stages: {{ Js::from($stageData) }},
                      applyStageTemplate(templateId) {
                          const tpl = this.stageTemplates.find(t => t.id == templateId);
                          if (tpl && tpl.stages_data && Array.isArray(tpl.stages_data)) {
                              this.stages = tpl.stages_data.map(s => ({
                                  id: null,
                                  name: s.name || '',
                                  desc: s.description || '',
                                  start_at: s.start_date ? s.start_date.substring(0, 10) : '',
                                  end_at: s.end_date ? s.end_date.substring(0, 10) : ''
                              }));
                          }
                      },
                      addStage() {
                          this.stages.push({ id: null, name: '', desc: '', start_at: '', end_at: '' });
                      },
                      removeStage(index) {
                          this.stages.splice(index, 1);
                      }
                  }">
                @csrf
                @method('PUT')

                <!-- Section 1: Informasi Utama -->
                <div class="space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-[#0179B5] text-white flex items-center justify-center text-xs font-bold">1</span>
                        Data Formasi & Posisi
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Periode Rekrutmen <span class="text-rose-500">*</span></label>
                            <select name="recruitment_period_id" required class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                @foreach ($periods as $p)
                                    <option value="{{ $p->id }}" {{ $vacancy->recruitment_period_id == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Posisi Formasi <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $vacancy->title) }}" required
                                   class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Unit / Departemen</label>
                            <input type="text" name="department" value="{{ old('department', $vacancy->department ?? $vacancy->unit) }}"
                                   class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Jenis Formasi <span class="text-rose-500">*</span></label>
                                <select name="formation_type" required class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    @php
                                        $currentFormation = old('formation_type', $vacancy->formation_type ?? $vacancy->employment_type);
                                        $standardFormations = [
                                            'Tenaga Medis',
                                            'Tenaga Keperawatan',
                                            'Tenaga Kebidanan',
                                            'Tenaga Kefarmasian',
                                            'Tenaga Penunjang Medis',
                                            'Tenaga Non-Medis / Umum',
                                            'PKWT',
                                            'PKWTT',
                                        ];
                                    @endphp
                                    @foreach($standardFormations as $fOption)
                                        <option value="{{ $fOption }}" {{ $currentFormation == $fOption ? 'selected' : '' }}>{{ $fOption }}</option>
                                    @endforeach
                                    @if($currentFormation && !in_array($currentFormation, $standardFormations, true))
                                        <option value="{{ $currentFormation }}" selected>{{ $currentFormation }}</option>
                                    @endif
                                </select>
                            </div>
                            <div x-data="{ hasQuota: {{ old('has_quota', $vacancy->hasQuotaLimit()) ? 'true' : 'false' }} }" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-semibold text-slate-700">
                                        Batas Kuota Formasi
                                    </label>
                                    <label class="relative inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" name="has_quota" value="1" x-model="hasQuota" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0179B5]"></div>
                                        <span class="ml-2 text-xs font-semibold" :class="hasQuota ? 'text-[#0179B5]' : 'text-slate-500'" x-text="hasQuota ? 'Dibatasi' : 'Unlimited'"></span>
                                    </label>
                                </div>

                                <div x-show="hasQuota" style="display: {{ old('has_quota', $vacancy->hasQuotaLimit()) ? 'block' : 'none' }};">
                                    <div class="relative">
                                        <input type="number" name="quota" x-bind:required="hasQuota" x-bind:disabled="!hasQuota"
                                               value="{{ old('quota', $vacancy->quota ?? 1) }}" min="1"
                                               placeholder="Masukkan jumlah kuota orang"
                                               class="w-full h-11 px-3 pr-16 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                                        <span class="absolute right-3 top-3 text-xs font-semibold text-slate-400">Orang</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-1">Kandidat lolos final dibatasi maksimal sesuai angka kuota ini.</p>
                                </div>

                                <div x-show="!hasQuota" style="display: {{ old('has_quota', $vacancy->hasQuotaLimit()) ? 'none' : 'flex' }};" class="p-2.5 rounded-lg bg-sky-50/70 border border-sky-100 flex items-center gap-2 text-sky-800 text-xs">
                                    <svg class="w-4 h-4 shrink-0 text-[#0179B5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span><strong>Unlimited:</strong> Kuota tidak dibatasi / sesuai kebutuhan rekrutmen.</span>
                                </div>

                                @error('quota')
                                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Mulai Pendaftaran <span class="text-rose-500">*</span></label>
                            <input type="date" name="open_at" value="{{ old('open_at', $vacancy->open_at ? $vacancy->open_at->format('Y-m-d') : now()->format('Y-m-d')) }}" required
                                   class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            @error('open_at')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Batas Akhir Pendaftaran (Deadline) <span class="text-rose-500">*</span></label>
                            <input type="date" name="close_at" value="{{ old('close_at', $vacancy->close_at ? $vacancy->close_at->format('Y-m-d') : now()->addMonth()->format('Y-m-d')) }}" required
                                   class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            @error('close_at')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Ringkasan / Summary Singkat</label>
                        <input type="text" name="summary" value="{{ old('summary', $vacancy->summary) }}"
                               class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi Lengkap Lowongan <span class="text-rose-500">*</span></label>
                        <textarea name="description" rows="5" required
                                  class="w-full p-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">{{ old('description', $vacancy->description) }}</textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="is_published" id="is_published" value="1" {{ $vacancy->is_published ? 'checked' : '' }}
                               class="w-4 h-4 text-[#0179B5] rounded border-slate-300 focus:ring-[#0179B5]">
                        <label for="is_published" class="text-sm font-semibold text-slate-800">Publikasikan lowongan ini di portal publik</label>
                    </div>
                </div>

                <!-- Section 2: Kualifikasi -->
                <div class="space-y-3 pt-6 border-t border-slate-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#0179B5] text-white flex items-center justify-center text-xs font-bold">2</span>
                            Kualifikasi & Persyaratan Khusus
                        </h2>
                        <button type="button" @click="addQual()" class="text-sm text-[#0179B5] hover:underline font-semibold flex items-center gap-1">
                            + Tambah Baris Kualifikasi
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(item, index) in qualifications" :key="index">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-slate-400 font-mono w-5 text-right" x-text="(index + 1) + '.'"></span>
                                <input type="text" name="qualifications[]" x-model="qualifications[index]" placeholder="Tuliskan kriteria kualifikasi..."
                                       class="flex-1 h-10 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                                <button type="button" @click="removeQual(index)" class="p-2 text-rose-500 hover:bg-rose-50 rounded" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Section 3: Persyaratan Dokumen Berkas (Kustom / Manual) -->
                <div class="space-y-4 pt-6 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#0179B5] text-white flex items-center justify-center text-xs font-bold">3</span>
                                Opsi Dokumen Berkas Unggahan (Kustom per Lowongan)
                            </h2>
                            <p class="text-sm text-slate-500 mt-0.5">Kelola berkas yang wajib / opsional diunggah oleh pelamar pada lowongan ini</p>
                        </div>

                        <!-- Preset Buttons -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs text-slate-500 font-medium">Gunakan Preset:</span>
                            <button type="button" @click="applyDocPreset('medis')" class="px-3 py-1.5 text-xs font-semibold bg-sky-50 text-[#0179B5] border border-sky-200 rounded-lg hover:bg-sky-100 transition-colors">
                                + Nakes / Medis
                            </button>
                            <button type="button" @click="applyDocPreset('umum')" class="px-3 py-1.5 text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-200 transition-colors">
                                + Umum / Non-Medis
                            </button>
                            <button type="button" @click="applyDocPreset('kosong')" class="px-3 py-1.5 text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 rounded-lg hover:bg-rose-100 transition-colors">
                                Reset Kosong
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(doc, index) in documents" :key="index">
                            <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200 space-y-3">
                                <input type="hidden" :name="'document_ids[' + index + ']'" :value="doc.id || ''">

                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="text-sm font-mono font-bold text-slate-400" x-text="'#' + (index + 1)"></span>
                                        <input type="text" :name="'document_names[' + index + ']'" x-model="doc.name" required
                                               placeholder="Nama Dokumen (Contoh: Ijazah, STR, Portofolio...)"
                                               class="flex-1 h-10 px-3 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <!-- Mandatory Toggle -->
                                        <select :name="'document_mandatory[' + index + ']'" x-model="doc.mandatory"
                                                class="h-10 px-3 text-xs font-semibold rounded-lg border focus:outline-none"
                                                :class="doc.mandatory == 1 ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-slate-100 border-slate-300 text-slate-600'">
                                            <option value="1">Wajib Diunggah (*)</option>
                                            <option value="0">Opsional</option>
                                        </select>

                                        <button type="button" @click="removeDoc(index)" class="p-2 text-rose-500 hover:bg-rose-50 rounded" title="Hapus Dokumen">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-sm">
                                    <div class="sm:col-span-5">
                                        <input type="text" :name="'document_descriptions[' + index + ']'" x-model="doc.desc"
                                               placeholder="Petunjuk / keterangan khusus dokumen (opsional)"
                                               class="w-full h-9 px-3 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>
                                    <div class="sm:col-span-4">
                                        <select :name="'document_mimes[' + index + ']'" x-model="doc.mimes"
                                                @change="if (doc.mimes === 'video/mp4' && (!doc.max_size || doc.max_size < 20480)) { doc.max_size = 20480; } else if (doc.mimes !== 'video/mp4' && doc.max_size > 10240) { doc.max_size = 5120; }"
                                                class="w-full h-9 px-2 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                            <option value="application/pdf">Dokumen PDF (.pdf)</option>
                                            <option value="image/jpeg,image/png">Gambar (.jpg, .png)</option>
                                            <option value="application/pdf,image/jpeg,image/png">Dokumen/Gambar (.pdf, .jpg, .png)</option>
                                            <option value="video/mp4">Video MP4 (.mp4)</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <select :name="'document_max_sizes[' + index + ']'" x-model.number="doc.max_size"
                                                class="w-full h-9 px-2 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                            <option value="2048">Maks 2 MB</option>
                                            <option value="5120">Maks 5 MB</option>
                                            <option value="10240">Maks 10 MB</option>
                                            <option value="20480">Maks 20 MB</option>
                                            <option value="51200">Maks 50 MB</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addDoc()" class="w-full py-3 border-2 border-dashed border-slate-300 hover:border-[#0179B5] text-slate-600 hover:text-[#0179B5] font-semibold text-sm rounded-xl transition-colors flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>+ Tambah Opsi Dokumen Berkas</span>
                        </button>
                    </div>
                </div>

                <!-- Section 4: Tahapan Seleksi & Batas Waktu Timeline -->
                <div class="space-y-4 pt-6 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#0179B5] text-white flex items-center justify-center text-xs font-bold">4</span>
                                Tahapan Seleksi & Jadwal Batas Waktu (Timeline)
                            </h2>
                            <p class="text-sm text-slate-500 mt-0.5">Edit tahapan seleksi beserta tanggal mulai dan batas waktu (deadline) terakhir</p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            @if(count($stageTemplates) > 0)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-slate-500 font-medium">Gunakan Template:</span>
                                    <select @change="if ($event.target.value) applyStageTemplate($event.target.value)" class="h-9 px-2 text-xs bg-sky-50 border border-sky-300 text-[#0179B5] font-semibold rounded-lg focus:outline-none">
                                        <option value="">-- Pilih Template --</option>
                                        @foreach($stageTemplates as $tpl)
                                            <option value="{{ $tpl->id }}">{{ $tpl->name }} {{ $tpl->is_default ? '(Default)' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <button type="button" @click="addStage()" class="text-sm text-[#0179B5] hover:underline font-semibold flex items-center gap-1">
                                + Tambah Tahapan
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(stage, index) in stages" :key="index">
                            <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200 space-y-3">
                                <input type="hidden" :name="'stage_ids[' + index + ']'" :value="stage.id || ''">

                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="w-7 h-7 rounded-full bg-[#0179B5] text-white font-bold text-xs flex items-center justify-center shrink-0" x-text="index + 1"></span>
                                        <input type="text" :name="'stage_names[' + index + ']'" x-model="stage.name" required
                                               placeholder="Nama Tahapan Seleksi (Contoh: Seleksi Administrasi, Ujian Tulis...)"
                                               class="flex-1 h-10 px-3 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>

                                    <button type="button" @click="removeStage(index)" class="p-2 text-rose-500 hover:bg-rose-50 rounded" title="Hapus Tahapan">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Deskripsi / Keterangan</label>
                                        <input type="text" :name="'stage_descriptions[' + index + ']'" x-model="stage.desc"
                                               placeholder="Keterangan alur tahapan"
                                               class="w-full h-9 px-3 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1">Tanggal Mulai</label>
                                        <input type="date" :name="'stage_start_ats[' + index + ']'" x-model="stage.start_at"
                                               class="w-full h-9 px-3 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-rose-600 mb-1">Batas Akhir / Deadline (*)</label>
                                        <input type="date" :name="'stage_end_ats[' + index + ']'" x-model="stage.end_at"
                                               class="w-full h-9 px-3 text-sm bg-white border border-rose-200 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.vacancies.show', $vacancy) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-[#0179B5] hover:bg-[#016293] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
