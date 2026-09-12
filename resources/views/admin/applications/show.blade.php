<x-layouts.admin title="Verifikasi: {{ $user->name }}">
    <div class="space-y-6" x-data="{ rejectModalOpen: false, stageModalOpen: false, activeTab: 'berkas' }">
        <!-- Breadcrumb & Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('admin.applications.index') }}" class="hover:text-[#0179B5]">Lamaran Pelamar</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">{{ $user->name }}</span>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.applications.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                    &larr; Kembali ke Daftar
                </a>
            </div>
        </div>

        <!-- Candidate Banner Header -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 pb-6 border-b border-slate-200">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-[#0179B5] text-white font-bold text-xl flex items-center justify-center shadow-md flex-shrink-0">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-xl font-bold text-slate-900">{{ $user->name }}</h1>
                            <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $application->status->badgeClass() }}">
                                {{ $application->status->label() }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-y-1 gap-x-4 mt-1.5 text-xs text-slate-600">
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
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1 min-w-[240px]">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Posisi Formasi Dilamar</div>
                    <div class="font-bold text-slate-900 text-sm">{{ $application->jobVacancy->title }}</div>
                    <div class="text-slate-500">{{ $application->jobVacancy->department }} &bull; Kuota: {{ $application->jobVacancy->quota }}</div>
                </div>
            </div>

            <!-- Status Banner Alert if Rejected or Passed -->
            @if($application->isRejected())
                <div class="mt-4 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-900 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-sm">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Lamaran Telah Ditolak (Tidak Memenuhi Syarat)</span>
                    </div>
                    <p class="text-rose-700"><strong>Alasan Penolakan:</strong> {{ $application->rejection_reason }}</p>
                    @if($application->announcement_seen_at)
                        <p class="text-[11px] text-rose-600 mt-1">✓ Pelamar telah membuka dan mengonfirmasi pengumuman ini pada {{ $application->announcement_seen_at->format('d M Y, H:i') }} WIB.</p>
                    @endif
                </div>
            @elseif($application->isPassedAdministration())
                <div class="mt-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-900 text-xs flex items-center justify-between">
                    <div>
                        <div class="font-bold text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Berkas Lengkap — Lolos Seleksi Administrasi</span>
                        </div>
                        <p class="text-emerald-700 mt-0.5">Diverifikasi oleh: {{ $application->verifier->name ?? 'Tim SDI' }} pada {{ $application->verified_at ? $application->verified_at->format('d M Y, H:i') : '-' }} WIB</p>
                    </div>
                    @if($application->currentStage)
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-full">
                            Tahap Aktif: {{ $application->currentStage->stage_name }}
                        </span>
                    @endif
                </div>
            @endif

            <!-- Navigation Tabs -->
            <div class="flex items-center gap-4 mt-6 border-b border-slate-200 text-xs font-semibold">
                <button @click="activeTab = 'berkas'" :class="activeTab === 'berkas' ? 'border-[#0179B5] text-[#0179B5] border-b-2' : 'text-slate-500 hover:text-slate-800'" class="pb-3 px-1 transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Verifikasi Berkas Dokumen ({{ $uploadedDocuments->count() }}/{{ $requirements->count() }})</span>
                </button>

                <button @click="activeTab = 'profil'" :class="activeTab === 'profil' ? 'border-[#0179B5] text-[#0179B5] border-b-2' : 'text-slate-500 hover:text-slate-800'" class="pb-3 px-1 transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Biodata & Riwayat Pelamar</span>
                </button>

                <button @click="activeTab = 'tahapan'" :class="activeTab === 'tahapan' ? 'border-[#0179B5] text-[#0179B5] border-b-2' : 'text-slate-500 hover:text-slate-800'" class="pb-3 px-1 transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>Tahapan Seleksi & Timeline</span>
                </button>
            </div>
        </div>

        <!-- TAB 1: VERIFIKASI BERKAS DOKUMEN -->
        <div x-show="activeTab === 'berkas'" class="space-y-6">
            <!-- Action Verification Panel -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Panel Keputusan Verifikasi Administrasi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Periksa seluruh dokumen sebelum memberikan persetujuan kelulusan berkas</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Reject Button Trigger -->
                    <button type="button" @click="rejectModalOpen = true" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Tolak Berkas (Reject)</span>
                    </button>

                    <!-- Approve Form -->
                    <form method="POST" action="{{ route('admin.applications.verify', $application) }}" onsubmit="return confirm('Apakah Anda yakin seluruh berkas dokumen telah lengkap dan memenuhi syarat?')">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Setujui (Lolos Administrasi)</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Documents Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Nama Persyaratan Dokumen</th>
                                <th class="py-3 px-4">Sifat</th>
                                <th class="py-3 px-4">File Terunggah</th>
                                <th class="py-3 px-4">Status Berkas</th>
                                <th class="py-3 px-4">Catatan Verifikator</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($requirements as $req)
                                @php
                                    $doc = $uploadedDocuments->get($req->id);
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-4 px-4 font-semibold text-slate-900">
                                        {{ $req->document_name }}
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($req->is_mandatory)
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-800 rounded">Wajib (*)</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] text-slate-500 bg-slate-100 rounded">Opsional</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($doc)
                                            <div class="font-medium text-slate-800 truncate max-w-[200px]" title="{{ $doc->original_name }}">
                                                {{ $doc->original_name }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ round($doc->file_size / 1024, 1) }} KB &bull; {{ $doc->mime_type }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Belum diunggah</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($doc)
                                            @if($doc->verification_status === 'valid')
                                                <span class="inline-block px-2.5 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 rounded-full">
                                                    ✓ Valid / Sesuai
                                                </span>
                                            @elseif($doc->verification_status === 'invalid')
                                                <span class="inline-block px-2.5 py-0.5 text-[10px] font-semibold bg-rose-100 text-rose-800 rounded-full">
                                                    ✕ Tidak Valid
                                                </span>
                                            @else
                                                <span class="inline-block px-2.5 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-800 rounded-full">
                                                    Menunggu Review
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">
                                        {{ $doc->verification_note ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        @if($doc)
                                            <div class="flex items-center justify-end gap-2" x-data="{ docModalOpen: false }">
                                                <!-- Download Button -->
                                                <a href="{{ route('documents.download', $doc) }}" target="_blank" class="px-2.5 py-1 bg-sky-50 text-[#0179B5] hover:bg-sky-100 font-semibold rounded text-[11px] transition-colors inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                    <span>Lihat File</span>
                                                </a>

                                                <!-- Quick Status Trigger -->
                                                <button type="button" @click="docModalOpen = true" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded text-[11px] transition-colors">
                                                    Status
                                                </button>

                                                <!-- Per-Document Verification Modal -->
                                                <div x-show="docModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                                    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="docModalOpen = false"></div>
                                                    <div class="relative bg-white rounded-xl shadow-xl border border-slate-200 p-6 max-w-sm w-full text-left">
                                                        <h3 class="text-sm font-bold text-slate-900 mb-2">Verifikasi Berkas</h3>
                                                        <p class="text-xs text-slate-500 mb-4">{{ $req->document_name }}</p>

                                                        <form method="POST" action="{{ route('admin.applications.documents.verify', $doc) }}" class="space-y-4">
                                                            @csrf
                                                            <div>
                                                                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Keabsahan</label>
                                                                <select name="verification_status" required class="w-full h-9 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                                                    <option value="valid" {{ $doc->verification_status === 'valid' ? 'selected' : '' }}>Valid (Sesuai Syarat)</option>
                                                                    <option value="invalid" {{ $doc->verification_status === 'invalid' ? 'selected' : '' }}>Tidak Valid / Tidak Lengkap</option>
                                                                    <option value="pending" {{ $doc->verification_status === 'pending' ? 'selected' : '' }}>Menunggu Review</option>
                                                                </select>
                                                            </div>

                                                            <div>
                                                                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Dokumen (Opsional)</label>
                                                                <input type="text" name="verification_note" value="{{ $doc->verification_note }}" placeholder="Contoh: Legalisir cap basah valid"
                                                                       class="w-full h-9 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                                            </div>

                                                            <div class="flex justify-end gap-2 pt-2">
                                                                <button type="button" @click="docModalOpen = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">Batal</button>
                                                                <button type="submit" class="px-4 py-1.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg">Simpan</button>
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

        <!-- TAB 2: BIODATA LENGKAP PELAMAR -->
        <div x-show="activeTab === 'profil'" class="space-y-6">
            <!-- Data Pribadi & Kontak -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Pribadi -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Data Pribadi & Identitas</h2>
                    <div class="space-y-2 text-xs divide-y divide-slate-100">
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">Nama Lengkap:</span> <strong class="text-slate-900">{{ $user->name }}</strong></div>
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">NIK (KTP):</span> <strong class="text-slate-900">{{ $profile->nik ?? '-' }}</strong></div>
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">Jenis Kelamin:</span> <strong class="text-slate-900">{{ $profile->gender === 'male' ? 'Laki-laki' : ($profile->gender === 'female' ? 'Perempuan' : '-') }}</strong></div>
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">Tempat, Tanggal Lahir:</span> <strong class="text-slate-900">{{ $profile->birth_place ?? '-' }}, {{ $profile->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->format('d F Y') : '-' }}</strong></div>
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">Agama:</span> <strong class="text-slate-900">{{ $profile->religion ?? '-' }}</strong></div>
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">Status Pernikahan:</span> <strong class="text-slate-900">{{ $profile->marital_status ?? '-' }}</strong></div>
                    </div>
                </div>

                <!-- Kontak & Alamat -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Alamat & Kontak Darurat</h2>
                    <div class="space-y-2 text-xs divide-y divide-slate-100">
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">Email Utama:</span> <strong class="text-slate-900">{{ $user->email }}</strong></div>
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">No. WhatsApp / HP:</span> <strong class="text-slate-900">{{ $user->phone ?? '-' }}</strong></div>
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">Alamat KTP:</span> <strong class="text-slate-900 text-right">{{ $profile->address ?? '-' }}</strong></div>
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">Kota / Kabupaten:</span> <strong class="text-slate-900">{{ $profile->city ?? '-' }} ({{ $profile->province ?? '-' }})</strong></div>
                        <div class="flex justify-between py-1.5"><span class="text-slate-500">Kontak Darurat:</span> <strong class="text-slate-900">{{ $profile->emergency_contact_name ?? '-' }} ({{ $profile->emergency_contact_phone ?? '-' }})</strong></div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Pendidikan & STR / Lisensi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pendidikan -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Riwayat Pendidikan</h2>
                    <div class="space-y-3 text-xs">
                        @forelse ($educations as $edu)
                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                <div class="font-bold text-slate-900">{{ $edu->institution_name }}</div>
                                <div class="text-slate-600 mt-0.5">{{ $edu->degree_level }} {{ $edu->major }} (Lulus {{ $edu->graduation_year }})</div>
                                <div class="text-slate-500 text-[11px] mt-1">IPK / Nilai: <strong>{{ $edu->gpa ?? '-' }}</strong></div>
                            </div>
                        @empty
                            <p class="text-slate-400 italic">Belum ada riwayat pendidikan diisi.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Lisensi / STR -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Surat Tanda Registrasi (STR) / Lisensi</h2>
                    <div class="space-y-3 text-xs">
                        @forelse ($licenses as $lic)
                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                <div class="font-bold text-slate-900">{{ $lic->license_type }}</div>
                                <div class="text-slate-600 font-mono mt-0.5">No: {{ $lic->license_number }}</div>
                                <div class="text-slate-500 text-[11px] mt-1">Berlaku s/d: <strong>{{ $lic->valid_until ? \Carbon\Carbon::parse($lic->valid_until)->format('d M Y') : 'Seumur Hidup' }}</strong></div>
                            </div>
                        @empty
                            <p class="text-slate-400 italic">Belum ada lisensi/STR dicatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Pengalaman Kerja & Sertifikasi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pengalaman Kerja -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Pengalaman Kerja</h2>
                    <div class="space-y-3 text-xs">
                        @forelse ($workExperiences as $exp)
                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                <div class="font-bold text-slate-900">{{ $exp->position }} &bull; {{ $exp->company_name }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($exp->start_date)->format('M Y') }} - {{ $exp->is_current ? 'Sekarang' : ($exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('M Y') : '-') }}
                                </div>
                                @if($exp->job_description)
                                    <p class="text-slate-600 mt-1.5 text-[11px]">{{ $exp->job_description }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-slate-400 italic">Belum ada pengalaman kerja diisi.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Sertifikasi & Pelatihan -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#0179B5]">Sertifikasi & Pelatihan Medis</h2>
                    <div class="space-y-3 text-xs">
                        @forelse ($certifications as $cert)
                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                <div class="font-bold text-slate-900">{{ $cert->name }}</div>
                                <div class="text-slate-600 text-[11px] mt-0.5">Lembaga: {{ $cert->issuer }} (Tahun {{ $cert->issue_year }})</div>
                            </div>
                        @empty
                            <p class="text-slate-400 italic">Belum ada data sertifikasi diisi.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: TAHAPAN SELEKSI & LOG -->
        <div x-show="activeTab === 'tahapan'" class="space-y-6">
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 mb-6">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Progres Tahapan Seleksi</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola kelulusan tahapan seleksi lanjutan calon karyawan</p>
                    </div>

                    <button type="button" @click="stageModalOpen = true" class="px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <span>Update Status Tahapan</span>
                    </button>
                </div>

                <!-- Stages Progress Line -->
                <div class="space-y-4">
                    @foreach ($selectionStages as $stg)
                        @php
                            $hist = $stageHistories->get($stg->id);
                        @endphp
                        <div class="p-4 rounded-xl border {{ $hist && $hist->status === 'passed' ? 'bg-emerald-50/50 border-emerald-200' : ($hist && $hist->status === 'rejected' ? 'bg-rose-50/50 border-rose-200' : 'bg-slate-50 border-slate-200') }} flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full {{ $hist && $hist->status === 'passed' ? 'bg-emerald-600 text-white' : ($hist && $hist->status === 'rejected' ? 'bg-rose-600 text-white' : 'bg-slate-300 text-slate-700') }} font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ $stg->sequence_order }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $stg->stage_name }}</div>
                                    @if($hist && $hist->note)
                                        <p class="text-xs text-slate-600 mt-1">Catatan: {{ $hist->note }}</p>
                                    @endif
                                    @if($hist && $hist->decided_at)
                                        <span class="text-[11px] text-slate-400 block mt-0.5">Keputusan pada: {{ $hist->decided_at->format('d M Y, H:i') }} WIB</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                @if($hist)
                                    @if($hist->status === 'passed')
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">✓ Lolos</span>
                                    @elseif($hist->status === 'rejected')
                                        <span class="px-3 py-1 bg-rose-100 text-rose-800 text-xs font-bold rounded-full">✕ Tidak Lolos</span>
                                    @else
                                        <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">Sedang Diproses</span>
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

        <!-- REJECT MODAL WITH MANDATORY REASON -->
        <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="rejectModalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 max-w-lg w-full text-left">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-200 mb-4">
                    <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tolak Berkas Lamaran</h3>
                        <p class="text-xs text-slate-500">Wajib memberikan rincian alasan penolakan berkas kepada pelamar</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.applications.verify', $application) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="reject">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Alasan Penolakan Berkas <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="rejection_reason" rows="4" required placeholder="Tuliskan alasan penolakan secara jelas (contoh: STR telah habis masa berlaku / Ijazah belum terlegalisir asli)..."
                                  class="w-full p-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-rose-500 focus:bg-white"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Alasan ini akan ditampilkan langsung di banner penolakan dashboard pelamar dan dikirim via email.</p>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm">
                            Konfirmasi Tolak Berkas
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

                <form method="POST" action="{{ route('admin.applications.update-stage', $application) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Tahapan</label>
                        <select name="stage_id" required class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                            @foreach ($selectionStages as $stg)
                                <option value="{{ $stg->id }}" {{ $application->current_stage_id === $stg->id ? 'selected' : '' }}>
                                    Tahap {{ $stg->sequence_order }}: {{ $stg->stage_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Keputusan</label>
                        <select name="status" required class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                            <option value="passed">Lolos Tahapan Ini (Lanjut Tahap Berikutnya)</option>
                            <option value="in_progress">Sedang Berlangsung / Dijadwalkan</option>
                            <option value="rejected">Gugur / Tidak Lolos</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Instruksi Jadwal (Opsional)</label>
                        <textarea name="stage_note" rows="3" placeholder="Contoh: Ujian Tulis dilaksanakan pada Senin, 20 September 2026 pukul 08.00 WIB di Aula RSI Fatimah..."
                                  class="w-full p-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                        <button type="button" @click="stageModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm">
                            Simpan Progres Tahapan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
