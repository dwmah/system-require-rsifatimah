@props([
    'percentage' => 0,
    'isComplete' => false,
    'missingItems' => [],
])

<div class="rounded-2xl border p-6 sm:p-7 {{ $isComplete ? 'bg-emerald-50/50 border-emerald-200' : 'bg-amber-50/50 border-amber-200' }}">
    <div class="flex items-start gap-4">
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

        <div class="space-y-2 flex-1">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <h3 class="text-base font-bold {{ $isComplete ? 'text-emerald-900' : 'text-amber-900' }}">
                    Ringkasan Kesiapan Profil
                </h3>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $isComplete ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $percentage }}% Terpenuhi
                </span>
            </div>

            @if ($isComplete)
                <p class="text-xs sm:text-sm text-emerald-800 leading-relaxed">
                    <strong>Profil dasar Anda telah lengkap.</strong> Seluruh data identitas, kontak, dan riwayat pendidikan formal telah siap digunakan untuk proses pendaftaran lowongan.
                </p>
                <div class="p-3 bg-white/80 rounded-xl border border-emerald-200/60 text-xs text-slate-600 mt-2">
                    <p class="font-medium text-slate-700 mb-0.5">Catatan Persyaratan Lanjutan:</p>
                    <p>Persyaratan khusus seperti STR/lisensi, sertifikasi keahlian, pengalaman kerja, dan kelengkapan dokumen pendukung akan mengikuti formasi lowongan yang Anda pilih pada tahapan pendaftaran.</p>
                </div>
                <div class="pt-2">
                    <x-ui.button href="{{ route('vacancies.index') }}" variant="primary" size="sm">
                        Jelajahi Lowongan Terbuka
                    </x-ui.button>
                </div>
            @else
                <p class="text-xs sm:text-sm text-amber-800 leading-relaxed">
                    Profil Anda belum mencapai 100%. Mohon lengkapi bagian berikut agar akun siap digunakan untuk melamar:
                </p>
                <div class="p-3 bg-white/80 rounded-xl border border-amber-200/60 text-xs text-amber-900 space-y-1 mt-2">
                    <p class="font-semibold text-amber-950 mb-1">Masih perlu dilengkapi:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-amber-900">
                        @foreach ($missingItems as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
