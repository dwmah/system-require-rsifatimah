<x-layouts.admin title="Edit Lowongan: {{ $vacancy->title }}">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('admin.vacancies.index') }}" class="hover:text-[#0179B5]">Lowongan SDM</a>
            <span>/</span>
            <a href="{{ route('admin.vacancies.show', $vacancy) }}" class="hover:text-[#0179B5]">{{ $vacancy->title }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Edit Data</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="pb-4 border-b border-slate-200 mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Edit Lowongan SDM</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Kode: {{ $vacancy->code }} &bull; Periode: {{ $vacancy->recruitmentPeriod->name ?? '-' }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.vacancies.update', $vacancy) }}" class="space-y-6">
                @csrf
                @method('PUT')

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
                        <input type="text" name="department" value="{{ old('department', $vacancy->department) }}"
                               class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Formasi <span class="text-rose-500">*</span></label>
                            <select name="formation_type" required class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                <option value="Tenaga Medis" {{ $vacancy->formation_type == 'Tenaga Medis' ? 'selected' : '' }}>Tenaga Medis</option>
                                <option value="Tenaga Keperawatan" {{ $vacancy->formation_type == 'Tenaga Keperawatan' ? 'selected' : '' }}>Tenaga Keperawatan</option>
                                <option value="Tenaga Kebidanan" {{ $vacancy->formation_type == 'Tenaga Kebidanan' ? 'selected' : '' }}>Tenaga Kebidanan</option>
                                <option value="Tenaga Kefarmasian" {{ $vacancy->formation_type == 'Tenaga Kefarmasian' ? 'selected' : '' }}>Tenaga Kefarmasian</option>
                                <option value="Tenaga Penunjang Medis" {{ $vacancy->formation_type == 'Tenaga Penunjang Medis' ? 'selected' : '' }}>Tenaga Penunjang Medis</option>
                                <option value="Tenaga Non-Medis / Umum" {{ $vacancy->formation_type == 'Tenaga Non-Medis / Umum' ? 'selected' : '' }}>Tenaga Non-Medis / Umum</option>
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
