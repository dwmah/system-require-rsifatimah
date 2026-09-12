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
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-base font-bold text-slate-900">Alamat & Kontak</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    WAJIB UNTUK PROFIL DASAR
                </span>
            </div>
            <p class="text-xs text-slate-500">Informasi domisili tempat tinggal dan jalur komunikasi resmi.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('profile.contact.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')

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

        <div class="pt-3 flex justify-end">
            <x-ui.button type="submit" variant="primary" size="sm">
                Simpan Alamat & Kontak
            </x-ui.button>
        </div>
    </form>
</div>
