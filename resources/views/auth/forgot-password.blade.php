<x-layouts.auth
    title="Lupa Password Akun"
    card-title="Pemulihan Password"
    card-subtitle="Masukkan alamat email terdaftar untuk menerima tautan pemulihan kata sandi akun Anda."
    back-url="{{ route('login') }}"
    back-label="Kembali ke Halaman Masuk"
>
    {{-- Status Alert (Generic Feedback) --}}
    @if (session('status'))
        <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-start gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <p class="leading-relaxed">{{ session('status') }}</p>
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

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4" novalidate>
        @csrf

        {{-- Alamat Email Terdaftar --}}
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Alamat Email Terdaftar <span class="text-red-500 font-bold">*</span>
            </label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email', old('identifier')) }}"
                placeholder="nama@email.com"
                required
                autofocus
                class="w-full rounded-lg text-sm transition-all py-2 px-3.5 border {{ $errors->has('email') || $errors->has('identifier') ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100' }} focus:outline-hidden focus:ring-2"
            >
            <x-form.error name="email" />
            <x-form.error name="identifiert/>
        </div>

        {{-- Tombol Submit --}}
        <div class="pt-2">
            <x-ui.button
                type="submit"
                variant="primary"
                size="md"
                class="w-full py-2.5 font-semibold text-sm shadow-sm"
            >
                Kirim Tautan Reset Password
            </x-ui.button>
        </div>
    </form>

    {{-- Footer Tautan Kembali --}}
    <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
        Ingat password Anda?
        <a href="{{ route('login') }}" class="font-semibold text-rsi-700 hover:text-rsi-800 hover:underline transition-colors ml-1">
            Masuk Sekarang
        </a>
    </div>
</x-layouts.auth>
