<x-layouts.applicant title="Lamaran Saya - Workspace Dokumen">
    <div class="space-y-6" x-data="{ uploadModalOpen: false, selectedReqId: null, selectedReqName: '' }">
        @if(!$application)
            <div class="flex justify-end">
                <a href="{{ route('portal.applications.create') }}" class="px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                    + Pilih Formasi Lowongan
                </a>
            </div>
        @endif

        @if($application)
            <!-- Active Application Overview Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-slate-200">
                    <div>
                        <span class="text-xs font-bold text-slate-400 font-mono">{{ $application->application_number ?? 'DRAFT' }}</span>
                        <h1 class="text-xl font-extrabold text-slate-900 mt-1">{{ $application->jobVacancy->title }}</h1>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600 mt-1">
                            <span>Unit: <strong>{{ $application->jobVacancy->department }}</strong></span>
                            <span>&bull;</span>
                            <span>Formasi: <strong>{{ $application->jobVacancy->formation_type }}</strong></span>
                            <span>&bull;</span>
                            <span>Periode: <strong>{{ $application->recruitmentPeriod->name ?? '-' }}</strong></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="px-3.5 py-1.5 text-xs font-bold rounded-full border {{ $application->status->badgeClass() }}">
                            {{ $application->status->label() }}
                        </span>
                    </div>
                </div>

                <!-- Status Guidance Notice -->
                @if($application->isDraft())
                    <div class="p-4 bg-sky-50 border border-sky-200 rounded-xl text-xs text-sky-900 flex items-start justify-between gap-4">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-[#0179B5] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <strong class="text-sm block mb-0.5 text-sky-950">Petunjuk Pengunggahan Berkas</strong>
                                <p class="text-sky-800">
                                    Silakan unggah seluruh dokumen wajib bertanda <strong>(*)</strong> di bawah ini dalam format <strong>PDF</strong>, ukuran maksimal 5 MB per file. Pastikan dokumen terbaca dengan jelas sebelum menekan tombol <strong>Final Submit</strong>.
                                </p>
                            </div>
                        </div>

                        <!-- Submit Final Button -->
                        <form method="POST" action="{{ route('portal.applications.submit', $application) }}">
                            @csrf
                            <button
                                type="button"
                                data-confirm="Apakah Anda yakin seluruh berkas dokumen telah sesuai? Berkas yang sudah disubmit tidak dapat diubah kembali."
                                data-confirm-title="Konfirmasi Submit Lamaran"
                                data-confirm-btn="Ya, Submit Lamaran"
                                data-confirm-variant="primary"
                                class="px-5 py-2.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-lg shadow-sm transition-colors whitespace-nowrap cursor-pointer"
                            >
                                Final Submit Lamaran &rarr;
                            </button>
                        </form>
                    </div>
                @elseif($application->isRejected())
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-900 space-y-2">
                        <div class="font-bold flex items-center gap-2 text-sm text-rose-950">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Hasil Verifikasi: Berkas Tidak Memenuhi Syarat</span>
                        </div>
                        <p class="text-rose-800"><strong>Catatan Alasan Penolakan:</strong> {{ $application->rejection_reason }}</p>

                        @if(is_null($application->announcement_seen_at))
                            <form method="POST" action="{{ route('portal.applications.acknowledge', $application) }}" class="pt-2">
                                @csrf
                                <button type="submit" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                                    Konfirmasi Telah Membaca Pengumuman
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900 flex items-center justify-between">
                        <div>
                            <strong class="text-sm block text-emerald-950">Berkas Terkunci & Telah Dikirim</strong>
                            <p class="text-emerald-800 mt-0.5">Waktu submit: {{ $application->submitted_at ? $application->submitted_at->format('d F Y, H:i') : '-' }} WIB</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Documents Checklist Workspace Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Berkas Persyaratan Lamaran</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar dokumen wajib & pendukung untuk formasi {{ $application->jobVacancy->title }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Nama Dokumen Persyaratan</th>
                                <th class="py-3.5 px-4">Sifat</th>
                                <th class="py-3.5 px-4">File Terunggah</th>
                                <th class="py-3.5 px-4">Status Validasi</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php
                                $uploadedDocs = $application->documents->keyBy('vacancy_document_requirement_id');
                            @endphp

                            @foreach ($application->jobVacancy->documentRequirements as $req)
                                @php
                                    $doc = $uploadedDocs->get($req->id);
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
                                            <div class="font-medium text-slate-800 truncate max-w-[240px]" title="{{ $doc->original_name }}">
                                                {{ $doc->original_name }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ round($doc->file_size / 1024, 1) }} KB &bull; Diunggah {{ $doc->updated_at->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Belum diunggah</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($doc)
                                            @if($doc->verification_status === 'valid')
                                                <span class="inline-block px-2.5 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 rounded-full">
                                                    ✓ Terverifikasi Sesuai
                                                </span>
                                            @elseif($doc->verification_status === 'invalid')
                                                <span class="inline-block px-2.5 py-0.5 text-[10px] font-semibold bg-rose-100 text-rose-800 rounded-full">
                                                    ✕ Berkas Tidak Valid
                                                </span>
                                            @else
                                                <span class="inline-block px-2.5 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-800 rounded-full">
                                                    ⏳ Menunggu Review
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($doc)
                                                <a href="{{ route('documents.download', $doc) }}" target="_blank" class="px-2.5 py-1 bg-sky-50 text-[#0179B5] hover:bg-sky-100 font-semibold rounded text-[11px] transition-colors inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    <span>Lihat</span>
                                                </a>

                                                @if($application->isDraft())
                                                    <button type="button" @click="uploadModalOpen = true; selectedReqId = {{ $req->id }}; selectedReqName = '{{ $req->document_name }}'" class="px-2.5 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 font-semibold rounded text-[11px] transition-colors">
                                                        Ganti File
                                                    </button>

                                                    <form method="POST" action="{{ route('portal.applications.documents.destroy', [$application, $doc]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="button"
                                                            data-confirm="Apakah Anda yakin ingin menghapus berkas dokumen ini?"
                                                            data-confirm-title="Hapus Dokumen"
                                                            data-confirm-btn="Ya, Hapus"
                                                            data-confirm-variant="danger"
                                                            class="p-1 text-rose-500 hover:bg-rose-50 rounded cursor-pointer"
                                                            title="Hapus"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                @if($application->isDraft())
                                                    <button type="button" @click="uploadModalOpen = true; selectedReqId = {{ $req->id }}; selectedReqName = '{{ $req->document_name }}'" class="px-3 py-1 bg-[#0179B5] hover:bg-[#016293] text-white font-semibold rounded text-[11px] shadow-sm transition-colors inline-flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                                        <span>Unggah</span>
                                                    </button>
                                                @else
                                                    <span class="text-slate-300 text-[11px]">Terkunci</span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- UPLOAD MODAL -->
            <div x-show="uploadModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="uploadModalOpen = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 max-w-md w-full text-left">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Unggah Berkas Dokumen</h3>
                            <p class="text-xs text-[#0179B5] font-semibold mt-0.5" x-text="selectedReqName"></p>
                        </div>
                        <button type="button" @click="uploadModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                    </div>

                    <form method="POST" :action="'{{ url('/portal/lamaran') }}/' + '{{ $application->id ?? '' }}' + '/berkas/' + selectedReqId" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="vacancy_document_requirement_id" :value="selectedReqId">

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Berkas (PDF)</label>
                            <input type="file" name="document_file" required accept=".pdf,application/pdf"
                                   class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-[#0179B5] hover:file:bg-sky-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">Ukuran maksimal file: 5 MB.</p>
                        </div>

                        <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                            <button type="button" @click="uploadModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm">
                                Simpan Dokumen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- Belum Ada Lamaran Aktif Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-sky-50 text-[#0179B5] flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Belum Ada Lamaran yang Diajukan</h2>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Silakan pilih formasi lowongan kerja yang sedang dibuka untuk memulai proses pengunggahan berkas persyaratan seleksi.
                    </p>
                </div>
                <div>
                    <a href="{{ route('portal.applications.create') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                        <span>Pilih Formasi Lowongan Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.applicant>
