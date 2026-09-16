@props([
    'user',
    'profile',
])

<div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6"
     x-data="{
         previewUrl: null,
         fileName: '',
         fileSize: '',
         isSelected: false,
         handleFileSelect(event) {
             const file = event.target.files[0];
             if (file) {
                 if (file.size > 5 * 1024 * 1024) {
                     alert('Ukuran berkas melebihi 5 MB. Silakan pilih foto dengan ukuran maksimal 5 MB.');
                     event.target.value = '';
                     return;
                 }
                 this.previewUrl = URL.createObjectURL(file);
                 this.fileName = file.name;
                 this.fileSize = (file.size / 1024).toFixed(0) + ' KB';
                 this.isSelected = true;
             }
         },
         cancelSelect() {
             this.previewUrl = null;
             this.fileName = '';
             this.fileSize = '';
             this.isSelected = false;
             const input = document.getElementById('photo_upload_input');
             if (input) input.value = '';
         }
     }">

    {{-- Error Alert --}}
    @if ($errors->has('photo'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $errors->first('photo') }}</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center gap-5">
        {{-- Frame Pas Foto 3:4 (Strict 105px x 140px, completely contained) --}}
        <div class="relative shrink-0 self-center sm:self-auto" style="width: 105px; height: 140px;">
            <div class="rounded-xl overflow-hidden bg-slate-100 border-2 border-slate-200 shadow-xs relative"
                 style="width: 105px; height: 140px; min-width: 105px; min-height: 140px; max-width: 105px; max-height: 140px; position: relative; overflow: hidden;">

                {{-- Live Preview Image --}}
                <template x-if="previewUrl">
                    <img :src="previewUrl"
                         alt="Pratinjau Foto"
                         class="w-full h-full object-cover"
                         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;">
                </template>

                {{-- Existing Saved Photo --}}
                @if($profile?->photo_url)
                    <img x-show="!previewUrl"
                         src="{{ $profile->photo_url }}"
                         alt="{{ $user->name }}"
                         class="w-full h-full object-cover"
                         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;">
                @else
                    <div x-show="!previewUrl"
                         class="w-full h-full bg-slate-50 flex flex-col items-center justify-center text-slate-400 p-2 text-center"
                         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <svg class="w-10 h-10 text-slate-300 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-[10px] font-semibold text-slate-400">Pas Foto Formal</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Info & Action Controls --}}
        <div class="flex-1 space-y-3 text-center sm:text-left min-w-0">
            <div>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
                    <h3 class="text-base font-bold text-slate-900">Pas Foto Formal</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $profile?->photo_url ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ $profile?->photo_url ? 'SUDAH DIUNGGAH' : 'WAJIB PROFIL LENGKAP' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    Pas foto formal berlatar belakang polos untuk identitas resmi dan kartu seleksi penerimaan karyawan.
                </p>
            </div>

            <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" id="photo_upload_form">
                @csrf

                <input
                    type="file"
                    name="photo"
                    id="photo_upload_input"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    @change="handleFileSelect($event)"
                >

                {{-- Default View: Buttons --}}
                <div x-show="!isSelected" class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 pt-1">
                    <label for="photo_upload_input" class="cursor-pointer inline-flex items-center gap-1.5 px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ $profile?->photo_url ? 'Ganti Pas Foto' : 'Unggah Pas Foto' }}</span>
                    </label>

                    @if($profile?->photo_url)
                        <button
                            type="button"
                            onclick="if(confirm('Apakah Anda yakin ingin menghapus pas foto?')) { document.getElementById('delete_photo_form').submit(); }"
                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-xl border border-rose-200 transition-colors cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Hapus</span>
                        </button>
                    @endif
                </div>

                {{-- Selected Preview: Confirmation --}}
                <div x-show="isSelected" x-cloak class="space-y-2.5 pt-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-sky-50 border border-sky-200 rounded-lg text-xs text-slate-700">
                        <span class="font-medium truncate max-w-xs" x-text="fileName"></span>
                        <span class="text-slate-400 font-mono text-[11px]" x-text="'(' + fileSize + ')'"></span>
                    </div>

                    <div class="flex items-center justify-center sm:justify-start gap-2">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Foto</span>
                        </button>

                        <button
                            type="button"
                            @click="cancelSelect()"
                            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </form>

            @if($profile?->photo_url)
                <form id="delete_photo_form" method="POST" action="{{ route('profile.photo.destroy') }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif

            {{-- Compact format guidance --}}
            <p class="text-[11px] text-slate-400 pt-1">
                Format: <strong class="text-slate-600">JPG, PNG, WEBP</strong> &bull; Ukuran: <strong class="text-slate-600">Maks. 5 MB</strong> &bull; Rasio: <strong class="text-slate-600">3:4</strong>
            </p>
        </div>
    </div>
</div>
