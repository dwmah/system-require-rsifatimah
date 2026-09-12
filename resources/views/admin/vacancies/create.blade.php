<x-layouts.admin title="Tambah Lowongan Baru">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('admin.vacancies.index') }}" class="hover:text-[#0179B5]">Lowongan SDM</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Tambah Formasi Baru</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="pb-4 border-b border-slate-200 mb-6">
                <h1 class="text-xl font-bold text-slate-900">Formulir Pembukaan Lowongan SDM</h1>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan periode, kualifikasi, kuota, persyaratan dokumen, dan tahapan seleksi</p>
            </div>

            <form method="POST" action="{{ route('admin.vacancies.store') }}" class="space-y-6">
                @csrf

                <!-- Section 1: Informasi Utama -->
                <div class="space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">1. Data Formasi & Posisi</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Periode Rekrutmen <span class="text-rose-500">*</span></label>
                            <select name="recruitment_period_id" required class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                @foreach ($periods as $p)
                                    <option value="{{ $p->id }}" {{ $p->is_active ? 'selected' : '' }}>
                                        {{ $p->name }} {{ $p->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Posisi Formasi <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Perawat Pelaksana IGD"
                                   class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Unit / Departemen</label>
                            <input type="text" name="department" value="{{ old('department', 'Instalasi Gawat Darurat') }}" placeholder="Contoh: Instalasi Gawat Darurat"
                                   class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Formasi <span class="text-rose-500">*</span></label>
                                <select name="formation_type" required class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                    <option value="Tenaga Medis">Tenaga Medis</option>
                                    <option value="Tenaga Keperawatan" selected>Tenaga Keperawatan</option>
                                    <option value="Tenaga Kebidanan">Tenaga Kebidanan</option>
                                    <option value="Tenaga Kefarmasian">Tenaga Kefarmasian</option>
                                    <option value="Tenaga Penunjang Medis">Tenaga Penunjang Medis</option>
                                    <option value="Tenaga Non-Medis / Umum">Tenaga Non-Medis / Umum</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Kuota <span class="text-rose-500">*</span></label>
                                <input type="number" name="quota" value="{{ old('quota', 1) }}" min="1" required
                                       class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Ringkasan / Summary Singkat</label>
                        <input type="text" name="summary" value="{{ old('summary') }}" placeholder="Ringkasan tugas posisi formasi (1 baris)"
                               class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Lengkap Lowongan <span class="text-rose-500">*</span></label>
                        <textarea name="description" rows="4" required placeholder="Jelaskan peran, tanggung jawab, dan gambaran tugas..."
                                  class="w-full p-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="is_published" id="is_published" value="1" checked class="w-4 h-4 text-[#0179B5] rounded border-slate-300 focus:ring-[#0179B5]">
                        <label for="is_published" class="text-xs font-semibold text-slate-800">Publikasikan lowongan ini secara langsung ke portal publik</label>
                    </div>
                </div>

                <!-- Section 2: Kualifikasi -->
                <div class="space-y-3 pt-4 border-t border-slate-200" x-data="{ items: ['Pendidikan minimal D3/S1 sesuai bidang keahlian', 'Memiliki STR aktif (khusus formasi nakes)', 'IPK minimal 3.00 dari perguruan tinggi terakreditasi', 'Usia maksimal 30 tahun', 'Sehat jasmani dan rohani'] }">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">2. Kualifikasi & Persyaratan Khusus</h2>
                        <button type="button" @click="items.push('')" class="text-xs text-[#0179B5] hover:underline font-semibold flex items-center gap-1">
                            + Tambah Baris Kualifikasi
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400 font-mono w-4" x-text="(index + 1) + '.'"></span>
                                <input type="text" name="qualifications[]" x-model="items[index]" placeholder="Tuliskan syarat kualifikasi..."
                                       class="flex-1 h-9 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                                <button type="button" @click="items.splice(index, 1)" class="p-2 text-rose-500 hover:bg-rose-50 rounded" title="Hapus">
                                    &times;
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Section 3: Persyaratan Dokumen -->
                <div class="space-y-3 pt-4 border-t border-slate-200">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">3. Dokumen Berkas Unggahan</h2>
                    <p class="text-xs text-slate-500">Standar dokumen yang harus diunggah oleh pelamar (sesuai regulasi RSI Fatimah):</p>

                    <div class="space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-slate-200 font-semibold text-slate-700">
                            <span>Nama Dokumen</span>
                            <span>Sifat Dokumen</span>
                        </div>
                        <div class="flex items-center justify-between py-1 text-slate-800">
                            <span>1. Surat Lamaran Kerja (Ditujukan ke Direktur RSI Fatimah)</span>
                            <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-semibold rounded">Wajib (*)</span>
                        </div>
                        <div class="flex items-center justify-between py-1 text-slate-800">
                            <span>2. Ijazah Terakhir Legalisir</span>
                            <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-semibold rounded">Wajib (*)</span>
                        </div>
                        <div class="flex items-center justify-between py-1 text-slate-800">
                            <span>3. STR / Sertifikat Kompetensi Keahlian Aktif</span>
                            <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-semibold rounded">Wajib (*)</span>
                        </div>
                        <div class="flex items-center justify-between py-1 text-slate-600">
                            <span>4. SKCK (Kepolisian)</span>
                            <span class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded">Opsional</span>
                        </div>
                        <div class="flex items-center justify-between py-1 text-slate-600">
                            <span>5. Rekomendasi Profesi / Ormas</span>
                            <span class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded">Opsional</span>
                        </div>
                        <div class="flex items-center justify-between py-1 text-slate-600">
                            <span>6. Dokumen Pendukung Lainnya (Sertifikat Pelatihan / MCU)</span>
                            <span class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded">Opsional</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.vacancies.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        Simpan & Buka Lowongan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
