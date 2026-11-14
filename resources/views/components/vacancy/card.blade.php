@props([
    'vacancy',
])

@php
    /** @var \App\Models\JobVacancy $vacancy */
    $isClosingSoon = $vacancy->isClosingSoon();
    $remainingText = $vacancy->getClosingRemainingText();

    // 1. Tentukan kata kunci pencocokan tema warna (unit, jabatan, atau jenis pegawai)
    $targetText = strtolower(($vacancy->unit ?? '') . ' ' . ($vacancy->title ?? '') . ' ' . ($vacancy->employment_type ?? ''));

    // 2. Pemetaan tema warna khas berdasarkan unit & divisi kerja di RS
    $themeKey = match (true) {
        str_contains($targetText, 'radiolog') || str_contains($targetText, 'radiografi') => 'cyan',
        str_contains($targetText, 'farmasi') || str_contains($targetText, 'apotek') => 'amber',
        str_contains($targetText, 'laboratorium') || str_contains($targetText, 'patologi') => 'purple',
        str_contains($targetText, 'teknologi') || str_contains($targetText, 'it') || str_contains($targetText, 'komputer') => 'indigo',
        str_contains($targetText, 'keamanan') || str_contains($targetText, 'satpam') || str_contains($targetText, 'security') => 'slate',
        str_contains($targetText, 'dokter') || str_contains($targetText, 'pelayanan medis') => 'sky',
        str_contains($targetText, 'gawat darurat') || str_contains($targetText, 'igd') || str_contains($targetText, 'perawat') => 'emerald',
        str_contains($targetText, 'kebidanan') || str_contains($targetText, 'bidan') => 'rose',
        str_contains($targetText, 'fisioterapi') || str_contains($targetText, 'rehabilitasi') => 'teal',
        // Fallback dinamis jika ada formasi baru di luar kata kunci di atas
        default => match (($vacancy->id ?? 1) % 6) {
            0 => 'sky',
            1 => 'emerald',
            2 => 'amber',
            3 => 'purple',
            4 => 'cyan',
            default => 'indigo',
        },
    };

    // 3. Palet background terang (pastel) per departemen — ditulis lengkap agar terbaca compiler Tailwind CSS v4
    $bgClasses = [
        'emerald' => 'bg-emerald-100/50',
        'sky'     => 'bg-sky-100/50',
        'amber'   => 'bg-amber-100/50',
        'purple'  => 'bg-purple-100/50',
        'cyan'    => 'bg-cyan-100/50',
        'indigo'  => 'bg-indigo-100/50',
        'slate'   => 'bg-slate-100/70',
        'rose'    => 'bg-rose-100/50',
        'teal'    => 'bg-teal-100/50',
    ];

    $cardBg = $bgClasses[$themeKey] ?? $bgClasses['sky'];
@endphp

<article {{ $attributes->merge(['class' => 'group ' . $cardBg . ' rounded-xl border border-slate-200 hover:border-slate-300 shadow-2xs hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden p-5']) }}>
    <div>
        {{-- Header Card: Status Badge & Code --}}
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[11px] font-mono font-medium text-slate-600 bg-white/80 px-2 py-0.5 rounded-md border border-slate-200/70">
                {{ $vacancy->code }}
            </span>

            @if (! $vacancy->isCurrentlyOpen())
                <x-ui.badge variant="neutral" size="sm">
                    Ditutup
                </x-ui.badge>
            @elseif ($isClosingSoon && $remainingText)
                <x-ui.badge variant="warning" dot size="sm">
                    Segera Ditutup ({{ $remainingText }})
                </x-ui.badge>
            @else
                <x-ui.badge variant="success" dot size="sm">
                    Dibuka
                </x-ui.badge>
            @endif
        </div>

        {{-- Job Title --}}
        <h3 class="text-base font-bold text-slate-900 leading-snug tracking-tight mb-2 hover:text-rsi-700 transition-colors">
            <a href="{{ route('vacancies.show', $vacancy) }}">
                {{ $vacancy->title }}
            </a>
        </h3>

        {{-- Meta Badges / Information --}}
        <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-600 mb-4">
            <span class="inline-flex items-center gap-1 bg-white/80 text-slate-700 px-2 py-1 rounded-md border border-slate-200/70">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                {{ $vacancy->unit }}
            </span>

            <span class="inline-flex items-center gap-1 bg-white/80 text-slate-700 px-2 py-1 rounded-md border border-slate-200/70">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ $vacancy->employment_type }}
            </span>

            <span class="inline-flex items-center gap-1 bg-white/80 text-slate-700 px-2 py-1 rounded-md border border-slate-200/70">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ $vacancy->hasQuotaLimit() ? $vacancy->quota . ' Formasi' : 'Sesuai Kebutuhan' }}
            </span>
        </div>
    </div>

    {{-- Footer: Deadline & CTA --}}
    <div class="pt-3.5 border-t border-slate-200/70 flex items-center justify-between gap-2 mt-2">
        <div class="text-xs">
            <span class="text-slate-500 block text-[11px]">Batas Pendaftaran</span>
            <span class="font-medium text-slate-700">
                {{ $vacancy->close_at->translatedFormat('d M Y') }}
            </span>
        </div>

        <a
            href="{{ route('vacancies.show', $vacancy) }}"
            class="inline-flex items-center justify-center font-medium rounded-lg text-xs px-3.5 py-1.5 min-h-8 bg-[#0179B5] hover:bg-[#016293] active:bg-[#01527C] text-white shadow-xs transition-all duration-150 select-none focus:outline-hidden focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#0179B5]"
        >
            Lihat Detail
        </a>
    </div>
</article>
