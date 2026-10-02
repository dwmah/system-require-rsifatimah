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

        @if ($jobApplication->isRejected())
            {{-- Rejection Announcement Alert --}}
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <span class="px-2.5 py-0.5 bg-rose-200 text-rose-900 text-[10px] font-bold uppercase tracking-wider rounded-full inline-block">Hasil Seleksi</span>
                        <h2 class="text-base font-bold text-rose-950">Berkas Lamaran Belum Memenuhi Syarat (Tidak Lolos)</h2>
                        <p class="text-xs text-rose-800 leading-relaxed max-w-2xl">
                            <strong>Alasan Penolakan:</strong> {{ $jobApplication->rejection_reason ?? 'Berkas tidak lengkap atau belum memenuhi kualifikasi.' }}
                        </p>
                        <p class="text-[11px] text-slate-500 pt-1">
                            Sesuai ketentuan RSI Fatimah Banyuwangi, data akun pelamar yang tidak lolos akan dibersihkan dari sistem setelah Anda mengonfirmasi pengumuman ini.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('portal.applications.acknowledge', $jobApplication) }}" class="shrink-0 w-full md:w-auto">
                    @csrf
                    <button type="submit" onclick="return confirm('Apakah Anda yakin telah membaca pengumuman ini? Akun dan berkas Anda akan dibersihkan dan sesi login akan diakhiri.')" class="w-full md:w-auto px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Konfirmasi &amp; Akhiri Akun</span>
                    </button>
                </form>
            </div>
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
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $publicStatus['badge_class'] }}">
                        {{ $publicStatus['label'] }}
                    </span>
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
        {{-- Next Actions Banner --}}
        @if ($isDraft)
            @if (! $isProfileComplete)
                <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-50/90 via-white to-amber-50/40 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <h3 class="text-sm font-bold text-amber-950 flex items-center gap-2">
                                <span>Profil Dasar Belum Lengkap ({{ $completenessPercentage }}%)</span>
                            </h3>
                            <p class="text-xs text-amber-800 leading-relaxed">
                                Anda wajib melengkapi data profil dasar (100%) terlebih dahulu sebelum dapat mengunggah dokumen persyaratan.
                            </p>
                        </div>
                    </div>

                    <a
                        href="{{ route('profile.show') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white transition-colors shrink-0 shadow-2xs cursor-pointer"
                    >
                        <span>Lengkapi Profil Sekarang</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            @else
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
                                Profil lengkap! Lanjutkan ke tahap pengunggahan berkas persyaratan formasi untuk dapat mengirimkan lamaran.
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
        @else
            <div class="p-5 rounded-2xl bg-gradient-to-r from-rsi-50 via-white to-rsi-50/40 border border-rsi-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rsi-700 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div class="space-y-0.5">
                        <h3 class="text-sm font-bold text-rsi-900">
                            Pantau Progres Tahapan Seleksi
                        </h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Lamaran resmi telah terkirim. Anda dapat melihat perkembangan status setiap tahapan seleksi secara berkala.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('portal.applications.tracking', $jobApplication) }}"
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-rsi-700 text-white hover:bg-rsi-800 transition-colors shrink-0 shadow-2xs"
                >
                    <span>Lihat Status Seleksi</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        @endif

        {{-- Formasi Lowongan (Interactive on DRAFT, Locked on SUBMITTED) --}}
        <div
            x-data="{ showCancelModal: false }"
            class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 space-y-5"
        >
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-slate-900">Formasi yang Dilamar</h2>
                    @if ($isDraft)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            DRAFT
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            TERKUNCI PERMANEN
                        </span>
                    @endif
                </div>

                @if ($isDraft)
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <button
                            type="button"
                            @click="showCancelModal = true"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 hover:border-rose-300 transition-colors shadow-2xs cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Batalkan Lamaran</span>
                        </button>
                    </div>
                @else
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                @endif
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

            @if ($isDraft)
                {{-- Informational Note on Draft --}}
                <div class="p-3.5 bg-sky-50/70 border border-sky-200 rounded-xl text-xs text-sky-900 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-sky-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="space-y-0.5 text-[11px] leading-relaxed">
                        <span class="font-bold block text-sky-950">Aturan Pemilihan Formasi:</span>
                        <span>Formasi yang telah dipilih <strong>tidak dapat diganti</strong>. Jika ingin melamar posisi lain, Anda harus <strong>Batalkan Lamaran</strong> (akun pendaftaran akan dihapus), kemudian mendaftar ulang. Pilihan formasi akan <strong>terkunci permanen</strong> setelah Anda melakukan <strong>Final Submit</strong>.</span>
                    </div>
                </div>

                {{-- Modal: Batalkan Draft Lamaran --}}
                <template x-teleport="body">
                    <div
                        x-show="showCancelModal"
                        x-cloak
                        class="fixed inset-0 z-50 overflow-y-auto"
                        role="dialog"
                        aria-modal="true"
                    >
                        {{-- Backdrop --}}
                        <div
                            x-show="showCancelModal"
                            x-transition:enter="ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                            @click="showCancelModal = false"
                            aria-hidden="true"
                        ></div>

                        {{-- Dialog Wrapper --}}
                        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 relative z-10 pointer-events-none">
                            <div
                                x-show="showCancelModal"
                                x-transition:enter="ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave="ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                class="relative w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-200 pointer-events-auto space-y-4"
                            >
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-slate-900">Batalkan Lamaran & Hapus Akun?</h3>
                                            <p class="text-xs text-slate-500">Formasi: {{ $jobApplication->jobVacancy->title }}</p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="showCancelModal = false"
                                        class="text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer"
                                    >
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="space-y-2.5 text-xs">
                                    <div class="p-3 bg-rose-50 rounded-xl border border-rose-200 text-rose-900 leading-relaxed">
                                        <strong class="block font-bold text-rose-950 flex items-center gap-1.5 mb-0.5">
                                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            Peringatan Pembatalan Akun & Lamaran:
                                        </strong>
                                        Sesuai kebijakan RSI Fatimah, akun pelamar wajib memiliki formasi lamaran aktif. Membatalkan lamaran ini akan <strong>menghapus akun dan seluruh data pendaftaran Anda</strong> dari sistem, serta sesi login Anda akan diakhiri.
                                    </div>

                                    <div class="p-3 bg-sky-50 rounded-xl border border-sky-200 text-sky-900 leading-relaxed">
                                        <strong class="block font-bold text-sky-950 flex items-center gap-1.5 mb-0.5">
                                            <svg class="w-4 h-4 text-sky-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            Ingin Melamar Posisi Lain?
                                        </strong>
                                        Formasi yang telah dipilih <strong>tidak dapat diganti</strong>. Jika ingin melamar posisi lain, batalkan lamaran ini (akun Anda akan dihapus), lalu daftar ulang dan pilih formasi baru.
                                    </div>

                                    <p class="text-slate-500 text-[11px] leading-relaxed">
                                        Sebanyak <strong>{{ $jobApplication->documents->count() }} berkas</strong> dokumen yang telah diunggah untuk formasi ini juga akan dihapus secara permanen dari server.
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('portal.applications.cancel-draft', $jobApplication) }}">
                                    @csrf
                                    @method('DELETE')
                                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                                        <button
                                            type="button"
                                            @click="showCancelModal = false"
                                            class="px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                        >
                                            Kembali
                                        </button>
                                        <button
                                            type="submit"
                                            class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-xs transition-colors cursor-pointer"
                                        >
                                            Ya, Batalkan Lamaran & Hapus Akun
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            @endif
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
                    @if ($isProfileComplete)
                        <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                            Periksa Profil &rarr;
                        </a>
                    @else
                        <a href="{{ route('profile.show') }}" class="text-xs font-bold text-amber-700 hover:text-amber-800 hover:underline flex items-center gap-1">
                            <span>Lengkapi Profil Sekarang</span>
                            <span>&rarr;</span>
                        </a>
                    @endif
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
                        @if ($isProfileComplete)
                            <span class="font-bold text-emerald-700">100% (Lengkap)</span>
                        @else
                            <span class="font-bold text-amber-600 font-mono">{{ $completenessPercentage }}% (Belum Lengkap)</span>
                        @endif
                    </div>
                </div>

                @if (! $isProfileComplete)
                    <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 leading-relaxed flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="space-y-0.5 text-[11px]">
                            <span class="font-bold block text-amber-950">Profil Dasar Anda Belum Lengkap ({{ $completenessPercentage }}%)</span>
                            <span>Pastikan seluruh 11 poin data dasar (foto profil, biodata diri lengkap, dan minimal 1 riwayat pendidikan formal) telah terisi agar tombol pengunggahan berkas persyaratan dapat diakses.</span>
                        </div>
                    </div>
                @else
                    <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-100 text-[11px] text-blue-800 leading-relaxed">
                        Data profil dasar masih berstatus aktif (live) dan dapat disesuaikan pada menu Profil Saya sebelum Anda melakukan Final Submit.
                    </div>
                @endif
            </div>
        @endif

        {{-- Berkas Dokumen yang Dilampirkan (Bisa Diunduh oleh Pemilik) --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    Berkas Persyaratan yang Terlampir ({{ $jobApplication->documents->count() }})
                </h2>
                @if ($isDraft)
                    @if ($isProfileComplete)
                        <a href="{{ route('portal.applications.documents.index', $jobApplication) }}" class="text-xs font-semibold text-rsi-700 hover:underline">
                            Kelola Berkas &rarr;
                        </a>
                    @else
                        <span class="text-xs font-semibold text-slate-400 cursor-not-allowed flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Profil Belum Lengkap
                        </span>
                    @endif
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
                            @if ($isProfileComplete)
                                <a href="{{ route('portal.applications.documents.index', $jobApplication) }}" class="text-rsi-700 underline font-semibold ml-1">Unggah berkas sekarang</a>
                            @else
                                <a href="{{ route('profile.show') }}" class="text-amber-700 underline font-semibold ml-1">Lengkapi profil terlebih dahulu</a>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.applicant>
