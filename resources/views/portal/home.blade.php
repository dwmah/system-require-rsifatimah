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

        {{-- Published Final Result Banner (If Available) --}}
        @if ($finalResult)
            <div class="p-6 rounded-2xl {{ $finalResult['status'] === 'accepted' ? 'bg-emerald-50 border border-emerald-200 text-emerald-950' : 'bg-slate-50 border border-slate-200 text-slate-900' }} shadow-2xs space-y-2">
                <div class="flex items-center gap-2 font-bold text-base {{ $finalResult['status'] === 'accepted' ? 'text-emerald-900' : 'text-slate-900' }}">
                    @if ($finalResult['status'] === 'accepted')
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                    <span>{{ $finalResult['title'] }}</span>
                </div>

                @if ($finalResult['message'])
                    <p class="text-xs leading-relaxed {{ $finalResult['status'] === 'accepted' ? 'text-emerald-800' : 'text-slate-700' }}">
                        {{ $finalResult['message'] }}
                    </p>
                @endif

                <div class="text-[11px] {{ $finalResult['status'] === 'accepted' ? 'text-emerald-700' : 'text-slate-500' }} pt-1">
                    Diumumkan resmi pada: {{ $finalResult['published_at']->translatedFormat('d F Y, H:i') }} WIB
                </div>
            </div>
        @endif

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
                        {{ $percentage === 100 ? 'Lihat Profil' : 'Lengkapi Profil Sekarang' }}
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

        {{-- Primary / Active Application Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900">
                    Status Lamaran Terbaru
                </h2>
                <a href="{{ route('portal.applications.index') }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                    Semua Lamaran Saya &rarr;
                </a>
            </div>

            @if ($primaryApplication)
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs font-bold text-rsi-800 bg-rsi-50 px-2.5 py-0.5 rounded border border-rsi-100">
                                {{ $primaryApplication->jobVacancy->code }}
                            </span>
                            <span class="text-xs text-slate-400">&bull;</span>
                            <span class="text-xs font-medium text-slate-500">
                                {{ $primaryApplication->recruitmentPeriod->name }}
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            {{ $primaryApplication->jobVacancy->title }}
                        </h3>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                            <div>
                                <span>Nomor Lamaran: </span>
                                <strong class="text-slate-800 font-mono">
                                    {{ $primaryApplication->application_number ?? 'Draft (Belum Diterbitkan)' }}
                                </strong>
                            </div>
                            @if ($primaryApplication->submitted_at)
                                <div>
                                    <span>Dikirim: </span>
                                    <strong class="text-slate-700">
                                        {{ $primaryApplication->submitted_at->translatedFormat('d M Y, H:i') }} WIB
                                    </strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row lg:flex-col items-start lg:items-end gap-3 shrink-0">
                        @if ($publicStatus)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $publicStatus['badge_class'] }}">
                                {{ $publicStatus['label'] }}
                            </span>
                        @endif

                        <a
                            href="{{ $quickAction['url'] }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-rsi-700 text-white hover:bg-rsi-800 transition-colors shadow-2xs"
                        >
                            <span>{{ $quickAction['label'] }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Compact Current Stage Indicator (for submitted applications) --}}
                @if ($currentStage)
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div class="space-y-0.5">
                            <span class="text-[11px] text-slate-400 font-medium">Tahapan Seleksi Saat Ini:</span>
                            <div class="font-bold text-slate-800 flex items-center gap-2">
                                <span>{{ $currentStage['name'] }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $currentStage['badge_class'] }}">
                                    {{ $currentStage['label'] }}
                                </span>
                            </div>
                        </div>

                        <a
                            href="{{ route('portal.applications.tracking', $primaryApplication) }}"
                            class="text-xs font-semibold text-rsi-700 hover:underline shrink-0"
                        >
                            Lihat Detail Tracking &rarr;
                        </a>
                    </div>
                @endif
            @else
                <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-300 space-y-3">
                    <div class="w-10 h-10 mx-auto rounded-full bg-slate-200 text-slate-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-bold text-slate-800">Belum Ada Lamaran Aktif</p>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">
                            Anda belum mengajukan lamaran pekerjaan pada periode rekrutmen RSI Fatimah saat ini.
                        </p>
                    </div>
                    <div class="pt-1">
                        <a
                            href="{{ route('vacancies.index') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-rsi-700 hover:bg-rsi-800 transition-colors shadow-2xs"
                        >
                            <span>Lihat Lowongan Dibuka</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Summary Cards --}}
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
