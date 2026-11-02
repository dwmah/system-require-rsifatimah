<?php

namespace Database\Seeders;

use App\Enums\RecruitmentGuideType;
use App\Models\RecruitmentGuide;
use Illuminate\Database\Seeder;

class RecruitmentGuideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultGuides = [
            [
                'type' => RecruitmentGuideType::FLOW->value,
                'title' => 'Alur Pendaftaran Rekrutmen SDM',
                'slug' => 'alur-pendaftaran',
                'subtitle' => 'Ikuti delapan tahapan resmi pendaftaran calon tenaga kesehatan dan non-kesehatan RSI Fatimah Banyuwangi.',
                'sort_order' => 1,
                'is_active' => true,
                'content' => [
                    [
                        'step' => 1,
                        'phase' => 'Fase 2: Akses & Pemilihan Formasi',
                        'title' => 'Eksplorasi Lowongan & Pemilihan Formasi',
                        'description' => "Calon pelamar mengakses menu Karir / Lowongan Kerja pada website resmi RSI Fatimah Banyuwangi untuk melihat daftar formasi aktif yang tersedia.\n\nPelajari detail kualifikasi pendidikan, kuota formasi, unit penempatan, serta jadwal pelaksanaan seleksi. Setelah menentukan posisi yang sesuai, klik tombol 'Lamar Lowongan' untuk memulai proses pendaftaran.",
                    ],
                    [
                        'step' => 2,
                        'phase' => 'Fase 2: Registrasi Akun Pelamar',
                        'title' => 'Registrasi Akun Baru & Login Portal',
                        'description' => "Pelamar baru wajib melakukan registrasi akun di sistem E-Recruitment dengan mengisi nama lengkap, alamat email aktif, nomor WhatsApp, serta membuat kata sandi akun.\n\nSetelah registrasi berhasil, pelamar memperoleh nomor registrasi resmi sistem dan dapat langsung login ke portal pelamar menggunakan email serta password yang didaftarkan.",
                    ],
                    [
                        'step' => 3,
                        'phase' => 'Fase 2: Kelengkapan Profil Wajib',
                        'title' => 'Pengisian Profil & Kelengkapan Biodata (100%)',
                        'description' => "Sebelum dapat mengajukan berkas lamaran, pelamar wajib melengkapi seluruh data profil diri hingga mencapai 100%:\n• Profil Dasar: Data identitas resmi (NIK KTP), tempat & tanggal lahir, jenis kelamin, alamat KTP & domisili, serta pas foto formal (3x4).\n• Pendidikan Formal: Jenjang pendidikan, nama institusi/universitas, program studi, IPK, dan status akreditasi.\n• Lisensi Medis: Data Surat Tanda Registrasi (STR) atau Surat Izin Praktik (SIP) yang masih berlaku aktif (wajib bagi pelamar formasi tenaga kesehatan).\n• Sertifikasi & Pengalaman: Pelatihan klinis/kompetensi keahlian dan riwayat pengalaman kerja yang relevan.",
                    ],
                    [
                        'step' => 4,
                        'phase' => 'Fase 3: Pengajuan Lamaran Formasi',
                        'title' => 'Mulai Pendaftaran Formasi & Pembuatan Draft',
                        'description' => 'Setelah profil dasar terisi 100%, pelamar memulai pendaftaran pada formasi yang telah dipilih. Sistem akan mengunci pilihan formasi (1 akun pelamar hanya diperbolehkan mendaftar pada 1 formasi per periode rekrutmen) dan membuat lembar kerja draft lamaran resmi.',
                    ],
                    [
                        'step' => 5,
                        'phase' => 'Fase 3: Pemberkasan Digital',
                        'title' => 'Unggah Dokumen Persyaratan Digital',
                        'description' => "Mengunggah seluruh berkas persyaratan wajib dan berkas pendukung sesuai formasi yang dilamar (Surat Lamaran Kerja, Ijazah & Transkrip Akademik, STR legalisir, KTP, KK, SKCK, serta sertifikat pelatihan/keahlian).\n\nKhusus dokumen Curriculum Vitae (CV) & Portofolio, sistem menerima berkas dalam format PDF maupun Video MP4 (maksimal 20 MB). Seluruh berkas dapat dipratinjau langsung di sistem sebelum dikirim.",
                    ],
                    [
                        'step' => 6,
                        'phase' => 'Fase 3: Review & Final Submit',
                        'title' => 'Review Kelengkapan Berkas & Submit Final Lamaran',
                        'description' => "Memeriksa kembali kebenaran ringkasan seluruh data biodata serta pratinjau dokumen yang telah diunggah. Pelamar masih dapat mengganti atau memperbaiki berkas selama status lamaran masih berupa draft.\n\nJika seluruh berkas sudah lengkap dan valid, lakukan Submit Final. Sistem akan mengunci lamaran serta berkas dokumen secara permanen dan menerbitkan nomor lamaran resmi untuk verifikasi panitia.",
                    ],
                    [
                        'step' => 7,
                        'phase' => 'Fase 4 & 5: Verifikasi & Live Tracking',
                        'title' => 'Verifikasi Berkas Administrasi & Live Tracking Seleksi',
                        'description' => "Tim Panitia Seleksi & SDI RSI Fatimah memvalidasi kesesuaian berkas asli serta keabsahan STR melalui basis data resmi Kemenkes/KTKI.\n\nPelamar dapat memantau setiap perkembangan status kelulusan berkas dan pengumuman tahapan secara transparan dan real-time melalui menu Live Tracking di portal pelamar.",
                    ],
                    [
                        'step' => 8,
                        'phase' => 'Fase 5 & 6: Tahapan Seleksi & Onboarding',
                        'title' => 'Pelaksanaan Ujian, Pengumuman Hasil Akhir & Onboarding',
                        'description' => "Kandidat yang lolos seleksi berkas administrasi mengikuti rangkaian tahapan tes yang dijadwalkan:\n• Ujian Tertulis & Skill Test (kompetensi teknis profesi).\n• Wawancara Direksi & Tes Al-Islam (komitmen pelayanan dan tes baca Al-Qur'an).\n• Medical Check Up (MCU) lengkap di RSI Fatimah Banyuwangi.\n\nPengumuman hasil akhir disampaikan resmi melalui notifikasi portal dan email. Kandidat yang dinyatakan diterima bekerja selanjutnya mengikuti masa pembekalan orientasi nilai keislaman, SOP rumah sakit, serta penempatan tugas resmi.",
                    ],
                ],
            ],
            [
                'type' => RecruitmentGuideType::REQUIREMENTS->value,
                'title' => 'Persyaratan & Ketentuan Pendaftaran',
                'slug' => 'persyaratan-pendaftaran',
                'subtitle' => 'Panduan kualifikasi umum, kelengkapan berkas digital, dan tata tertib seleksi SDM RSI Fatimah Banyuwangi.',
                'sort_order' => 2,
                'is_active' => true,
                'content' => [
                    [
                        'step' => 1,
                        'title' => '1. Persyaratan Umum Pelamar',
                        'description' => "Warga Negara Indonesia (WNI) yang berakhlak mulia.\nMampu membaca Al-Qur'an dengan baik dan benar (nilai tambah).\nSehat jasmani dan rohani, serta bebas dari narkoba (NAPZA).\nBerkelakuan baik dan tidak pernah dipidana (dibuktikan dengan SKCK).\nBersedia mematuhi peraturan kekaryawanan dan nilai-nilai Islami RSI Fatimah Banyuwangi.",
                    ],
                    [
                        'step' => 2,
                        'title' => '2. Ketentuan Akun & Data Pelamar',
                        'description' => "Wajib menggunakan alamat email dan nomor WhatsApp pribadi yang aktif.\n1 NIK = 1 Akun: Nomor Induk Kependudukan hanya dapat didaftarkan untuk satu akun.\nPelamar bertanggung jawab penuh atas kebenaran seluruh data yang diisi.\nPemberian data palsu akan mengakibatkan diskualifikasi otomatis pada semua tahapan.",
                    ],
                    [
                        'step' => 3,
                        'title' => '3. Ketentuan Dokumen Pendaftaran',
                        'description' => "Berkas diunggah dalam format PDF (.pdf) dengan ukuran maksimal 2 MB per file.\nHasil scan dokumen asli harus tegak lurus, terbaca jelas, dan tidak terpotong.\nDokumen Wajib: Surat Lamaran, CV terbaru, Ijazah Asli, Transkrip Nilai, e-KTP, dan Kartu Keluarga (KK).\nTenaga Kesehatan: Wajib melampirkan STR aktif yang masih berlaku dan terdaftar resmi.\nDokumen Pendukung: Sertifikat pelatihan/keahlian atau berkas lain dapat dilampirkan sesuai persyaratan lowongan.",
                    ],
                    [
                        'step' => 4,
                        'title' => '4. Ketentuan Pengajuan Lamaran',
                        'description' => "Setiap pelamar hanya dapat memilih 1 (satu) formasi lowongan dalam satu periode seleksi.\nSetelah lamaran dikirim (submit), pilihan formasi dan berkas terkunci dan tidak dapat diubah.\nPelamar akan memperoleh Nomor Registrasi Lamaran untuk memantau status seleksi di menu Lamaran Saya.",
                    ],
                    [
                        'step' => 5,
                        'title' => '5. Persyaratan Khusus Formasi Lowongan',
                        'description' => "Setiap formasi lowongan memiliki kualifikasi khusus (pendidikan minimal, IPK, batas usia, dan keahlian teknis) sesuai kebutuhan unit kerja.\nRincian kualifikasi masing-masing posisi dapat dilihat langsung pada Halaman Lowongan Pekerjaan.",
                    ],
                    [
                        'step' => 6,
                        'title' => '6. Hal yang Perlu Diperhatikan',
                        'description' => "Berkas lamaran yang tidak lengkap, tidak terbaca, atau melewati batas waktu pendaftaran tidak akan diproses.\nKeputusan Panitia Seleksi Rekrutmen bersifat final, mengikat, dan tidak dapat diganggu gugat.\nSeluruh proses rekrutmen pegawai RSI Fatimah Banyuwangi GRATIS (tidak dipungut biaya apapun). Waspadai segala bentuk penipuan.",
                    ],
                ],
            ],
        ];

        foreach ($defaultGuides as $guideData) {
            RecruitmentGuide::updateOrCreate(
                ['slug' => $guideData['slug']],
                $guideData
            );
        }
    }
}
