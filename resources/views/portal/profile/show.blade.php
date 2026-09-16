<x-layouts.applicant
    title="Profil Saya"
    active-menu="profile"
    :user-name="$user->name"
    :user-email="$user->email"
>
    <div class="space-y-6 max-w-5xl">
        {{-- Breadcrumb --}}
        <x-ui.breadcrumb :items="[
            ['label' => 'Portal Pelamar', 'url' => route('portal.home')],
            ['label' => 'Profil Saya', 'active' => true],
        ]" />

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Profil Saya</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Lengkapi data pribadi dan profesional Anda sebagai basis administrasi pendaftaran formasi.
                </p>
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

        {{-- 1. Pas Foto Formal Pelamar (3:4) --}}
        @include('portal.profile.partials.photo', [
            'user' => $user,
            'profile' => $profile,
        ])

        {{-- 2. Profile Completeness Overview --}}
        @include('portal.profile.partials.completeness', [
            'percentage' => $percentage,
            'isComplete' => $isComplete,
            'details' => $details,
            'missingItems' => $missingItems,
        ])

        {{-- 2. Data Pribadi --}}
        @include('portal.profile.partials.personal-data', [
            'user' => $user,
            'profile' => $profile,
        ])

        {{-- 3. Alamat & Kontak --}}
        @include('portal.profile.partials.contact-address', [
            'user' => $user,
            'profile' => $profile,
        ])

        {{-- 4. Riwayat Pendidikan --}}
        @include('portal.profile.partials.educations', [
            'educations' => $profile?->educations ?? collect(),
        ])

        {{-- 5. Ringkasan Kesiapan Profil --}}
        @include('portal.profile.partials.readiness-review', [
            'percentage' => $percentage,
            'isComplete' => $isComplete,
            'missingItems' => $missingItems,
        ])
    </div>
</x-layouts.applicant>
