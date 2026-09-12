<x-layouts.applicant
    title="Beranda Portal Pelamar"
    active-menu="dashboard"
    :user-name="$user->name"
    :user-email="$user->email"
>
    <div class="space-y-6">
        {{-- Breadcrumb --}}
        <x-ui.breadcrumb :items="[
            ['label' => 'Portal Pelamar', 'url' => route('portal.home')],
            ['label' => 'Beranda', 'active' => true],
        ]" />

        {{-- Welcome Header Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            Akun Aktif
                        </span>
                        <span class="text-xs text-slate-400">&bull;</span>
                        <span class="text-xs font-mono font-medium text-slate-500">
                            {{ $user->registration_number ?? 'Belum ada nomor registrasi' }}
                        </span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                        Selamat Datang, {{ $user->name }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500">
                        Sistem Rekrutmen SDM Rumah Sakit Islam Fatimah Banyuwangi
                    </p>
                </div>

                {{-- Action / Logout --}}
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button
                            type="submit"
                            variant="secondary"
                            size="sm"
                            class="text-xs text-red-600 hover:text-red-700 hover:bg-red-50"
                        >
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Keluar Akun
                        </x-ui.button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Profile Completeness Status Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h2 class="text-base font-bold text-slate-900">Kelengkapan Profil Dasar</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $percentage === 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $percentage }}% Selesai
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">
                        {{ $percentage === 100 ? 'Profil dasar Anda sudah lengkap dan siap digunakan untuk melamar pekerjaan.' : 'Lengkapi data pribadi, kontak, dan riwayat pendidikan Anda agar dapat mengajukan lamaran.' }}
                    </p>
                </div>

                <div>
                    <x-ui.button
                        href="{{ route('profile.show') }}"
                        variant="{{ $percentage === 100 ? 'outline' : 'primary' }}"
                        size="sm"
                    >
                        {{ $percentage === 100 ? 'Lihat / Edit Profil' : 'Lengkapi Profil Sekarang' }}
                    </x-ui.button>
                </div>
            </div>

            {{-- Progress Bar --}}
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                <div
                    class="h-full rounded-full transition-all duration-500 {{ $percentage === 100 ? 'bg-emerald-500' : 'bg-rsi-600' }}"
                    style="width: {{ $percentage }}%"
                    role="progressbar"
                    aria-valuenow="{{ $percentage }}"
                    aria-valuemin="0"
                    aria-valuemax="100"
                ></div>
            </div>
        </div>

        {{-- Temporary Notice & Information --}}
        <div class="bg-linear-to-r from-rsi-50 via-sky-50 to-white rounded-2xl border border-rsi-100 p-6 sm:p-7">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-rsi-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <h2 class="text-sm sm:text-base font-semibold text-slate-900">
                        Portal Pelamar Telah Berhasil Terhubung
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-3xl">
                        Akun pelamar Anda telah berhasil terverifikasi dan aktif di sistem rekrutmen. Pada tahap pengembangan berikutnya, modul <strong>Pengisian Profil Lengkap & Biodata</strong> serta <strong>Pemilihan Formasi Lamaran</strong> akan diaktifkan secara bertahap.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <a
                            href="{{ route('vacancies.index') }}"
                            class="inline-flex items-center text-xs font-semibold text-rsi-700 hover:text-rsi-800 hover:underline"
                        >
                            <span>Lihat Lowongan Terbuka Saat Ini</span>
                            <svg class="w-3.5 h-3.5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <span class="text-slate-300">&bull;</span>
                        <a
                            href="{{ route('guides.flow') }}"
                            class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-800 hover:underline"
                        >
                            <span>Baca Alur Seleksi</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards (Informational Shell) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nomor Registrasi</p>
                <p class="text-base font-bold font-mono text-slate-900">{{ $user->registration_number ?? '-' }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Gunakan nomor ini sebagai identitas rekrutmen.</p>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Email Terdaftar</p>
                <p class="text-sm font-semibold text-slate-900 truncate">{{ $user->email }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Pemberitahuan resmi dikirim ke email ini.</p>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">WhatsApp / HP</p>
                <p class="text-sm font-semibold text-slate-900 font-mono">+{{ $user->phone }}</p>
                <p class="text-[11px] text-slate-500 mt-1">Nomor kontak aktif pelamar.</p>
            </div>
        </div>
    </div>
</x-layouts.applicant>
