<x-layouts.auth
    title="Pendaftaran Akun & Lamaran Formasi"
    card-title="Registrasi Akun Pelamar"
    back-url="{{ route('vacancies.index') }}"
    back-label="Kembali ke Daftar Lowongan"
>

    @if ($errors->any())
        <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 space-y-1">
            <p class="font-semibold text-red-800 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Mohon periksa kembali isian formulir:</span>
            </p>
            <ul class="list-disc list-inside text-red-600 pl-1 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4" novalidate>
        @csrf
        @if (isset($targetVacancy))
            <input type="hidden" name="job_vacancy_id" value="{{ $targetVacancy->id }}">
        @endif

        {{-- Nama Lengkap --}}
        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Nama Lengkap Sesuai KTP <span class="text-red-500 font-bold">*</span>
            </label>
            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name') }}"
                placeholder="Nama lengkap beserta gelar (bila ada)"
                required
                autocomplete="name"
                class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('name') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
            >
            <x-form.error name="name" />
        </div>

        {{-- NIK --}}
        <div>
            <label for="nik" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Nomor Induk Kependudukan (NIK) <span class="text-red-500 font-bold">*</span>
            </label>
            <input
                type="text"
                name="nik"
                id="nik"
                value="{{ old('nik') }}"
                placeholder="16 digit angka NIK KTP"
                maxlength="16"
                inputmode="numeric"
                required
                class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('nik') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
            >
            <p class="text-[11px] text-slate-400 mt-1">NIK digunakan untuk verifikasi data kependudukan dan satu akun hanya untuk satu NIK.</p>
            <x-form.error name="nik" />
        </div>

        {{-- Nomor WhatsApp / HP --}}
        <div>
            <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Nomor WhatsApp / HP Aktif <span class="text-red-500 font-bold">*</span>
            </label>
            <input
                type="tel"
                name="phone"
                id="phone"
                value="{{ old('phone') }}"
                placeholder="Contoh: 081234567890"
                required
                autocomplete="tel"
                class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('phone') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
            >
            <x-form.error name="phone" />
        </div>

        {{-- Alamat Email --}}
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Alamat Email Aktif <span class="text-red-500 font-bold">*</span>
            </label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                placeholder="nama@email.com"
                required
                autocomplete="email"
                class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('email') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
            >
            <x-form.error name="email" />
        </div>

        {{-- Password dengan Show/Hide Alpine.js --}}
        <div x-data="{ show: false }">
            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Password <span class="text-red-500 font-bold">*</span>
            </label>
            <div class="relative">
                <input
                    :type="show ? 'text' : 'password'"
                    name="password"
                    id="password"
                    placeholder="Minimal 8 karakter kombinasi huruf & angka"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-lg text-sm transition-all py-2 pl-3.5 pr-10 border {{ $errors->has('password') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
                >
                <button
                    type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-hidden"
                    :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                >
                    <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-form.error name="password" />
        </div>

        {{-- Konfirmasi Password dengan Show/Hide Alpine.js --}}
        <div x-data="{ show: false }">
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Konfirmasi Password <span class="text-red-500 font-bold">*</span>
            </label>
            <div class="relative">
                <input
                    :type="show ? 'text' : 'password'"
                    name="password_confirmation"
                    id="password_confirmation"
                    placeholder="Ulangi password di atas"
                    required
                    autocomplete="new-password"
                    class="w-full rounded-lg text-sm transition-all py-2 pl-3.5 pr-10 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                >
                <button
                    type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-hidden"
                    :aria-label="show ? 'Sembunyikan konfirmasi password' : 'Tampilkan konfirmasi password'"
                >
                    <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Checkbox Persetujuan Ketentuan --}}
        <div class="pt-2">
            <x-form.checkbox
                name="terms"
                id="terms"
                value="1"
                label="Saya menyetujui Syarat & Ketentuan serta Kebijakan Privasi Rekrutmen RSI Fatimah Banyuwangi"
                :required="true"
            />
        </div>

        {{-- Submit Button --}}
        <div class="pt-2">
            <x-ui.button
                type="submit"
                variant="primary"
                size="md"
                class="w-full py-2.5 font-semibold text-sm shadow-sm"
            >
                Daftar Akun & Mulai Lamaran
            </x-ui.button>
        </div>
    </form>

    {{-- Footer Tautan Masuk --}}
    <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
        Sudah memiliki akun?
        <a href="{{ isset($targetVacancy) ? route('login', ['intended' => route('vacancies.show', $targetVacancy)]) : route('login') }}" class="font-semibold text-rsi-700 hover:text-rsi-800 hover:underline transition-colors ml-1">
            Masuk ke Akun
        </a>
    </div>
</x-layouts.auth>
