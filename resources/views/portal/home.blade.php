<x-layouts.applicant title="Dashboard Pelamar">
    <div class="space-y-6">
        <!-- Candidate Greeting Header -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-4">
                @if($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-2xl object-cover shadow-md shrink-0 border border-slate-200" style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px; object-fit: cover; border-radius: 16px;">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-[#0179B5] text-white font-bold text-xl flex items-center justify-center shadow-md shrink-0">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-bold text-slate-900 leading-tight">Selamat Datang, {{ $user->name }}!</h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 mt-1">
                        <span>No. Registrasi: <strong class="font-mono text-slate-700">{{ $user->registration_number ?? '-' }}</strong></span>
                        <span>Email: <strong class="text-slate-700">{{ $user->email }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Profile Completeness Widget -->
            <div class="p-4 sm:p-5 bg-slate-50 border border-slate-200 rounded-xl w-full md:w-[380px] lg:w-[440px] shrink-0">
                <div class="flex items-center justify-between text-xs font-semibold mb-2 gap-3">
                    <span class="text-slate-700 font-medium">
                        {{ $percentage >= 100 ? 'Profil Anda Sudah Lengkap' : 'Kelengkapan Profil Dasar' }}
                    </span>
                    <span class="{{ $percentage >= 100 ? 'text-emerald-600' : 'text-[#0179B5]' }} font-bold shrink-0">
                        {{ $percentage >= 100 ? '100% Selesai' : $percentage . '% Selesai' }}
                    </span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div class="{{ $percentage >= 100 ? 'bg-emerald-600' : 'bg-[#0179B5]' }} h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $percentage) }}%"></div>
                </div>
                <div class="mt-2.5 text-right">
                    <a href="{{ route('profile.show') }}" class="text-[11px] font-semibold {{ $percentage >= 100 ? 'text-emerald-700 hover:text-emerald-800' : 'text-[#0179B5] hover:text-[#016293]' }} hover:underline inline-flex items-center gap-1">
                        <span>{{ $percentage >= 100 ? 'Lihat / Edit Profil' : 'Lengkapi Profil Sekarang' }}</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- SELECTION TIMELINE TRACKER (DYNAMIC) -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">Timeline Alur Seleksi Karyawan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau perkembangan tahapan seleksi secara transparan</p>
                </div>
                @if($activePeriod && $activePeriod->submission_deadline)
                    <div class="text-xs text-slate-600 bg-rose-50 border border-rose-100 px-3 py-1 rounded-lg self-start sm:self-auto">
                        Batas Submit Berkas: <strong class="text-rose-600 font-semibold">{{ \Carbon\Carbon::parse($activePeriod->submission_deadline)->locale('id')->translatedFormat('d F Y, H.i') }} WIB</strong>
                    </div>
                @endif
            </div>

            @php
                $stageCount = count($timelineStages);
                $gridColsClass = match(true) {
                    $stageCount === 1 => 'grid-cols-1 max-w-md',
                    $stageCount === 2 => 'grid-cols-1 sm:grid-cols-2',
                    $stageCount === 3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
                    $stageCount === 4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
                    $stageCount === 5 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
                    default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
                };
            @endphp

            <div class="grid {{ $gridColsClass }} gap-3 sm:gap-4">
                @forelse($timelineStages as $index => $stage)
                    @php
                        $state = $stage['public_state'] ?? 'waiting';
                        $isPassed = in_array($state, ['passed', 'completed']);
                        $isFailed = $state === 'failed';
                        $isInProgress = $state === 'in_progress' || !empty($stage['is_current']);

                        $cardBg = match(true) {
                            $isPassed => 'bg-emerald-50/60 border-emerald-200 ring-1 ring-emerald-200/50',
                            $isFailed => 'bg-rose-50/60 border-rose-200 ring-1 ring-rose-200/50',
                            $isInProgress => 'bg-sky-50/80 border-sky-300 ring-1 ring-sky-300',
                            default => 'bg-slate-50/70 border-slate-200/80 hover:bg-white hover:border-slate-300',
                        };

                        $stepBadgeBg = match(true) {
                            $isPassed => 'bg-emerald-600 text-white shadow-xs',
                            $isFailed => 'bg-rose-600 text-white shadow-xs',
                            $isInProgress => 'bg-[#0179B5] text-white shadow-xs',
                            default => 'bg-slate-200 text-slate-700',
                        };
                    @endphp
                    <div class="p-3.5 sm:p-4 rounded-xl border {{ $cardBg }} flex flex-col justify-between transition-all min-h-[125px]">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="w-6 h-6 rounded-full {{ $stepBadgeBg }} font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $stage['sort_order'] ?? ($index + 1) }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border whitespace-nowrap shrink-0 {{ $stage['badge_class'] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ $stage['label'] ?? 'Menunggu' }}
                                </span>
                            </div>

                            <h3 class="font-bold text-xs text-slate-900 leading-snug">
                                {{ $stage['name'] }}
                            </h3>

                            @if(!empty($stage['description']))
                                <p class="text-[11px] text-slate-500 leading-relaxed">
                                    {{ $stage['description'] }}
                                </p>
                            @endif
                        </div>

                        @if(!empty($stage['published_at']))
                            <div class="mt-2 pt-2 border-t border-slate-200/50 text-[10px] text-slate-400">
                                {{ \Carbon\Carbon::parse($stage['published_at'])->locale('id')->translatedFormat('d M Y') }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <p class="text-xs text-slate-500">Informasi tahapan seleksi akan ditampilkan setelah Anda memilih formasi lowongan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Published Final Result Banner (If Officially Published) --}}
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
                        Diumumkan resmi pada: {{ $finalResult['published_at']->locale('id')->translatedFormat('d F Y, H.i') }} WIB
                    </div>
                @endif
            </div>
        @endif

        <!-- MAJOR STATUS ANNOUNCEMENT BANNERS -->
        @if($application)
            @if($application->isRejected() && !empty($finalResult))
                <!-- BANNER REJECTED / TIDAK LOLOS SELEKSI -->
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 bg-rose-200 text-rose-900 text-[10px] font-bold uppercase tracking-wider rounded-full inline-block">Hasil Seleksi</span>
                                <span class="text-xs text-rose-700 font-semibold">{{ $application->jobVacancy->title ?? 'Lowongan' }}</span>
                            </div>
                            <h2 class="text-base font-bold text-rose-950">Hasil Verifikasi: Berkas Belum Memenuhi Syarat (Tidak Lolos)</h2>
                            <p class="text-xs text-rose-800 leading-relaxed max-w-2xl">
                                <strong>Alasan Penolakan:</strong> {{ $application->rejection_reason ?? ($finalResult['message'] ?? 'Berkas persyaratan belum lengkap atau tidak sesuai kualifikasi yang dipersyaratkan.') }}
                            </p>
                            <p class="text-[11px] text-slate-500 pt-1">
                                Sesuai SOP privasi RSI Fatimah Banyuwangi, seluruh data akun dan berkas fisik pelamar yang tidak lolos akan dibersihkan setelah Anda mengonfirmasi pengumuman ini.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('portal.applications.acknowledge', $application) }}" class="shrink-0 w-full md:w-auto">
                        @csrf
                        <button
                            type="button"
                            data-confirm="Apakah Anda yakin telah membaca pengumuman ini? Sesuai ketentuan, akun dan berkas lamaran Anda akan dibersihkan dari sistem dan sesi login akan diakhiri."
                            data-confirm-title="Konfirmasi Pengumuman & Akhiri Akun"
                            data-confirm-btn="Ya, Konfirmasi & Akhiri Akun"
                            data-confirm-variant="danger"
                            class="w-full md:w-auto px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span>Konfirmasi &amp; Akhiri Akun</span>
                        </button>
                    </form>
                </div>
            @elseif($application->status === \App\Enums\JobApplicationStatus::SUBMITTED)
                <!-- BANNER SUBMITTED: MENUNGGU VERIFIKASI DOKUMEN -->
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 shadow-sm flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="px-2.5 py-0.5 bg-amber-200 text-amber-900 text-[10px] font-bold uppercase tracking-wider rounded-full inline-block mb-1">Status Berkas</span>
                        <h2 class="text-base font-bold text-amber-950">Berkas Telah Disubmit — Menunggu Verifikasi Tim SDI</h2>
                        <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                            Lamaran Anda pada formasi <strong>{{ $application->jobVacancy->title ?? 'Lowongan' }}</strong> telah diterima. Tim verifikator SDI RSI Fatimah sedang memeriksa keabsahan dan kelengkapan dokumen persyaratan Anda.
                        </p>
                    </div>
                </div>
            @elseif($application->status !== \App\Enums\JobApplicationStatus::DRAFT && empty($finalResult))
                <!-- BANNER PROSES SELEKSI BERJALAN / MENUNGGU PENGUMUMAN (ANTI-LEAK) -->
                <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-6 shadow-sm flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="px-2.5 py-0.5 bg-indigo-200 text-indigo-900 text-[10px] font-bold uppercase tracking-wider rounded-full inline-block mb-1">Proses Seleksi</span>
                        <h2 class="text-base font-bold text-indigo-950">Proses Seleksi Sedang Berlangsung</h2>
                        <p class="text-xs text-indigo-800 mt-1 leading-relaxed">
                            Lamaran Anda pada formasi <strong>{{ $application->jobVacancy->title ?? 'Lowongan' }}</strong> sedang dalam tahapan evaluasi seleksi. Pantau perkembangan setiap tahapan pada timeline di bawah.
                        </p>
                    </div>
                </div>
            @endif

            @if($application->isRejected() && !empty($finalResult) && is_null($application->announcement_seen_at))
                <!-- MODAL POPUP PENGUMUMAN TIDAK LOLOS (AUTO-OPEN) -->
                <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 relative z-10">
                        <div class="relative w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-200 space-y-5">
                            <div class="flex items-center gap-3.5 pb-3 border-b border-slate-100">
                                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">Pengumuman Hasil Seleksi</h3>
                                    <p class="text-xs text-slate-500">Formasi: {{ $application->jobVacancy->title ?? 'Lowongan' }}</p>
                                </div>
                            </div>

                            <div class="space-y-3 text-xs leading-relaxed">
                                <div class="p-4 bg-rose-50 rounded-xl border border-rose-200 text-rose-900 space-y-1.5">
                                    <strong class="text-sm font-bold block text-rose-950">Status: Berkas Belum Memenuhi Syarat (Tidak Lolos)</strong>
                                    <p class="text-rose-800">
                                        <strong>Catatan Alasan Penolakan:</strong><br>
                                        {{ $application->rejection_reason ?? ($finalResult['message'] ?? 'Berkas persyaratan belum lengkap atau tidak sesuai kualifikasi yang dipersyaratkan.') }}
                                    </p>
                                </div>

                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-600 space-y-1">
                                    <p>
                                        Terima kasih atas minat dan antusiasme Anda untuk berpartisipasi dalam Rekrutmen <strong>RS Islam Fatimah Banyuwangi</strong>.
                                    </p>
                                    <p class="text-[11px] text-slate-500">
                                        Sesuai ketentuan tata kelola privasi data pelamar, setelah Anda menekan tombol konfirmasi di bawah, akun Anda akan dibersihkan dari sistem dan sesi login Anda akan diakhiri.
                                    </p>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                                <form method="POST" action="{{ route('portal.applications.acknowledge', $application) }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full px-5 py-3 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span>Saya Mengerti, Konfirmasi &amp; Akhiri Akun</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- PRIMARY APPLICATION SUMMARY CARD (UNIFIED STATUS LAMARAN AKTIF) -->
            <div x-data="{ showCancelModal: false }" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-sm sm:text-base font-bold text-slate-900">
                        Status Lamaran Aktif
                    </h2>
                    <a href="{{ route('portal.applications.index') }}" class="text-xs font-semibold text-[#0179B5] hover:underline">
                        Lamaran Saya &rarr;
                    </a>
                </div>

                @if ($application->status === \App\Enums\JobApplicationStatus::DRAFT)
                    <div class="space-y-4">
                        <!-- Header & Judul Formasi -->
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold text-[#0179B5] bg-sky-50 px-2.5 py-0.5 rounded-md border border-sky-100">
                                    {{ $application->jobVacancy->code ?? 'VAC' }}
                                </span>
                                <span class="text-xs text-slate-300">&bull;</span>
                                <span class="text-xs font-medium text-slate-500">
                                    {{ $application->recruitmentPeriod->name ?? '-' }}
                                </span>
                                <span class="text-xs text-slate-300">&bull;</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $isDocumentsComplete ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ $isDocumentsComplete ? 'Siap Dikirim' : 'Draft' }}
                                </span>
                            </div>

                            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-slate-900 leading-tight">
                                {{ $application->jobVacancy->title ?? '-' }}
                            </h3>
                        </div>

                        <!-- Progress Bar Kelengkapan Berkas (Full Width) -->
                        @if (($documentProgress['total'] ?? 0) > 0)
                            <div class="space-y-1.5 pt-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-700">
                                        Kelengkapan Berkas:
                                        <span class="text-slate-500 font-normal">
                                            {{ $documentProgress['completed'] }} dari {{ $documentProgress['total'] }} dokumen
                                        </span>
                                    </span>
                                    <span class="font-bold {{ $isDocumentsComplete ? 'text-emerald-700' : 'text-[#0179B5]' }} font-mono">
                                        {{ $documentProgress['percentage'] }}%
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden border border-slate-200/50">
                                    <div
                                        class="h-2.5 rounded-full transition-all duration-500 {{ $isDocumentsComplete ? 'bg-emerald-600' : 'bg-[#0179B5]' }}"
                                        style="width: {{ min(100, $documentProgress['percentage']) }}%"
                                    ></div>
                                </div>
                                <p class="text-xs text-slate-500">
                                    @if (! $isDocumentsComplete)
                                        Masih ada <strong>{{ $documentProgress['missing'] }}</strong> dokumen yang belum dilengkapi sebelum lamaran dapat dikirim.
                                    @else
                                        Semua dokumen persyaratan telah lengkap. Periksa kembali data Anda sebelum mengirim lamaran.
                                    @endif
                                </p>
                            </div>
                        @endif

                        <!-- Action Bar: Tombol Aksi Simpel & Terpadu -->
                        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <button
                                    type="button"
                                    @click="showCancelModal = true"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-white hover:bg-rose-50 border border-rose-200 hover:border-rose-300 transition-colors shadow-2xs cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Batalkan Lamaran</span>
                                </button>
                            </div>

                            <div class="flex items-center sm:justify-end">
                                @if (! $isComplete)
                                    <div class="flex flex-col items-center sm:items-end gap-1.5 w-full sm:w-auto">
                                        <span class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-200 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed select-none">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <span>Lengkapi Berkas</span>
                                        </span>
                                        <a href="{{ route('profile.show') }}" class="text-[11px] text-amber-700 font-semibold hover:underline flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Profil belum lengkap &rarr; Lengkapi Sekarang
                                        </a>
                                    </div>
                                @elseif (! $isDocumentsComplete)
                                    <a
                                        href="{{ route('portal.applications.documents.index', $application) }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-xl shadow-xs transition-colors"
                                    >
                                        <span>Lengkapi Berkas</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @else
                                    <a
                                        href="{{ route('portal.applications.review', $application) }}"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors"
                                    >
                                        <span>Review &amp; Kirim Lamaran</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- MODAL KONFIRMASI: BATALKAN DRAFT LAMARAN & HAPUS AKUN -->
                    <template x-teleport="body">
                        <div
                            x-show="showCancelModal"
                            x-cloak
                            class="fixed inset-0 z-50 overflow-y-auto"
                            role="dialog"
                            aria-modal="true"
                        >
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
                                                <p class="text-xs text-slate-500">Formasi: {{ $application->jobVacancy->title }}</p>
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
                                            Sebanyak <strong>{{ $application->documents ? $application->documents->count() : 0 }} berkas</strong> dokumen yang telah diunggah untuk formasi ini juga akan dihapus secara permanen dari server.
                                        </p>
                                    </div>

                                    <form method="POST" action="{{ route('portal.applications.cancel-draft', $application) }}">
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
                @else
                    <!-- SUBMITTED / ONGOING SELECTION STATUS -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1.5 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold text-[#0179B5] bg-sky-50 px-2.5 py-0.5 rounded border border-sky-100">
                                    {{ $application->jobVacancy->code ?? 'VAC' }}
                                </span>
                                <span class="text-xs text-slate-300">&bull;</span>
                                <span class="text-xs font-medium text-slate-500">
                                    {{ $application->recruitmentPeriod->name ?? '-' }}
                                </span>
                            </div>

                            <h3 class="text-base sm:text-lg font-bold text-slate-900 truncate">
                                {{ $application->jobVacancy->title ?? '-' }}
                            </h3>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 pt-0.5">
                                @if ($application->application_number)
                                    <div>
                                        <span>Nomor Lamaran: </span>
                                        <strong class="text-slate-800 font-mono">
                                            {{ $application->application_number }}
                                        </strong>
                                    </div>
                                @endif
                                @if ($application->submitted_at)
                                    <div>
                                        <span>Dikirim: </span>
                                        <strong class="text-slate-700">
                                            {{ $application->submitted_at->locale('id')->translatedFormat('d F Y, H.i') }} WIB
                                        </strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
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
                    </div>
                @endif
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
    </div>
</x-layouts.applicant>
