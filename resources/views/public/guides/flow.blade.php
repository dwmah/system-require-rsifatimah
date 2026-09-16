<x-layouts.public title="Panduan Alur Pendaftaran" active-menu="alur">
    <div class="space-y-6 max-w-4xl">
        {{-- Breadcrumb --}}
        <x-ui.breadcrumb :items="[
            ['label' => 'Panduan'],
            ['label' => 'Alur Pendaftaran']
        ]" />

        {{-- Section Header --}}
        <div class="border-b border-slate-200 pb-5">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Alur Pendaftaran Rekrutmen SDM
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Ikuti delapan tahapan resmi pendaftaran calon tenaga kesehatan dan non-kesehatan RSI Fatimah Banyuwangi.
            </p>
        </div>

        {{-- Step-by-Step Vertical Timeline --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-2xs">
            <div class="relative pl-8 space-y-8 before:content-[''] before:absolute before:left-3.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                {{-- Step 1 --}}
                <div class="relative flex items-start gap-4">
                    <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">
                        1
                    </span>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Registrasi Akun Pelamar</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Calon pelamar membuat akun baru menggunakan alamat email aktif, password, nomor WhatsApp aktif, serta memperoleh nomor registrasi resmi sistem.
                        </p>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="relative flex items-start gap-4">
                    <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">
                        2
                    </span>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Pengisian Profil &amp; Biodata Lengkap</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Melengkapi data profil diri pelamar meliputi Nomor Induk Kependudukan (NIK KTP), tempat &amp; tanggal lahir, jenis kelamin, alamat KTP, riwayat pendidikan formal, sertifikasi pelatihan, serta data Surat Tanda Registrasi (STR) bagi tenaga kesehatan.
                        </p>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="relative flex items-start gap-4">
                    <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">
                        3
                    </span>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Pemilihan Formasi Lowongan</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Memilih salah satu lowongan pekerjaan yang sedang dibuka. Satu akun pelamar hanya diperbolehkan mendaftar pada <strong class="text-slate-800">1 (satu) formasi pekerjaan</strong> dalam satu periode rekrutmen aktif.
                        </p>
                    </div>
                </div>

                {{-- Step 4 --}}
                <div class="relative flex items-start gap-4">
                    <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">
                        4
                    </span>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Unggah Dokumen Persyaratan Digital</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Mengunggah seluruh berkas persyaratan wajib (Surat Lamaran, CV, Ijazah, Transkrip, STR, KTP, KK) serta dokumen pendukung opsional (sertifikat keahlian, surat kesehatan, SKCK) dalam format PDF maksimal 2 MB per file.
                        </p>
                    </div>
                </div>

                {{-- Step 5 --}}
                <div class="relative flex items-start gap-4">
                    <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">
                        5
                    </span>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Review &amp; Final Submit Lamaran</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Memeriksa kembali kebenaran dan kelengkapan seluruh isian data. Setelah tombol submit ditekan, berkas lamaran terkunci secara permanen dan nomor lamaran unik diterbitkan.
                        </p>
                    </div>
                </div>

                {{-- Step 6 --}}
                <div class="relative flex items-start gap-4">
                    <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">
                        6
                    </span>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Seleksi Administrasi &amp; Verifikasi STR</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Panitia seleksi SDM RSI Fatimah memvalidasi kesesuaian berkas asli dan keabsahan STR tenaga kesehatan melalui basis data Kemenkes/KTKI. Status kelulusan administrasi diumumkan melalui portal akun pelamar.
                        </p>
                    </div>
                </div>

                {{-- Step 7 --}}
                <div class="relative flex items-start gap-4">
                    <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-rsi-700 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">
                        7
                    </span>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Ujian Tertulis, Wawancara &amp; Medical Check Up (MCU)</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Kandidat yang lolos seleksi berkas mengikuti tes kompetensi profesi/skill test, wawancara direksi &amp; tes pemahaman Al-Islam (baca Al-Qur'an), serta pemeriksaan kesehatan lengkap di RSI Fatimah Banyuwangi.
                        </p>
                    </div>
                </div>

                {{-- Step 8 --}}
                <div class="relative flex items-start gap-4">
                    <span class="absolute -left-8 top-0 w-7 h-7 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-xs">
                        8
                    </span>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Pengumuman Akhir, Orientasi &amp; Penempatan</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Peserta yang dinyatakan lolos tahap akhir menerima keputusan penerimaan resmi, menandatangani kontrak kerja (PKWT), menjalani masa orientasi rumah sakit, dan resmi ditempatkan di unit kerja RSI Fatimah Banyuwangi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>
