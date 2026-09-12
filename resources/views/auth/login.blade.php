<x-layouts.auth
    title="Masuk ke Portal Pelamar"
    card-title="Masuk Portal Pelamar"
    card-subtitle="Silakan masukkan kredensial akun pelamar Anda untuk mengakses portal rekrutmen"
    back-url="{{ route('vacancies.index') }}"
    back-label="Kembali ke Beranda"
>
    {{-- Status Alert (misal setelah password reset berhasil) --}}
    @if (session('status'))
        <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-start gap-2">
            <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4" novalidate>
        @csrf

        {{-- Email atau Nomor Registrasi --}}
        <div>
            <label for="login" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Email / Nomor Registrasi <span class="text-red-500 font-bold">*</span>
            </label>
            <input
                type="text"
                name="login"
                id="login"
                value="{{ old('login') }}"
                placeholder="nama@email.com atau PLM-2026-000001"
                required
                autofocus
                autocomplete="username"
                class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('login') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
            >
            <p class="text-[11px] text-slate-400 mt-1">Gunakan alamat email aktif atau nomor registrasi yang didapatkan saat pendaftaran.</p>
        </div>

        {{-- Password dengan Show/Hide Alpine.js --}}
        <div x-data="{ show: false }">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold text-slate-700">
                    Password <span class="text-red-500 font-bold">*</span>
                </label>
                <a
                    href="{{ route('password.request') }}"
                    class="text-xs font-medium text-rsi-700 hover:text-rsi-800 hover:underline transition-colors"
                >
                    Lupa Password?
                </a>
            </div>
            <div class="relative">
                <input
                    :type="show ? 'text' : 'password'"
                    name="password"
                    id="password"
                    placeholder="Masukkan password akun Anda"
                    required
                    autocomplete="current-password"
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
        </div>

        {{-- Ingat Saya Checkbox --}}
        <div class="pt-1">
            <x-form.checkbox
                name="remember"
                id="remember"
                value="1"
                label="Ingat Saya di perangkat ini"
            />
        </div>

        {{-- Tombol Masuk --}}
        <div class="pt-2">
            <x-ui.button
                type="submit"
                variant="primary"
                size="md"
                class="w-full py-2.5 font-semibold text-sm shadow-sm"
            >
                Masuk Sekarang
            </x-ui.button>
        </div>
    </form>

    {{-- Footer Tautan Daftar --}}
    <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
        Belum memiliki akun?
        <a href="{{ route('register') }}" class="font-semibold text-rsi-700 hover:text-rsi-800 hover:underline transition-colors ml-1">
            Daftar Sekarang
        </a>
    </div>
</x-layouts.auth>
