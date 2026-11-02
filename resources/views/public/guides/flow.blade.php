<x-layouts.public :title="$guide->title ?? 'Panduan Alur Pendaftaran'" active-menu="alur">
    <div class="space-y-6 max-w-4xl">
        {{-- Section Header --}}
        <div class="border-b border-slate-200 pb-5">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                {{ $guide->title ?? 'Alur Pendaftaran Rekrutmen SDM' }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ $guide->subtitle ?? 'Ikuti delapan tahapan resmi pendaftaran calon tenaga kesehatan dan non-kesehatan RSI Fatimah Banyuwangi.' }}
            </p>
        </div>

        {{-- Step-by-Step Vertical Timeline --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs">
            <div class="relative pl-8 space-y-8 before:content-[''] before:absolute before:left-3.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                @php
                    $steps = $guide?->content ?? [];
                @endphp

                @forelse($steps as $index => $step)
                    @php
                        $stepNumber = $step['step'] ?? ($loop->iteration);
                        $isLast = $loop->last;
                    @endphp
                    <div class="relative flex items-start gap-4">
                        <span class="absolute -left-8 top-0 w-7 h-7 rounded-full {{ $isLast ? 'bg-emerald-600' : 'bg-rsi-700' }} text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">
                            {{ $stepNumber }}
                        </span>
                        <div class="space-y-1 flex-1 min-w-0">
                            @if (!empty($step['phase']))
                                <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide bg-sky-50 text-[#0179B5] border border-sky-100 mb-0.5">
                                    {{ $step['phase'] }}
                                </div>
                            @endif
                            <h3 class="text-sm font-bold text-slate-900">{{ $step['title'] ?? '' }}</h3>
                            <div class="text-xs text-slate-600 leading-relaxed">
                                {!! nl2br(e($step['description'] ?? '')) !!}
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Fallback Default Steps (Sesuai Flowchart Pelamar) --}}
                    <div class="relative flex items-start gap-4">
                        <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">1</span>
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide bg-sky-50 text-[#0179B5] border border-sky-100 mb-0.5">
                                Fase 2: Akses &amp; Pemilihan Formasi
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Eksplorasi Lowongan &amp; Pemilihan Formasi</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Calon pelamar mengakses menu Karir / Lowongan Kerja pada website resmi RSI Fatimah Banyuwangi untuk melihat daftar formasi aktif yang tersedia.<br><br>
                                Pelajari detail kualifikasi pendidikan, kuota formasi, unit penempatan, serta jadwal pelaksanaan seleksi. Setelah menentukan posisi yang sesuai, klik tombol 'Lamar Lowongan' untuk memulai proses pendaftaran.
                            </p>
                        </div>
                    </div>
                    <div class="relative flex items-start gap-4">
                        <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">2</span>
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide bg-sky-50 text-[#0179B5] border border-sky-100 mb-0.5">
                                Fase 2: Registrasi Akun Pelamar
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Registrasi Akun Baru &amp; Login Portal</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Pelamar baru wajib melakukan registrasi akun di sistem E-Recruitment dengan mengisi nama lengkap, alamat email aktif, nomor WhatsApp, serta membuat kata sandi akun.<br><br>
                                Setelah registrasi berhasil, pelamar memperoleh nomor registrasi resmi sistem dan dapat langsung login ke portal pelamar menggunakan email serta password yang didaftarkan.
                            </p>
                        </div>
                    </div>
                    <div class="relative flex items-start gap-4">
                        <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">3</span>
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide bg-sky-50 text-[#0179B5] border border-sky-100 mb-0.5">
                                Fase 2: Kelengkapan Profil Wajib
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Pengisian Profil &amp; Kelengkapan Biodata (100%)</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Sebelum dapat mengajukan berkas lamaran, pelamar wajib melengkapi seluruh data profil diri hingga mencapai 100%:<br>
                                • Profil Dasar: Data identitas resmi (NIK KTP), tempat &amp; tanggal lahir, jenis kelamin, alamat KTP &amp; domisili, serta pas foto formal (3x4).<br>
                                • Pendidikan Formal: Jenjang pendidikan, nama institusi/universitas, program studi, IPK, dan status akreditasi.<br>
                                • Lisensi Medis: Data Surat Tanda Registrasi (STR) atau Surat Izin Praktik (SIP) yang masih berlaku aktif (wajib bagi pelamar formasi tenaga kesehatan).<br>
                                • Sertifikasi &amp; Pengalaman: Pelatihan klinis/kompetensi keahlian dan riwayat pengalaman kerja yang relevan.
                            </p>
                        </div>
                    </div>
                    <div class="relative flex items-start gap-4">
                        <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">4</span>
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide bg-sky-50 text-[#0179B5] border border-sky-100 mb-0.5">
                                Fase 3: Pengajuan Lamaran Formasi
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Mulai Pendaftaran Formasi &amp; Pembuatan Draft</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Setelah profil dasar terisi 100%, pelamar memulai pendaftaran pada formasi yang telah dipilih. Sistem akan mengunci pilihan formasi (1 akun pelamar hanya diperbolehkan mendaftar pada 1 formasi per periode rekrutmen) dan membuat lembar kerja draft lamaran resmi.
                            </p>
                        </div>
                    </div>
                    <div class="relative flex items-start gap-4">
                        <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">5</span>
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide bg-sky-50 text-[#0179B5] border border-sky-100 mb-0.5">
                                Fase 3: Pemberkasan Digital
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Unggah Dokumen Persyaratan Digital</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Mengunggah seluruh berkas persyaratan wajib dan berkas pendukung sesuai formasi yang dilamar (Surat Lamaran Kerja, Ijazah &amp; Transkrip Akademik, STR legalisir, KTP, KK, SKCK, serta sertifikat pelatihan/keahlian).<br><br>
                                Khusus dokumen Curriculum Vitae (CV) &amp; Portofolio, sistem menerima berkas dalam format PDF maupun Video MP4 (maksimal 20 MB). Seluruh berkas dapat dipratinjau langsung di sistem sebelum dikirim.
                            </p>
                        </div>
                    </div>
                    <div class="relative flex items-start gap-4">
                        <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">6</span>
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide bg-sky-50 text-[#0179B5] border border-sky-100 mb-0.5">
                                Fase 3: Review &amp; Final Submit
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Review Kelengkapan Berkas &amp; Submit Final Lamaran</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Memeriksa kembali kebenaran ringkasan seluruh data biodata serta pratinjau dokumen yang telah diunggah. Pelamar masih dapat mengganti atau memperbaiki berkas selama status lamaran masih berupa draft.<br><br>
                                Jika seluruh berkas sudah lengkap dan valid, lakukan Submit Final. Sistem akan mengunci lamaran serta berkas dokumen secara permanen dan menerbitkan nomor lamaran resmi untuk verifikasi panitia.
                            </p>
                        </div>
                    </div>
                    <div class="relative flex items-start gap-4">
                        <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">7</span>
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide bg-sky-50 text-[#0179B5] border border-sky-100 mb-0.5">
                                Fase 4 &amp; 5: Verifikasi &amp; Live Tracking
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Verifikasi Berkas Administrasi &amp; Live Tracking Seleksi</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Tim Panitia Seleksi &amp; SDI RSI Fatimah memvalidasi kesesuaian berkas asli serta keabsahan STR melalui basis data resmi Kemenkes/KTKI.<br><br>
                                Pelamar dapat memantau setiap perkembangan status kelulusan berkas dan pengumuman tahapan secara transparan dan real-time melalui menu Live Tracking di portal pelamar.
                            </p>
                        </div>
                    </div>
                    <div class="relative flex items-start gap-4">
                        <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">8</span>
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide bg-emerald-50 text-emerald-700 border border-emerald-100 mb-0.5">
                                Fase 5 &amp; 6: Tahapan Seleksi &amp; Onboarding
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">Pelaksanaan Ujian, Pengumuman Hasil Akhir &amp; Onboarding</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Kandidat yang lolos seleksi berkas administrasi mengikuti rangkaian tahapan tes yang dijadwalkan:<br>
                                • Ujian Tertulis &amp; Skill Test (kompetensi teknis profesi).<br>
                                • Wawancara Direksi &amp; Tes Al-Islam (komitmen pelayanan dan tes baca Al-Qur'an).<br>
                                • Medical Check Up (MCU) lengkap di RSI Fatimah Banyuwangi.<br><br>
                                Pengumuman hasil akhir disampaikan resmi melalui notifikasi portal dan email. Kandidat yang dinyatakan diterima bekerja selanjutnya mengikuti masa pembekalan orientasi nilai keislaman, SOP rumah sakit, serta penempatan tugas resmi.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.public>
