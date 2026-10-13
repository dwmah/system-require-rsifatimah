<x-layouts.applicant title="Review & Kirim Lamaran — {{ $jobApplication->jobVacancy->title }}">
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Breadcrumb & Back Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200">
            <nav class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('portal.home') }}" class="hover:text-rsi-700 transition-colors">Portal</a>
                <span>/</span>
                <a href="{{ route('portal.applications.index') }}" class="hover:text-rsi-700 transition-colors">Lamaran Saya</a>
                <span>/</span>
                <a href="{{ route('portal.applications.documents.index', $jobApplication) }}" class="hover:text-rsi-700 transition-colors">Unggah Berkas</a>
                <span>/</span>
                <span class="text-slate-900 font-medium">Review & Kirim</span>
            </nav>

            <a
                href="{{ route('portal.applications.documents.index', $jobApplication) }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-rsi-700 transition-colors self-start sm:self-auto"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Unggah Berkas</span>
            </a>
        </div>

        {{-- Flash Alerts --}}
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
                    Review & Konfirmasi Final Lamaran
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Silakan tinjau kembali data diri dan kelengkapan berkas Anda sebelum mengirimkan lamaran secara resmi.
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-2">
                <span class="text-xs text-slate-500">Status Saat Ini:</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    {{ $jobApplication->status->label() }}
                </span>
            </div>
        </div>


        {{-- Verification Status Alerts --}}
        @php
            $canSubmit = $isProfileComplete && $isVacancyOpen && $missingRequirements->isEmpty();
        @endphp

        @if (! $canSubmit)
            <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-2">
                <div class="flex items-center gap-2 font-bold text-sm text-amber-800">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Persyaratan Belum Lengkap untuk Final Submit</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs text-amber-800 pl-2">
                    @if (! $isProfileComplete)
                        <li>
                            Profil dasar Anda belum 100% lengkap.
                            <a href="{{ route('profile.show') }}" class="underline font-semibold text-amber-900">Lengkapi profil sekarang &rarr;</a>
                        </li>
                    @endif
                    @if (! $isVacancyOpen)
                        <li>Pendaftaran untuk formasi ini sedang ditutup atau telah melewati batas waktu pendaftaran.</li>
                    @endif
                    @if ($missingRequirements->isNotEmpty())
                        <li>
                            Masih terdapat {{ $missingRequirements->count() }} berkas persyaratan wajib yang belum diunggah:
                            <strong>{{ $missingRequirements->pluck('name')->join(', ') }}</strong>.
                            <a href="{{ route('portal.applications.documents.index', $jobApplication) }}" class="underline font-semibold text-amber-900">Unggah berkas sekarang &rarr;</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        {{-- Review Section 1: Formasi Lowongan --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    1. Formasi Lowongan yang Dilamar
                </h2>
                <span class="text-[11px] font-semibold text-slate-500">Terkunci</span>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 block">Nama Formasi:</span>
                    <strong class="text-slate-900 text-sm">{{ $jobApplication->jobVacancy->title }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Kode Formasi:</span>
                    <strong class="font-mono text-slate-900">{{ $jobApplication->jobVacancy->code }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Periode Rekrutmen:</span>
                    <span class="text-slate-800">{{ $jobApplication->recruitmentPeriod->name }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Unit Kerja:</span>
                    <span class="text-slate-800">{{ $jobApplication->jobVacancy->unit }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Penempatan:</span>
                    <span class="text-slate-800">{{ $jobApplication->jobVacancy->location }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Tipe Kepegawaian:</span>
                    <span class="text-slate-800">{{ $jobApplication->jobVacancy->employment_type }}</span>
                </div>
            </div>
        </div>

        {{-- Review Section 2: Data Pribadi Pelamar --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    2. Data Pribadi Pelamar
                </h2>
                <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                    Ubah di Profil &rarr;
                </a>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 block">Nama Lengkap:</span>
                    <strong class="text-slate-900">{{ $user->name }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">NIK (KTP):</span>
                    <strong class="font-mono text-slate-900">{{ $profile?->nik ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Nomor Registrasi Akun:</span>
                    <strong class="font-mono text-rsi-700">{{ $user->registration_number }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block">Tempat, Tanggal Lahir:</span>
                    <span class="text-slate-800">{{ $profile?->birth_place ?? '-' }}, {{ $profile?->birth_date?->translatedFormat('d F Y') ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Jenis Kelamin:</span>
                    <span class="text-slate-800">{{ is_object($profile?->gender) ? $profile->gender->label() : ($profile?->gender ?? '-') }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Agama:</span>
                    <span class="text-slate-800">{{ $profile?->religion ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Email:</span>
                    <span class="text-slate-800">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Nomor WhatsApp:</span>
                    <strong class="font-mono text-slate-800">{{ $user->phone }}</strong>
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <span class="text-slate-500 block">Alamat KTP:</span>
                    <span class="text-slate-800">{{ $profile?->address_ktp ?? '-' }}</span>
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <span class="text-slate-500 block">Alamat Domisili:</span>
                    <span class="text-slate-800">{{ $profile?->address_domicile ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Review Section 3: Riwayat Pendidikan --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    3. Riwayat Pendidikan ({{ $educations->count() }})
                </h2>
                <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                    Kelola di Profil &rarr;
                </a>
            </div>
            <div class="p-6">
                @if ($educations->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach ($educations as $edu)
                            <div class="py-3 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $edu->institution_name }}</div>
                                    <div class="text-slate-500">{{ is_object($edu->education_level) ? $edu->education_level->label() : $edu->education_level }} - {{ $edu->study_program }} (Akreditasi: {{ $edu->accreditation ?? '-' }})</div>
                                </div>
                                <div class="text-slate-600 sm:text-right">
                                    <span>Lulus: <strong>{{ $edu->graduation_year }}</strong></span>
                                    <span class="mx-1">&bull;</span>
                                    <span>IPK: <strong>{{ number_format($edu->gpa, 2) }}</strong></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-xs text-slate-400">Belum ada riwayat pendidikan yang dicantumkan.</div>
                @endif
            </div>
        </div>

        {{-- Review Section 4: Dokumen Persyaratan yang Diunggah --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    4. Dokumen Persyaratan Terlampir ({{ $jobApplication->documents->count() }})
                </h2>
                <a href="{{ route('portal.applications.documents.index', $jobApplication) }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                    Kelola Berkas &rarr;
                </a>
            </div>
            <div class="p-6">
                @if ($jobApplication->documents->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach ($jobApplication->documents as $doc)
                            <div class="py-3 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-rsi-50 text-rsi-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $doc->documentRequirement?->name ?? 'Dokumen' }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $doc->original_name }} ({{ round($doc->file_size / 1024, 1) }} KB)</div>
                                    </div>
                                </div>
                                <div>
                                    <a
                                        href="{{ route('portal.applications.documents.download', [$jobApplication, $doc]) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Periksa Berkas
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-xs text-slate-400">Belum ada berkas yang diunggah.</div>
                @endif
            </div>
        </div>

        {{-- Final Declaration & Submission Card with Custom Modal --}}
        <div
            x-data="{
                showConfirmModal: false,
                isSubmitting: false
            }"
            class="p-6 bg-white rounded-2xl border-2 {{ $canSubmit ? 'border-rsi-600' : 'border-slate-200' }} shadow-2xs space-y-5"
        >
            <div class="space-y-1">
                <h2 class="text-base font-bold text-slate-900">
                    Pernyataan Integritas & Final Submit
                </h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Harap baca dengan seksama sebelum melakukan pengiriman lamaran pekerjaan.
                </p>
            </div>

            <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl text-xs text-amber-900 leading-relaxed space-y-1.5">
                <p class="font-bold flex items-center gap-1.5 text-amber-950">
                    <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    PENTING: Data Akan Terkunci Permanen
                </p>
                <p>
                    Setelah tombol <strong>"Kirim Lamaran Sekarang"</strong> ditekan, seluruh berkas persyaratan dan data profil Anda akan <strong>dibekukan secara permanen (Snapshot)</strong> ke dalam basis data rekrutmen RSI Fatimah Banyuwangi. Anda tidak dapat lagi mengubah berkas, formasi yang dilamar, maupun data diri untuk periode rekrutmen ini.
                </p>
            </div>

            <form
                x-ref="submitForm"
                action="{{ route('portal.applications.submit', $jobApplication) }}"
                method="POST"
                @submit="isSubmitting = true"
                class="space-y-4"
            >
                @csrf

                <label class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl border {{ $errors->has('declaration') ? 'border-red-300 bg-red-50/30' : 'border-slate-200' }} cursor-pointer select-none">
                    <input
                        type="checkbox"
                        required
                        id="agreement"
                        name="declaration"
                        value="1"
                        x-ref="declarationInput"
                        class="w-4 h-4 rounded text-rsi-700 focus:ring-rsi-500 border-slate-300 mt-0.5"
                    >
                    <span class="text-xs text-slate-700 leading-relaxed">
                        Saya menyatakan dengan sebenarnya bahwa seluruh data profil, dokumen, dan berkas yang saya lampirkan adalah <strong>benar, sah, dan sesuai dengan aslinya</strong>. Apabila di kemudian hari ditemukan data palsu atau ketidaksesuaian, saya bersedia menerima sanksi gugur dalam proses seleksi sesuai kebijakan RSI Fatimah Banyuwangi.
                    </span>
                </label>
                @error('declaration')
                    <p class="text-xs font-semibold text-red-600 pl-1">{{ $message }}</p>
                @enderror

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                    <a
                        href="{{ route('portal.applications.documents.index', $jobApplication) }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors"
                    >
                        &larr; Kembali ke Berkas Persyaratan
                    </a>

                    @if ($canSubmit)
                        <button
                            type="button"
                            @click="if ($refs.declarationInput && $refs.declarationInput.checked) { showConfirmModal = true; } else { $refs.declarationInput.reportValidity(); }"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl text-xs font-bold text-white bg-rsi-700 hover:bg-rsi-800 shadow-sm cursor-pointer transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Kirim Lamaran Sekarang (Final Submit)</span>
                        </button>
                    @else
                        <button
                            type="button"
                            disabled
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl text-xs font-bold text-white bg-slate-400 cursor-not-allowed select-none"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Kirim Lamaran Sekarang (Final Submit)</span>
                        </button>
                    @endif
                </div>
            </form>

            {{-- Custom Modal Popup Konfirmasi Pengiriman Lamaran khas RSI Fatimah --}}
            <template x-teleport="body">
                <div
                    x-show="showConfirmModal"
                    x-cloak
                    class="fixed inset-0 z-50 overflow-y-auto"
                    role="dialog"
                    aria-modal="true"
                    @keydown.escape.window="if (!isSubmitting) showConfirmModal = false"
                >
                    {{-- Backdrop --}}
                    <div
                        x-show="showConfirmModal"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                        @click="if (!isSubmitting) showConfirmModal = false"
                        aria-hidden="true"
                    ></div>

                    {{-- Dialog Content --}}
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 relative z-10 pointer-events-none">
                        <div
                            x-show="showConfirmModal"
                            x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="relative w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-200 pointer-events-auto space-y-4"
                        >
                            {{-- Header --}}
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-rsi-50 border border-rsi-100 text-rsi-700 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-rsi-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">Konfirmasi Kirim Lamaran</h3>
                                        <p class="text-[11px] text-slate-500 font-medium">Formasi: {{ $jobApplication->jobVacancy->title }}</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="showConfirmModal = false"
                                    :disabled="isSubmitting"
                                    class="text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer p-1 rounded-lg hover:bg-slate-50"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Message Content --}}
                            <div class="space-y-3 text-xs">
                                <p class="text-slate-700 leading-relaxed font-medium">
                                    Apakah Anda yakin seluruh data diri dan berkas persyaratan sudah benar?
                                </p>

                                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 leading-relaxed space-y-1">
                                    <p class="font-bold text-amber-950 flex items-center gap-1.5 text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-amber-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>Perhatian: Berkas Terkunci Permanen</span>
                                    </p>
                                    <p class="text-[11px] text-amber-900/90">
                                        Lamaran yang telah dikirim <strong>tidak dapat dibatalkan, diedit, atau diubah</strong> kembali demi menjaga objektivitas seleksi.
                                    </p>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                                <button
                                    type="button"
                                    @click="showConfirmModal = false"
                                    :disabled="isSubmitting"
                                    class="px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                >
                                    Batal
                                </button>
                                <button
                                    type="button"
                                    @click="isSubmitting = true; $refs.submitForm.submit()"
                                    :disabled="isSubmitting"
                                    class="inline-flex items-center gap-1.5 px-5 py-2 text-xs font-bold text-white bg-rsi-700 hover:bg-rsi-800 rounded-lg shadow-xs transition-colors cursor-pointer disabled:opacity-70"
                                >
                                    <svg x-show="!isSubmitting" class="w-3.5 h-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg x-show="isSubmitting" x-cloak class="animate-spin w-3.5 h-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span x-text="isSubmitting ? 'Mengirim Lamaran...' : 'Ya, Kirim Lamaran'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-layouts.applicant>
