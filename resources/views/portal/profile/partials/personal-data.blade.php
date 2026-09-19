@props([
    'user',
    'profile',
])

<div
    class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7"
    x-data="{
        sameAsKtp: {{ old('same_as_ktp', $profile?->same_as_ktp ? 'true' : 'false') }},
        addressKtp: '{{ addslashes(old('address_ktp', $profile?->address_ktp ?? '')) }}'
    }"
>
    {{-- Card Header --}}
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-base font-bold text-slate-900">Data Pribadi & Kontak</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    WAJIB
                </span>
            </div>
            <p class="text-xs text-slate-500">Identitas diri, alamat domisili, dan jalur komunikasi resmi.</p>
        </div>
    </div>

    {{-- Single Form --}}
    <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('PATCH')

        {{-- Section: Identitas Diri --}}
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
                        Nama Lengkap <span class="text-red-500 font-bold">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('name') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="name" />
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
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('birth_place') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
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
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('birth_date') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
                    >
                    <x-form.error name="birth_date" />
                </div>

                {{-- Jenis Kelamin --}}
                <div class="sm:col-span-2">
                    <label for="gender" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Jenis Kelamin <span class="text-red-500 font-bold">*</span>
                    </label>
                    <select
                        name="gender"
                        id="gender"
                        required
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('gender') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2 bg-white"
                    >
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Laki-laki" {{ old('gender', $profile?->gender) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('gender', $profile?->gender) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    <x-form.error name="gender" />
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-slate-100"></div>

        {{-- Section: Alamat & Kontak --}}
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
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('email') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
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
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('phone') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
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
                        placeholder="Alamat lengkap beserta RT/RW, Kelurahan, Kecamatan, dan Kota/Kabupaten"
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('address_ktp') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
                    >{{ old('address_ktp', $profile?->address_ktp) }}</textarea>
                    <x-form.error name="address_ktp" />
                </div>

                {{-- Checkbox Same As KTP --}}
                <div class="sm:col-span-2 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="same_as_ktp"
                            value="1"
                            x-model="sameAsKtp"
                            class="w-4 h-4 rounded border-slate-300 text-rsi-700 focus:ring-rsi-500"
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
                        class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('address_domicile') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
                    >{{ old('address_domicile', $profile?->address_domicile) }}</textarea>
                    <x-form.error name="address_domicile" />
                </div>
            </div>
        </div>

        {{-- Single CTA --}}
        <div class="pt-2 flex justify-end border-t border-slate-100">
            <x-ui.button type="submit" variant="primary" size="sm">
                Simpan Perubahan
            </x-ui.button>
        </div>
    </form>
</div>
