<x-layouts.admin title="Verifikasi: {{ $user->name }}">
    @php
        $mandatoryRequirements = $requirements->where('is_mandatory', true);
        $allMandatoryValid = $mandatoryRequirements->isNotEmpty() && $mandatoryRequirements->every(function($req) use ($uploadedDocuments) {
            $d = $uploadedDocuments->get($req->id);
            return $d && $d->verification_status === 'valid';
        });
        $noInvalidOrPending = !$uploadedDocuments->contains(fn($d) => in_array($d->verification_status, ['invalid', 'pending', null], true));
        $isAllDocumentsValid = $uploadedDocuments->isNotEmpty() && $noInvalidOrPending && ($mandatoryRequirements->isEmpty() || $allMandatoryValid);

        $docsForPreview = $requirements->map(function($req) use ($uploadedDocuments) {
            $doc = $uploadedDocuments->get($req->id);
            if (!$doc) return null;
            return [
                'id' => $doc->id,
                'req_name' => $req->document_name,
                'is_mandatory' => (bool)$req->is_mandatory,
                'file_name' => $doc->original_name,
                'file_size' => round($doc->file_size / 1024, 1) . ' KB',
                'mime_type' => $doc->mime_type,
                'status' => $doc->verification_status,
                'status_label' => $doc->verification_status === 'valid' ? 'Valid' : ($doc->verification_status === 'invalid' ? 'Tidak Valid' : 'Menunggu Review'),
                'preview_url' => route('documents.preview', $doc),
                'download_url' => route('documents.download', $doc),
                'is_pdf' => str_contains(strtolower($doc->mime_type ?? ''), 'pdf') || str_ends_with(strtolower($doc->original_name ?? ''), '.pdf'),
                'is_image' => str_contains(strtolower($doc->mime_type ?? ''), 'image') || in_array(strtolower(pathinfo($doc->original_name ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']),
                'is_video' => str_contains(strtolower($doc->mime_type ?? ''), 'video') || str_ends_with(strtolower($doc->original_name ?? ''), '.mp4'),
            ];
        })->filter()->values();
    @endphp

    <div class="space-y-6" x-data="{
        rejectModalOpen: false,
        approveModalOpen: false,
        stageModalOpen: false,
        allDocsModalOpen: false,
        selectedDocIdx: 0,
        docsBundle: {{ Js::from($docsForPreview) }},
        activeTab: (new URLSearchParams(window.location.search).get('tab')) || '{{ request('tab', 'profil') }}',
        setTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },
        scrollToTarget() {
            if (window.location.hash) {
                const el = document.querySelector(window.location.hash);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.classList.add('ring-2', 'ring-[#0179B5]', 'ring-offset-2', 'transition-all', 'duration-500');
                    setTimeout(() => {
                        el.classList.remove('ring-2', 'ring-[#0179B5]', 'ring-offset-2');
                    }, 2500);
                }
            }
        }
    }"
    x-init="
        $nextTick(() => {
            setTimeout(() => scrollToTarget(), 80);
        });
        window.addEventListener('hashchange', () => scrollToTarget());
    ">
        <!-- Breadcrumb & Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('admin.applications.index') }}" class="hover:text-[#0179B5]">Lamaran Pelamar</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">{{ $user->name }}</span>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.applications.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                    &larr; Kembali ke Daftar
                </a>
            </div>
        </div>

        <!-- Candidate Banner Header -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 pb-6 border-b border-slate-200">
                <div class="flex items-start gap-4">
                    @if($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-2xl object-cover shadow-md flex-shrink-0 border border-slate-200" style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px; object-fit: cover; border-radius: 16px;">
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-[#0179B5] text-white font-bold text-xl flex items-center justify-center shadow-md flex-shrink-0">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
                            <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $application->status->badgeClass() }}">
                                {{ $application->status->label() }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-y-1 gap-x-4 mt-2 text-sm text-slate-600">
                            <span>No. Reg: <strong class="font-mono">{{ $user->registration_number ?? '-' }}</strong></span>
                            <span>&bull;</span>
                            <span>No. Lamaran: <strong class="font-mono">{{ $application->application_number ?? '-' }}</strong></span>
                            <span>&bull;</span>
                            <span>Email: <strong>{{ $user->email }}</strong></span>
                            <span>&bull;</span>
                            <span>Telepon: <strong>{{ $user->phone ?? '-' }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Formasi Info Badge -->
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm space-y-1 min-w-[240px]">
                    <div class="text-xs uppercase font-bold text-slate-400">Posisi Formasi Dilamar</div>
                    <div class="font-bold text-slate-900 text-base">{{ $application->jobVacancy->title }}</div>
                    <div class="text-slate-500 text-xs">{{ $application->jobVacancy->department }} &bull; Kuota: {{ $application->jobVacancy->quota_label }}</div>
                </div>
            </div>

            <!-- Status Banner Alert if Rejected or Passed -->
            @if($application->isRejected())
                <div class="mt-4 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-900 text-sm space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-base">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Lamaran Telah Ditolak (Tidak Memenuhi Syarat)</span>
                    </div>
                    <p class="text-rose-700"><strong>Alasan Penolakan:</strong> {{ $application->rejection_reason }}</p>
                    @if($application->announcement_seen_at)
                        <p class="text-xs text-rose-600 mt-1">✓ Pelamar telah membuka dan mengonfirmasi pengumuman ini pada {{ $application->announcement_seen_at->format('d M Y, H:i') }} WIB.</p>
                    @endif
                </div>
            @elseif($application->isPassedAdministration())
                <div class="mt-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-900 text-sm flex items-center justify-between">
                    <div>
                        <div class="font-bold text-base flex items-center gap-1.5">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Berkas Lengkap — Lolos Seleksi Administrasi</span>
                        </div>
                        <p class="text-emerald-700 mt-0.5 text-xs">Diverifikasi oleh: {{ $application->verifier->name ?? 'Tim SDI' }} pada {{ $application->verified_at ? $application->verified_at->format('d M Y, H:i') : '-' }} WIB</p>
                    </div>
                    @if($application->currentStage)
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-full text-xs">
                            Tahap Aktif: {{ $application->currentStage->stage_name }}
                        </span>
                    @endif
                </div>
            @endif

            <!-- Navigation Tabs (Urutan: Biodata -> Berkas -> Tahapan) -->
            <div class="mt-6 pt-5 border-t border-slate-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 p-1.5 bg-slate-100/80 border border-slate-200 rounded-2xl">
                    <!-- Tab 1: Biodata & Riwayat Pelamar -->
                    <button type="button" @click="setTab('profil')"
                        :class="activeTab === 'profil'
                            ? 'bg-[#0179B5] text-white shadow-md border-[#0179B5] ring-2 ring-[#0179B5]/20'
                            : 'bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border-slate-200 hover:border-slate-300 shadow-2xs'"
                        class="p-3 sm:p-3.5 rounded-xl border transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div :class="activeTab === 'profil' ? 'bg-white/20 text-white' : 'bg-[#0179B5]/10 text-[#0179B5] group-hover:bg-[#0179B5]/20'" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <span class="block font-bold text-sm leading-tight">Biodata &amp; Riwayat Pelamar</span>
                                <span :class="activeTab === 'profil' ? 'text-sky-100' : 'text-slate-500'" class="text-xs block mt-0.5">Identitas &amp; Pendidikan</span>
                            </div>
                        </div>
                        <span :class="activeTab === 'profil' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-md text-[11px] font-bold shrink-0">
                            Tab 1
                        </span>
                    </button>

                    <!-- Tab 2: Verifikasi Berkas Dokumen -->
                    <button type="button" @click="setTab('berkas')"
                        :class="activeTab === 'berkas'
                            ? 'bg-[#0179B5] text-white shadow-md border-[#0179B5] ring-2 ring-[#0179B5]/20'
                            : 'bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border-slate-200 hover:border-slate-300 shadow-2xs'"
                        class="p-3 sm:p-3.5 rounded-xl border transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div :class="activeTab === 'berkas' ? 'bg-white/20 text-white' : 'bg-[#0179B5]/10 text-[#0179B5] group-hover:bg-[#0179B5]/20'" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <span class="block font-bold text-sm leading-tight">Verifikasi Berkas Dokumen ({{ $uploadedDocuments->count() }}/{{ $requirements->count() }})</span>
                                <span :class="activeTab === 'berkas' ? 'text-sky-100' : 'text-slate-500'" class="text-xs block mt-0.5">Validasi Dokumen Syarat</span>
                            </div>
                        </div>
                        <span :class="activeTab === 'berkas' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-md text-[11px] font-bold shrink-0">
                            Tab 2
                        </span>
                    </button>

                    <!-- Tab 3: Tahapan Seleksi & Timeline -->
                    <button type="button" @click="setTab('tahapan')"
                        :class="activeTab === 'tahapan'
                            ? 'bg-[#0179B5] text-white shadow-md border-[#0179B5] ring-2 ring-[#0179B5]/20'
                            : 'bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border-slate-200 hover:border-slate-300 shadow-2xs'"
                        class="p-3 sm:p-3.5 rounded-xl border transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div :class="activeTab === 'tahapan' ? 'bg-white/20 text-white' : 'bg-[#0179B5]/10 text-[#0179B5] group-hover:bg-[#0179B5]/20'" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <div>
                                <span class="block font-bold text-sm leading-tight">Tahapan Seleksi &amp; Timeline</span>
                                <span :class="activeTab === 'tahapan' ? 'text-sky-100' : 'text-slate-500'" class="text-xs block mt-0.5">Jadwal &amp; Hasil Seleksi</span>
                            </div>
                        </div>
                        <span :class="activeTab === 'tahapan' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-md text-[11px] font-bold shrink-0">
                            Tab 3
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- TAB 1: BIODATA & RIWAYAT PELAMAR (SISI KIRI SENDIRI) -->
        <div x-show="activeTab === 'profil'" class="space-y-6">
            @php
                $latestEdu = $educations->first();
                $isNikValid = strlen(preg_replace('/[^0-9]/', '', $profile->nik ?? '')) === 16;
                $applicantAge = $profile && $profile->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->age : null;

                $genderRaw = strtolower(trim((string)($profile->gender ?? '')));
                $genderLabel = match(true) {
                    in_array($genderRaw, ['laki-laki', 'male', 'l', 'pria'], true) => 'Laki-laki',
                    in_array($genderRaw, ['perempuan', 'female', 'p', 'wanita'], true) => 'Perempuan',
                    !empty($profile->gender) => (string)$profile->gender,
                    default => '-',
                };
            @endphp

            <!-- PANEL VERIFIKASI BIODATA -->
            <div id="biodata-panel" class="scroll-mt-24 bg-white p-5 sm:p-6 rounded-xl border border-slate-200 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-[#0179B5]/10 text-[#0179B5] rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </span>
                            <h2 class="text-base font-bold text-slate-900">Panel Verifikasi Kelayakan Biodata Pelamar</h2>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Pemeriksaan instan kesesuaian data identitas dan jenjang pendidikan pelamar</p>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-50 text-[#0179B5] border border-sky-200 rounded-lg text-xs font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Formasi: {{ $application->jobVacancy->title }}
                        </span>

                        <!-- Action Buttons: Tolak & Lolos Biodata -->
                        @php
                            $biodataStage = $selectionStages->first(fn($s) => stripos($s->name, 'Pendaftaran') !== false || stripos($s->name, 'Biodata') !== false || stripos($s->name, 'Portofolio') !== false) ?? $selectionStages->first();
                            $biodataHistory = $application->stageHistories->firstWhere('vacancy_selection_stage_id', $biodataStage?->id);
                            $isBiodataPassed = $biodataHistory && $biodataHistory->status === \App\Enums\ApplicationStageStatus::PASSED;
                        @endphp

                        @if(!$application->isRejected() && !$isBiodataPassed)
                            <div class="flex items-center gap-2">
                                <button type="button" @click="rejectModalOpen = true" class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5 cursor-pointer shadow-2xs" title="Buka form konfirmasi penolakan biodata">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    <span>Tolak Biodata</span>
                                </button>

                                <button type="button" @click="approveModalOpen = true" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5 cursor-pointer" title="Buka form konfirmasi kelulusan biodata">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Lolos Verifikasi Biodata</span>
                                </button>
                            </div>
                        @else
                            @if($isBiodataPassed)
                                <span class="px-3 py-1.5 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-lg border border-emerald-300 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Biodata Terverifikasi Sesuai
                                </span>
                            @elseif($application->isRejected())
                                <span class="px-3 py-1.5 bg-rose-100 text-rose-800 text-xs font-bold rounded-lg border border-rose-300 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Lamaran Ditolak
                                </span>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- 2 Key Checklist Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- 1. Identitas & NIK -->
                    <div class="p-4 rounded-xl border {{ $isNikValid ? 'bg-emerald-50/40 border-emerald-200' : 'bg-amber-50/40 border-amber-200' }} space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Identitas & NIK</span>
                            <span class="px-2 py-0.5 text-[11px] font-bold rounded {{ $isNikValid ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $isNikValid ? '✓ NIK 16 Digit' : 'Perlu Cek' }}
                            </span>
                        </div>
                        <div>
                            <div class="text-sm font-mono font-bold text-slate-900">{{ $profile->nik ?? 'Belum Diisi' }}</div>
                            <div class="text-xs text-slate-600 mt-0.5 font-medium">
                                {{ $genderLabel }} 
                                @if($applicantAge) &bull; {{ $applicantAge }} Tahun @endif
                            </div>
                        </div>
                    </div>

                    <!-- 2. Pendidikan Terakhir -->
                    <div class="p-4 rounded-xl border {{ $latestEdu ? 'bg-emerald-50/40 border-emerald-200' : 'bg-amber-50/40 border-amber-200' }} space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Pendidikan</span>
                            <span class="px-2 py-0.5 text-[11px] font-bold rounded {{ $latestEdu ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $latestEdu ? ($latestEdu->education_level ?? $latestEdu->degree_level ?? 'Tercatat') : 'Belum Diisi' }}
                            </span>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900 truncate" title="{{ $latestEdu?->institution_name }}">
                                {{ $latestEdu ? ($latestEdu->study_program ?? $latestEdu->major ?? $latestEdu->institution_name) : 'Belum ada data' }}
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5 truncate">
                                @if($latestEdu)
                                    IPK: <strong>{{ $latestEdu->gpa ?? '-' }}</strong> &bull; Lulus: {{ $latestEdu->graduation_year ?? '-' }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kualifikasi Lowongan Cross-Check -->
                @if($application->jobVacancy->qualifications && $application->jobVacancy->qualifications->isNotEmpty())
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2.5">
                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#0179B5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            <span>Standar Kualifikasi Formasi {{ $application->jobVacancy->title }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                            @foreach($application->jobVacancy->qualifications as $qual)
                                <div class="bg-white p-2.5 rounded-lg border border-slate-200 text-xs">
                                    <span class="font-bold text-[#0179B5] block mb-0.5">{{ $qual->label ?? 'Kualifikasi' }}</span>
                                    <span class="text-slate-700">{{ $qual->value ?? $qual->description ?? '-' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- RINCIAN BIODATA LENGKAP -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Pribadi -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>Data Pribadi & Identitas</span>
                    </h2>
                    <div class="space-y-2 text-sm divide-y divide-slate-100">
                        <div class="flex justify-between py-2"><span class="text-slate-500">Nama Lengkap:</span> <strong class="text-slate-900">{{ $user->name }}</strong></div>
                        <div class="flex justify-between py-2"><span class="text-slate-500">NIK (KTP):</span> <strong class="text-slate-900 font-mono">{{ $profile->nik ?? '-' }}</strong></div>
                        <div class="flex justify-between py-2"><span class="text-slate-500">Jenis Kelamin:</span> <strong class="text-slate-900">{{ $genderLabel }}</strong></div>
                        <div class="flex justify-between py-2"><span class="text-slate-500">Tempat, Tanggal Lahir:</span> <strong class="text-slate-900">{{ $profile->birth_place ?? '-' }}, {{ $profile->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->locale('id')->translatedFormat('d F Y') : '-' }}</strong></div>
                        <div class="flex justify-between py-2"><span class="text-slate-500">Usia:</span> <strong class="text-slate-900">{{ $applicantAge ? $applicantAge . ' Tahun' : '-' }}</strong></div>
                        <div class="flex justify-between py-2"><span class="text-slate-500">No. Registrasi:</span> <strong class="text-slate-900 font-mono">{{ $user->registration_number ?? '-' }}</strong></div>
                    </div>
                </div>

                <!-- Kontak & Alamat -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Alamat & Kontak</span>
                    </h2>
                    <div class="space-y-2 text-sm divide-y divide-slate-100">
                        <div class="flex justify-between py-2"><span class="text-slate-500">Email Utama:</span> <strong class="text-slate-900">{{ $user->email }}</strong></div>
                        <div class="flex justify-between py-2"><span class="text-slate-500">No. WhatsApp / HP:</span> <strong class="text-slate-900">{{ $user->phone ?? '-' }}</strong></div>
                        <div class="flex justify-between py-2"><span class="text-slate-500">Alamat KTP:</span> <strong class="text-slate-900 text-right max-w-[280px]">{{ $profile->address_ktp ?? '-' }}</strong></div>
                        <div class="flex justify-between py-2"><span class="text-slate-500">Alamat Domisili:</span> <strong class="text-slate-900 text-right max-w-[280px]">{{ $profile->same_as_ktp ? 'Sama dengan KTP' : ($profile->address_domicile ?? '-') }}</strong></div>
                        <div class="flex justify-between py-2"><span class="text-slate-500">Status Domisili:</span> <strong class="text-slate-900">{{ $profile->same_as_ktp ? 'Sesuai KTP' : 'Domisili Berbeda' }}</strong></div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Pendidikan -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#0179B5] flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                    <span>Riwayat Pendidikan</span>
                </h2>
                <div class="space-y-3 text-sm">
                    @forelse ($educations as $edu)
                        <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="font-bold text-slate-900">{{ $edu->institution_name }}</div>
                            <div class="text-slate-700 mt-0.5 text-xs">{{ $edu->education_level ?? $edu->degree_level }} {{ $edu->study_program ?? $edu->major }} (Lulus {{ $edu->graduation_year }})</div>
                            <div class="text-slate-500 text-xs mt-1">IPK / Nilai: <strong>{{ $edu->gpa ?? '-' }}</strong> @if($edu->accreditation) &bull; Akreditasi: <strong>{{ $edu->accreditation }}</strong> @endif</div>
                        </div>
                    @empty
                        <p class="text-slate-400 italic text-sm">Belum ada riwayat pendidikan diisi.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB 2: VERIFIKASI BERKAS DOKUMEN -->
        <div x-show="activeTab === 'berkas'" class="space-y-6">
            @if($isAllDocumentsValid)
                <!-- Banner & Quick Action Seluruh Berkas Valid -->
                <div class="p-4 sm:p-5 bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-sky-500/10 border border-emerald-300 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md ring-4 ring-emerald-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm sm:text-base flex items-center gap-2">
                                <span>Seluruh Berkas Persyaratan Terverifikasi Valid ({{ $uploadedDocuments->count() }} Berkas)</span>
                                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-full border border-emerald-200">Siap Diunduh</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-0.5">Semua berkas persyaratan telah lengkap dan berstatus valid. Anda dapat melihat preview seluruh berkas sekaligus serta mengunduh seluruhnya dalam satu arsip ZIP.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap">
                        <button type="button" @click="allDocsModalOpen = true; selectedDocIdx = 0" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span>Preview &amp; Unduh Semua Berkas</span>
                        </button>
                        <a href="{{ route('admin.applications.documents.download-all', $application) }}" class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-emerald-800 border border-emerald-300 hover:border-emerald-400 font-bold text-xs rounded-xl shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer" title="Langsung unduh semua berkas dalam format ZIP">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Unduh ZIP</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Header Ringkasan Verifikasi Dokumen -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Dokumen Persyaratan Pelamar</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Klik <strong>Lihat Berkas</strong> untuk memeriksa preview dan validasi dokumen. Jika <strong>seluruh berkas telah valid</strong>, sistem otomatis meloloskan pelamar ke tahap berikutnya.
                    </p>
                </div>
                <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                    @if($uploadedDocuments->isNotEmpty() && $uploadedDocuments->contains(fn($d) => $d->verification_status !== 'valid'))
                        <form method="POST" action="{{ route('admin.applications.documents.verify-all', $application) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin memvalidkan sekaligus semua berkas yang sudah diunggah oleh pelamar ({{ $uploadedDocuments->count() }} berkas)?');">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow-md transition-all flex items-center gap-1.5 cursor-pointer group" title="Satu klik untuk memvalidkan seluruh berkas yang sudah diisi/diunggah pelamar">
                                <svg class="w-4 h-4 text-emerald-100 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Validkan Semua Berkas ({{ $uploadedDocuments->count() }})</span>
                            </button>
                        </form>
                    @endif

                    @if($isAllDocumentsValid)
                        <button type="button" @click="allDocsModalOpen = true; selectedDocIdx = 0" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span>Preview &amp; Unduh Semua (ZIP)</span>
                        </button>
                    @endif
                    <span class="px-3 py-1.5 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg shrink-0">
                        Terunggah: <strong>{{ $uploadedDocuments->count() }}</strong> dari <strong>{{ $requirements->count() }}</strong> Berkas
                    </span>
                </div>
            </div>

            <!-- Documents Table -->
            <div id="berkas-table" class="scroll-mt-24 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Nama Persyaratan Dokumen</th>
                                <th class="py-3.5 px-4">Sifat</th>
                                <th class="py-3.5 px-4">File Terunggah</th>
                                <th class="py-3.5 px-4">Status Berkas</th>
                                <th class="py-3.5 px-4">Catatan Verifikator</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($requirements as $req)
                                @php
                                    $doc = $uploadedDocuments->get($req->id);
                                @endphp
                                <tr id="doc-row-{{ $doc ? $doc->id : ('req-'.$req->id) }}" class="hover:bg-slate-50 transition-colors scroll-mt-24">
                                    <td class="py-4 px-4 font-semibold text-slate-900">
                                        {{ $req->document_name }}
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($req->is_mandatory)
                                            <span class="px-2.5 py-0.5 text-xs font-bold bg-rose-100 text-rose-800 rounded">Wajib (*)</span>
                                        @else
                                            <span class="px-2.5 py-0.5 text-xs text-slate-500 bg-slate-100 rounded">Opsional</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($doc)
                                            <div class="font-medium text-slate-800 truncate max-w-[220px]" title="{{ $doc->original_name }}">
                                                {{ $doc->original_name }}
                                            </div>
                                            <div class="text-xs text-slate-400 mt-0.5">
                                                {{ round($doc->file_size / 1024, 1) }} KB &bull; {{ $doc->mime_type }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic text-xs">Belum diunggah</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($doc)
                                            @if($doc->verification_status === 'valid')
                                                <span class="inline-block px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">
                                                    ✓ Valid / Sesuai
                                                </span>
                                            @elseif($doc->verification_status === 'invalid')
                                                <span class="inline-block px-2.5 py-1 text-xs font-semibold bg-rose-100 text-rose-800 rounded-full">
                                                    ✕ Tidak Valid
                                                </span>
                                            @else
                                                <span class="inline-block px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full">
                                                    Menunggu Review
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-slate-600 text-xs">
                                        {{ $doc->verification_note ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        @if($doc)
                                            <div class="flex items-center justify-end gap-2" x-data="{ previewModalOpen: false, statusModalOpen: false }">
                                                <!-- Preview & Verifikasi Button -->
                                                <button type="button" @click="previewModalOpen = true" class="px-3 py-1.5 bg-[#0179B5]/10 text-[#0179B5] hover:bg-[#0179B5]/20 font-semibold rounded-lg text-xs transition-colors inline-flex items-center gap-1.5 cursor-pointer shadow-2xs" title="Lihat dokumen langsung tanpa unduh">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    <span>Lihat Berkas</span>
                                                </button>

                                                <!-- Quick Status Trigger -->
                                                <button type="button" @click="statusModalOpen = true" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition-colors cursor-pointer" title="Ubah status verifikasi berkas">
                                                    Status
                                                </button>

                                                <!-- Interactive In-Browser Document Preview & Verification Modal -->
                                                <div x-show="previewModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 md:p-6" @keydown.escape.window="previewModalOpen = false">
                                                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="previewModalOpen = false"></div>
                                                    
                                                    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 flex flex-col w-full max-w-6xl h-[92vh] overflow-hidden z-10 text-left">
                                                        
                                                        <!-- Modal Top Bar -->
                                                        <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between gap-4 shrink-0 border-b border-slate-800">
                                                            <div class="flex items-center gap-3 min-w-0">
                                                                <div class="w-9 h-9 rounded-xl bg-[#0179B5] text-white flex items-center justify-center shrink-0">
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                </div>
                                                                <div class="truncate">
                                                                    <div class="flex items-center gap-2">
                                                                        <h3 class="font-bold text-sm sm:text-base text-white truncate">{{ $req->document_name }}</h3>
                                                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded {{ $doc->verification_status === 'valid' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($doc->verification_status === 'invalid' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30') }}">
                                                                            {{ $doc->verification_status === 'valid' ? 'Valid' : ($doc->verification_status === 'invalid' ? 'Tidak Valid' : 'Menunggu Review') }}
                                                                        </span>
                                                                    </div>
                                                                    <p class="text-xs text-slate-300 truncate mt-0.5">
                                                                        {{ $doc->original_name }} &bull; {{ round($doc->file_size / 1024, 1) }} KB &bull; Pelamar: <strong class="text-white">{{ $user->name }}</strong>
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center gap-2 shrink-0">
                                                                <!-- Open New Tab -->
                                                                <a href="{{ route('documents.preview', $doc) }}" target="_blank" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1.5" title="Buka di tab browser baru">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                                    <span class="hidden sm:inline">Review</span>
                                                                </a>

                                                                <!-- Download Button -->
                                                                <a href="{{ route('documents.download', $doc) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1.5" title="Unduh file ke komputer">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                                    <span class="hidden sm:inline">Unduh</span>
                                                                </a>

                                                                <!-- Close Button -->
                                                                <button type="button" @click="previewModalOpen = false" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white flex items-center justify-center transition-colors cursor-pointer ml-1" title="Tutup">
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Modal Main Content (Split View: Preview Viewer + Verification Form) -->
                                                        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden min-h-0 bg-slate-100">
                                                            
                                                            <!-- Left: Document Interactive Viewer -->
                                                            <div class="flex-1 h-full p-2 sm:p-3 overflow-hidden flex flex-col min-h-0">
                                                                @if(str_contains(strtolower($doc->mime_type ?? ''), 'pdf') || str_ends_with(strtolower($doc->original_name ?? ''), '.pdf'))
                                                                    <iframe src="{{ route('documents.preview', $doc) }}" class="w-full h-full bg-white rounded-xl border border-slate-300 shadow-inner" style="min-height: 100%;"></iframe>
                                                                @elseif(str_contains(strtolower($doc->mime_type ?? ''), 'image') || in_array(strtolower(pathinfo($doc->original_name ?? '', PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']))
                                                                    <div class="w-full h-full bg-slate-900 rounded-xl border border-slate-300 overflow-auto flex items-center justify-center p-4">
                                                                        <img src="{{ route('documents.preview', $doc) }}" alt="{{ $doc->original_name }}" class="max-h-full max-w-full object-contain rounded shadow-lg">
                                                                    </div>
                                                                @elseif(str_contains(strtolower($doc->mime_type ?? ''), 'video') || str_ends_with(strtolower($doc->original_name ?? ''), '.mp4'))
                                                                    <div class="w-full h-full bg-slate-900 rounded-xl border border-slate-300 overflow-auto flex flex-col items-center justify-center p-4 gap-3">
                                                                        <video controls class="w-full max-h-[70vh] rounded-lg">
                                                                            <source src="{{ route('documents.preview', $doc) }}" type="video/mp4">
                                                                            Browser tidak mendukung video preview.
                                                                        </video>
                                                                        <a href="{{ route('documents.download', $doc) }}" class="px-3.5 py-1.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1.5">
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                                            <span>Download Video</span>
                                                                        </a>
                                                                    </div>
                                                                @else
                                                                    <div class="w-full h-full bg-white rounded-xl border border-slate-300 flex flex-col items-center justify-center p-8 text-center">
                                                                        <svg class="w-16 h-16 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                        <p class="font-bold text-slate-800 text-base mb-1">{{ $doc->original_name }}</p>
                                                                        <p class="text-xs text-slate-500 mb-4">Preview langsung tidak didukung untuk format ini ({{ $doc->mime_type }}).</p>
                                                                        <a href="{{ route('documents.download', $doc) }}" class="px-4 py-2 bg-[#0179B5] text-white text-xs font-semibold rounded-lg shadow-sm hover:bg-[#016293] transition-colors inline-flex items-center gap-2">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                                            <span>Unduh File</span>
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <!-- Right: Live Verification Form & Details Panel -->
                                                            <div class="w-full lg:w-96 bg-white border-t lg:border-t-0 lg:border-l border-slate-200 p-5 flex flex-col justify-between overflow-y-auto shrink-0 shadow-sm">
                                                                <form method="POST" action="{{ route('admin.applications.documents.verify', $doc) }}" class="space-y-4">
                                                                    @csrf
                                                                    
                                                                    <div>
                                                                        <h4 class="text-xs font-bold uppercase tracking-wider text-[#0179B5] mb-0.5">Verifikasi Berkas</h4>
                                                                        <p class="text-xs text-slate-500">Tentukan status keabsahan dokumen persyaratan ini</p>
                                                                    </div>

                                                                    <!-- Status Selection -->
                                                                    <div class="space-y-2">
                                                                        <label class="block text-xs font-bold text-slate-800">Status Keabsahan <span class="text-rose-500">*</span></label>
                                                                        
                                                                        <div class="space-y-1.5">
                                                                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/40 cursor-pointer transition-all has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 has-[:checked]:ring-1 has-[:checked]:ring-emerald-500/30">
                                                                                <input type="radio" name="verification_status" value="valid" {{ $doc->verification_status === 'valid' ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                                                                <div>
                                                                                    <span class="block text-xs font-bold text-slate-900">✓ Valid (Sesuai Syarat)</span>
                                                                                    <span class="block text-[11px] text-slate-500">Berkas lengkap dan sesuai</span>
                                                                                </div>
                                                                            </label>

                                                                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:border-rose-500 hover:bg-rose-50/40 cursor-pointer transition-all has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50 has-[:checked]:ring-1 has-[:checked]:ring-rose-500/30">
                                                                                <input type="radio" name="verification_status" value="invalid" {{ $doc->verification_status === 'invalid' ? 'checked' : '' }} class="w-4 h-4 text-rose-600 focus:ring-rose-500 border-slate-300">
                                                                                <div>
                                                                                    <span class="block text-xs font-bold text-slate-900">✕ Tidak Valid</span>
                                                                                    <span class="block text-[11px] text-slate-500">Berkas buram/tidak memenuhi syarat</span>
                                                                                </div>
                                                                            </label>

                                                                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:border-amber-500 hover:bg-amber-50/40 cursor-pointer transition-all has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50 has-[:checked]:ring-1 has-[:checked]:ring-amber-500/30">
                                                                                <input type="radio" name="verification_status" value="pending" {{ in_array($doc->verification_status, ['pending', null], true) ? 'checked' : '' }} class="w-4 h-4 text-amber-600 focus:ring-amber-500 border-slate-300">
                                                                                <div>
                                                                                    <span class="block text-xs font-bold text-slate-900">⏳ Menunggu Review</span>
                                                                                    <span class="block text-[11px] text-slate-500">Pemeriksaan lanjutan</span>
                                                                                </div>
                                                                            </label>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Verification Note -->
                                                                    <div>
                                                                        <label class="block text-xs font-bold text-slate-800 mb-1">Catatan Verifikator (Opsional)</label>
                                                                        <input type="text" name="verification_note" value="{{ $doc->verification_note }}" placeholder="Contoh: Legalisir cap basah valid"
                                                                               class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:border-[#0179B5] focus:bg-white transition-all">
                                                                    </div>

                                                                    <!-- Submit Buttons -->
                                                                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                                                                        <button type="button" @click="previewModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                                                                            Tutup
                                                                        </button>
                                                                        <button type="submit" class="px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5 cursor-pointer">
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                            <span>Simpan Status</span>
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Per-Document Quick Status Modal -->
                                                <div x-show="statusModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="statusModalOpen = false">
                                                    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="statusModalOpen = false"></div>
                                                    <div class="relative bg-white rounded-xl shadow-xl border border-slate-200 p-6 max-w-sm w-full text-left">
                                                        <h3 class="text-base font-bold text-slate-900 mb-1">Status Verifikasi Berkas</h3>
                                                        <p class="text-xs text-slate-500 mb-4">{{ $req->document_name }}</p>

                                                        <form method="POST" action="{{ route('admin.applications.documents.verify', $doc) }}" class="space-y-4">
                                                            @csrf
                                                            <div>
                                                                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Keabsahan</label>
                                                                <select name="verification_status" required class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                                                    <option value="valid" {{ $doc->verification_status === 'valid' ? 'selected' : '' }}>Valid (Sesuai Syarat)</option>
                                                                    <option value="invalid" {{ $doc->verification_status === 'invalid' ? 'selected' : '' }}>Tidak Valid / Tidak Lengkap</option>
                                                                    <option value="pending" {{ $doc->verification_status === 'pending' ? 'selected' : '' }}>Menunggu Review</option>
                                                                </select>
                                                            </div>

                                                            <div>
                                                                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Dokumen (Opsional)</label>
                                                                <input type="text" name="verification_note" value="{{ $doc->verification_note }}" placeholder="Contoh: Legalisir cap basah valid"
                                                                       class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                                            </div>

                                                            <div class="flex justify-end gap-2 pt-2">
                                                                <button type="button" @click="statusModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg cursor-pointer">Batal</button>
                                                                <button type="submit" class="px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-sm font-semibold rounded-lg cursor-pointer">Simpan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: TAHAPAN SELEKSI & LOG -->
        <div x-show="activeTab === 'tahapan'" class="space-y-6">
            @php
                $vacancy = $application->jobVacancy;
                $hasQuotaLimit = $vacancy->hasQuotaLimit();
                $vacQuota = $vacancy->quota;
                $vacAcceptedCount = \App\Models\JobApplication::where('job_vacancy_id', $application->job_vacancy_id)
                    ->where('status', \App\Enums\JobApplicationStatus::ACCEPTED)
                    ->count();
                $isVacQuotaFull = $hasQuotaLimit && ($vacAcceptedCount >= $vacQuota);
                $isCurrentAppAccepted = $application->status === \App\Enums\JobApplicationStatus::ACCEPTED;
            @endphp

            <!-- Quota Status Banner -->
            <div class="p-4 rounded-xl border {{ $isVacQuotaFull ? 'bg-amber-50/70 border-amber-200 text-amber-900' : 'bg-sky-50/70 border-sky-200 text-sky-950' }} flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $isVacQuotaFull ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-[#0179B5]' }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider {{ $isVacQuotaFull ? 'text-amber-800' : 'text-[#0179B5]' }}">Kapasitas Kuota Formasi</span>
                            @if(! $hasQuotaLimit)
                                <span class="px-2 py-0.5 text-[10px] font-extrabold bg-sky-200 text-sky-900 rounded-md">TIDAK DIBATASI (UNLIMITED)</span>
                            @elseif($isVacQuotaFull)
                                <span class="px-2 py-0.5 text-[10px] font-extrabold bg-amber-200 text-amber-900 rounded-md">KUOTA PENUH</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-extrabold bg-emerald-100 text-emerald-800 rounded-md">TERSEDIA</span>
                            @endif
                        </div>
                        <p class="text-xs mt-0.5 {{ $isVacQuotaFull ? 'text-amber-800' : 'text-slate-600' }}">
                            @if($hasQuotaLimit)
                                Kuota Dibutuhkan: <strong>{{ $vacQuota }} Orang</strong> &bull; Kandidat Diterima: <strong>{{ $vacAcceptedCount }} Orang</strong> &bull; Sisa Kuota: <strong>{{ max(0, $vacQuota - $vacAcceptedCount) }} Orang</strong>
                            @else
                                Kuota Formasi: <strong>Tidak Dibatasi / Sesuai Kebutuhan</strong> &bull; Kandidat Diterima Saat Ini: <strong>{{ $vacAcceptedCount }} Orang</strong>
                            @endif
                        </p>
                    </div>
                </div>

                @if($isVacQuotaFull && !$isCurrentAppAccepted)
                    <div class="text-xs font-medium text-amber-800 bg-amber-100/80 px-3 py-1.5 rounded-lg border border-amber-300/60">
                        ⚠️ Auto-Validation Aktif: Kelulusan akhir pelamar ini dicegah otomatis sampai kuota ditambah.
                    </div>
                @endif
            </div>

            <div id="tahapan-timeline" class="scroll-mt-24 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Progres Tahapan Seleksi</h2>
                        <p class="text-sm text-slate-500 mt-0.5">Kelola kelulusan tahapan seleksi lanjutan calon karyawan</p>
                    </div>

                    <button type="button" @click="stageModalOpen = true" class="px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-sm font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <span>Update Status Tahapan</span>
                    </button>
                </div>

                <!-- Stages Progress Line -->
                <div class="space-y-4">
                    @foreach ($selectionStages as $stg)
                        @php
                            $hist = $stageHistories->get($stg->id);
                            $histStatus = $hist ? ($hist->status instanceof \App\Enums\ApplicationStageStatus ? $hist->status->value : (string)$hist->status) : null;
                        @endphp
                        <div id="stage-item-{{ $stg->id }}" class="scroll-mt-24 p-4 rounded-xl border {{ $histStatus === 'passed' ? 'bg-emerald-50/50 border-emerald-200' : (in_array($histStatus, ['failed', 'rejected']) ? 'bg-rose-50/50 border-rose-200' : ($histStatus === 'in_progress' ? 'bg-blue-50/50 border-blue-200' : 'bg-slate-50 border-slate-200')) }} flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full {{ $histStatus === 'passed' ? 'bg-emerald-600 text-white' : (in_array($histStatus, ['failed', 'rejected']) ? 'bg-rose-600 text-white' : ($histStatus === 'in_progress' ? 'bg-blue-600 text-white' : 'bg-slate-300 text-slate-700')) }} font-bold text-sm flex items-center justify-center flex-shrink-0">
                                    {{ $stg->sequence_order ?? $stg->sort_order }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $stg->stage_name }}</div>
                                    @if($hist && $hist->note)
                                        <p class="text-sm text-slate-600 mt-1">Catatan: {{ $hist->note }}</p>
                                    @endif
                                    @if($hist && $hist->decided_at)
                                        <span class="text-xs text-slate-400 block mt-0.5">Keputusan pada: {{ $hist->decided_at->format('d M Y, H:i') }} WIB</span>
                                    @endif
                                    @if($hist && $hist->attachment_path)
                                        <div class="mt-2">
                                            <a href="{{ route('admin.applications.stages.download-attachment', ['application' => $application, 'history' => $hist]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-sky-50 text-[#0179B5] hover:bg-sky-100 border border-sky-200 rounded-md transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                <span>📎 Lihat Lampiran Hasil / Dokumen ({{ $hist->attachment_original_name ?: basename($hist->attachment_path) }})</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div>
                                @if($hist)
                                    @if($histStatus === 'passed')
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">✓ Lolos</span>
                                    @elseif($histStatus === 'failed' || $histStatus === 'rejected')
                                        <span class="px-3 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-full">✕ Tidak Lolos</span>
                                    @elseif($histStatus === 'in_progress')
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">Sedang Berlangsung</span>
                                    @else
                                        <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">Menunggu</span>
                                    @endif
                                @else
                                    <span class="px-3 py-1 bg-slate-200 text-slate-600 text-xs font-semibold rounded-full">Belum Dimulai</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- APPROVE CONFIRMATION MODAL (POPUP PERINGATAN LOLOS BIODATA) -->
        <div x-show="approveModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="approveModalOpen = false">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="approveModalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 max-w-lg w-full text-left z-10">
                <div class="flex items-start gap-3.5 pb-4 border-b border-slate-200">
                    <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Konfirmasi Kelulusan Verifikasi Biodata</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pemeriksaan kelayakan data identitas, pendidikan, STR, dan riwayat pelamar.</p>
                    </div>
                </div>

                <!-- Candidate & Vacancy Summary -->
                <div class="my-4 p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Pelamar:</span>
                        <strong class="text-slate-900">{{ $user->name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Formasi Dilamar:</span>
                        <strong class="text-[#0179B5]">{{ $application->jobVacancy->title }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">No. Registrasi / Lamaran:</span>
                        <span class="font-mono text-slate-700 font-semibold">{{ $user->registration_number ?? '-' }} / {{ $application->application_number ?? '-' }}</span>
                    </div>
                </div>

                <!-- Warning Notice -->
                <div class="p-3 bg-emerald-50/80 border border-emerald-200 rounded-xl text-xs text-emerald-900 flex items-start gap-2 mb-4">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Setelah disetujui, pelamar akan dinyatakan <strong>Lolos Verifikasi Biodata & Portofolio</strong>. (Verifikasi berkas persyaratan diproses tersendiri pada tab Verifikasi Berkas Dokumen).</span>
                </div>

                <form method="POST" action="{{ route('admin.applications.verify', $application) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="target" value="biodata">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Catatan Verifikator Biodata (Opsional)
                        </label>
                        <textarea name="admin_note" rows="2" placeholder="Contoh: Kualifikasi pendidikan dan STR aktif sesuai formasi..."
                                  class="w-full p-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:border-emerald-500 focus:bg-white"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" @click="approveModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Ya, Loloskan Biodata</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- REJECT CONFIRMATION MODAL (POPUP PERINGATAN TOLAK BIODATA) -->
        <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="rejectModalOpen = false">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="rejectModalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 max-w-lg w-full text-left z-10">
                <div class="flex items-start gap-3.5 pb-4 border-b border-slate-200">
                    <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Peringatan: Konfirmasi Penolakan Biodata</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Lamaran akan dinyatakan tidak memenuhi syarat pada verifikasi biodata/portofolio.</p>
                    </div>
                </div>

                <!-- Candidate & Vacancy Summary -->
                <div class="my-4 p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Pelamar:</span>
                        <strong class="text-slate-900">{{ $user->name }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Formasi Dilamar:</span>
                        <strong class="text-rose-600">{{ $application->jobVacancy->title }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">No. Registrasi / Lamaran:</span>
                        <span class="font-mono text-slate-700 font-semibold">{{ $user->registration_number ?? '-' }} / {{ $application->application_number ?? '-' }}</span>
                    </div>
                </div>

                <!-- Warning Notice -->
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-900 flex items-start gap-2 mb-4">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>Peringatan: Pelamar akan dinyatakan <strong>Tidak Lolos</strong> dan menerima email notifikasi resmi beserta rincian alasan penolakan di bawah.</span>
                </div>

                <form method="POST" action="{{ route('admin.applications.verify', $application) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="target" value="biodata">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Alasan Penolakan Biodata <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="rejection_reason" rows="3" required placeholder="Tuliskan alasan penolakan secara jelas (contoh: Kualifikasi pendidikan tidak sesuai / STR kedaluwarsa)..."
                                  class="w-full p-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:border-rose-500 focus:bg-white"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span>Konfirmasi Tolak Biodata</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- STAGE UPDATE MODAL -->
        <div x-show="stageModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="stageModalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 max-w-lg w-full text-left">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-200 mb-4">
                    <div class="w-10 h-10 rounded-full bg-sky-100 text-[#0179B5] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Pembaruan Tahapan Seleksi</h3>
                        <p class="text-xs text-slate-500">Update hasil kelulusan tahapan pelamar</p>
                    </div>
                </div>

                <div class="p-3 mb-4 rounded-lg bg-slate-50 border border-slate-200 text-xs flex items-center justify-between">
                    <div>
                        <span class="text-slate-500">Kuota Formasi:</span>
                        <strong class="text-slate-800">{{ $hasQuotaLimit ? $vacQuota . ' Orang' : 'Tidak Dibatasi' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500">Telah Diterima:</span>
                        <strong class="{{ $isVacQuotaFull ? 'text-amber-700' : 'text-emerald-700' }}">{{ $vacAcceptedCount }} Orang</strong>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.applications.update-stage', $application) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Tahapan</label>
                        <select name="stage_id" required class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                            @foreach ($selectionStages as $stg)
                                <option value="{{ $stg->id }}" {{ $application->current_stage_id === $stg->id ? 'selected' : '' }}>
                                    Tahap {{ $stg->sequence_order ?? $stg->sort_order }}: {{ $stg->stage_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Keputusan</label>
                        <select name="status" required class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                            <option value="passed">Lolos Tahapan Ini (Lanjut Tahap Berikutnya)</option>
                            <option value="in_progress">Sedang Berlangsung / Dijadwalkan</option>
                            <option value="rejected">Gugur / Tidak Lolos</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Instruksi Jadwal (Opsional)</label>
                        <textarea name="stage_note" rows="3" placeholder="Contoh: Ujian Tulis dilaksanakan pada Senin, 20 September 2026 pukul 08.00 WIB di Aula RSI Fatimah..."
                                  class="w-full p-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lampiran Hasil Tes / Dokumen Pengumuman (Opsional)</label>
                        <input type="file" name="stage_attachment" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-[#0179B5] hover:file:bg-sky-100 border border-slate-300 rounded-lg bg-slate-50 p-2 focus:outline-none">
                        <p class="text-[11px] text-slate-500 mt-1">Format: PDF, JPG, PNG (Maks 10 MB)</p>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" @click="stageModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-sm font-semibold rounded-lg shadow-sm cursor-pointer">
                            Simpan Progres Tahapan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MULTI-DOCUMENT PREVIEW & BATCH DOWNLOAD MODAL -->
        <div x-show="allDocsModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 md:p-6" @keydown.escape.window="allDocsModalOpen = false">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="allDocsModalOpen = false"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 flex flex-col w-full max-w-7xl h-[92vh] overflow-hidden z-10 text-left">
                <!-- Modal Top Bar -->
                <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between gap-4 shrink-0 border-b border-slate-800">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="truncate">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-sm sm:text-base text-white truncate">Preview &amp; Unduh Seluruh Berkas Pelamar</h3>
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    ✓ <span x-text="docsBundle.length"></span> Berkas Terlampir
                                </span>
                            </div>
                            <p class="text-xs text-slate-300 truncate mt-0.5">
                                Pelamar: <strong class="text-white">{{ $user->name }}</strong> &bull; No. Reg: <span class="font-mono text-slate-200">{{ $user->registration_number ?? '-' }}</span> &bull; Posisi: <span class="text-slate-200">{{ $application->jobVacancy->title }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <!-- Batch ZIP Download Button -->
                        <a href="{{ route('admin.applications.documents.download-all', $application) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all inline-flex items-center gap-2 cursor-pointer" title="Unduh seluruh berkas terlampir dalam 1 file ZIP">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Unduh Semua (.ZIP)</span>
                        </a>

                        <!-- Close Button -->
                        <button type="button" @click="allDocsModalOpen = false" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white flex items-center justify-center transition-colors cursor-pointer ml-1" title="Tutup">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body: Split View (Left Document List Sidebar + Right Live Preview Window) -->
                <div class="flex-1 flex flex-col lg:flex-row overflow-hidden min-h-0 bg-slate-100">
                    <!-- Left Sidebar: Document List -->
                    <div class="w-full lg:w-80 bg-white border-b lg:border-b-0 lg:border-r border-slate-200 flex flex-col shrink-0 overflow-hidden">
                        <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Daftar Dokumen</span>
                            <span class="text-[11px] font-semibold text-slate-500" x-text="'Dokumen ' + (selectedDocIdx + 1) + ' dari ' + docsBundle.length"></span>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto divide-y divide-slate-100 p-2 space-y-1.5">
                            <template x-for="(doc, idx) in docsBundle" :key="doc.id">
                                <div @click="selectedDocIdx = idx"
                                     :class="selectedDocIdx === idx ? 'bg-sky-50/90 border-[#0179B5] shadow-xs ring-1 ring-[#0179B5]/20' : 'bg-white hover:bg-slate-50/80 border-slate-200'"
                                     class="p-3 rounded-xl border transition-all cursor-pointer group flex items-start justify-between gap-2.5">
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <div :class="selectedDocIdx === idx ? 'bg-[#0179B5] text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-[#0179B5]/10 group-hover:text-[#0179B5]'"
                                             class="w-7 h-7 rounded-lg text-xs font-bold flex items-center justify-center shrink-0 transition-colors mt-0.5"
                                             x-text="idx + 1">
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <h4 :class="selectedDocIdx === idx ? 'text-[#0179B5] font-bold' : 'text-slate-900 font-semibold'"
                                                    class="text-xs leading-snug truncate"
                                                    x-text="doc.req_name"></h4>
                                                <template x-if="doc.is_mandatory">
                                                    <span class="text-[9px] font-bold text-rose-600 bg-rose-50 px-1 py-0.2 rounded shrink-0">*</span>
                                                </template>
                                            </div>
                                            <p class="text-[11px] text-slate-500 truncate mt-0.5" x-text="doc.file_name"></p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] text-slate-400 font-mono" x-text="doc.file_size"></span>
                                                <span :class="doc.status === 'valid' ? 'bg-emerald-100 text-emerald-800' : (doc.status === 'invalid' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800')"
                                                      class="px-1.5 py-0.2 text-[9px] font-bold rounded"
                                                      x-text="doc.status_label"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <a :href="doc.download_url"
                                       @click.stop
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors shrink-0"
                                       title="Unduh file ini">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Right Panel: Document Interactive Viewer -->
                    <div class="flex-1 flex flex-col overflow-hidden min-h-0">
                        <!-- Viewer Toolbar -->
                        <div class="px-4 py-2.5 bg-white border-b border-slate-200 flex items-center justify-between gap-3 shrink-0">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="px-2 py-0.5 bg-sky-100 text-[#0179B5] rounded text-xs font-bold shrink-0" x-text="'Dokumen #' + (selectedDocIdx + 1)"></span>
                                <span class="font-bold text-slate-800 text-xs sm:text-sm truncate" x-text="docsBundle[selectedDocIdx]?.req_name"></span>
                                <span class="text-xs text-slate-400 hidden sm:inline">&bull;</span>
                                <span class="text-xs text-slate-500 font-mono truncate hidden sm:inline" x-text="docsBundle[selectedDocIdx]?.file_name"></span>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <!-- Previous Document -->
                                <button type="button"
                                        @click="if(selectedDocIdx > 0) selectedDocIdx--"
                                        :disabled="selectedDocIdx === 0"
                                        :class="selectedDocIdx === 0 ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200 cursor-pointer shadow-2xs'"
                                        class="px-2.5 py-1 rounded-lg border text-xs font-semibold flex items-center gap-1 transition-all"
                                        title="Dokumen Sebelumnya">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                    <span class="hidden md:inline">Sebelumnya</span>
                                </button>

                                <!-- Next Document -->
                                <button type="button"
                                        @click="if(selectedDocIdx < docsBundle.length - 1) selectedDocIdx++"
                                        :disabled="selectedDocIdx === docsBundle.length - 1"
                                        :class="selectedDocIdx === docsBundle.length - 1 ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200 cursor-pointer shadow-2xs'"
                                        class="px-2.5 py-1 rounded-lg border text-xs font-semibold flex items-center gap-1 transition-all"
                                        title="Dokumen Selanjutnya">
                                    <span class="hidden md:inline">Selanjutnya</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>

                                <div class="h-4 w-px bg-slate-200 mx-1"></div>

                                <!-- Open in New Tab -->
                                <a :href="docsBundle[selectedDocIdx]?.preview_url" target="_blank" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1 shadow-2xs" title="Buka berkas di tab browser baru">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    <span class="hidden sm:inline">Tab Baru</span>
                                </a>

                                <!-- Single File Download -->
                                <a :href="docsBundle[selectedDocIdx]?.download_url" class="px-2.5 py-1 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1 shadow-2xs" title="Unduh berkas ini">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    <span class="hidden sm:inline">Unduh</span>
                                </a>
                            </div>
                        </div>

                        <!-- Viewer Canvas -->
                        <div class="flex-1 p-2 sm:p-3 overflow-hidden flex flex-col min-h-0 bg-slate-100">
                            <template x-if="docsBundle[selectedDocIdx]?.is_pdf">
                                <iframe :src="docsBundle[selectedDocIdx]?.preview_url" class="w-full h-full bg-white rounded-xl border border-slate-300 shadow-inner" style="min-height: 100%;"></iframe>
                            </template>

                            <template x-if="docsBundle[selectedDocIdx]?.is_image">
                                <div class="w-full h-full bg-slate-900 rounded-xl border border-slate-300 overflow-auto flex items-center justify-center p-4">
                                    <img :src="docsBundle[selectedDocIdx]?.preview_url" :alt="docsBundle[selectedDocIdx]?.file_name" class="max-h-full max-w-full object-contain rounded shadow-lg">
                                </div>
                            </template>

                            <template x-if="docsBundle[selectedDocIdx]?.is_video">
                                <div class="w-full h-full bg-slate-900 rounded-xl border border-slate-300 overflow-auto flex flex-col items-center justify-center p-4 gap-3">
                                    <video controls class="w-full max-h-[70vh] rounded-lg">
                                        <source :src="docsBundle[selectedDocIdx]?.preview_url" type="video/mp4">
                                        Browser tidak mendukung video preview.
                                    </video>
                                    <a :href="docsBundle[selectedDocIdx]?.download_url" class="px-3.5 py-1.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Download Video</span>
                                    </a>
                                </div>
                            </template>

                            <template x-if="!docsBundle[selectedDocIdx]?.is_pdf && !docsBundle[selectedDocIdx]?.is_image && !docsBundle[selectedDocIdx]?.is_video">
                                <div class="w-full h-full bg-white rounded-xl border border-slate-300 flex flex-col items-center justify-center p-8 text-center">
                                    <svg class="w-16 h-16 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="font-bold text-slate-800 text-base mb-1" x-text="docsBundle[selectedDocIdx]?.file_name"></p>
                                    <p class="text-xs text-slate-500 mb-4">Preview langsung tidak didukung untuk format ini (<span x-text="docsBundle[selectedDocIdx]?.mime_type"></span>).</p>
                                    <a :href="docsBundle[selectedDocIdx]?.download_url" class="px-4 py-2 bg-[#0179B5] text-white text-xs font-semibold rounded-lg shadow-sm hover:bg-[#016293] transition-colors inline-flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span>Unduh File</span>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
