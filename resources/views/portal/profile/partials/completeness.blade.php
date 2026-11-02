@props([
    'percentage' => 0,
    'isComplete' => false,
    'details' => [],
    'missingItems' => [],
])

<div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Kelengkapan</span>
                <span class="text-xs text-slate-300">&bull;</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $isComplete ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $percentage }}% Selesai
                </span>
            </div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">
                {{ $isComplete ? 'Profil Dasar Lengkap' : 'Lengkapi Identitas Dasar Anda' }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $isComplete ? 'Seluruh kriteria dasar telah terpenuhi. Anda siap mengajukan lamaran pekerjaan.' : 'Terdapat ' . count($missingItems) . ' data dasar yang masih perlu dilengkapi.' }}
            </p>
        </div>

        <div class="text-right sm:text-right">
            <span class="text-3xl font-extrabold font-mono {{ $isComplete ? 'text-emerald-600' : 'text-rsi-700' }}">
                {{ $percentage }}<span class="text-sm font-sans font-semibold text-slate-400">%</span>
            </span>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden mb-6">
        <div
            class="h-full rounded-full transition-all duration-500 {{ $isComplete ? 'bg-emerald-500' : 'bg-rsi-600' }}"
            style="width: {{ $percentage }}%"
            role="progressbar"
            aria-valuenow="{{ $percentage }}"
            aria-valuemin="0"
            aria-valuemax="100"
        ></div>
    </div>

    {{-- Checklist Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-2.5 pt-4 border-t border-slate-100 text-xs">
        @php
            $items = [
                'photo' => 'Pas Foto 3x4',
                'name' => 'Nama Lengkap',
                'nickname' => 'Nama Panggilan',
                'nik' => 'NIK Terdaftar',
                'email' => 'Alamat Email',
                'phone' => 'Nomor HP/WA',
                'birth_place' => 'Tempat Lahir',
                'birth_date' => 'Tanggal Lahir',
                'gender' => 'Jenis Kelamin',
                'address_ktp' => 'Alamat KTP',
                'address_domicile' => 'Alamat Domisili',
                'education' => 'Min. 1 Pendidikan',
            ];
        @endphp

        @foreach ($items as $key => $label)
            @php $done = $details[$key] ?? false; @endphp
            <div class="flex items-center gap-2 p-2 rounded-lg {{ $done ? 'bg-emerald-50/60 text-emerald-800' : 'bg-slate-50 text-slate-500' }}">
                @if ($done)
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                @else
                    <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle cx="12" cy="12" r="9" stroke-width="2" />
                    </svg>
                @endif
                <span class="truncate font-medium text-[11px]">{{ $label }}</span>
            </div>
        @endforeach
    </div>
</div>
