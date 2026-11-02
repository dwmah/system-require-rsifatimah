@props([
    'user',
    'profile',
    'isProfileLocked' => false,
    'activeApplication' => null,
])

@php
    $inputLockedClass = $isProfileLocked ? 'bg-slate-50 text-slate-600 cursor-not-allowed border-slate-200 select-none' : '';
@endphp

<div
    class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7"
    x-data="{
        sameAsKtp: {{ old('same_as_ktp', $profile?->same_as_ktp ? 'true' : 'false') }},
        addressKtp: '{{ addslashes(old('address_ktp', $profile?->address_ktp ?? '')) }}',
        previewUrl: null,
        fileName: '',
        fileSize: '',
        hasNewPhoto: false,
        removePhoto: false,
        hasExistingPhoto: {{ $profile?->photo_url ? 'true' : 'false' }},
        handlePhotoSelect(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    window.rsiAlert('Ukuran berkas melebihi 5 MB. Silakan pilih foto dengan ukuran maksimal 5 MB.', 'Ukuran Foto Terlalu Besar', 'warning');
                    event.target.value = '';
                    return;
                }
                this.previewUrl = URL.createObjectURL(file);
                this.fileName = file.name;
                this.fileSize = (file.size / 1024).toFixed(0) + ' KB';
                this.hasNewPhoto = true;
                this.removePhoto = false;
            }
        },
        cancelPhoto() {
            this.previewUrl = null;
            this.fileName = '';
            this.fileSize = '';
            this.hasNewPhoto = false;
            const input = document.getElementById('profile_photo_input');
            if (input) input.value = '';
        },
        markRemovePhoto() {
            this.removePhoto = true;
            this.cancelPhoto();
        },
        undoRemovePhoto() {
            this.removePhoto = false;
        }
    }"
