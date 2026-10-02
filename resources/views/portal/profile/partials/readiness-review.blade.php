@props([
    'percentage' => 0,
    'isComplete' => false,
    'missingItems' => [],
])

<div class="rounded-2xl border p-4 sm:p-5 {{ $isComplete ? 'bg-emerald-50/60 border-emerald-200' : 'bg-amber-50/60 border-amber-200' }}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-2xs {{ $isComplete ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white' }}">
                @if ($isComplete)
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                @else
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                @endif
            </div>

            <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm sm:text-base font-bold {{ $isComplete ? 'text-emerald-950' : 'text-amber-950' }}">
                        {{ $isComplete ? 'Profil Dasar Lengkap' : 'Profil Belum Lengkap' }}
                    </h3>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $isComplete ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $percentage }}% Terpenuhi
                    </span>
                </div>
                <p class="text-xs {{ $isComplete ? 'text-emerald-800' : 'text-amber-800' }}">
                    @if ($isComplete)
                        Data identitas, pas foto, dan riwayat Anda telah siap digunakan untuk pendaftaran lowongan.
                    @else
                        Masih perlu dilengkapi:
                        <span class="font-semibold">{{ implode(', ', $missingItems) }}</span>
                    @endif
                </p>
            </div>
        </div>

        @if ($isComplete)
            <div class="shrink-0 self-end sm:self-center">
                <a href="{{ route('portal.applications.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-xl shadow-xs transition-colors whitespace-nowrap">
                    <span>Lihat Lamaran Saya</span>
                    <span>&rarr;</span>
                </a>
            </div>
        @endif
    </div>
</div>
