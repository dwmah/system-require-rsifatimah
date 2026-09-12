<x-layouts.applicant title="Status Seleksi — {{ $jobApplication->jobVacancy->title }}">
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Breadcrumb & Back Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200">
            <nav class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('portal.home') }}" class="hover:text-rsi-700 transition-colors">Portal</a>
                <span>/</span>
                <a href="{{ route('portal.applications.index') }}" class="hover:text-rsi-700 transition-colors">Lamaran Saya</a>
                <span>/</span>
                <a href="{{ route('portal.applications.show', $jobApplication) }}" class="hover:text-rsi-700 transition-colors">Detail</a>
                <span>/</span>
                <span class="text-slate-900 font-medium">Tracking Seleksi</span>
            </nav>

            <a
                href="{{ route('portal.applications.show', $jobApplication) }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-rsi-700 transition-colors self-start sm:self-auto"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Detail Lamaran</span>
            </a>
        </div>

        {{-- Flash Alerts --}}
        @if (session('info'))
            <x-ui.alert type="info">
                {{ session('info') }}
            </x-ui.alert>
        @endif

        {{-- Application Status Header Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs font-bold text-rsi-800 bg-rsi-50 px-2.5 py-0.5 rounded border border-rsi-100">
                        {{ $jobApplication->jobVacancy->code }}
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs font-medium text-slate-500">
                        {{ $jobApplication->recruitmentPeriod->name }}
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">
                    {{ $jobApplication->jobVacancy->title }}
                </h1>
                <p class="text-xs text-slate-500">
                    Unit: {{ $jobApplication->jobVacancy->unit }} &bull; Penempatan: {{ $jobApplication->jobVacancy->location }}
                </p>
            </div>

            <div class="flex flex-col md:items-end gap-2 shrink-0 pt-4 md:pt-0 border-t md:border-t-0 border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-medium">Status Lamaran:</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $publicStatus['badge_class'] }}">
                        {{ $publicStatus['label'] }}
                    </span>
                </div>
                <div class="text-xs text-slate-600">
                    Nomor: <strong class="font-mono text-rsi-700">{{ $jobApplication->application_number }}</strong>
                </div>
                @if ($jobApplication->submitted_at)
                    <div class="text-[11px] text-slate-400">
                        Dikirim: {{ $jobApplication->submitted_at->translatedFormat('d M Y, H:i') }} WIB
                    </div>
                @endif
            </div>
        </div>

        {{-- Final Result Banner (If Published) --}}
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

        {{-- Selection Process Timeline --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Alur & Tahapan Seleksi
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Progres tahapan seleksi yang telah diverifikasi dan diumumkan oleh panitia rekrutmen.
                    </p>
                </div>
            </div>

            @if (! $trackingData['has_snapshot'])
                <div class="p-5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 space-y-1">
                    <p class="font-bold">Pemberitahuan</p>
                    <p>
                        Data konfigurasi tahapan seleksi belum dapat dimuat untuk lamaran ini. Silakan hubungi panitia seleksi jika kendala berlanjut.
                    </p>
                </div>
            @elseif (empty($trackingData['stages']))
                <div class="p-8 text-center bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-500">
                    Belum ada tahapan seleksi yang dikonfigurasi untuk formasi ini.
                </div>
            @else
                <ol class="relative border-l-2 border-slate-200 ml-4 sm:ml-6 space-y-8 my-2">
                    @foreach ($trackingData['stages'] as $index => $stage)
                        @php
                            $isPassed = $stage['public_state'] === 'passed';
                            $isFailed = $stage['public_state'] === 'failed';
                            $isInProgress = $stage['public_state'] === 'in_progress';
                            $isPending = $stage['public_state'] === 'pending';
                            $isNotContinued = $stage['public_state'] === 'not_continued';
                            $isWaiting = $stage['public_state'] === 'waiting';

                            $circleClass = match ($stage['public_state']) {
                                'passed' => 'bg-emerald-600 text-white ring-4 ring-emerald-100',
                                'failed' => 'bg-red-600 text-white ring-4 ring-red-100',
                                'in_progress' => 'bg-blue-600 text-white ring-4 ring-blue-100',
                                'pending' => 'bg-amber-500 text-white ring-4 ring-amber-100',
                                default => 'bg-slate-300 text-slate-600 ring-4 ring-slate-100',
                            };
                        @endphp

                        <li
                            class="ml-6 sm:ml-8"
                            @if ($stage['is_current']) aria-current="step" @endif
                        >
                            {{-- Step Marker Icon --}}
                            <span class="absolute -left-3.5 sm:-left-4 flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $circleClass }} text-xs font-bold shadow-2xs">
                                @if ($isPassed)
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @elseif ($isFailed)
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @elseif ($isInProgress)
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                                    </span>
                                @else
                                    <span>{{ $index + 1 }}</span>
                                @endif
                            </span>

                            {{-- Step Content Card --}}
                            <div class="bg-slate-50/70 hover:bg-slate-50 rounded-xl border border-slate-200 p-4 sm:p-5 transition-colors space-y-2">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-sm font-bold text-slate-900">
                                            {{ $stage['name'] }}
                                        </h3>
                                        @if ($stage['is_current'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                Tahap Aktif
                                            </span>
                                        @endif
                                    </div>

                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold border {{ $stage['badge_class'] }} self-start sm:self-auto">
                                        {{ $stage['label'] }}
                                    </span>
                                </div>

                                @if ($stage['description'])
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        {{ $stage['description'] }}
                                    </p>
                                @endif

                                {{-- Safe Applicant Message (Only if published) --}}
                                @if ($stage['applicant_message'])
                                    <div class="p-3 bg-white rounded-lg border border-slate-200 text-xs text-slate-700 leading-relaxed space-y-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pesan Panitia:</span>
                                        <p>{{ $stage['applicant_message'] }}</p>
                                    </div>
                                @endif

                                {{-- Publication Date (Only if published) --}}
                                @if ($stage['published_at'])
                                    <div class="text-[11px] text-slate-400 pt-1">
                                        Diinformasikan pada: {{ $stage['published_at']->translatedFormat('d F Y, H:i') }} WIB
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        {{-- Bottom Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-5 bg-white rounded-2xl border border-slate-200 shadow-2xs">
            <a
                href="{{ route('portal.applications.show', $jobApplication) }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors"
            >
                &larr; Kembali ke Detail Lamaran
            </a>

            <a
                href="{{ route('portal.applications.index') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-rsi-700 bg-rsi-50 hover:bg-rsi-100 transition-colors"
            >
                Lihat Seluruh Lamaran Saya &rarr;
            </a>
        </div>
    </div>
</x-layouts.applicant>