>
    {{-- Card Header --}}
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
        <div>
            <div class="mb-1">
                <h3 class="text-base font-bold text-slate-900">Data Pribadi & Profil</h3>
            </div>
            <p class="text-xs text-slate-500">Pas foto formal, identitas diri lengkap, alamat kontak resmi, serta riwayat pendidikan formal terakhir Anda.</p>
        </div>
    </div>

    {{-- Single Unified Form --}}
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        {{-- Hidden input for remove_photo --}}
        <input type="hidden" name="remove_photo" :value="removePhoto ? '1' : '0'">

        {{-- SECTION 1: PAS FOTO FORMAL (Paling Atas Sendiri) --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 border border-slate-200/90">
            <div class="flex flex-col sm:flex-row items-center gap-5">
                {{-- Frame Pas Foto 3:4 (Strict 105px x 140px) --}}
                <div class="relative shrink-0" style="width: 105px; height: 140px;">
                    <div class="rounded-xl overflow-hidden bg-white border-2 border-slate-200 shadow-xs relative"
                         style="width: 105px; height: 140px; min-width: 105px; min-height: 140px; max-width: 105px; max-height: 140px; position: relative; overflow: hidden;">

                        {{-- Live Preview Image --}}
                        <template x-if="previewUrl">
                            <img :src="previewUrl"
                                 alt="Pratinjau Pas Foto Baru"
                                 class="w-full h-full object-cover"
                                 style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;">
                        </template>

                        {{-- Existing Saved Photo --}}
                        @if($profile?->photo_url)
                            <img x-show="!previewUrl && !removePhoto"
                                 src="{{ $profile->photo_url }}"
                                 alt="{{ $user->name }}"
                                 class="w-full h-full object-cover"
                                 style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;">
                        @endif

                        {{-- Placeholder when no photo or marked for removal --}}
                        <div x-show="(!hasExistingPhoto && !previewUrl) || (removePhoto && !previewUrl)"
                             class="w-full h-full bg-slate-100 flex flex-col items-center justify-center text-slate-400 p-2 text-center"
                             style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <svg class="w-10 h-10 text-slate-300 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-[10px] font-semibold text-slate-400">Pas Foto 3x4</span>
                        </div>
                    </div>
                </div>

                {{-- Info & Action Controls --}}
                <div class="flex-1 space-y-2.5 text-center sm:text-left min-w-0">
                    <div>
                        <div class="mb-1">
                            <h4 class="text-sm font-bold text-slate-900">Pas Foto Formal (3x4)</h4>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pas foto formal berlatar belakang polos untuk identitas resmi dan kartu seleksi penerimaan karyawan. Format JPG, PNG, atau WEBP (maks. 5 MB).
                        </p>
                    </div>

                    {{-- Hidden File Input --}}
                    <input
                        type="file"
                        id="profile_photo_input"
                        name="photo"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="handlePhotoSelect($event)"
                    >

                    {{-- Photo Actions --}}
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                        @if ($isProfileLocked)
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-600 text-xs font-semibold rounded-xl border border-slate-200 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Pas Foto Terkunci</span>
                            </div>
                        @else
                            <label
                                for="profile_photo_input"
                                class="cursor-pointer inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-xl shadow-xs transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span x-text="hasExistingPhoto || hasNewPhoto ? 'Ganti Pas Foto' : 'Pilih Pas Foto'"></span>
                            </label>

                            <template x-if="hasNewPhoto">
                                <button
                                    type="button"
                                    @click="cancelPhoto()"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-colors cursor-pointer"
                                >
                                    Batalkan Pilihan
                                </button>
                            </template>

                            <template x-if="hasExistingPhoto && !hasNewPhoto && !removePhoto">
                                <button
                                    type="button"
                                    @click="markRemovePhoto()"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold rounded-xl border border-rose-200 transition-colors cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Hapus Foto</span>
                                </button>
                            </template>

                            <template x-if="removePhoto && !hasNewPhoto">
                                <button
                                    type="button"
                                    @click="undoRemovePhoto()"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors cursor-pointer"
                                >
                                    Batal Hapus
                                </button>
                            </template>
                        @endif
                    </div>

                    <x-form.error name="photo" />
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-slate-100"></div>

        {{-- SECTION 2: IDENTITAS DIRI --}}
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Identitas Diri</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- NIK (Readonly & Locked) --}}
                <div>
                    <label for="nik" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nomor Induk Kependudukan (NIK)
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            id="nik"
                            value="{{ $profile?->nik ?? '-' }}"
                            readonly
                            disabled
                            class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-200 bg-slate-50 text-slate-600 font-mono cursor-not-allowed select-none"
                        >
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">NIK telah terdaftar dan tidak dapat diubah secara mandiri.</p>
                </div>

                {{-- Nama Lengkap --}}
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Lengkap (Wajib Beserta Gelar Jika Ada) <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        placeholder="Contoh: dr. Budi Santoso, Sp.A / Siti Aisyah, S.Kep., Ns."
                        required
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('name') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Wajib mencantumkan gelar akademik atau profesi jika memilikinya.</p>
                    <x-form.error name="name" />
                </div>

                {{-- Nama Panggilan --}}
                <div>
                    <label for="nickname" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Panggilan <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        type="text"
                        name="nickname"
                        id="nickname"
                        value="{{ old('nickname', $profile?->nickname) }}"
                        placeholder="Contoh: Budi / Siti"
                        required
                        maxlength="50"
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('nickname') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Nama sapaan akrab untuk komunikasi dan wawancara.</p>
                    <x-form.error name="nickname" />
                </div>

                {{-- Jenis Kelamin --}}
                <div>
                    <label for="gender" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Jenis Kelamin <span class="text-red-500 font-bold">*</span>
                    </label>
                    <select
                        name="gender"
                        id="gender"
                        required
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('gender') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2 bg-white"
                    >
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Laki-laki" {{ old('gender', $profile?->gender) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('gender', $profile?->gender) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    <x-form.error name="gender" />
                </div>

                {{-- Tempat Lahir --}}
                <div>
                    <label for="birth_place" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tempat Lahir <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        type="text"
                        name="birth_place"
                        id="birth_place"
                        value="{{ old('birth_place', $profile?->birth_place) }}"
                        placeholder="Kota / Kabupaten tempat lahir"
                        required
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('birth_place') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="birth_place" />
                </div>

                {{-- Tanggal Lahir --}}
                <div>
                    <label for="birth_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tanggal Lahir <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        type="date"
                        name="birth_date"
                        id="birth_date"
                        value="{{ old('birth_date', $profile?->birth_date?->format('Y-m-d')) }}"
                        max="{{ now()->subDay()->toDateString() }}"
                        required
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('birth_date') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="birth_date" />
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-slate-100"></div>

        {{-- SECTION 3: ALAMAT & KONTAK --}}
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Alamat & Kontak</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Alamat Email --}}
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Alamat Email Aktif <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('email') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="email" />
                </div>

                {{-- Nomor WhatsApp / HP --}}
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nomor WhatsApp / HP <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        type="tel"
                        name="phone"
                        id="phone"
                        value="{{ old('phone', $user->phone) }}"
                        placeholder="Contoh: 081234567890"
                        required
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('phone') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="phone" />
                </div>

                {{-- Alamat KTP --}}
                <div class="sm:col-span-2">
                    <label for="address_ktp" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Alamat Sesuai KTP <span class="text-red-500 font-bold">*</span>
                    </label>
                    <textarea
                        name="address_ktp"
                        id="address_ktp"
                        rows="3"
                        x-model="addressKtp"
                        required
                        @disabled($isProfileLocked)
                        placeholder="Alamat lengkap beserta RT/RW, Kelurahan, Kecamatan, dan Kota/Kabupaten"
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('address_ktp') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >{{ old('address_ktp', $profile?->address_ktp) }}</textarea>
                    <x-form.error name="address_ktp" />
                </div>

                {{-- Checkbox Same As KTP --}}
                <div class="sm:col-span-2 pt-1">
                    <label class="flex items-center gap-2 {{ $isProfileLocked ? 'cursor-not-allowed opacity-75' : 'cursor-pointer' }} select-none">
                        <input
                            type="checkbox"
                            name="same_as_ktp"
                            value="1"
                            x-model="sameAsKtp"
                            @disabled($isProfileLocked)
                            class="w-4 h-4 rounded border-slate-300 text-rsi-700 focus:ring-rsi-500 {{ $isProfileLocked ? 'cursor-not-allowed' : '' }}"
                        >
                        <span class="text-xs font-medium text-slate-700">
                            Alamat domisili saat ini sama dengan alamat KTP
                        </span>
                    </label>
                </div>

                {{-- Alamat Domisili --}}
                <div class="sm:col-span-2" x-show="!sameAsKtp" x-cloak>
                    <label for="address_domicile" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Alamat Domisili Saat Ini <span class="text-red-500 font-bold">*</span>
                    </label>
                    <textarea
                        name="address_domicile"
                        id="address_domicile"
                        rows="3"
                        placeholder="Alamat tempat tinggal saat ini"
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('address_domicile') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >{{ old('address_domicile', $profile?->address_domicile) }}</textarea>
                    <x-form.error name="address_domicile" />
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-slate-100"></div>

        {{-- SECTION 4: RIWAYAT PENDIDIKAN TERAKHIR --}}
        @php
            $education = $profile?->educations?->first();
        @endphp
        <div>
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Riwayat Pendidikan Terakhir</p>
                <span class="text-[11px] text-slate-400">Pendidikan formal terakhir yang telah diselesaikan</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Jenjang Pendidikan --}}
                <div>
                    <label for="education_level" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Jenjang Pendidikan <span class="text-red-500 font-bold">*</span>
                    </label>
                    <select
                        name="education_level"
                        id="education_level"
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('education_level') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2 bg-white"
                    >
                        <option value="">-- Pilih Jenjang Pendidikan --</option>
                        @foreach ([
                            'SMA/SMK' => 'SMA / SMK / Sederajat',
                            'D3' => 'D3 (Diploma 3)',
                            'D4' => 'D4 (Diploma 4)',
                            'S1' => 'S1 (Sarjana)',
                            'Profesi' => 'Profesi / Ners / Apoteker',
                            'S2' => 'S2 (Magister)',
                        ] as $key => $label)
                            <option value="{{ $key }}" {{ old('education_level', $education?->education_level) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <x-form.error name="education_level" />
                </div>

                {{-- Tahun Kelulusan --}}
                <div>
                    <label for="graduation_year" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tahun Kelulusan
                    </label>
                    <input
                        type="number"
                        name="graduation_year"
                        id="graduation_year"
                        min="1970"
                        max="{{ now()->year }}"
                        value="{{ old('graduation_year', $education?->graduation_year) }}"
                        placeholder="Contoh: {{ now()->year }}"
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('graduation_year') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="graduation_year" />
                </div>

                {{-- Nama Institusi / Universitas --}}
                <div>
                    <label for="institution_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Institusi / Universitas <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        type="text"
                        name="institution_name"
                        id="institution_name"
                        value="{{ old('institution_name', $education?->institution_name) }}"
                        placeholder="Contoh: Politeknik Negeri Banyuwangi"
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('institution_name') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="institution_name" />
                </div>

                {{-- Program Studi / Jurusan --}}
                <div>
                    <label for="study_program" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Program Studi / Jurusan <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        type="text"
                        name="study_program"
                        id="study_program"
                        value="{{ old('study_program', $education?->study_program) }}"
                        placeholder="Contoh: Teknologi Rekayasa Perangkat Lunak"
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('study_program') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="study_program" />
                </div>

                {{-- IPK / Nilai --}}
                <div>
                    <label for="gpa" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        IPK / Nilai Rata-rata
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        max="4"
                        name="gpa"
                        id="gpa"
                        value="{{ old('gpa', $education?->gpa ? number_format((float) $education->gpa, 2, '.', '') : '') }}"
                        placeholder="Contoh: 3.90"
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('gpa') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="gpa" />
                </div>

                {{-- Akreditasi --}}
                <div>
                    <label for="accreditation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Akreditasi
                    </label>
                    <input
                        type="text"
                        name="accreditation"
                        id="accreditation"
                        value="{{ old('accreditation', $education?->accreditation) }}"
                        placeholder="Contoh: Unggul / A / B"
                        @disabled($isProfileLocked)
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $isProfileLocked ? $inputLockedClass : ($errors->has('accreditation') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100') }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="accreditation" />
                </div>
            </div>
        </div>

        {{-- Single CTA: Simpan Perubahan --}}
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100">
            <p class="text-xs text-slate-400 text-center sm:text-left">
                @if ($isProfileLocked)
                    Data profil tidak dapat diubah karena lamaran Anda telah resmi terkirim ke panitia seleksi.
                @else
                    Semua data pribadi, kontak, pas foto, dan riwayat pendidikan akan disimpan secara bersamaan.
                @endif
            </p>
            @if ($isProfileLocked)
                <button
                    type="button"
                    disabled
                    aria-disabled="true"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed select-none shadow-none opacity-80"
                    title="Data profil dikunci karena lamaran Anda sedang dalam proses seleksi."
                >
                    <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            @else
                <x-ui.button type="submit" variant="primary" size="sm" class="w-full sm:w-auto shadow-xs">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Perubahan</span>
                </x-ui.button>
            @endif
        </div>
    </form>
</div>
