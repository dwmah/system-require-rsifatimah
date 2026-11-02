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

    // 3. Palet kelas Tailwind ditulis lengkap agar otomatis terbaca oleh compiler Tailwind CSS v4
    $palettes = [
        'emerald' => [
            'top_border'   => 'border-t-emerald-500',
            'code_badge'   => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
            'unit_badge'   => 'bg-emerald-50/70 text-emerald-800 border-emerald-200/60',
            'unit_icon'    => 'text-emerald-500',
            'hover_border' => 'hover:border-emerald-300',
            'title_hover'  => 'group-hover:text-emerald-700',
        ],
        'sky' => [
            'top_border'   => 'border-t-sky-500',
            'code_badge'   => 'bg-sky-50 text-sky-700 border-sky-200/80',
            'unit_badge'   => 'bg-sky-50/70 text-sky-800 border-sky-200/60',
            'unit_icon'    => 'text-sky-500',
            'hover_border' => 'hover:border-sky-300',
            'title_hover'  => 'group-hover:text-sky-700',
        ],
        'amber' => [
            'top_border'   => 'border-t-amber-500',
            'code_badge'   => 'bg-amber-50 text-amber-800 border-amber-200/80',
            'unit_badge'   => 'bg-amber-50/70 text-amber-800 border-amber-200/60',
            'unit_icon'    => 'text-amber-500',
            'hover_border' => 'hover:border-amber-300',
            'title_hover'  => 'group-hover:text-amber-700',
        ],
        'purple' => [
            'top_border'   => 'border-t-purple-500',
            'code_badge'   => 'bg-purple-50 text-purple-700 border-purple-200/80',
            'unit_badge'   => 'bg-purple-50/70 text-purple-800 border-purple-200/60',
            'unit_icon'    => 'text-purple-500',
            'hover_border' => 'hover:border-purple-300',
            'title_hover'  => 'group-hover:text-purple-700',
        ],
        'cyan' => [
            'top_border'   => 'border-t-cyan-500',
            'code_badge'   => 'bg-cyan-50 text-cyan-700 border-cyan-200/80',
            'unit_badge'   => 'bg-cyan-50/70 text-cyan-800 border-cyan-200/60',
            'unit_icon'    => 'text-cyan-500',
            'hover_border' => 'hover:border-cyan-300',
            'title_hover'  => 'group-hover:text-cyan-700',
        ],
        'indigo' => [
            'top_border'   => 'border-t-indigo-500',
            'code_badge'   => 'bg-indigo-50 text-indigo-700 border-indigo-200/80',
            'unit_badge'   => 'bg-indigo-50/70 text-indigo-800 border-indigo-200/60',
            'unit_icon'    => 'text-indigo-500',
            'hover_border' => 'hover:border-indigo-300',
            'title_hover'  => 'group-hover:text-indigo-700',
        ],
        'slate' => [
            'top_border'   => 'border-t-slate-500',
            'code_badge'   => 'bg-slate-100 text-slate-700 border-slate-300',
            'unit_badge'   => 'bg-slate-100 text-slate-800 border-slate-200',
            'unit_icon'    => 'text-slate-500',
            'hover_border' => 'hover:border-slate-400',
            'title_hover'  => 'group-hover:text-slate-800',
        ],
        'rose' => [
            'top_border'   => 'border-t-rose-500',
            'code_badge'   => 'bg-rose-50 text-rose-700 border-rose-200/80',
            'unit_badge'   => 'bg-rose-50/70 text-rose-800 border-rose-200/60',
            'unit_icon'    => 'text-rose-500',
            'hover_border' => 'hover:border-rose-300',
            'title_hover'  => 'group-hover:text-rose-700',
        ],
        'teal' => [
            'top_border'   => 'border-t-teal-500',
            'code_badge'   => 'bg-teal-50 text-teal-700 border-teal-200/80',
            'unit_badge'   => 'bg-teal-50/70 text-teal-800 border-teal-200/60',
            'unit_icon'    => 'text-teal-500',
            'hover_border' => 'hover:border-teal-300',
            'title_hover'  => 'group-hover:text-teal-700',
        ],
    ];

    $palette = $palettes[$themeKey] ?? $palettes['sky'];
@endphp

<article {{ $attributes->merge(['class' => 'group bg-white rounded-xl border border-slate-200 border-t-4 ' . $palette['top_border'] . ' ' . $palette['hover_border'] . ' shadow-2xs hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden p-5']) }}>
    <div>
        {{-- Header Card: Status Badge & Code --}}
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[11px] font-mono font-semibold px-2.5 py-0.5 rounded-md border {{ $palette['code_badge'] }}">
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
        <h3 class="text-base font-bold text-slate-900 leading-snug tracking-tight mb-2 {{ $palette['title_hover'] }} transition-colors">
            <a href="{{ route('vacancies.show', $vacancy) }}">
                {{ $vacancy->title }}
            </a>
        </h3>

        {{-- Meta Badges / Information --}}
        <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-600 mb-4">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium border {{ $palette['unit_badge'] }}">
                <svg class="w-3.5 h-3.5 {{ $palette['unit_icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                {{ $vacancy->unit }}
            </span>

            <span class="inline-flex items-center gap-1 bg-slate-50 text-slate-700 px-2 py-1 rounded-md border border-slate-100">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ $vacancy->employment_type }}
            </span>

            <span class="inline-flex items-center gap-1 bg-slate-50 text-slate-700 px-2 py-1 rounded-md border border-slate-100">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ $vacancy->hasQuotaLimit() ? $vacancy->quota . ' Formasi' : 'Sesuai Kebutuhan' }}
            </span>
        </div>
    </div>

    {{-- Footer: Deadline & CTA --}}
    <div class="pt-3.5 border-t border-slate-100 flex items-center justify-between gap-2 mt-2">
        <div class="text-xs">
            <span class="text-slate-400 block text-[11px]">Batas Pendaftaran</span>
            <span class="font-medium text-slate-700">
                {{ $vacancy->close_at->translatedFormat('d M Y') }}
            </span>
        </div>

        <x-ui.button
            :href="route('vacancies.show', $vacancy)"
            variant="outline"
            size="sm"
        >
            Lihat Detail
        </x-ui.button>
    </div>
</article>
