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

        {{-- Application Workflow Stepper --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                {{-- STEP 1: Formasi --}}
                <a href="{{ route('portal.applications.show', $jobApplication) }}" class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-xs font-bold">1</span>
                        <span class="text-[11px] font-semibold text-rsi-700 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            {{ $jobApplication->isDraft() ? 'Dapat Diganti' : 'Terkunci' }}
                        </span>
                    </div>
                    <div class="text-xs font-bold text-slate-800">Formasi & Profil</div>
                </a>

                {{-- STEP 2: Unggah Berkas --}}
                <a href="{{ route('portal.applications.documents.index', $jobApplication) }}" class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-xs font-bold">2</span>
                        <span class="text-[11px] font-semibold text-rsi-700 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            {{ $missingRequirements->isEmpty() ? 'Lengkap' : 'Belum Lengkap' }}
                        </span>
                    </div>
                    <div class="text-xs font-bold text-slate-800">Unggah Berkas Persyaratan</div>
                </a>

                {{-- STEP 3: Review & Kirim --}}
                <div class="p-3.5 rounded-xl border-2 border-rsi-600 bg-rsi-50/50">
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rsi-700 text-white text-xs font-bold">3</span>
                        <span class="text-[11px] font-bold text-rsi-800">Tahap Akhir</span>
                    </div>
                    <div class="text-xs font-bold text-slate-900">Review & Final Submit</div>
                </div>
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

        {{-- Review Section 4: Lisensi / STR --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    4. STR / Lisensi Profesi ({{ $licenses->count() }})
                </h2>
                <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                    Kelola di Profil &rarr;
                </a>
            </div>
            <div class="p-6">
                @if ($licenses->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach ($licenses as $lic)
                            <div class="py-3 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $lic->license_type ?? 'STR' }}</div>
                                    <div class="font-mono text-slate-600">Nomor: {{ $lic->license_number }}</div>
                                </div>
                                <div class="text-slate-600 sm:text-right">
                                    @if ($lic->is_lifetime)
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[11px] font-bold">Seumur Hidup</span>
                                    @else
                                        <span>Berlaku s/d: <strong>{{ $lic->valid_until?->translatedFormat('d M Y') ?? '-' }}</strong></span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-xs text-slate-400">Tidak ada STR / lisensi profesi (opsional bagi non-nakes).</div>
                @endif
            </div>
        </div>

        {{-- Review Section 5: Sertifikasi & Pelatihan --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    5. Sertifikasi & Pelatihan ({{ $certifications->count() }})
                </h2>
                <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                    Kelola di Profil &rarr;
                </a>
            </div>
            <div class="p-6">
                @if ($certifications->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach ($certifications as $cert)
                            <div class="py-3 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $cert->certificate_name }}</div>
                                    <div class="text-slate-500">Penerbit: {{ $cert->issuing_organization }} (Tahun: {{ $cert->issue_year }})</div>
                                </div>
                                <div class="text-slate-600 sm:text-right">
                                    <span>Berlaku s/d: <strong>{{ $cert->valid_until?->translatedFormat('d M Y') ?? 'Tidak Ada Batas' }}</strong></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-xs text-slate-400">Tidak ada sertifikasi yang dicantumkan (opsional).</div>
                @endif
            </div>
        </div>

        {{-- Review Section 6: Pengalaman Kerja --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    6. Riwayat Pengalaman Kerja ({{ $workExperiences->count() }})
                </h2>
                <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                    Kelola di Profil &rarr;
                </a>
            </div>
            <div class="p-6">
                @if ($workExperiences->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach ($workExperiences as $exp)
                            <div class="py-3 first:pt-0 last:pb-0 space-y-1 text-xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                    <strong class="text-slate-900">{{ $exp->position }} &bull; {{ $exp->company_name }}</strong>
                                    <span class="text-slate-500">
                                        {{ $exp->start_date?->translatedFormat('M Y') }} s/d {{ $exp->is_current ? 'Sekarang' : ($exp->end_date?->translatedFormat('M Y') ?? '-') }}
                                    </span>
                                </div>
                                @if ($exp->job_description)
                                    <p class="text-slate-600 text-[11px] leading-relaxed">{{ $exp->job_description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-xs text-slate-400">Tidak ada pengalaman kerja yang dicantumkan (Fresh Graduate).</div>
                @endif
            </div>
        </div>

        {{-- Review Section 7: Dokumen Persyaratan yang Diunggah --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    7. Dokumen Persyaratan Terlampir ({{ $jobApplication->documents->count() }})
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

        {{-- Final Declaration & Submission Card --}}
        <div class="p-6 bg-white rounded-2xl border-2 {{ $canSubmit ? 'border-rsi-600' : 'border-slate-200' }} shadow-2xs space-y-5">
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
                action="{{ route('portal.applications.submit', $jobApplication) }}"
                method="POST"
                onsubmit="return confirm('Apakah Anda yakin seluruh data dan berkas sudah benar? Lamaran yang telah dikirim tidak dapat dibatalkan atau diubah.');"
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

                    <button
                        type="submit"
                        @disabled(! $canSubmit)
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl text-xs font-bold text-white {{ $canSubmit ? 'bg-rsi-700 hover:bg-rsi-800 shadow-sm cursor-pointer' : 'bg-slate-400 cursor-not-allowed' }} transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Kirim Lamaran Sekarang (Final Submit)</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.applicant>
