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

        {{-- Active Stage Deadline Reminder / Verification Status Banner --}}
        @php
            $currentActiveStage = collect($trackingData['stages'] ?? [])->firstWhere('is_current', true);
            $isSubmitted = $jobApplication->status === \App\Enums\JobApplicationStatus::SUBMITTED;
            $hasApplicantAction = $currentActiveStage && (
                !empty($currentActiveStage['requires_action']) ||
                (!empty($currentActiveStage['applicant_message']) && in_array($currentActiveStage['public_state'], ['in_progress', 'pending'], true))
            );
        @endphp

        @if ($isSubmitted)
            {{-- Banner Status Lamaran Terkirim & Antrean Verifikasi Panitia --}}
            <div class="p-5 rounded-2xl border bg-sky-50/80 border-sky-200 text-sky-950 shadow-2xs space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 font-bold text-sm text-sky-900">
                        <div class="w-7 h-7 rounded-lg bg-sky-100 text-rsi-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span>Lamaran Terkirim &bull; Dalam Antrean Verifikasi Panitia</span>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200 self-start sm:self-auto">
                        Antrean Verifikasi
                    </span>
                </div>
                <p class="text-xs text-sky-800 leading-relaxed pl-0 sm:pl-9.5">
                    Berkas lamaran Anda telah berhasil diterima oleh sistem dan saat ini sedang menunggu antrean verifikasi berkas oleh tim panitia rekrutmen RSI Fatimah.
                    @if ($currentActiveStage && $currentActiveStage['end_at'])
                        Tahapan <strong>{{ $currentActiveStage['name'] }}</strong> dijadwalkan berlangsung hingga <strong>{{ $currentActiveStage['end_at']->translatedFormat('d F Y') }}</strong>.
                    @endif
                    Hasil seleksi akan dipublikasikan resmi melalui portal ini. Anda tidak perlu mengunggah ulang dokumen, silakan pantau pembaruan status secara berkala.
                </p>
            </div>
        @elseif ($hasApplicantAction && $currentActiveStage && $currentActiveStage['end_at'])
            {{-- Banner Pengingat Batas Waktu Hanya Jika Ada Aksi yang Harus Dilakukan Pelamar --}}
            <div class="p-5 rounded-2xl border {{ $currentActiveStage['is_deadline_approaching'] ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-sky-50 border-sky-200 text-sky-900' }} shadow-2xs space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <svg class="w-5 h-5 {{ $currentActiveStage['is_deadline_approaching'] ? 'text-amber-600' : 'text-[#0179B5]' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Pengingat Batas Waktu: {{ $currentActiveStage['name'] }}</span>
                    </div>
                    @if ($currentActiveStage['days_remaining'] !== null && $currentActiveStage['days_remaining'] >= 0)
                        <span class="px-3 py-1 text-xs font-extrabold rounded-full {{ $currentActiveStage['is_deadline_approaching'] ? 'bg-amber-200 text-amber-900' : 'bg-sky-200 text-sky-900' }} self-start sm:self-auto">
                            {{ $currentActiveStage['days_remaining'] === 0 ? 'Hari Terakhir!' : 'Sisa ' . $currentActiveStage['days_remaining'] . ' Hari Lagi' }}
                        </span>
                    @elseif ($currentActiveStage['is_past_deadline'])
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-slate-200 text-slate-700 self-start sm:self-auto">
                            Batas Waktu Berakhir
                        </span>
                    @endif
                </div>
                <p class="text-xs {{ $currentActiveStage['is_deadline_approaching'] ? 'text-amber-800' : 'text-sky-800' }} leading-relaxed">
                    Batas waktu penyelesaian untuk tahapan ini adalah <strong>{{ $currentActiveStage['end_at']->translatedFormat('d F Y') }}</strong>. Pastikan Anda menyelesaikan instruksi dan ketentuan tahapan ini tepat waktu.
                </p>
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
                            $isCurrent = !empty($stage['is_current']);

                            $circleClass = match (true) {
                                $isPassed => 'bg-emerald-600 text-white ring-4 ring-emerald-100',
                                $isFailed => 'bg-red-600 text-white ring-4 ring-red-100',
                                $isCurrent || $isInProgress => 'bg-rsi-700 text-white ring-4 ring-rsi-100 shadow-sm',
                                $isPending => 'bg-amber-500 text-white ring-4 ring-amber-100',
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
                            <div class="{{ $isCurrent ? 'bg-white rounded-xl border border-sky-300 ring-1 ring-sky-200/60 shadow-2xs' : 'bg-slate-50/70 hover:bg-slate-50 rounded-xl border border-slate-200' }} p-4 sm:p-5 transition-colors space-y-2.5">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-sm font-bold text-slate-900">
                                            {{ $stage['name'] }}
                                        </h3>
                                        @if ($stage['is_current'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-[#0179B5] border border-sky-200">
                                                Tahap Aktif
                                            </span>
                                        @endif
                                    </div>

                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold border whitespace-nowrap shrink-0 {{ $stage['badge_class'] }} self-start sm:self-auto">
                                        {{ $stage['label'] }}
                                    </span>
                                </div>

                                @if ($stage['description'])
                                    <p class="text-xs text-slate-600 leading-relaxed">
                                        {{ $stage['description'] }}
                                    </p>
                                @endif

                                {{-- Schedule & Deadline Display --}}
                                @if ($stage['start_at'] || $stage['end_at'])
                                    <div class="flex flex-wrap items-center gap-2 pt-1">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium bg-white border border-slate-200 text-slate-700 shadow-2xs">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <span>
                                                @if ($stage['start_at'] && $stage['end_at'])
                                                    {{ $stage['start_at']->translatedFormat('d M Y') }} &ndash; {{ $stage['end_at']->translatedFormat('d M Y') }}
                                                @elseif ($stage['end_at'])
                                                    Batas Akhir: {{ $stage['end_at']->translatedFormat('d M Y') }}
                                                @elseif ($stage['start_at'])
                                                    Mulai: {{ $stage['start_at']->translatedFormat('d M Y') }}
                                                @endif
                                            </span>
                                        </div>

                                        @if ($stage['is_current'] && $stage['end_at'] && ! $stage['is_past_deadline'] && $stage['days_remaining'] !== null && !empty($stage['requires_action']))
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $stage['is_deadline_approaching'] ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                                {{ $stage['days_remaining'] === 0 ? 'Hari Terakhir!' : 'Sisa ' . $stage['days_remaining'] . ' Hari' }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Safe Applicant Message (Only if published) --}}
                                @if ($stage['applicant_message'])
                                    <div class="p-3 bg-white rounded-lg border border-slate-200 text-xs text-slate-700 leading-relaxed space-y-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pesan Panitia:</span>
                                        <p>{{ $stage['applicant_message'] }}</p>
                                    </div>
                                @endif

                                {{-- Stage Attachment / Test Result Document --}}
                                @if (!empty($stage['has_attachment']) && !empty($stage['attachment_url']))
                                    @php
                                        $attachmentName = $stage['attachment_name'] ?? 'Lampiran';
                                        $attachmentMime = strtolower($stage['attachment_mime'] ?? '');
                                        $isPdf = str_contains($attachmentMime, 'pdf') || str_ends_with(strtolower($attachmentName), '.pdf');
                                        $isImg = str_contains($attachmentMime, 'image') || in_array(strtolower(pathinfo($attachmentName, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']);
                                        $previewUrl = $stage['attachment_url'] . (str_contains($stage['attachment_url'], '?') ? '&' : '?') . 'preview=1';
                                        $downloadUrl = $stage['attachment_url'];
                                    @endphp
                                    <div x-data="{ showPreviewModal: false }" class="p-3 bg-sky-50/70 border border-sky-200 rounded-lg flex items-center justify-between gap-3 flex-wrap">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-8 h-8 rounded-lg bg-sky-100 text-[#0179B5] flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-slate-800">Lampiran Hasil Seleksi</p>
                                                <p class="text-[11px] text-slate-500 truncate max-w-xs">{{ $stage['attachment_name'] }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" @click="showPreviewModal = true"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-medium rounded-lg shadow-xs transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                <span>Lihat / Unduh Lampiran Hasil Tes (PDF/Gambar)</span>
                                            </button>
                                        </div>

                                        {{-- Modal Pratinjau / Review Sebelum Unduh --}}
                                        <div x-show="showPreviewModal" x-cloak
                                             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
                                             @keydown.escape.window="showPreviewModal = false">
                                            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showPreviewModal = false"></div>

                                            <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[92vh] flex flex-col overflow-hidden z-10 border border-slate-200">
                                                {{-- Modal Header --}}
                                                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50/80">
                                                    <div class="min-w-0 pr-3">
                                                        <h4 class="text-sm font-bold text-slate-900 truncate">Pratinjau Hasil: {{ $stage['name'] }}</h4>
                                                        <p class="text-[11px] text-slate-500 font-mono truncate">{{ $stage['attachment_name'] }}</p>
                                                    </div>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <a href="{{ $downloadUrl }}"
                                                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-[#0179B5] text-white hover:bg-[#016293] shadow-xs transition-colors"
                                                           title="Unduh Berkas Hasil Seleksi">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                            <span>Unduh Berkas</span>
                                                        </a>
                                                        <button type="button" @click="showPreviewModal = false"
                                                                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                                                                title="Tutup">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Modal Body (Preview Viewer) --}}
                                                <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-slate-100 min-h-[350px]">
                                                    @if ($isPdf)
                                                        <iframe src="{{ $previewUrl }}" class="w-full h-[72vh] rounded-xl border border-slate-300 bg-white shadow-xs"></iframe>
                                                    @elseif ($isImg)
                                                        <div class="w-full flex items-center justify-center">
                                                            <img src="{{ $previewUrl }}" alt="{{ $stage['attachment_name'] }}" class="max-h-[72vh] max-w-full object-contain rounded-lg shadow-md bg-white p-1">
                                                        </div>
                                                    @else
                                                        <div class="text-center p-6 bg-white rounded-xl border border-slate-200">
                                                            <p class="text-xs text-slate-500 mb-3">Pratinjau visual tidak dapat ditampilkan langsung untuk format file ini.</p>
                                                            <a href="{{ $downloadUrl }}" class="px-4 py-2 bg-[#0179B5] text-white text-xs font-semibold rounded-lg inline-flex items-center gap-1.5">
                                                                Unduh File Lampiran
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Modal Footer --}}
                                                <div class="px-5 py-3 border-t border-slate-100 bg-white flex items-center justify-between text-xs text-slate-500">
                                                    <span>Diunggah resmi oleh Panitia Rekrutmen RSI Fatimah Banyuwangi</span>
                                                    <div class="flex items-center gap-2">
                                                        <button type="button" @click="showPreviewModal = false" class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer">
                                                            Tutup
                                                        </button>
                                                        <a href="{{ $downloadUrl }}" class="px-3.5 py-1.5 rounded-lg bg-[#0179B5] text-white font-semibold hover:bg-[#016293] transition-colors inline-flex items-center gap-1.5">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                            <span>Unduh File</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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

    </div>
</x-layouts.applicant>
