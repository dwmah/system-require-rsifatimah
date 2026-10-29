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
                        'title' => 'Registrasi Akun Pelamar',
                        'description' => 'Calon pelamar membuat akun baru menggunakan alamat email aktif, password, nomor WhatsApp aktif, serta memperoleh nomor registrasi resmi sistem.',
                    ],
                    [
                        'step' => 2,
                        'title' => 'Pengisian Profil & Biodata Lengkap',
                        'description' => 'Melengkapi data profil diri pelamar meliputi Nomor Induk Kependudukan (NIK KTP), tempat & tanggal lahir, jenis kelamin, alamat KTP, riwayat pendidikan formal, sertifikasi pelatihan, serta data Surat Tanda Registrasi (STR) bagi tenaga kesehatan.',
                    ],
                    [
                        'step' => 3,
                        'title' => 'Pemilihan Formasi Lowongan',
                        'description' => 'Memilih salah satu lowongan pekerjaan yang sedang dibuka. Satu akun pelamar hanya diperbolehkan mendaftar pada 1 (satu) formasi pekerjaan dalam satu periode rekrutmen aktif.',
                    ],
                    [
                        'step' => 4,
                        'title' => 'Unggah Dokumen Persyaratan Digital',
                        'description' => 'Mengunggah seluruh berkas persyaratan wajib (Surat Lamaran, CV, Ijazah, Transkrip, STR, KTP, KK) serta dokumen pendukung opsional (sertifikat keahlian, surat kesehatan, SKCK) dalam format PDF maksimal 2 MB per file.',
                    ],
                    [
                        'step' => 5,
                        'title' => 'Review & Final Submit Lamaran',
                        'description' => 'Memeriksa kembali kebenaran dan kelengkapan seluruh isian data. Setelah tombol submit ditekan, berkas lamaran terkunci secara permanen dan nomor lamaran unik diterbitkan.',
                    ],
                    [
                        'step' => 6,
                        'title' => 'Seleksi Administrasi & Verifikasi STR',
                        'description' => 'Panitia seleksi SDM RSI Fatimah memvalidasi kesesuaian berkas asli dan keabsahan STR tenaga kesehatan melalui basis data Kemenkes/KTKI. Status kelulusan administrasi diumumkan melalui portal akun pelamar.',
                    ],
                    [
                        'step' => 7,
                        'title' => 'Ujian Tertulis, Wawancara & Medical Check Up (MCU)',
                        'description' => 'Kandidat yang lolos seleksi berkas mengikuti tes kompetensi profesi/skill test, wawancara direksi & tes pemahaman Al-Islam (baca Al-Qur\'an), serta pemeriksaan kesehatan lengkap di RSI Fatimah Banyuwangi.',
                    ],
                    [
                        'step' => 8,
                        'title' => 'Pengumuman Akhir, Orientasi & Penempatan',
                        'description' => 'Pengumuman kelulusan final melalui portal akun pelamar dan website resmi RSI Fatimah. Kandidat terpilih mengikuti pembekalan orientasi nilai-nilai keislaman dan rumah sakit sebelum mulai bertugas.',
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
