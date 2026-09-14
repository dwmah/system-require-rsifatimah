<x-layouts.admin title="Edit Lowongan: {{ $vacancy->title }}">
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('admin.vacancies.index') }}" class="hover:text-[#0179B5]">Lowongan SDM</a>
            <span>/</span>
            <a href="{{ route('admin.vacancies.show', $vacancy) }}" class="hover:text-[#0179B5]">{{ $vacancy->title }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Edit Lowongan & Opsi Berkas</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="pb-4 border-b border-slate-200 mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Edit Lowongan SDM</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Kode: <span class="font-mono font-semibold">{{ $vacancy->code }}</span> &bull; Periode: {{ $vacancy->recruitmentPeriod->name ?? '-' }}</p>
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
                    'mimes' => $d->allowed_mimes ?? 'application/pdf,image/jpeg,image/png',
                    'max_size' => $d->max_size_kb ?? 5120,
                ])->values();
                if ($docData->isEmpty()) {
                    $docData = collect([
                        ['id' => null, 'name' => 'Surat Lamaran Kerja', 'desc' => 'Ditujukan ke Direktur RSI Fatimah', 'mandatory' => 1, 'mimes' => 'application/pdf,image/jpeg,image/png', 'max_size' => 5120],
                        ['id' => null, 'name' => 'Ijazah Terakhir Legalisir', 'desc' => 'Scan ijazah asli legalisir', 'mandatory' => 1, 'mimes' => 'application/pdf,image/jpeg,image/png', 'max_size' => 5120],
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
                        ['id' => null, 'name' => 'Seleksi Administrasi & Verifikasi Berkas', 'desc' => 'Pengecekan berkas pelamar', 'start_at' => now()->format('Y-m-d'), 'end_at' => now()->addDays(7)->format('Y-m-d')],
                        ['id' => null, 'name' => 'Ujian Tulis & Kompetensi Bidang', 'desc' => 'Tes tertulis', 'start_at' => now()->addDays(8)->format('Y-m-d'), 'end_at' => now()->addDays(12)->format('Y-m-d')],
                        ['id' => null, 'name' => 'Wawancara SDI & Direksi', 'desc' => 'Wawancara', 'start_at' => now()->addDays(13)->format('Y-m-d'), 'end_at' => now()->addDays(17)->format('Y-m-d')],
                        ['id' => null, 'name' => 'Medical Check-Up (MCU)', 'desc' => 'Pemeriksaan kesehatan', 'start_at' => now()->addDays(18)->format('Y-m-d'), 'end_at' => now()->addDays(22)->format('Y-m-d')],
                        ['id' => null, 'name' => 'Pengumuman Kelulusan Akhir', 'desc' => 'Pengumuman resmi', 'start_at' => now()->addDays(23)->format('Y-m-d'), 'end_at' => now()->addDays(25)->format('Y-m-d')],
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
                          this.documents.push({ id: null, name: '', desc: '', mandatory: 1, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 });
                      },
                      removeDoc(index) {
                          this.documents.splice(index, 1);
                      },
                      applyDocPreset(type) {
                          if (type === 'medis') {
                              this.documents = [
                                  { id: null, name: 'Surat Lamaran Kerja', desc: 'Ditujukan kepada Direktur RSI Fatimah', mandatory: 1, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 },
                                  { id: null, name: 'Ijazah Profesi / Terakhir', desc: 'Scan ijazah asli legalisir', mandatory: 1, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 },
                                  { id: null, name: 'Transkrip Nilai Akademik', desc: 'Scan transkrip nilai akademik legalisir', mandatory: 1, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 },
                                  { id: null, name: 'STR (Surat Tanda Registrasi) Aktif', desc: 'Wajib masih aktif dari MTKI / KKI', mandatory: 1, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 },
                                  { id: null, name: 'Sertifikat ACLS / BTCLS / BCLS', desc: 'Sertifikat pelatihan kegawatdaruratan', mandatory: 0, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 },
                                  { id: null, name: 'SKCK Kepolisian', desc: 'Surat keterangan catatan kepolisian', mandatory: 0, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 }
                              ];
                          } else if (type === 'umum') {
                              this.documents = [
                                  { id: null, name: 'Surat Lamaran Kerja', desc: 'Ditujukan kepada Direktur RSI Fatimah', mandatory: 1, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 },
                                  { id: null, name: 'Curriculum Vitae (CV) & Portofolio', desc: 'Riwayat hidup dan bukti hasil karya / proyek', mandatory: 1, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 },
                                  { id: null, name: 'Ijazah & Transkrip Terakhir', desc: 'Scan legalisir ijazah dan transkrip nilai', mandatory: 1, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 },
                                  { id: null, name: 'KTP & Kartu Keluarga', desc: 'Scan identitas kependudukan', mandatory: 1, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 },
                                  { id: null, name: 'Sertifikat Keahlian / Kompetensi', desc: 'Sertifikat sertifikasi IT / akuntansi / administrasi', mandatory: 0, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 },
                                  { id: null, name: 'SKCK Kepolisian', desc: 'Surat catatan kepolisian aktif', mandatory: 0, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 }
                              ];
                          } else if (type === 'kosong') {
                              this.documents = [
                                  { id: null, name: 'Surat Lamaran Kerja', desc: '', mandatory: 1, mimes: 'application/pdf,image/jpeg,image/png', max_size: 5120 }
                              ];
                          }
                      },

                      // Section 4: Tahapan Seleksi & Batas Waktu
                      stages: {{ Js::from($stageData) }},
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
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-[#0179B5] text-white flex items-center justify-center text-[10px]">1</span>
                        Data Formasi & Posisi
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Periode Rekrutmen <span class="text-rose-500">*</span></label>
                            <select name="recruitment_period_id" required class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                @foreach ($periods as $p)
                                    <option value="{{ $p->id }}" {{ $vacancy->recruitment_period_id == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Posisi Formasi <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $vacancy->title) }}" required
                                   class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Unit / Departemen</label>
                            <input type="text" name="department" value="{{ old('department', $vacancy->department ?? $vacancy->unit) }}"
                                   class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Formasi <span class="text-rose-500">*</span></label>
                                <select name="formation_type" required class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    <option value="Tenaga Medis" {{ $vacancy->formation_type == 'Tenaga Medis' || $vacancy->employment_type == 'Tenaga Medis' ? 'selected' : '' }}>Tenaga Medis</option>
                                    <option value="Tenaga Keperawatan" {{ $vacancy->formation_type == 'Tenaga Keperawatan' || $vacancy->employment_type == 'Tenaga Keperawatan' ? 'selected' : '' }}>Tenaga Keperawatan</option>
                                    <option value="Tenaga Kebidanan" {{ $vacancy->formation_type == 'Tenaga Kebidanan' || $vacancy->employment_type == 'Tenaga Kebidanan' ? 'selected' : '' }}>Tenaga Kebidanan</option>
                                    <option value="Tenaga Kefarmasian" {{ $vacancy->formation_type == 'Tenaga Kefarmasian' || $vacancy->employment_type == 'Tenaga Kefarmasian' ? 'selected' : '' }}>Tenaga Kefarmasian</option>
                                    <option value="Tenaga Penunjang Medis" {{ $vacancy->formation_type == 'Tenaga Penunjang Medis' || $vacancy->employment_type == 'Tenaga Penunjang Medis' ? 'selected' : '' }}>Tenaga Penunjang Medis</option>
                                    <option value="Tenaga Non-Medis / Umum" {{ $vacancy->formation_type == 'Tenaga Non-Medis / Umum' || $vacancy->employment_type == 'Tenaga Non-Medis / Umum' ? 'selected' : '' }}>Tenaga Non-Medis / Umum</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Kuota <span class="text-rose-500">*</span></label>
                                <input type="number" name="quota" value="{{ old('quota', $vacancy->quota) }}" min="1" required
                                       class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Pendaftaran <span class="text-rose-500">*</span></label>
                            <input type="date" name="open_at" value="{{ old('open_at', $vacancy->open_at ? $vacancy->open_at->format('Y-m-d') : now()->format('Y-m-d')) }}" required
                                   class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            @error('open_at')
                                <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Akhir Pendaftaran (Deadline) <span class="text-rose-500">*</span></label>
                            <input type="date" name="close_at" value="{{ old('close_at', $vacancy->close_at ? $vacancy->close_at->format('Y-m-d') : now()->addMonth()->format('Y-m-d')) }}" required
                                   class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            @error('close_at')
                                <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Ringkasan / Summary Singkat</label>
                        <input type="text" name="summary" value="{{ old('summary', $vacancy->summary) }}"
                               class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Lengkap Lowongan <span class="text-rose-500">*</span></label>
                        <textarea name="description" rows="5" required
                                  class="w-full p-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">{{ old('description', $vacancy->description) }}</textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="is_published" id="is_published" value="1" {{ $vacancy->is_published ? 'checked' : '' }}
                               class="w-4 h-4 text-[#0179B5] rounded border-slate-300 focus:ring-[#0179B5]">
                        <label for="is_published" class="text-xs font-semibold text-slate-800">Publikasikan lowongan ini di portal publik</label>
                    </div>
                </div>

                <!-- Section 2: Kualifikasi -->
                <div class="space-y-3 pt-6 border-t border-slate-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-[#0179B5] text-white flex items-center justify-center text-[10px]">2</span>
                            Kualifikasi & Persyaratan Khusus
                        </h2>
                        <button type="button" @click="addQual()" class="text-xs text-[#0179B5] hover:underline font-semibold flex items-center gap-1">
                            + Tambah Baris Kualifikasi
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(item, index) in qualifications" :key="index">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400 font-mono w-5 text-right" x-text="(index + 1) + '.'"></span>
                                <input type="text" name="qualifications[]" x-model="qualifications[index]" placeholder="Tuliskan kriteria kualifikasi..."
                                       class="flex-1 h-9 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                                <button type="button" @click="removeQual(index)" class="p-2 text-rose-500 hover:bg-rose-50 rounded" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Section 3: Persyaratan Dokumen Berkas (Kustom / Manual) -->
                <div class="space-y-4 pt-6 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#0179B5] text-white flex items-center justify-center text-[10px]">3</span>
                                Opsi Dokumen Berkas Unggahan (Kustom per Lowongan)
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Kelola berkas yang wajib / opsional diunggah oleh pelamar pada lowongan ini</p>
                        </div>

                        <!-- Preset Buttons -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[11px] text-slate-400 font-medium">Gunakan Preset:</span>
                            <button type="button" @click="applyDocPreset('medis')" class="px-2.5 py-1 text-[11px] font-semibold bg-sky-50 text-[#0179B5] border border-sky-200 rounded-lg hover:bg-sky-100 transition-colors">
                                + Nakes / Medis
                            </button>
                            <button type="button" @click="applyDocPreset('umum')" class="px-2.5 py-1 text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-200 transition-colors">
                                + Umum / Non-Medis
                            </button>
                            <button type="button" @click="applyDocPreset('kosong')" class="px-2.5 py-1 text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 rounded-lg hover:bg-rose-100 transition-colors">
                                Reset Kosong
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(doc, index) in documents" :key="index">
                            <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-200 space-y-2.5">
                                <input type="hidden" :name="'document_ids[' + index + ']'" :value="doc.id || ''">

                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="text-xs font-mono font-bold text-slate-400" x-text="'#' + (index + 1)"></span>
                                        <input type="text" :name="'document_names[' + index + ']'" x-model="doc.name" required
                                               placeholder="Nama Dokumen (Contoh: Ijazah, STR, Portofolio...)"
                                               class="flex-1 h-9 px-3 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <!-- Mandatory Toggle -->
                                        <select :name="'document_mandatory[' + index + ']'" x-model="doc.mandatory"
                                                class="h-9 px-2.5 text-xs font-semibold rounded-lg border focus:outline-none"
                                                :class="doc.mandatory == 1 ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-slate-100 border-slate-300 text-slate-600'">
                                            <option value="1">Wajib Diunggah (*)</option>
                                            <option value="0">Opsional</option>
                                        </select>

                                        <button type="button" @click="removeDoc(index)" class="p-2 text-rose-500 hover:bg-rose-50 rounded" title="Hapus Dokumen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <input type="text" :name="'document_descriptions[' + index + ']'" x-model="doc.desc"
                                               placeholder="Petunjuk / keterangan khusus dokumen (opsional)"
                                               class="w-full h-8 px-3 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="hidden" :name="'document_mimes[' + index + ']'" :value="doc.mimes || 'application/pdf,image/jpeg,image/png'">
                                        <input type="hidden" :name="'document_max_sizes[' + index + ']'" :value="doc.max_size || 5120">
                                        <span class="text-[11px] text-slate-400 font-mono">Format: PDF, JPG, PNG &bull; Maks 5 MB</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addDoc()" class="w-full py-2.5 border-2 border-dashed border-slate-300 hover:border-[#0179B5] text-slate-600 hover:text-[#0179B5] font-semibold text-xs rounded-xl transition-colors flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>+ Tambah Opsi Dokumen Berkas</span>
                        </button>
                    </div>
                </div>

                <!-- Section 4: Tahapan Seleksi & Batas Waktu Timeline -->
                <div class="space-y-4 pt-6 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#0179B5] text-white flex items-center justify-center text-[10px]">4</span>
                                Tahapan Seleksi & Jadwal Batas Waktu (Timeline)
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Edit tahapan seleksi beserta tanggal mulai dan batas waktu (deadline) terakhir</p>
                        </div>

                        <button type="button" @click="addStage()" class="text-xs text-[#0179B5] hover:underline font-semibold flex items-center gap-1">
                            + Tambah Tahapan
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(stage, index) in stages" :key="index">
                            <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-200 space-y-2.5">
                                <input type="hidden" :name="'stage_ids[' + index + ']'" :value="stage.id || ''">

                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-1">
                                        <span class="w-6 h-6 rounded-full bg-[#0179B5] text-white font-bold text-[10px] flex items-center justify-center shrink-0" x-text="index + 1"></span>
                                        <input type="text" :name="'stage_names[' + index + ']'" x-model="stage.name" required
                                               placeholder="Nama Tahapan Seleksi (Contoh: Seleksi Administrasi, Ujian Tulis...)"
                                               class="flex-1 h-9 px-3 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>

                                    <button type="button" @click="removeStage(index)" class="p-2 text-rose-500 hover:bg-rose-50 rounded" title="Hapus Tahapan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Deskripsi / Keterangan</label>
                                        <input type="text" :name="'stage_descriptions[' + index + ']'" x-model="stage.desc"
                                               placeholder="Keterangan alur tahapan"
                                               class="w-full h-8 px-3 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Tanggal Mulai</label>
                                        <input type="date" :name="'stage_start_ats[' + index + ']'" x-model="stage.start_at"
                                               class="w-full h-8 px-3 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-semibold text-rose-600 mb-0.5">Batas Akhir / Deadline (*)</label>
                                        <input type="date" :name="'stage_end_ats[' + index + ']'" x-model="stage.end_at"
                                               class="w-full h-8 px-3 text-xs bg-white border border-rose-200 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.vacancies.show', $vacancy) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
