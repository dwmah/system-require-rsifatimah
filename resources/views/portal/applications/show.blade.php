<x-layouts.applicant title="Detail Lamaran — {{ $jobApplication->jobVacancy->title }}">
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Breadcrumb & Top Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200">
            <nav class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('portal.home') }}" class="hover:text-rsi-700 transition-colors">Portal</a>
                <span>/</span>
                <a href="{{ route('portal.applications.index') }}" class="hover:text-rsi-700 transition-colors">Lamaran Saya</a>
                <span>/</span>
                <span class="text-slate-900 font-medium">Detail Lamaran</span>
            </nav>

            <a
                href="{{ route('portal.applications.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-rsi-700 transition-colors self-start sm:self-auto"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Daftar Lamaran</span>
            </a>
        </div>

        {{-- Flash Alerts --}}
        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('info'))
            <x-ui.alert type="info">
                {{ session('info') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert type="error">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        @php
            $isDraft = $jobApplication->status->value === 'draft';
            $snapshot = $jobApplication->snapshot;
        @endphp

        {{-- Application Status Banner --}}
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
                    Unit Kerja: {{ $jobApplication->jobVacancy->unit }} &bull; Penempatan: {{ $jobApplication->jobVacancy->location }}
                </p>
            </div>

            <div class="flex flex-col md:items-end gap-2 shrink-0 pt-4 md:pt-0 border-t md:border-t-0 border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-medium">Status:</span>
                    @if ($isDraft)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            {{ $jobApplication->status->label() }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $jobApplication->status->label() }}
                        </span>
                    @endif
                </div>
                <div class="text-xs text-slate-600">
                    Nomor Lamaran:
                    @if ($jobApplication->application_number)
                        <span class="font-mono font-bold text-rsi-700 bg-rsi-50 px-2 py-0.5 rounded border border-rsi-100">
                            {{ $jobApplication->application_number }}
                        </span>
                    @else
                        <span class="font-mono text-slate-400 italic">Belum Diterbitkan (Draft)</span>
                    @endif
                </div>
                @if ($jobApplication->submitted_at)
                    <div class="text-[11px] text-slate-400">
                        Dikirim: {{ $jobApplication->submitted_at->translatedFormat('d M Y, H:i') }} WIB
                    </div>
                @endif
            </div>
        </div>

        {{-- Application Workflow Stepper --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">
                Tahapan Alur Pendaftaran
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                {{-- STEP 1: Formasi & Data Dasar --}}
                <div class="p-4 rounded-xl border border-rsi-200 bg-rsi-50/50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rsi-700 text-white text-xs font-bold">
                            1
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rsi-800">
                            <svg class="w-3.5 h-3.5 text-rsi-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Selesai
                        </span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Formasi & Profil</h3>
                    <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                        Pilihan formasi lowongan telah tersimpan dan terkunci.
                    </p>
                </div>

                {{-- STEP 2: Unggah Berkas --}}
                @if ($isDraft)
                    <a href="{{ route('portal.applications.documents.index', $jobApplication) }}" class="p-4 rounded-xl border-2 border-rsi-600 bg-rsi-50/30 hover:bg-rsi-50/60 transition-colors block">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rsi-700 text-white text-xs font-bold">
                                2
                            </span>
                            <span class="text-[11px] font-bold text-rsi-800">
                                Kelola Berkas &rarr;
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Unggah Berkas Persyaratan</h3>
                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                            {{ $jobApplication->documents->count() }} berkas terunggah saat ini.
                        </p>
                    </a>
                @else
                    <div class="p-4 rounded-xl border border-rsi-200 bg-rsi-50/50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rsi-700 text-white text-xs font-bold">
                                2
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rsi-800">
                                <svg class="w-3.5 h-3.5 text-rsi-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Berkas Terkunci
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Berkas Persyaratan</h3>
                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                            {{ $jobApplication->documents->count() }} dokumen tersimpan resmi.
                        </p>
                    </div>
                @endif

                {{-- STEP 3: Review & Final Submit --}}
                @if ($isDraft)
                    <a href="{{ route('portal.applications.review', $jobApplication) }}" class="p-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors block">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-xs font-bold">
                                3
                            </span>
                            <span class="text-[11px] font-semibold text-slate-500">
                                Belum Dikirim &rarr;
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Review & Final Submit</h3>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            Periksa kelengkapan data sebelum dikirimkan secara resmi.
                        </p>
                    </a>
                @else
                    <div class="p-4 rounded-xl border border-rsi-200 bg-rsi-50/50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rsi-700 text-white text-xs font-bold">
                                3
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rsi-800">
                                <svg class="w-3.5 h-3.5 text-rsi-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Terkirim Resmi
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Lamaran Terkirim</h3>
                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                            Snapshot data dan nomor lamaran telah resmi tercatat.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Next Actions Banner (Only for DRAFT) --}}
        @if ($isDraft)
            <div class="p-5 rounded-2xl bg-gradient-to-r from-rsi-50 via-white to-rsi-50/40 border border-rsi-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rsi-700 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="space-y-0.5">
                        <h3 class="text-sm font-bold text-rsi-900">
                            Draft Lamaran Siap Dilengkapi
                        </h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Lanjutkan ke tahap pengunggahan berkas persyaratan formasi untuk dapat mengirimkan lamaran.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('portal.applications.documents.index', $jobApplication) }}"
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-rsi-700 text-white hover:bg-rsi-800 transition-colors shrink-0 shadow-2xs"
                >
                    <span>Lanjutkan Unggah Berkas</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        @endif

        {{-- Formasi Lowongan (Locked Details) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-slate-900">Formasi yang Dilamar</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        TERKUNCI
                    </span>
                </div>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
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

        {{-- Submission Data: Snapshot (SUBMITTED) vs Live Profile (DRAFT) --}}
        @if (! $isDraft)
            {{-- HISTORICAL VIEW: APPLICATION SNAPSHOT --}}
            @if ($snapshot)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden space-y-6">
                    <div class="px-6 py-4 bg-emerald-50/60 border-b border-emerald-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-xs font-bold text-emerald-950 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Data Lamaran Resmi yang Dikirim (Snapshot Terkunci)
                            </h2>
                            <p class="text-[11px] text-emerald-800 mt-0.5">
                                Salinan permanen dibekukan pada {{ $snapshot->captured_at?->translatedFormat('d F Y, H:i') }} WIB.
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            IMMUTABLE SNAPSHOT
                        </span>
                    </div>

                    {{-- 1. Data Diri Snapshot --}}
                    @php
                        $pd = $snapshot->personal_data ?? [];
                    @endphp
                    <div class="px-6 space-y-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                            1. Identitas Pelamar
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                            <div>
                                <span class="text-slate-500 block">Nama Lengkap:</span>
                                <strong class="text-slate-900">{{ $pd['name'] ?? '-' }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-500 block">NIK (KTP):</span>
                                <strong class="font-mono text-slate-900">{{ $pd['nik'] ?? '-' }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Nomor Registrasi Akun:</span>
                                <strong class="font-mono text-rsi-700">{{ $pd['registration_number'] ?? '-' }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Tempat, Tanggal Lahir:</span>
                                <span class="text-slate-800">{{ $pd['birth_place'] ?? '-' }}, {{ isset($pd['birth_date']) ? \Carbon\Carbon::parse($pd['birth_date'])->translatedFormat('d F Y') : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Jenis Kelamin:</span>
                                <span class="text-slate-800">{{ $pd['gender'] ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Agama:</span>
                                <span class="text-slate-800">{{ $pd['religion'] ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Email:</span>
                                <span class="text-slate-800">{{ $pd['email'] ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Nomor WhatsApp:</span>
                                <strong class="font-mono text-slate-800">{{ $pd['phone'] ?? '-' }}</strong>
                            </div>
                            <div class="md:col-span-2 lg:col-span-3">
                                <span class="text-slate-500 block">Alamat KTP:</span>
                                <span class="text-slate-800">{{ $pd['address_ktp'] ?? '-' }}</span>
                            </div>
                            <div class="md:col-span-2 lg:col-span-3">
                                <span class="text-slate-500 block">Alamat Domisili:</span>
                                <span class="text-slate-800">{{ $pd['address_domicile'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Pendidikan Snapshot --}}
                    @php
                        $edus = $snapshot->educations ?? [];
                    @endphp
                    <div class="px-6 space-y-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                            2. Riwayat Pendidikan ({{ count($edus) }})
                        </h3>
                        @if (! empty($edus))
                            <div class="divide-y divide-slate-100">
                                @foreach ($edus as $edu)
                                    <div class="py-2.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                        <div>
                                            <strong class="text-slate-900">{{ $edu['institution_name'] ?? '-' }}</strong>
                                            <div class="text-slate-500">{{ $edu['education_level'] ?? '-' }} &bull; {{ $edu['study_program'] ?? '-' }} (Akreditasi: {{ $edu['accreditation'] ?? '-' }})</div>
                                        </div>
                                        <div class="text-slate-600 sm:text-right">
                                            <span>Lulus: <strong>{{ $edu['graduation_year'] ?? '-' }}</strong></span>
                                            <span class="mx-1">&bull;</span>
                                            <span>IPK: <strong>{{ isset($edu['gpa']) ? number_format($edu['gpa'], 2) : '-' }}</strong></span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-xs text-slate-400 italic">Tidak ada data pendidikan.</div>
                        @endif
                    </div>

                    {{-- 3. STR / Lisensi Snapshot --}}
                    @php
                        $lics = $snapshot->licenses ?? [];
                    @endphp
                    <div class="px-6 space-y-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                            3. STR / Lisensi Profesi ({{ count($lics) }})
                        </h3>
                        @if (! empty($lics))
                            <div class="divide-y divide-slate-100">
                                @foreach ($lics as $lic)
                                    <div class="py-2.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                        <div>
                                            <strong class="text-slate-900">{{ $lic['license_type'] ?? 'STR' }}</strong>
                                            <div class="font-mono text-slate-600">Nomor: {{ $lic['license_number'] ?? '-' }}</div>
                                        </div>
                                        <div class="text-slate-600 sm:text-right">
                                            @if (! empty($lic['is_lifetime']))
                                                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[11px] font-bold">Seumur Hidup</span>
                                            @else
                                                <span>Berlaku s/d: <strong>{{ isset($lic['valid_until']) ? \Carbon\Carbon::parse($lic['valid_until'])->translatedFormat('d M Y') : '-' }}</strong></span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-xs text-slate-400 italic">Tidak ada STR / lisensi profesi.</div>
                        @endif
                    </div>

                    {{-- 4. Pengalaman Kerja Snapshot --}}
                    @php
                        $exps = $snapshot->work_experiences ?? [];
                    @endphp
                    <div class="px-6 space-y-3">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                            4. Riwayat Pengalaman Kerja ({{ count($exps) }})
                        </h3>
                        @if (! empty($exps))
                            <div class="divide-y divide-slate-100">
                                @foreach ($exps as $exp)
                                    <div class="py-2.5 first:pt-0 last:pb-0 space-y-1 text-xs">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                            <strong class="text-slate-900">{{ $exp['position'] ?? '-' }} &bull; {{ $exp['company_name'] ?? '-' }}</strong>
                                            <span class="text-slate-500">
                                                {{ isset($exp['start_date']) ? \Carbon\Carbon::parse($exp['start_date'])->translatedFormat('M Y') : '-' }} s/d
                                                {{ ! empty($exp['is_current']) ? 'Sekarang' : (isset($exp['end_date']) ? \Carbon\Carbon::parse($exp['end_date'])->translatedFormat('M Y') : '-') }}
                                            </span>
                                        </div>
                                        @if (! empty($exp['job_description']))
                                            <p class="text-slate-600 text-[11px] leading-relaxed">{{ $exp['job_description'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-xs text-slate-400 italic">Tidak ada pengalaman kerja (Fresh Graduate).</div>
                        @endif
                    </div>

                    <div class="pb-4"></div>
                </div>
            @else
                {{-- Data Integrity Problem: Submitted but Snapshot is NULL --}}
                <div class="p-5 rounded-2xl bg-red-50 border border-red-200 text-red-900 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-sm text-red-800">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Peringatan Integritas Data</span>
                    </div>
                    <p class="text-xs text-red-800 leading-relaxed">
                        Lamaran ini tercatat telah dikirimkan, namun salinan data historis resmi (Snapshot) tidak ditemukan pada sistem. Untuk menjaga keaslian data, sistem tidak menampilkan data live profil. Silakan hubungi panitia rekrutmen RSI Fatimah.
                    </p>
                </div>
            @endif
        @else
            {{-- LIVE PROFILE SUMMARY FOR DRAFT --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900">Data Pelamar Terhubung</h2>
                    <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                        Periksa Profil &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 block">Nama Lengkap:</span>
                        <strong class="text-slate-900">{{ $jobApplication->user->name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">NIK (KTP):</span>
                        <strong class="font-mono text-slate-800">{{ $jobApplication->user->applicantProfile?->nik ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Nomor Registrasi Akun:</span>
                        <strong class="font-mono text-rsi-700">{{ $jobApplication->user->registration_number }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Email:</span>
                        <span class="text-slate-800">{{ $jobApplication->user->email }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Nomor WhatsApp:</span>
                        <strong class="font-mono text-slate-800">{{ $jobApplication->user->phone }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Kelengkapan Profil:</span>
                        <span class="font-bold text-emerald-700">100% (Lengkap)</span>
                    </div>
                </div>

                <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-100 text-[11px] text-blue-800 leading-relaxed">
                    Data profil dasar masih berstatus aktif (live) dan dapat disesuaikan pada menu Profil Saya sebelum Anda melakukan Final Submit.
                </div>
            </div>
        @endif

        {{-- Berkas Dokumen yang Dilampirkan (Bisa Diunduh oleh Pemilik) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    Berkas Persyaratan yang Terlampir ({{ $jobApplication->documents->count() }})
                </h2>
                @if ($isDraft)
                    <a href="{{ route('portal.applications.documents.index', $jobApplication) }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                        Kelola Berkas &rarr;
                    </a>
                @endif
            </div>

            <div class="p-6">
                @if ($jobApplication->documents->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach ($jobApplication->documents as $doc)
                            <div class="py-3 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-rsi-50 text-rsi-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $doc->documentRequirement?->name ?? 'Dokumen' }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">
                                            {{ $doc->original_name }} &bull; {{ round($doc->file_size / 1024, 1) }} KB
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <a
                                        href="{{ route('portal.applications.documents.download', [$jobApplication, $doc]) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-rsi-700 transition-colors shadow-2xs"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Unduh Berkas
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-xs text-slate-400">
                        Belum ada dokumen yang diunggah.
                        @if ($isDraft)
                            <a href="{{ route('portal.applications.documents.index', $jobApplication) }}" class="text-rsi-700 underline font-semibold ml-1">Unggah berkas sekarang</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.applicant>
