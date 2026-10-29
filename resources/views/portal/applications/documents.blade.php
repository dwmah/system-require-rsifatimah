<x-layouts.applicant title="Unggah Berkas Persyaratan — {{ $jobApplication->jobVacancy->title }}">
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Breadcrumb & Back Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200">
            <nav class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('portal.home') }}" class="hover:text-rsi-700 transition-colors">Portal</a>
                <span>/</span>
                <a href="{{ route('portal.applications.index') }}" class="hover:text-rsi-700 transition-colors">Lamaran Saya</a>
                <span>/</span>
                <a href="{{ route('portal.applications.show', $jobApplication) }}" class="hover:text-rsi-700 transition-colors">Detail</a>
                <span>/</span>
                <span class="text-slate-900 font-medium">Unggah Berkas</span>
            </nav>

            <a
                href="{{ route('portal.applications.show', $jobApplication) }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-rsi-700 transition-colors self-start sm:self-auto"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Detail</span>
            </a>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert type="error">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert type="error">
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif        {{-- Top Summary & Progress Bar --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4 sm:p-5 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="font-mono text-xs font-bold text-rsi-800 bg-rsi-50 px-2 py-0.5 rounded border border-rsi-100">
                            {{ $jobApplication->jobVacancy->code }}
                        </span>
                        <span class="text-xs text-slate-400">&bull;</span>
                        <span class="text-xs font-medium text-slate-500">{{ $jobApplication->recruitmentPeriod->name }}</span>
                    </div>
                    <h1 class="text-lg sm:text-xl font-bold text-slate-900">
                        Unggah Berkas Persyaratan &mdash; {{ $jobApplication->jobVacancy->title }}
                    </h1>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    @if ($missingRequirements->isEmpty())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Berkas Wajib Lengkap
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            {{ $missingRequirements->count() }} Berkas Wajib Belum Diunggah
                        </span>
                    @endif
                </div>
            </div>

            {{-- Compact Progress & Deadline Row --}}
            @php
                $stage1 = $jobApplication->jobVacancy?->selectionStages()->orderBy('sort_order')->first();
                $deadline = $stage1?->end_at ?? ($jobApplication->jobVacancy->close_at ?? $jobApplication->recruitmentPeriod->registration_end_at);
                $isDeadlineEnded = $jobApplication->isSubmissionDeadlinePassed();
                $daysLeft = ($deadline && ! $isDeadlineEnded) ? (int) ceil(now()->diffInDays($deadline->endOfDay(), false)) : null;
                $requiredCount = $requirements->where('is_required', true)->count();
                $uploadedRequiredCount = $requirements->where('is_required', true)->filter(fn($r) => $uploadedDocuments->has($r->id))->count();
                $progressPercent = $requiredCount > 0 ? round(($uploadedRequiredCount / $requiredCount) * 100) : 100;
            @endphp
            <div class="pt-2.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2 flex-1 max-w-md">
                    <span class="text-[11px] text-slate-500 shrink-0 font-medium">Progres: {{ $uploadedRequiredCount }}/{{ $requiredCount }} Berkas Wajib</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-rsi-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                    </div>
                    <span class="text-[11px] font-bold text-slate-700 shrink-0">{{ $progressPercent }}%</span>
                </div>

                @if ($deadline && $jobApplication->status->value === 'draft')
                    <div class="flex items-center gap-1.5 text-[11px] {{ $isDeadlineEnded ? 'text-rose-700 font-bold' : ($daysLeft !== null && $daysLeft <= 3 ? 'text-amber-700 font-bold' : 'text-slate-500') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Batas Waktu Pengumpulan Berkas: {{ $deadline->translatedFormat('d F Y') }} ({{ $isDeadlineEnded ? 'Batas pengumpulan berakhir' : ($daysLeft === 0 ? 'Hari terakhir!' : 'Sisa ' . $daysLeft . ' hari') }})</span>
                    </div>
                @endif
            </div>
        </div>

        @if ($isDeadlineEnded)
            {{-- Banner Batas Waktu Pengumpulan Berkas Berakhir --}}
            <div class="bg-rose-50 border-2 border-rose-300 rounded-xl p-4 sm:p-5 flex items-start gap-3.5 text-rose-900 shadow-sm">
                <div class="p-2 bg-rose-100 rounded-lg text-rose-600 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm sm:text-base text-rose-900">Batas Pengumpulan Berkas Telah Berakhir</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-200 text-rose-800">
                            Batas pengumpulan berakhir
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-rose-800">
                        Batas pengumpulan berkas telah berakhir. Anda tidak dapat lagi mengunggah atau mengubah berkas persyaratan.
                    </p>
                </div>
            </div>
        @endif

        {{-- Requirements List Container --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-5 py-3 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                        Daftar Berkas Persyaratan ({{ $requirements->count() }})
                    </h2>
                </div>
                <span class="text-[11px] text-slate-500 font-medium">
                    {{ $uploadedDocuments->count() }} dari {{ $requirements->count() }} berkas terunggah
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($requirements as $requirement)
                    @php
                        $uploaded = $uploadedDocuments->get($requirement->id);
                        $allowedTokens = array_filter(array_map('trim', explode(',', $requirement->allowed_mimes)));
                        $formattedAllowed = implode(', ', array_map(function ($token) {
                            return strtoupper(\App\Services\Recruitment\ApplicationDocumentService::mimeToExtension($token) ?: $token);
                        }, $allowedTokens));
                        $maxMb = round($requirement->max_size_kb / 1024, 1);
                    @endphp

                    <div
                        id="requirement-row-{{ $requirement->id }}"
                        class="p-3.5 sm:px-5 sm:py-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3 transition-all duration-300 {{ $uploaded ? 'bg-emerald-50/50 hover:bg-emerald-50/70' : ($requirement->is_required ? 'bg-amber-50/15 hover:bg-amber-50/30' : 'hover:bg-slate-50/60') }}"
                    >
                        {{-- Requirement Info --}}
                        <div class="space-y-0.5 flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900">
                                    {{ $requirement->name }}
                                </h3>
                                @if ($requirement->is_required)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        WAJIB
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        OPSIONAL
                                    </span>
                                @endif

                                @if (str_contains(strtolower($requirement->allowed_mimes), 'video/mp4') || str_contains(strtolower($requirement->allowed_mimes), 'mp4'))
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                        MP4 Video
                                    </span>
                                @endif

                                @if ($uploaded)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                        TERUNGGAH
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-0.5 text-[11px] text-slate-500">
                                @if ($requirement->description)
                                    <span class="text-slate-600">{{ $requirement->description }}</span>
                                    <span>&bull;</span>
                                @endif
                                <span class="text-slate-400 font-mono text-[10px]">
                                    Format: {{ $formattedAllowed }} &bull; Maks: {{ $maxMb }} MB ({{ number_format($requirement->max_size_kb) }} KB)
                                </span>
                            </div>
                        </div>

                        {{-- Action Section --}}
                        <div class="shrink-0">
                            @if ($uploaded)
                                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                    {{-- File badge --}}
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 text-xs max-w-[200px] sm:max-w-[240px]">
                                        <svg class="w-3.5 h-3.5 text-rsi-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A1 1 0 0112 2.586L15.414 6A1 1 0 0116 6.707V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="truncate font-medium text-[11px]" title="{{ $uploaded->original_name }}">
                                            {{ $uploaded->original_name }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono shrink-0">
                                            {{ round($uploaded->file_size / 1024) }}KB
                                        </span>
                                    </div>

                                    {{-- Preview button --}}
                                    <div x-data="{ previewOpen: false }" class="inline">
                                        <button
                                            type="button"
                                            @click="previewOpen = true"
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-rsi-700 hover:bg-slate-100 transition-colors cursor-pointer"
                                            title="Lihat preview berkas"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        {{-- Preview Modal --}}
                                        <div
                                            x-show="previewOpen"
                                            x-cloak
                                            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
                                            @keydown.escape.window="previewOpen = false"
                                        >
                                            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="previewOpen = false"></div>

                                            <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden z-10 border border-slate-200">
                                                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50/80">
                                                    <div>
                                                        <h4 class="text-sm font-bold text-slate-900">{{ $requirement->name }}</h4>
                                                        <p class="text-[11px] text-slate-500 font-mono">{{ $uploaded->original_name }} &bull; {{ round($uploaded->file_size / 1024, 1) }} KB</p>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <a
                                                            href="{{ route('portal.applications.documents.download', [$jobApplication, $uploaded]) }}"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0179B5] text-white hover:bg-[#016293] transition-colors"
                                                            title="Unduh berkas"
                                                        >
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                            </svg>
                                                            <span>Unduh</span>
                                                        </a>
                                                        <button
                                                            type="button"
                                                            @click="previewOpen = false"
                                                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                                                            title="Tutup"
                                                        >
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-slate-100 min-h-[300px]">
                                                    @php
                                                        $previewUrl = route('documents.preview', $uploaded);
                                                        $isMp4 = str_contains(strtolower($uploaded->mime_type ?? ''), 'video') || str_ends_with(strtolower($uploaded->original_name ?? ''), '.mp4');
                                                        $isPdf = str_contains(strtolower($uploaded->mime_type ?? ''), 'pdf') || str_ends_with(strtolower($uploaded->original_name ?? ''), '.pdf');
                                                        $isImg = str_contains(strtolower($uploaded->mime_type ?? ''), 'image') || in_array(strtolower(pathinfo($uploaded->original_name ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']);
                                                    @endphp

                                                    @if ($isMp4)
                                                        <div class="w-full flex flex-col items-center gap-3">
                                                            <video controls class="w-full max-h-[70vh] rounded-lg">
                                                                <source src="{{ $previewUrl }}" type="video/mp4">
                                                                Browser tidak mendukung video preview.
                                                            </video>
                                                            <a href="{{ route('portal.applications.documents.download', [$jobApplication, $uploaded]) }}" class="px-4 py-2 bg-[#0179B5] text-white text-xs font-semibold rounded-lg shadow-sm hover:bg-[#016293] transition-colors inline-flex items-center gap-2">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                                <span>Download Video</span>
                                                            </a>
                                                        </div>
                                                    @elseif ($isPdf)
                                                        <iframe src="{{ $previewUrl }}" class="w-full h-[70vh] rounded-xl border border-slate-300 bg-white"></iframe>
                                                    @elseif ($isImg)
                                                        <img src="{{ $previewUrl }}" alt="{{ $uploaded->original_name }}" class="max-h-[70vh] max-w-full object-contain rounded-lg shadow-md">
                                                    @else
                                                        <div class="text-center p-6 bg-white rounded-xl border border-slate-200">
                                                            <p class="text-xs text-slate-500 mb-3">Pratinjau langsung tidak tersedia untuk tipe berkas ini.</p>
                                                            <a href="{{ route('portal.applications.documents.download', [$jobApplication, $uploaded]) }}" class="px-3 py-1.5 bg-[#0179B5] text-white text-xs font-semibold rounded-lg inline-flex items-center gap-1.5">
                                                                Unduh Berkas
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Download button --}}
                                    <a
                                        href="{{ route('portal.applications.documents.download', [$jobApplication, $uploaded]) }}"
                                        class="p-1.5 rounded-lg text-slate-600 hover:text-rsi-700 hover:bg-slate-100 transition-colors"
                                        title="Unduh berkas"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>

                                    @if ($jobApplication->status->value === 'draft' && $jobApplication->locked_at === null && ! $isDeadlineEnded)
                                        {{-- Ganti berkas: 1-klik buka file picker & langsung auto-unggah --}}
                                        <form
                                            action="{{ route('portal.applications.documents.store', [$jobApplication, $requirement]) }}"
                                            method="POST"
                                            enctype="multipart/form-data"
                                            x-data="{ uploading: false }"
                                            class="inline"
                                        >
                                            @csrf
                                            <input
                                                type="file"
                                                name="file"
                                                x-ref="replaceInput"
                                                accept="{{ $requirement->allowed_mimes }}"
                                                class="sr-only"
                                                @change="if ($event.target.files && $event.target.files.length > 0) {
                                                    const file = $event.target.files[0];
                                                    const maxKb = {{ $requirement->max_size_kb }};
                                                    if (file.size > maxKb * 1024) {
                                                        window.rsiAlert('Ukuran berkas melebihi batas maksimal {{ $maxMb }} MB ({{ number_format($requirement->max_size_kb) }} KB).', 'Ukuran Berkas Melebihi Batas', 'warning');
                                                        $el.value = '';
                                                        return;
                                                    }
                                                    sessionStorage.setItem('last_doc_target', 'requirement-row-{{ $requirement->id }}');
                                                    sessionStorage.setItem('last_doc_scroll_y', window.scrollY);
                                                    uploading = true;
                                                    $el.closest('form').submit();
                                                }"
                                            >
                                            <button
                                                type="button"
                                                @click="$refs.replaceInput.click()"
                                                :disabled="uploading"
                                                :class="uploading ? 'opacity-60 cursor-wait' : 'cursor-pointer hover:bg-slate-50 active:scale-95'"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 transition-all shadow-2xs"
                                                title="Ganti berkas ini dengan memilih berkas baru"
                                            >
                                                <svg x-show="uploading" x-cloak class="animate-spin w-3 h-3 text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                </svg>
                                                <span x-text="uploading ? 'Mengunggah...' : 'Ganti'"></span>
                                            </button>
                                        </form>

                                        {{-- Delete button --}}
                                        <form
                                            id="delete_doc_form_{{ $uploaded->id }}"
                                            action="{{ route('portal.applications.documents.destroy', [$jobApplication, $uploaded]) }}"
                                            method="POST"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                onclick="window.rsiConfirm({
                                                    title: 'Hapus Berkas Persyaratan',
                                                    message: 'Apakah Anda yakin ingin menghapus berkas {{ $requirement->document_name }} ini?',
                                                    confirmText: 'Ya, Hapus Berkas',
                                                    variant: 'danger',
                                                    onConfirm: () => {
                                                        sessionStorage.setItem('last_doc_target', 'requirement-row-{{ $requirement->id }}');
                                                        sessionStorage.setItem('last_doc_scroll_y', window.scrollY);
                                                        document.getElementById('delete_doc_form_{{ $uploaded->id }}').submit();
                                                    }
                                                })"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                                title="Hapus berkas"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                {{-- Auto-Pick & Auto-Upload CTA Button --}}
                                @if ($jobApplication->status->value === 'draft' && $jobApplication->locked_at === null && ! $isDeadlineEnded)
                                    <form
                                        action="{{ route('portal.applications.documents.store', [$jobApplication, $requirement]) }}"
                                        method="POST"
                                        enctype="multipart/form-data"
                                        x-data="{ uploading: false }"
                                        class="inline-flex items-center"
                                    >
                                        @csrf
                                        <input
                                            type="file"
                                            name="file"
                                            x-ref="fileInput"
                                            accept="{{ $requirement->allowed_mimes }}"
                                            class="sr-only"
                                            @change="if ($event.target.files && $event.target.files.length > 0) {
                                                const file = $event.target.files[0];
                                                const maxKb = {{ $requirement->max_size_kb }};
                                                if (file.size > maxKb * 1024) {
                                                    window.rsiAlert('Ukuran berkas melebihi batas maksimal {{ $maxMb }} MB ({{ number_format($requirement->max_size_kb) }} KB).', 'Ukuran Berkas Melebihi Batas', 'warning');
                                                    $el.value = '';
                                                    return;
                                                }
                                                sessionStorage.setItem('last_doc_target', 'requirement-row-{{ $requirement->id }}');
                                                sessionStorage.setItem('last_doc_scroll_y', window.scrollY);
                                                uploading = true;
                                                $el.closest('form').submit();
                                            }"
                                        >
                                        <button
                                            type="button"
                                            @click="$refs.fileInput.click()"
                                            :disabled="uploading"
                                            :class="uploading ? 'opacity-70 cursor-wait' : 'cursor-pointer hover:bg-rsi-800 active:scale-95 shadow-2xs hover:shadow-xs'"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white bg-rsi-700 transition-all select-none"
                                            title="Klik untuk memilih berkas & unggah otomatis"
                                        >
                                            <svg x-show="!uploading" class="w-3.5 h-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            <svg x-show="uploading" x-cloak class="animate-spin w-3.5 h-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <span x-text="uploading ? 'Mengunggah...' : 'Unggah Berkas'"></span>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs {{ $isDeadlineEnded ? 'text-rose-500 font-semibold' : 'text-slate-400 italic' }}">
                                        {{ $isDeadlineEnded ? 'Batas Pengumpulan Berakhir' : 'Terkunci' }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-xs">
                        Tidak ada persyaratan berkas khusus untuk formasi ini.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Bottom Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-white rounded-2xl border border-slate-200 shadow-2xs">
            <a
                href="{{ route('portal.applications.show', $jobApplication) }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors"
            >
                &larr; Kembali ke Step 1 (Formasi)
            </a>

            <div class="flex flex-col sm:flex-row items-center gap-3">
                @if ($missingRequirements->isNotEmpty())
                    <span class="text-xs text-amber-700 font-medium text-center sm:text-right">
                        Lengkapi {{ $missingRequirements->count() }} berkas wajib sebelum mengirim lamaran.
                    </span>

                    <button
                        type="button"
                        disabled
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-slate-200 text-slate-400 cursor-not-allowed shadow-none select-none"
                        title="Unggah seluruh berkas wajib untuk melanjutkan ke Step 3"
                    >
                        <span>Lanjut ke Step 3: Review & Kirim</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                @else
                    <a
                        href="{{ route('portal.applications.review', $jobApplication) }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-rsi-700 hover:bg-rsi-800 text-white transition-colors shadow-2xs"
                    >
                        <span>Lanjut ke Step 3: Review & Kirim</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Script pemulihan posisi scroll setelah unggah / ganti / hapus berkas --}}
    <script>
        (function () {
            const sessionTargetId = @json(session('uploaded_requirement_id') ? 'requirement-row-' . session('uploaded_requirement_id') : (session('deleted_requirement_id') ? 'requirement-row-' . session('deleted_requirement_id') : null));
            const storedTargetId = sessionStorage.getItem('last_doc_target');
            const storedScrollY = sessionStorage.getItem('last_doc_scroll_y');

            const targetId = sessionTargetId || storedTargetId;

            // Bersihkan sessionStorage setelah dibaca
            sessionStorage.removeItem('last_doc_target');
            sessionStorage.removeItem('last_doc_scroll_y');

            if (targetId || storedScrollY !== null) {
                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }

                let hasScrolled = false;

                const performScroll = () => {
                    if (hasScrolled) return;
                    const el = targetId ? document.getElementById(targetId) : null;
                    if (el) {
                        hasScrolled = true;
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.classList.add('bg-emerald-100/80');
                        setTimeout(() => {
                            el.classList.remove('bg-emerald-100/80');
                        }, 2500);
                    } else if (storedScrollY !== null) {
                        hasScrolled = true;
                        window.scrollTo({ top: parseInt(storedScrollY, 10), behavior: 'smooth' });
                    }
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => {
                        setTimeout(performScroll, 50);
                    });
                } else {
                    setTimeout(performScroll, 50);
                }

                window.addEventListener('load', () => {
                    if (!hasScrolled) {
                        performScroll();
                    }
                });
            }
        })();
    </script>
</x-layouts.applicant>
