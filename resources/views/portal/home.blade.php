<x-layouts.applicant title="Dashboard Pelamar">
    <div class="space-y-6">
        <!-- Candidate Greeting Header -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#0179B5] text-white font-bold text-xl flex items-center justify-center shadow-md shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 leading-tight">Selamat Datang, {{ $user->name }}!</h1>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 mt-1">
                        <span>No. Registrasi: <strong class="font-mono text-slate-700">{{ $user->registration_number ?? '-' }}</strong></span>
                        <span>&bull;</span>
                        <span>Email: <strong class="text-slate-700">{{ $user->email }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Profile Completeness Widget -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl min-w-[260px]">
                <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                    <span class="text-slate-600">Kelengkapan Profil Dasar</span>
                    <span class="text-[#0179B5] font-bold">{{ $percentage }}% Selesai</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div class="bg-[#0179B5] h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                </div>
                <div class="mt-2 text-right">
                    <a href="{{ route('profile.show') }}" class="text-[11px] font-semibold text-[#0179B5] hover:underline">
                        Lengkapi Profil Sekarang &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Published Final Result Banner (If Available) --}}
        @if (!empty($finalResult))
            <div class="p-6 rounded-2xl {{ $finalResult['status'] === 'accepted' ? 'bg-emerald-50 border border-emerald-200 text-emerald-950' : 'bg-slate-50 border border-slate-200 text-slate-900' }} shadow-sm space-y-2">
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

                @if (!empty($finalResult['message']))
                    <p class="text-xs leading-relaxed {{ $finalResult['status'] === 'accepted' ? 'text-emerald-800' : 'text-slate-700' }}">
                        {{ $finalResult['message'] }}
                    </p>
                @endif

                @if (!empty($finalResult['published_at']))
                    <div class="text-[11px] {{ $finalResult['status'] === 'accepted' ? 'text-emerald-700' : 'text-slate-500' }} pt-1">
                        Diumumkan resmi pada: {{ $finalResult['published_at']->translatedFormat('d F Y, H:i') }} WIB
                    </div>
                @endif
            </div>
        @endif

        <!-- MAJOR STATUS ANNOUNCEMENT BANNERS -->
        @if($application)
            @if($application->isPassedAdministration())
                <!-- BANNER HIJAU: LOLOS SELEKSI ADMINISTRASI -->
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-md space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0 text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <span class="px-2.5 py-0.5 bg-white/20 text-white text-[10px] font-bold uppercase tracking-wider rounded-full inline-block mb-1">Pengumuman Kelulusan Berkas</span>
                                <h2 class="text-lg sm:text-xl font-extrabold tracking-tight">SELAMAT! ANDA LOLOS TAHAP SELEKSI ADMINISTRASI</h2>
                                <p class="text-xs sm:text-sm text-emerald-100 mt-1 max-w-2xl leading-relaxed">
                                    Berkas lamaran Anda untuk posisi <strong>{{ $application->jobVacancy->title }}</strong> telah diverifikasi oleh Tim SDI RSI Fatimah Banyuwangi dan dinyatakan <strong>LENGKAP & MEMENUHI SYARAT</strong>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-white/20 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <span class="text-emerald-100">
                            @if($application->currentStage)
                                Tahapan Selanjutnya: <strong>{{ $application->currentStage->stage_name }}</strong>
                            @else
                                Berkas telah diverifikasi. Menunggu instruksi tahapan lanjutan.
                            @endif
                        </span>
                        <a href="{{ route('portal.applications.index') }}" class="px-4 py-2 bg-white text-emerald-800 hover:bg-emerald-50 font-bold rounded-lg transition-colors shadow-sm text-xs">
                            Lihat Rincian Lamaran
                        </a>
                    </div>
                </div>
            @elseif($application->isRejected())
                <!-- BANNER MERAH: TIDAK LOLOS DENGAN ALASAN PENOLAKAN -->
                <div class="bg-gradient-to-r from-rose-700 to-red-800 rounded-2xl p-6 text-white shadow-md space-y-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0 text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <span class="px-2.5 py-0.5 bg-white/20 text-white text-[10px] font-bold uppercase tracking-wider rounded-full inline-block mb-1">Hasil Evaluasi Berkas</span>
                            <h2 class="text-lg sm:text-xl font-extrabold tracking-tight">MOHON MAAF, ANDA BELUM LOLOS SELEKSI ADMINISTRASI</h2>
                            <p class="text-xs sm:text-sm text-rose-100 mt-1 max-w-2xl leading-relaxed">
                                Terima kasih atas partisipasi Anda pada lowongan <strong>{{ $application->jobVacancy->title }}</strong>. Berdasarkan verifikasi dokumen oleh Tim SDI, berkas Anda dinyatakan belum memenuhi kriteria kualifikasi.
                            </p>

                            @if($application->rejection_reason)
                                <div class="mt-3 p-3 bg-black/20 rounded-xl border border-white/20 text-xs">
                                    <strong class="text-rose-200">Catatan Alasan Penolakan:</strong>
                                    <p class="mt-0.5 text-white leading-relaxed">{{ $application->rejection_reason }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="pt-3 border-t border-white/20 flex flex-wrap items-center justify-between gap-3 text-xs">
                        @if(is_null($application->announcement_seen_at) && Route::has('portal.applications.acknowledge'))
                            <form method="POST" action="{{ route('portal.applications.acknowledge', $application) }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-white text-rose-800 hover:bg-rose-50 font-bold rounded-lg transition-colors shadow-sm text-xs">
                                    Saya Mengerti & Konfirmasi Hasil
                                </button>
                            </form>
                        @elseif($application->announcement_seen_at)
                            <span class="text-rose-200 text-xs">✓ Anda telah mengonfirmasi hasil pengumuman ini pada {{ $application->announcement_seen_at->format('d M Y, H:i') }} WIB.</span>
                        @endif
                    </div>
                </div>
            @elseif($application->isSubmitted())
                <!-- BANNER AMBER: MENUNGGU VERIFIKASI -->
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 shadow-sm flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="flex-1">
                        <span class="px-2.5 py-0.5 bg-amber-200 text-amber-900 text-[10px] font-bold uppercase tracking-wider rounded-full inline-block mb-1">Status Berkas</span>
                        <h2 class="text-base font-bold text-amber-950">Berkas Telah Disubmit — Menunggu Verifikasi Tim SDI</h2>
                        <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                            Lamaran Anda pada formasi <strong>{{ $application->jobVacancy->title }}</strong> telah diterima. Tim verifikator SDI RSI Fatimah sedang memeriksa keabsahan dan kelengkapan dokumen persyaratan Anda.
                        </p>
                    </div>
                </div>
            @else
                <!-- BANNER DRAFT: PERLU DIKIRIM -->
                <div class="bg-sky-50 border border-sky-200 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-sky-100 text-[#0179B5] flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <div>
                            <span class="px-2.5 py-0.5 bg-sky-100 text-[#0179B5] text-[10px] font-bold uppercase tracking-wider rounded-full inline-block mb-1">Formulir Draft</span>
                            <h2 class="text-base font-bold text-slate-900">Lamaran Belum Difinalisasi (Submit)</h2>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Anda telah memilih formasi <strong>{{ $application->jobVacancy->title }}</strong>. Segera lengkapi dan unggah seluruh dokumen persyaratan sebelum batas akhir submit.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('portal.applications.documents.index', $application) }}" class="px-5 py-2.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-lg shadow-sm transition-colors text-center whitespace-nowrap">
                        Unggah Berkas Persyaratan &rarr;
                    </a>
                </div>
            @endif

            <!-- PRIMARY APPLICATION SUMMARY CARD -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">
                        Status Lamaran Aktif
                    </h2>
                    <a href="{{ route('portal.applications.index') }}" class="text-xs font-semibold text-[#0179B5] hover:underline">
                        Semua Lamaran Saya &rarr;
                    </a>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs font-bold text-[#0179B5] bg-sky-50 px-2.5 py-0.5 rounded border border-sky-100">
                                {{ $application->jobVacancy->code ?? 'VAC' }}
                            </span>
                            <span class="text-xs text-slate-400">&bull;</span>
                            <span class="text-xs font-medium text-slate-500">
                                {{ $application->recruitmentPeriod->name ?? '-' }}
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            {{ $application->jobVacancy->title ?? '-' }}
                        </h3>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                            <div>
                                <span>Nomor Lamaran: </span>
                                <strong class="text-slate-800 font-mono">
                                    {{ $application->application_number ?? 'Draft (Belum Diterbitkan)' }}
                                </strong>
                            </div>
                            @if ($application->submitted_at)
                                <div>
                                    <span>Dikirim: </span>
                                    <strong class="text-slate-700">
                                        {{ $application->submitted_at->translatedFormat('d M Y, H:i') }} WIB
                                    </strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(!empty($quickAction))
                        <div class="flex flex-col sm:flex-row lg:flex-col items-start lg:items-end gap-3 shrink-0">
                            @if (!empty($publicStatus))
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $publicStatus['badge_class'] ?? '' }}">
                                    {{ $publicStatus['label'] ?? '' }}
                                </span>
                            @endif

                            <a
                                href="{{ $quickAction['url'] }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-[#0179B5] hover:bg-[#016293] text-white transition-colors shadow-sm"
                            >
                                <span>{{ $quickAction['label'] }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- BANNER BELUM MEMILIH FORMASI -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                <div>
                    <span class="px-2.5 py-0.5 bg-sky-50 text-[#0179B5] text-[10px] font-bold uppercase tracking-wider rounded-full inline-block mb-1">Langkah Pertama</span>
                    <h2 class="text-lg font-bold text-slate-900">Anda Belum Memilih Posisi Formasi</h2>
                    <p class="text-xs text-slate-500 mt-1 max-w-xl">
                        RSI Fatimah Banyuwangi membuka penerimaan tenaga kerja profesional. Silakan pilih salah satu posisi lowongan yang sesuai dengan kualifikasi dan kompetensi Anda.
                    </p>
                </div>

                @if($activePeriod && $activePeriod->isOpenForSubmission())
                    <a href="{{ route('vacancies.index') }}" class="px-5 py-2.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-lg shadow-sm transition-colors text-center whitespace-nowrap">
                        Pilih Formasi Lowongan &rarr;
                    </a>
                @else
                    <span class="px-3 py-1.5 bg-slate-100 text-slate-500 text-xs font-semibold rounded-lg">Pendaftaran Sedang Ditutup</span>
                @endif
            </div>
        @endif

        <!-- SELECTION TIMELINE TRACKER -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-4 border-b border-slate-200">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Timeline Alur Seleksi Karyawan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau perkembangan tahapan seleksi secara transparan</p>
                </div>
                @if($activePeriod)
                    <div class="text-xs text-slate-500">
                        Batas Submit Berkas: <strong class="text-rose-600">{{ $activePeriod->submission_deadline ? \Carbon\Carbon::parse($activePeriod->submission_deadline)->format('d F Y, 23:59') : '-' }} WIB</strong>
                    </div>
                @endif
            </div>

            <!-- Timeline Bullets Stepper -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Step 1: Pendaftaran & Unggah Berkas -->
                <div class="p-4 rounded-xl border {{ $application && $application->isSubmitted() ? 'bg-emerald-50/60 border-emerald-300' : 'bg-sky-50 border-sky-200' }}">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-6 h-6 rounded-full {{ $application && $application->isSubmitted() ? 'bg-emerald-600' : 'bg-[#0179B5]' }} text-white font-bold text-xs flex items-center justify-center">1</span>
                        <span class="font-bold text-xs text-slate-900">Buka Pendaftaran & Upload</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Registrasi akun, pengisian profil, dan upload berkas wajib.</p>
                    <div class="mt-2 text-[10px] font-semibold {{ $application && $application->isSubmitted() ? 'text-emerald-700' : 'text-[#0179B5]' }}">
                        {{ $application && $application->isSubmitted() ? '✓ Berkas Terkirim' : 'Tahap Aktif' }}
                    </div>
                </div>

                <!-- Step 2: Verifikasi Berkas Administrasi -->
                <div class="p-4 rounded-xl border {{ $application && $application->isPassedAdministration() ? 'bg-emerald-50/60 border-emerald-300' : ($application && $application->isRejected() ? 'bg-rose-50/60 border-rose-300' : ($application && $application->isSubmitted() ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200')) }}">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-6 h-6 rounded-full {{ $application && $application->isPassedAdministration() ? 'bg-emerald-600' : ($application && $application->isRejected() ? 'bg-rose-600' : ($application && $application->isSubmitted() ? 'bg-amber-600' : 'bg-slate-300 text-slate-700')) }} text-white font-bold text-xs flex items-center justify-center">2</span>
                        <span class="font-bold text-xs text-slate-900">Verifikasi Berkas SDI</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Pemeriksaan keabsahan dokumen persyaratan oleh tim HRD.</p>
                    <div class="mt-2 text-[10px] font-semibold">
                        @if($application && $application->isPassedAdministration())
                            <span class="text-emerald-700">✓ Lolos Administrasi</span>
                        @elseif($application && $application->isRejected())
                            <span class="text-rose-700">✕ Tidak Lolos</span>
                        @elseif($application && $application->isSubmitted())
                            <span class="text-amber-700">Sedang Diverifikasi</span>
                        @else
                            <span class="text-slate-400">Menunggu Submit</span>
                        @endif
                    </div>
                </div>

                <!-- Step 3: Tes Tulis & Wawancara -->
                <div class="p-4 rounded-xl border {{ $application && in_array($application->status, [\App\Enums\JobApplicationStatus::IN_SELECTION, \App\Enums\JobApplicationStatus::ACCEPTED]) ? 'bg-emerald-50/60 border-emerald-300' : 'bg-slate-50 border-slate-200' }}">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-6 h-6 rounded-full {{ $application && in_array($application->status, [\App\Enums\JobApplicationStatus::IN_SELECTION, \App\Enums\JobApplicationStatus::ACCEPTED]) ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-700' }} font-bold text-xs flex items-center justify-center">3</span>
                        <span class="font-bold text-xs text-slate-900">Ujian Tulis & Wawancara</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Uji kompetensi teknis bidang dan wawancara direksi/SDI.</p>
                    <div class="mt-2 text-[10px] font-semibold text-slate-400">
                        {{ $application && $application->currentStage ? $application->currentStage->stage_name : 'Menunggu Jadwal' }}
                    </div>
                </div>

                <!-- Step 4: Pengumuman Akhir & MCU -->
                @php
                    $isFinalAccepted = !empty($finalResult) && ($finalResult['status'] ?? null) === 'accepted';
                @endphp
                <div class="p-4 rounded-xl border {{ $isFinalAccepted ? 'bg-teal-50 border-teal-300' : 'bg-slate-50 border-slate-200' }}">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-6 h-6 rounded-full {{ $isFinalAccepted ? 'bg-teal-700 text-white' : 'bg-slate-300 text-slate-700' }} font-bold text-xs flex items-center justify-center">4</span>
                        <span class="font-bold text-xs text-slate-900">Pengumuman Akhir / MCU</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Pemeriksaan kesehatan kerja dan penetapan karyawan baru.</p>
                    <div class="mt-2 text-[10px] font-semibold {{ $isFinalAccepted ? 'text-teal-800' : 'text-slate-400' }}">
                        {{ $isFinalAccepted ? '✓ Diterima Menjadi Karyawan' : 'Jadwal Akhir' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('portal.applications.index') }}" class="p-5 bg-white rounded-xl border border-slate-200 hover:border-[#0179B5] shadow-sm flex items-center justify-between group transition-colors">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#0179B5] flex items-center justify-center group-hover:bg-[#0179B5] group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Workspace Lamaran Saya</h3>
                        <p class="text-xs text-slate-500">Kelola unggahan berkas & cek verifikasi dokumen</p>
                    </div>
                </div>
                <span class="text-slate-400 group-hover:text-[#0179B5]">&rarr;</span>
            </a>

            <a href="{{ route('profile.show') }}" class="p-5 bg-white rounded-xl border border-slate-200 hover:border-[#0179B5] shadow-sm flex items-center justify-between group transition-colors">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-[#0179B5] flex items-center justify-center group-hover:bg-[#0179B5] group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Lengkapi Profil & Riwayat</h3>
                        <p class="text-xs text-slate-500">Update pendidikan, STR/Lisensi, sertifikasi, & pengalaman</p>
                    </div>
                </div>
                <span class="text-slate-400 group-hover:text-[#0179B5]">&rarr;</span>
            </a>
        </div>
    </div>
</x-layouts.applicant>
