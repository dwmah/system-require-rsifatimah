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
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        {{ $jobApplication->status->label() }}
                    </span>
                </div>
                <div class="text-xs text-slate-500">
                    Nomor Lamaran: <span class="font-mono font-bold text-slate-700">{{ $jobApplication->application_number ?? 'Belum diterbitkan' }}</span>
                </div>
            </div>
        </div>

        {{-- Application Workflow Stepper --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">
                Tahapan Alur Pendaftaran
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                {{-- STEP 1: Formasi & Data Dasar --}}
                <div class="p-4 rounded-xl border-2 border-rsi-600 bg-rsi-50/50 relative">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rsi-700 text-white text-xs font-bold">
                            1
                        </span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rsi-800">
                            <svg class="w-3.5 h-3.5 text-rsi-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Selesai (Terkunci)
                        </span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Formasi & Profil</h3>
                    <p class="text-[11px] text-slate-600 mt-1 leading-relaxed">
                        Pilihan lowongan telah dikonfirmasi dan profil dasar telah terhubung.
                    </p>
                </div>

                {{-- STEP 2: Unggah Berkas --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 text-slate-400">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-600 text-xs font-bold">
                            2
                        </span>
                        <span class="text-[10px] font-semibold bg-slate-200 text-slate-600 px-2 py-0.5 rounded">
                            Fase 8B
                        </span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Unggah Berkas Persyaratan</h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Pengunggahan berkas digital akan diaktifkan pada tahapan berikutnya.
                    </p>
                </div>

                {{-- STEP 3: Review & Final Submit --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 text-slate-400">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-600 text-xs font-bold">
                            3
                        </span>
                        <span class="text-[10px] font-semibold bg-slate-200 text-slate-600 px-2 py-0.5 rounded">
                            Fase 8B
                        </span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Review & Kirim Lamaran</h3>
                    <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                        Penetapan nomor resmi dan pembuatan data snapshot permanen.
                    </p>
                </div>
            </div>
        </div>

        {{-- Two Columns: Vacancy Locked Details + Applicant Profile Summary --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            {{-- Formasi Lowongan (Locked) --}}
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

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nama Jabatan:</span>
                        <span class="font-bold text-slate-900 text-right">{{ $jobApplication->jobVacancy->title }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Kode Formasi:</span>
                        <span class="font-mono font-bold text-slate-900">{{ $jobApplication->jobVacancy->code }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Unit / Bagian:</span>
                        <span class="font-semibold text-slate-800">{{ $jobApplication->jobVacancy->unit }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Tipe Kepegawaian:</span>
                        <span class="font-semibold text-slate-800">{{ $jobApplication->jobVacancy->employment_type }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Batas Pendaftaran:</span>
                        <span class="font-semibold text-red-600">{{ $jobApplication->jobVacancy->close_at->translatedFormat('d M Y, H:i') }} WIB</span>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-500 leading-relaxed">
                    Sesuai ketentuan, formasi yang telah dipilih dan tersimpan sebagai draft pada periode ini tidak dapat diubah secara mandiri.
                </div>
            </div>

            {{-- Identitas Data Pelamar --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900">Data Pelamar Terhubung</h2>
                    <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                        Periksa Profil &rarr;
                    </a>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nama Lengkap:</span>
                        <span class="font-bold text-slate-900">{{ $jobApplication->user->name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">NIK (KTP):</span>
                        <span class="font-mono font-semibold text-slate-800">{{ $jobApplication->user->applicantProfile?->nik ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Email:</span>
                        <span class="font-semibold text-slate-800">{{ $jobApplication->user->email }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nomor WhatsApp:</span>
                        <span class="font-mono font-semibold text-slate-800">{{ $jobApplication->user->phone }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nomor Registrasi Akun:</span>
                        <span class="font-mono font-bold text-rsi-700">{{ $jobApplication->user->registration_number }}</span>
                    </div>
                </div>

                <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-100 text-[11px] text-blue-800 leading-relaxed">
                    Data profil dasar pelamar telah terverifikasi lengkap. Jika terdapat penyesuaian nomor kontak atau alamat, Anda masih dapat memperbaruinya di menu Profil Saya sebelum Final Submit.
                </div>
            </div>
        </div>

        {{-- Next Steps Informative Box --}}
        <div class="p-5 rounded-2xl bg-gradient-to-r from-rsi-50 via-white to-rsi-50/40 border border-rsi-200/80 flex items-start gap-4">
            <div class="w-9 h-9 rounded-xl bg-rsi-700 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-rsi-900">
                    Pondasi Draft Lamaran Berhasil Dibuat
                </h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Anda telah menyelesaikan tahapan konfirmasi formasi (Fase 8A). Modul pengunggahan berkas persyaratan digital dan pengiriman resmi (Final Submit) dengan nomor registrasi lamaran akan dilanjutkan pada <strong>Fase 8B</strong>.
                </p>
            </div>
        </div>
    </div>
</x-layouts.applicant>
