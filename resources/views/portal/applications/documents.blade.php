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
        @endif

        {{-- Top Summary Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono text-xs font-bold text-rsi-800 bg-rsi-50 px-2 py-0.5 rounded border border-rsi-100">
                        {{ $jobApplication->jobVacancy->code }}
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs text-slate-500">{{ $jobApplication->recruitmentPeriod->name }}</span>
                </div>
                <h1 class="text-xl font-bold text-slate-900">
                    Unggah Berkas Persyaratan
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Formasi: <strong>{{ $jobApplication->jobVacancy->title }}</strong> &bull; Unit: {{ $jobApplication->jobVacancy->unit }}
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                @if ($missingRequirements->isEmpty())
                    <div class="px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Berkas Wajib Lengkap
                    </div>
                @else
                    <div class="px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        {{ $missingRequirements->count() }} Berkas Wajib Belum Diunggah
                    </div>
                @endif
            </div>
        </div>

        {{-- Deadline Reminder Alert --}}
        @php
            $deadline = $jobApplication->jobVacancy->close_at ?? $jobApplication->recruitmentPeriod->registration_end_at;
            $daysLeft = $deadline ? (int) ceil(now()->diffInDays($deadline->endOfDay(), false)) : null;
        @endphp

        @if ($deadline && $jobApplication->status->value === 'draft')
            <div class="p-4 rounded-2xl border {{ $daysLeft !== null && $daysLeft <= 3 ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-sky-50 border-sky-200 text-sky-900' }} shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl {{ $daysLeft !== null && $daysLeft <= 3 ? 'bg-amber-100 text-amber-700' : 'bg-[#0179B5]/10 text-[#0179B5]' }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="text-xs">
                        <span class="font-bold block">Batas Waktu Pengisian & Unggah Berkas: {{ $deadline->translatedFormat('d F Y') }} (23:59 WIB)</span>
                        <span class="{{ $daysLeft !== null && $daysLeft <= 3 ? 'text-amber-800' : 'text-sky-800' }}">Pastikan seluruh berkas wajib diunggah dan lamaran difinalisasi sebelum batas waktu berakhir.</span>
                    </div>
                </div>

                @if ($daysLeft !== null && $daysLeft >= 0)
                    <span class="px-3 py-1 text-xs font-extrabold rounded-full {{ $daysLeft <= 3 ? 'bg-amber-200 text-amber-900' : 'bg-sky-200 text-sky-900' }} shrink-0 self-start sm:self-auto">
                        {{ $daysLeft === 0 ? 'Hari Terakhir!' : 'Sisa ' . $daysLeft . ' Hari Lagi' }}
                    </span>
                @endif
            </div>
        @endif

        {{-- Application Workflow Stepper --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                {{-- STEP 1: Formasi --}}
                <a href="{{ route('portal.applications.show', $jobApplication) }}" class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-xs font-bold">1</span>
                        <span class="text-[11px] font-semibold text-rsi-700 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            Terkunci
                        </span>
                    </div>
                    <div class="text-xs font-bold text-slate-800">Formasi & Profil</div>
                </a>

                {{-- STEP 2: Unggah Berkas --}}
                <div class="p-3.5 rounded-xl border-2 border-rsi-600 bg-rsi-50/50">
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rsi-700 text-white text-xs font-bold">2</span>
                        <span class="text-[11px] font-bold text-rsi-800">Sedang Aktif</span>
                    </div>
                    <div class="text-xs font-bold text-slate-900">Unggah Berkas Persyaratan</div>
                </div>

                {{-- STEP 3: Review & Kirim --}}
                <a href="{{ route('portal.applications.review', $jobApplication) }}" class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-xs font-bold">3</span>
                        <span class="text-[11px] font-semibold text-slate-400">Selanjutnya</span>
                    </div>
                    <div class="text-xs font-bold text-slate-700">Review & Final Submit</div>
                </a>
            </div>
        </div>

        {{-- Requirements List --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    Daftar Dokumen yang Dibutuhkan ({{ $requirements->count() }})
                </h2>
                <span class="text-xs text-slate-500">
                    Format & batas ukuran sesuai ketentuan formasi
                </span>
            </div>

            @forelse ($requirements as $requirement)
                @php
                    $uploaded = $uploadedDocuments->get($requirement->id);
                    $allowedTokens = array_filter(array_map('trim', explode(',', $requirement->allowed_mimes)));
                    $formattedAllowed = implode(', ', array_map(function ($token) {
                        return strtoupper(\App\Services\Recruitment\ApplicationDocumentService::mimeToExtension($token) ?: $token);
                    }, $allowedTokens));
                    $maxMb = round($requirement->max_size_kb / 1024, 1);
                @endphp

                <div class="bg-white rounded-2xl border {{ $uploaded ? 'border-slate-200' : ($requirement->is_required ? 'border-amber-200 bg-amber-50/20' : 'border-slate-200') }} shadow-2xs p-5 sm:p-6 transition-all">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        {{-- Requirement Info --}}
                        <div class="space-y-1.5 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900">
                                    {{ $requirement->name }}
                                </h3>
                                @if ($requirement->is_required)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                        WAJIB
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        OPSIONAL
                                    </span>
                                @endif

                                @if ($uploaded)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                        TERUNGGAH
                                    </span>
                                @endif
                            </div>

                            @if ($requirement->description)
                                <p class="text-xs text-slate-600 leading-relaxed">
                                    {{ $requirement->description }}
                                </p>
                            @endif

                            <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500 pt-1">
                                <span>Format: <strong class="text-slate-700">{{ $formattedAllowed }}</strong></span>
                                <span>&bull;</span>
                                <span>Maksimal: <strong class="text-slate-700">{{ $maxMb }} MB</strong> ({{ number_format($requirement->max_size_kb) }} KB)</span>
                            </div>
                        </div>

                        {{-- Upload / File Status Card --}}
                        <div class="lg:w-80 shrink-0">
                            @if ($uploaded)
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-rsi-100 text-rsi-800 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-semibold text-slate-900 truncate" title="{{ $uploaded->original_name }}">
                                                {{ $uploaded->original_name }}
                                            </div>
                                            <div class="text-[10px] text-slate-500">
                                                {{ round($uploaded->file_size / 1024, 1) }} KB &bull; {{ $uploaded->updated_at->translatedFormat('d M Y, H:i') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-1 border-t border-slate-200/60">
                                        {{-- Download Button --}}
                                        <a
                                            href="{{ route('portal.applications.documents.download', [$jobApplication, $uploaded]) }}"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-rsi-700 transition-colors shadow-2xs"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Unduh
                                        </a>

                                        @if ($jobApplication->status->value === 'draft' && $jobApplication->locked_at === null)
                                            {{-- Delete Button --}}
                                            <form
                                                action="{{ route('portal.applications.documents.destroy', [$jobApplication, $uploaded]) }}"
                                                method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas ini?');"
                                                class="inline"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-red-200 text-red-600 hover:bg-red-50 transition-colors shadow-2xs"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    @if ($jobApplication->status->value === 'draft' && $jobApplication->locked_at === null)
                                        {{-- Replace Form --}}
                                        <div class="pt-2 border-t border-slate-200/60">
                                            <form
                                                action="{{ route('portal.applications.documents.store', [$jobApplication, $requirement]) }}"
                                                method="POST"
                                                enctype="multipart/form-data"
                                                class="space-y-2"
                                            >
                                                @csrf
                                                <label class="block text-[11px] font-medium text-slate-600">
                                                    Ganti berkas dengan file baru:
                                                </label>
                                                <div class="flex items-center gap-1.5">
                                                    <input
                                                        type="file"
                                                        name="file"
                                                        required
                                                        class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-rsi-50 file:text-rsi-700 hover:file:bg-rsi-100 cursor-pointer"
                                                    >
                                                    <button
                                                        type="submit"
                                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-rsi-700 text-white hover:bg-rsi-800 transition-colors shrink-0 shadow-2xs"
                                                    >
                                                        Ganti
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Upload Form for new file --}}
                                @if ($jobApplication->status->value === 'draft' && $jobApplication->locked_at === null)
                                    <form
                                        action="{{ route('portal.applications.documents.store', [$jobApplication, $requirement]) }}"
                                        method="POST"
                                        enctype="multipart/form-data"
                                        class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-300 space-y-3"
                                    >
                                        @csrf
                                        <div class="space-y-1">
                                            <label class="block text-xs font-semibold text-slate-700">
                                                Pilih Berkas:
                                            </label>
                                            <input
                                                type="file"
                                                name="file"
                                                required
                                                class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-rsi-50 file:text-rsi-700 hover:file:bg-rsi-100 cursor-pointer"
                                            >
                                        </div>

                                        <button
                                            type="submit"
                                            class="w-full py-1.5 px-3 rounded-lg text-xs font-bold bg-rsi-700 text-white hover:bg-rsi-800 transition-colors flex items-center justify-center gap-1.5 shadow-2xs"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            Unggah Berkas
                                        </button>
                                    </form>
                                @else
                                    <div class="p-3 bg-slate-100 rounded-xl text-center text-xs text-slate-500">
                                        Lamaran telah dikunci. Berkas tidak dapat diunggah.
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 bg-white rounded-2xl border border-slate-200 text-center text-slate-500 text-xs">
                    Tidak ada persyaratan berkas khusus untuk formasi ini.
                </div>
            @endforelse
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
                @endif

                <a
                    href="{{ route('portal.applications.review', $jobApplication) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold {{ $missingRequirements->isEmpty() ? 'bg-rsi-700 hover:bg-rsi-800 text-white' : 'bg-slate-700 hover:bg-slate-800 text-white' }} transition-colors shadow-2xs"
                >
                    <span>Lanjut ke Step 3: Review & Kirim</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</x-layouts.applicant>
