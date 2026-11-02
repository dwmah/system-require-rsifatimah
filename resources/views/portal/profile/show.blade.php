<x-layouts.applicant
    title="Profil Saya"
    active-menu="profile"
    :user-name="$user->name"
    :user-email="$user->email"
>
    <div class="space-y-6 max-w-5xl">
        @php
            $targetPemberkasanUrl = $pemberkasanUrl ?? (
                isset($activeDraft) && $activeDraft
                    ? route('portal.applications.documents.index', $activeDraft)
                    : (auth()->user()?->jobApplications()->where('status', \App\Enums\JobApplicationStatus::DRAFT)->latest()->first()
                        ? route('portal.applications.documents.index', auth()->user()->jobApplications()->where('status', \App\Enums\JobApplicationStatus::DRAFT)->latest()->first())
                        : route('portal.applications.index'))
            );
        @endphp

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Profil Saya</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Lengkapi data pribadi dan profesional Anda sebagai basis administrasi pendaftaran formasi.
                </p>
            </div>

            {{-- CTA Menuju Pemberkasan / Detail Lamaran (Pojok Kanan Atas) --}}
            <div class="shrink-0">
                @if (!empty($isProfileLocked) && !empty($activeApplication))
                    <a
                        href="{{ route('portal.applications.show', $activeApplication) }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-[#0179B5] hover:bg-[#016293] active:bg-[#01527c] shadow-xs hover:shadow-sm transition-all duration-150 group"
                        id="btn-cta-pemberkasan"
                    >
                        <span>Lihat Detail Lamaran</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @elseif ($isComplete)
                    <a
                        href="{{ $targetPemberkasanUrl }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-[#0179B5] hover:bg-[#016293] active:bg-[#01527c] shadow-xs hover:shadow-sm transition-all duration-150 group"
                        id="btn-cta-pemberkasan"
                    >
                        <span>Menuju ke Pemberkasan</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @else
                    <button
                        type="button"
                        disabled
                        aria-disabled="true"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed select-none shadow-none opacity-80"
                        id="btn-cta-pemberkasan"
                        title="Lengkapi profil Anda hingga 100% untuk melanjutkan ke pemberkasan."
                    >
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Menuju ke Pemberkasan</span>
                        <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>


        {{-- Flash Feedback Alerts --}}
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center gap-2.5 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 shadow-2xs space-y-1">
                <p class="font-semibold text-red-800 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Terdapat kesalahan pada input formulir:</span>
                </p>
                <ul class="list-disc list-inside text-red-600 pl-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulir Profil Lengkap (1 Card: Pas Foto Teratas, Data Pribadi, Kontak & Alamat, Riwayat Pendidikan) --}}
        @include('portal.profile.partials.personal-data', [
            'user' => $user,
            'profile' => $profile,
            'isProfileLocked' => $isProfileLocked ?? false,
            'activeApplication' => $activeApplication ?? null,
        ])

    </div>
</x-layouts.applicant>
