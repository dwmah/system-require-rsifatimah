<x-layouts.public title="Panduan Persyaratan Pendaftaran" active-menu="persyaratan">
    <div class="space-y-6 max-w-4xl">
        {{-- Breadcrumb --}}
        <x-ui.breadcrumb :items="[
            ['label' => 'Panduan'],
            ['label' => 'Persyaratan Pendaftaran']
        ]" />

        {{-- Section Header --}}
        <div class="border-b border-slate-200 pb-5">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Persyaratan &amp; Ketentuan Pendaftaran
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Panduan kualifikasi umum, kelengkapan berkas digital, dan tata tertib seleksi SDM RSI Fatimah Banyuwangi.
            </p>
        </div>

        <div class="space-y-5">
            {{-- 1. Persyaratan Umum Pelamar --}}
            <x-ui.card title="1. Persyaratan Umum Pelamar">
                <ul class="space-y-2 text-xs text-slate-700 list-disc list-inside leading-relaxed">
                    <li>Warga Negara Indonesia (WNI) yang taat beragama dan berakhlak mulia.</li>
                    <li>Mampu membaca Al-Qur'an dengan baik dan benar (menjadi nilai tambah dalam wawancara Al-Islam).</li>
                    <li>Sehat jasmani dan rohani, tidak memiliki riwayat penyakit menular kronis yang mengganggu tugas pelayanan rumah sakit.</li>
                    <li>Bebas dari penyalahgunaan narkotika, psikotropika, dan zat adiktif lainnya (NAPZA).</li>
                    <li>Berkelakuan baik, tidak pernah dihukum penjara atau terlibat tindak pidana kejahatan dibuktikan dengan SKCK.</li>
                    <li>Bersedia mematuhi seluruh peraturan kekaryawanan, SOP pelayanan, dan nilai-nilai Islami Rumah Sakit Islam Fatimah Banyuwangi.</li>
                </ul>
            </x-ui.card>

            {{-- 2. Ketentuan Akun & Data Pelamar --}}
            <x-ui.card title="2. Ketentuan Akun & Data Pelamar">
                <ul class="space-y-2 text-xs text-slate-700 list-disc list-inside leading-relaxed">
                    <li>Setiap pelamar wajib menggunakan alamat email pribadi yang aktif dan valid selama proses rekrutmen.</li>
                    <li>Nomor Induk Kependudukan (NIK) bersifat unik; 1 (satu) NIK hanya dapat didaftarkan untuk 1 (satu) akun pelamar.</li>
                    <li>Pelamar bertanggung jawab penuh atas kebenaran biodata diri, riwayat pendidikan, dan nomor telepon WhatsApp yang didaftarkan.</li>
                    <li>Pemberian data palsu, manipulatif, atau tidak sesuai dokumen asli akan mengakibatkan diskualifikasi seketika pada tahap apapun.</li>
                </ul>
            </x-ui.card>

            {{-- 3. Ketentuan Dokumen Pendaftaran --}}
            <x-ui.card title="3. Ketentuan Dokumen Pendaftaran">
                <ul class="space-y-2 text-xs text-slate-700 list-disc list-inside leading-relaxed">
                    <li>Seluruh berkas persyaratan diunggah dalam bentuk softcopy format digital <strong class="text-slate-900">PDF</strong>.</li>
                    <li>Ukuran maksimal tiap berkas yang diunggah adalah <strong class="text-slate-900">2048 KB (2 Megabyte)</strong>.</li>
                    <li>Hasil scan dokumen harus terbaca jelas, tegak lurus, tidak terpotong, dan tidak buram/pecah.</li>
                    <li>Dokumen wajib mencakup: Surat Lamaran, CV terbaru, Ijazah Asli, Transkrip Akademik legalisir, STR aktif (bagi nakes), KTP elektronik, dan Kartu Keluarga.</li>
                    <li>Bagi tenaga kesehatan (Dokter, Perawat, Bidan, Apoteker, Radiografer, Pranata Lab), Surat Tanda Registrasi (STR) wajib masih berlaku dan terverifikasi di KTKI/KKI/Kemenkes.</li>
                </ul>
            </x-ui.card>

            {{-- 4. Ketentuan Pengajuan Lamaran --}}
            <x-ui.card title="4. Ketentuan Pengajuan Lamaran">
                <ul class="space-y-2 text-xs text-slate-700 list-disc list-inside leading-relaxed">
                    <li>Satu pelamar hanya diperbolehkan memilih dan mengajukan <strong class="text-slate-900">1 (satu) posisi lowongan</strong> dalam satu periode rekrutmen.</li>
                    <li>Setelah lamaran dikirimkan secara final (submit), data berkas dan pilihan formasi lowongan <strong class="text-red-600">terkunci dan tidak dapat diubah kembali</strong>.</li>
                    <li>Pelamar akan memperoleh Nomor Lamaran resmi untuk memantau status seleksi berkas secara mandiri melalui portal.</li>
                </ul>
            </x-ui.card>

            {{-- 5. Persyaratan Khusus Formasi Lowongan --}}
            <x-ui.card title="5. Persyaratan Khusus Formasi Lowongan">
                <p class="text-xs text-slate-600 leading-relaxed mb-3">
                    Setiap formasi lowongan memiliki kualifikasi pendidikan, sertifikasi pelatihan, IPK minimal, dan batas usia yang berbeda-beda.
                </p>
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-600 space-y-1.5">
                    <p class="font-semibold text-slate-800">Contoh Persyaratan Khusus:</p>
                    <p>&bull; <strong class="text-slate-700">Perawat IGD</strong>: D3 Keperawatan/Profesi Ners, IPK min 3.00, STR aktif, sertifikat BTCLS/PPGD/ACLS aktif, usia maks 30 tahun.</p>
                    <p>&bull; <strong class="text-slate-700">Dokter Umum</strong>: Profesi Dokter, STR aktif KKI, sertifikat ACLS/ATLS aktif, usia maks 35 tahun.</p>
                    <p>&bull; <strong class="text-slate-700">Staff IT &amp; SIMRS</strong>: S1/D4 Informatika/Sistem Informasi, menguasai PHP/Laravel &amp; SQL, pemahaman integrasi BPJS/SatuSehat.</p>
                    <p class="text-slate-500 text-[11px] pt-1">
                        *Silakan buka rincian formasi pada halaman detail masing-masing lowongan.
                    </p>
                </div>
            </x-ui.card>

            {{-- 6. Hal yang Perlu Diperhatikan --}}
            <x-ui.card title="6. Hal yang Perlu Diperhatikan">
                <ul class="space-y-2 text-xs text-slate-700 list-disc list-inside leading-relaxed">
                    <li>Panitia rekrutmen SDM RSI Fatimah Banyuwangi berhak menolak berkas lamaran yang tidak lengkap, tidak terbaca, atau melewati batas waktu pendaftaran.</li>
                    <li>Keputusan Panitia Seleksi Rekrutmen bersifat final, mengikat, dan tidak dapat diganggu gugat.</li>
                    <li>Proses rekrutmen ini bersih, transparan, dan <strong class="text-slate-900">bebas dari segala bentuk pungutan biaya / gratifikasi</strong>.</li>
                </ul>
            </x-ui.card>
        </div>
    </div>
</x-layouts.public>
