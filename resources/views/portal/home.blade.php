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
                @php
                    $regDeadline = null;
                    $firstStageEnd = !empty($timelineStages[0]['end_at']) ? \Carbon\Carbon::parse($timelineStages[0]['end_at']) : null;
                    if ($firstStageEnd) {
                        $regDeadline = $firstStageEnd;
                    } elseif ($activePeriod && ($activePeriod->registration_end_at || $activePeriod->submission_deadline)) {
                        $regDeadline = \Carbon\Carbon::parse($activePeriod->registration_end_at ?? $activePeriod->submission_deadline);
                        if ($regDeadline->year > now()->year && str_contains($activePeriod->name ?? '', (string) now()->year)) {
                            $regDeadline = $regDeadline->year(now()->year);
                        }
                    }
                @endphp
                @if($regDeadline)
                    <div class="text-xs text-slate-600 bg-rose-50 border border-rose-100 px-3 py-1 rounded-lg self-start sm:self-auto">
                        Batas Akhir Pendaftaran: <strong class="text-rose-600 font-semibold">{{ $regDeadline->locale('id')->translatedFormat('d F Y, H.i') }} WIB</strong>
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
                        $isCurrent = !empty($stage['is_current']);
                        $isInProgress = $state === 'in_progress' || $isCurrent;

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

                        // Prioritas 3: Bedakan badge Tahap Aktif dengan tahapan yang belum dimulai
                        $stageLabel = $stage['label'] ?? 'Menunggu';
                        $badgeClass = $stage['badge_class'] ?? 'bg-slate-100 text-slate-600 border-slate-200';

                        if ($isCurrent && in_array($state, ['waiting', 'in_progress'], true)) {
                            $stageLabel = 'Tahap Aktif';
                            $badgeClass = 'bg-sky-50 text-[#0179B5] border-sky-300 font-bold';
                        } elseif (! $isPassed && ! $isFailed && ! $isCurrent) {
                            $stageLabel = 'Menunggu';
                            $badgeClass = 'bg-slate-100 text-slate-500 border-slate-200';
                        }
                    @endphp
                    <div class="p-3.5 sm:p-4 rounded-xl border {{ $cardBg }} flex flex-col justify-between transition-all min-h-[140px]">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="w-6 h-6 rounded-full {{ $stepBadgeBg }} font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ $stage['sort_order'] ?? ($index + 1) }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border whitespace-nowrap shrink-0 {{ $badgeClass }}">
                                    {{ $stageLabel }}
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

                        {{-- Prioritas 1: Rentang Tanggal Tahapan --}}
                        @php
                            $startAt = !empty($stage['start_at']) ? (\Carbon\Carbon::parse($stage['start_at'])) : null;
                            $endAt = !empty($stage['end_at']) ? (\Carbon\Carbon::parse($stage['end_at'])) : null;
                        @endphp
                        @if($startAt || $endAt)
                            <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center gap-1.5 text-[11px] text-slate-600 font-medium">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>
                                    @if($startAt && $endAt)
                                        {{ $startAt->translatedFormat('d M') }} &ndash; {{ $endAt->translatedFormat('d M Y') }}
                                    @elseif($endAt)
                                        Batas: {{ $endAt->translatedFormat('d M Y') }}
                                    @elseif($startAt)
                                        Mulai: {{ $startAt->translatedFormat('d M Y') }}
                                    @endif
                                </span>
                            </div>
                        @elseif(!empty($stage['published_at']))
                            <div class="mt-3 pt-2.5 border-t border-slate-200/60 text-[10px] text-slate-400">
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

            @if ($application)
                <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                    <a
                        href="{{ route('portal.applications.tracking', $application) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-[#0179B5] hover:bg-[#016293] text-white transition-colors shadow-xs"
                    >
                        <span>Lihat Detail Alur Seleksi</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            @endif
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
