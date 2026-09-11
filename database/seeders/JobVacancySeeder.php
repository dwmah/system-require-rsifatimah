<?php

namespace Database\Seeders;

use App\Enums\JobVacancyStatus;
use App\Models\JobVacancy;
use App\Models\RecruitmentPeriod;
use App\Models\VacancyDocumentRequirement;
use App\Models\VacancyQualification;
use App\Models\VacancySelectionStage;
use Illuminate\Database\Seeder;

class JobVacancySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $period = RecruitmentPeriod::where('code', 'RSIF-2026')->firstOrFail();

        $vacanciesData = [
            // 1. Perawat IGD
            [
                'vacancy' => [
                    'recruitment_period_id' => $period->id,
                    'code' => 'POS-2026-IGD-01',
                    'title' => 'Perawat Instalasi Gawat Darurat (IGD)',
                    'unit' => 'Instalasi Gawat Darurat',
                    'employment_type' => 'PKWT',
                    'quota' => 5,
                    'location' => 'RSI Fatimah Banyuwangi',
                    'description' => 'Memberikan asuhan keperawatan gawat darurat yang cepat, tepat, akurat, dan berlandaskan nilai-nilai islami kepada pasien gawat darurat.',
                    'responsibilities' => "1. Melakukan triase dan penanganan awal kegawatdaruratan pasien.\n2. Mengoperasikan alat medis darurat (defibrilator, monitor, suction, ventilator transport).\n3. Berkoordinasi dengan dokter jaga IGD dalam resusitasi dan stabilisasi pasien.\n4. Mendokumentasikan rekam medis asuhan keperawatan secara digital di SIMRS.",
                    'open_at' => now()->subDays(5),
                    'close_at' => now()->addDays(20),
                    'status' => JobVacancyStatus::OPEN,
                ],
                'qualifications' => [
                    ['label' => 'Pendidikan Formal', 'value' => 'D3 Keperawatan atau Profesi Ners', 'sort_order' => 1],
                    ['label' => 'IPK', 'value' => 'Minimal 3.00', 'sort_order' => 2],
                    ['label' => 'Pengalaman Klinis', 'value' => 'Minimal 1 tahun IGD/ICU, Fresh Graduate dipersilakan', 'sort_order' => 3],
                    ['label' => 'STR', 'value' => 'STR Perawat aktif', 'sort_order' => 4],
                    ['label' => 'Usia', 'value' => 'Maksimal 30 tahun', 'sort_order' => 5],
                    ['label' => 'Sertifikasi', 'value' => 'BTCLS / PPGD / ACLS aktif', 'sort_order' => 6],
                ],
                'documents' => [
                    ['name' => 'Surat Lamaran', 'description' => 'Ditujukan kepada Direktur RSI Fatimah Banyuwangi', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 1],
                    ['name' => 'Curriculum Vitae (CV)', 'description' => 'Daftar riwayat hidup terbaru dan nomor kontak aktif', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 2],
                    ['name' => 'Ijazah & Transkrip Nilai', 'description' => 'Scan ijazah asli dan transkrip legalisir', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 3],
                    ['name' => 'Surat Tanda Registrasi (STR)', 'description' => 'Scan STR Perawat yang masih aktif', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 4],
                    ['name' => 'KTP', 'description' => 'Scan KTP elektronik yang masih berlaku', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 5],
                    ['name' => 'Kartu Keluarga', 'description' => 'Scan Kartu Keluarga terbaru', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 6],
                    ['name' => 'Sertifikat BTCLS / PPGD / ACLS', 'description' => 'Scan sertifikat kegawatdaruratan yang masih berlaku', 'is_required' => false, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 7],
                    ['name' => 'Surat Keterangan Sehat', 'description' => 'Dari fasilitas kesehatan pemerintah/RS', 'is_required' => false, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 8],
                    ['name' => 'SKCK', 'description' => 'Surat Keterangan Catatan Kepolisian yang masih berlaku', 'is_required' => false, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 9],
                ],
                'stages' => [
                    ['name' => 'Pendaftaran Online & Unggah Berkas', 'description' => 'Pengisian data diri dan unggah dokumen persyaratan', 'sort_order' => 1, 'start_at' => now()->subDays(5), 'end_at' => now()->addDays(20)],
                    ['name' => 'Seleksi Administrasi & Verifikasi STR', 'description' => 'Pemeriksaan keabsahan dokumen dan status STR di KTKI/Kemenkes', 'sort_order' => 2, 'start_at' => now()->addDays(21), 'end_at' => now()->addDays(24)],
                    ['name' => 'Ujian Tulis & Skill Test', 'description' => 'Tes teori keperawatan gawat darurat dan simulasi tindakan BHD/resusitasi', 'sort_order' => 3, 'start_at' => now()->addDays(25), 'end_at' => now()->addDays(27)],
                    ['name' => 'Wawancara Direksi & Tes Al-Islam', 'description' => 'Wawancara kompetensi manajerial, komitmen pelayanan, dan tes baca Al-Qur\'an', 'sort_order' => 4, 'start_at' => now()->addDays(28), 'end_at' => now()->addDays(30)],
                    ['name' => 'Medical Check Up (MCU)', 'description' => 'Pemeriksaan kesehatan fisik, bebas narkoba, dan laboratorium di RSI Fatimah', 'sort_order' => 5, 'start_at' => now()->addDays(31), 'end_at' => now()->addDays(33)],
                    ['name' => 'Pengumuman Akhir', 'description' => 'Pengumuman resmi kandidat yang diterima melalui portal rekrutmen', 'sort_order' => 6, 'start_at' => now()->addDays(34), 'end_at' => now()->addDays(35)],
                    ['name' => 'Orientasi', 'description' => 'Pembekalan nilai keislaman, SOP RS, patient safety, dan PPI', 'sort_order' => 7, 'start_at' => now()->addDays(36), 'end_at' => now()->addDays(42)],
                    ['name' => 'Penempatan', 'description' => 'Penempatan tugas resmi di Instalasi Gawat Darurat', 'sort_order' => 8, 'start_at' => now()->addDays(43), 'end_at' => now()->addDays(44)],
                ],
            ],

            // 2. Dokter Umum
            [
                'vacancy' => [
                    'recruitment_period_id' => $period->id,
                    'code' => 'POS-2026-DOK-01',
                    'title' => 'Dokter Umum',
                    'unit' => 'Pelayanan Medis',
                    'employment_type' => 'PKWT',
                    'quota' => 3,
                    'location' => 'RSI Fatimah Banyuwangi',
                    'description' => 'Melaksanakan pelayanan medis dasar, kegawatdaruratan, dan rawat inap sesuai standar profesi medik dan etika kedokteran islami.',
                    'responsibilities' => "1. Menjalankan tugas jaga IGD dan bangsal rawat inap.\n2. Melakukan diagnosis medis dan tatalaksana awal pasien.\n3. Berkolaborasi dan konsultasi dengan dokter spesialis penanggung jawab pasien.",
                    'open_at' => now()->subDays(5),
                    'close_at' => now()->addDays(20),
                    'status' => JobVacancyStatus::OPEN,
                ],
                'qualifications' => [
                    ['label' => 'Pendidikan Formal', 'value' => 'Profesi Dokter (dr.)', 'sort_order' => 1],
                    ['label' => 'IPK', 'value' => 'Minimal 3.00', 'sort_order' => 2],
                    ['label' => 'STR', 'value' => 'STR Dokter Umum aktif (KKI / Kemenkes)', 'sort_order' => 3],
                    ['label' => 'Sertifikasi', 'value' => 'Sertifikat ACLS atau ATLS masih berlaku', 'sort_order' => 4],
                    ['label' => 'Usia', 'value' => 'Maksimal 35 tahun', 'sort_order' => 5],
                ],
                'documents' => [
                    ['name' => 'Surat Lamaran', 'description' => 'Ditujukan kepada Direktur RSI Fatimah Banyuwangi', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 1],
                    ['name' => 'Curriculum Vitae (CV)', 'description' => 'Riwayat hidup lengkap dan pengalaman klinis', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 2],
                    ['name' => 'Ijazah Sarjana & Profesi Dokter', 'description' => 'Scan ijazah S.Ked dan Dokter', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 3],
                    ['name' => 'STR Dokter Aktif', 'description' => 'Surat Tanda Registrasi KKI/Kemenkes aktif', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 4],
                    ['name' => 'Sertifikat ACLS / ATLS', 'description' => 'Sertifikat resusitasi kardiovaskular lanjut', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 5],
                    ['name' => 'KTP', 'description' => 'Scan KTP elektronik', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 6],
                    ['name' => 'Surat Bebas Narkoba', 'description' => 'Dari BNN atau RS Pemerintah', 'is_required' => false, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 7],
                ],
                'stages' => [
                    ['name' => 'Pendaftaran Online & Upload Berkas', 'description' => 'Registrasi kandidat dokter', 'sort_order' => 1, 'start_at' => now()->subDays(5), 'end_at' => now()->addDays(20)],
                    ['name' => 'Seleksi Berkas & Kredensial Medis', 'description' => 'Verifikasi keabsahan STR dan portofolio klinis', 'sort_order' => 2, 'start_at' => now()->addDays(21), 'end_at' => now()->addDays(23)],
                    ['name' => 'Wawancara Komite Medik & Direksi', 'description' => 'Penilaian kompetensi klinis, etika medik, dan visi islami', 'sort_order' => 3, 'start_at' => now()->addDays(24), 'end_at' => now()->addDays(26)],
                    ['name' => 'Medical Check Up (MCU)', 'description' => 'Pemeriksaan kesehatan menyeluruh', 'sort_order' => 4, 'start_at' => now()->addDays(27), 'end_at' => now()->addDays(29)],
                    ['name' => 'Keputusan Akhir & Orientasi', 'description' => 'Penetapan Surat Izin Praktik (SIP) dan penugasan klinis', 'sort_order' => 5, 'start_at' => now()->addDays(30), 'end_at' => now()->addDays(32)],
                ],
            ],

            // 3. Radiografer Madya
            [
                'vacancy' => [
                    'recruitment_period_id' => $period->id,
                    'code' => 'POS-2026-RAD-01',
                    'title' => 'Radiografer Madya',
                    'unit' => 'Instalasi Radiologi',
                    'employment_type' => 'PKWT',
                    'quota' => 2,
                    'location' => 'RSI Fatimah Banyuwangi',
                    'description' => 'Melaksanakan pemeriksaan radiodiagnostik konvensional dan imaging lanjutan (CT-Scan) dengan menerapkan prinsip proteksi radiasi.',
                    'responsibilities' => "1. Melakukan pemeriksaan rontgen dan CT-Scan sesuai permintaan klinis.\n2. Melakukan kontrol mutu gambar diagnostik sebelum divalidasi dokter spesialis radiologi.\n3. Menjamin keselamatan radiasi bagi pasien, petugas, dan lingkungan.",
                    'open_at' => now()->subDays(5),
                    'close_at' => now()->addDays(20),
                    'status' => JobVacancyStatus::OPEN,
                ],
                'qualifications' => [
                    ['label' => 'Pendidikan Formal', 'value' => 'D3 / D4 Teknik Radiodiagnostik & Radioterapi (Radiologi)', 'sort_order' => 1],
                    ['label' => 'IPK', 'value' => 'Minimal 3.00', 'sort_order' => 2],
                    ['label' => 'STR', 'value' => 'STR Radiografer aktif', 'sort_order' => 3],
                    ['label' => 'Keahlian Khusus', 'value' => 'Berpengalaman mengoperasikan CT-Scan dan X-Ray digital', 'sort_order' => 4],
                    ['label' => 'Usia', 'value' => 'Maksimal 30 tahun', 'sort_order' => 5],
                ],
                'documents' => [
                    ['name' => 'Surat Lamaran', 'description' => 'Ditujukan kepada Direktur RSI Fatimah Banyuwangi', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 1],
                    ['name' => 'Curriculum Vitae (CV)', 'description' => 'Daftar riwayat hidup terbaru', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 2],
                    ['name' => 'Ijazah & Transkrip Radiologi', 'description' => 'Scan ijazah D3/D4 Radiologi', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 3],
                    ['name' => 'STR Radiografer', 'description' => 'STR aktif dari KTKI', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 4],
                    ['name' => 'KTP', 'description' => 'Scan KTP elektronik', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 5],
                    ['name' => 'Sertifikat Proteksi Radiasi / CT-Scan', 'description' => 'Pelatihan Petugas Proteksi Radiasi (PPR) atau aplikasi CT-Scan', 'is_required' => false, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 6],
                ],
                'stages' => [
                    ['name' => 'Pendaftaran Online', 'description' => 'Pengunggahan berkas lamaran', 'sort_order' => 1, 'start_at' => now()->subDays(5), 'end_at' => now()->addDays(20)],
                    ['name' => 'Seleksi Administrasi', 'description' => 'Verifikasi kualifikasi dan berkas radiografer', 'sort_order' => 2, 'start_at' => now()->addDays(21), 'end_at' => now()->addDays(23)],
                    ['name' => 'Tes Praktik & Keterampilan Radiologi', 'description' => 'Uji kompetensi teknik radiografi dan CT-Scan', 'sort_order' => 3, 'start_at' => now()->addDays(24), 'end_at' => now()->addDays(26)],
                    ['name' => 'Wawancara User & Al-Islam', 'description' => 'Wawancara teknis dan komitmen ibadah', 'sort_order' => 4, 'start_at' => now()->addDays(27), 'end_at' => now()->addDays(29)],
                    ['name' => 'Medical Check Up (MCU)', 'description' => 'Pemeriksaan kesehatan dan paparan radiasi', 'sort_order' => 5, 'start_at' => now()->addDays(30), 'end_at' => now()->addDays(32)],
                ],
            ],

            // 4. Apoteker Pendamping Farmasi
            [
                'vacancy' => [
                    'recruitment_period_id' => $period->id,
                    'code' => 'POS-2026-FAR-01',
                    'title' => 'Apoteker Pendamping Farmasi',
                    'unit' => 'Instalasi Farmasi',
                    'employment_type' => 'PKWT',
                    'quota' => 2,
                    'location' => 'RSI Fatimah Banyuwangi',
                    'description' => 'Melaksanakan pengelolaan perbekalan farmasi, dispensing obat rawat jalan/inap, dan pelayanan farmasi klinis (PTO, MESO, Konseling).',
                    'responsibilities' => "1. Skrining resep dokter secara administratif, farmaseutik, dan klinis.\n2. Melakukan konseling informasi obat (PIO) kepada pasien pulang.\n3. Melakukan monitoring stok obat darurat, narkotika, dan psikotropika.",
                    'open_at' => now()->subDays(5),
                    'close_at' => now()->addDays(20),
                    'status' => JobVacancyStatus::OPEN,
                ],
                'qualifications' => [
                    ['label' => 'Pendidikan Formal', 'value' => 'Profesi Apoteker (S.Farm., Apt.)', 'sort_order' => 1],
                    ['label' => 'IPK', 'value' => 'Minimal 3.00', 'sort_order' => 2],
                    ['label' => 'STRA', 'value' => 'Surat Tanda Registrasi Apoteker (STRA) aktif', 'sort_order' => 3],
                    ['label' => 'Pengetahuan', 'value' => 'Memahami regulasi kefarmasian rumah sakit dan akreditasi RS', 'sort_order' => 4],
                    ['label' => 'Usia', 'value' => 'Maksimal 32 tahun', 'sort_order' => 5],
                ],
                'documents' => [
                    ['name' => 'Surat Lamaran', 'description' => 'Ditujukan kepada Direktur RSI Fatimah Banyuwangi', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 1],
                    ['name' => 'Curriculum Vitae (CV)', 'description' => 'Riwayat hidup lengkap', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 2],
                    ['name' => 'Ijazah Apoteker & Berita Acara Sumpah', 'description' => 'Scan ijazah profesi dan bukti sumpah apoteker', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 3],
                    ['name' => 'STRA Aktif', 'description' => 'Surat Tanda Registrasi Apoteker yang masih berlaku', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 4],
                    ['name' => 'KTP', 'description' => 'Scan KTP elektronik', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 5],
                    ['name' => 'Sertifikat Pelatihan Farmasi Klinis', 'description' => 'Pelatihan PTO / MESO / Konseling Obat', 'is_required' => false, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 6],
                ],
                'stages' => [
                    ['name' => 'Pendaftaran Online & Kelengkapan Berkas', 'description' => 'Registrasi awal berkas pelamar apoteker', 'sort_order' => 1, 'start_at' => now()->subDays(5), 'end_at' => now()->addDays(20)],
                    ['name' => 'Verifikasi Administrasi & STRA', 'description' => 'Pemeriksaan keabsahan STRA di KFN/Kemenkes', 'sort_order' => 2, 'start_at' => now()->addDays(21), 'end_at' => now()->addDays(23)],
                    ['name' => 'Ujian Kompetensi Farmasi Klinis', 'description' => 'Tes farmakoterapi, interaksi obat, dan manajemen farmasi RS', 'sort_order' => 3, 'start_at' => now()->addDays(24), 'end_at' => now()->addDays(26)],
                    ['name' => 'Wawancara Direksi & Kepribadian Islami', 'description' => 'Wawancara etika kefarmasian dan tes Al-Islam', 'sort_order' => 4, 'start_at' => now()->addDays(27), 'end_at' => now()->addDays(29)],
                    ['name' => 'Medical Check Up (MCU)', 'description' => 'Pemeriksaan kesehatan fisik calon apoteker', 'sort_order' => 5, 'start_at' => now()->addDays(30), 'end_at' => now()->addDays(32)],
                ],
            ],

            // 5. Pranata Laboratorium / Analis Medis
            [
                'vacancy' => [
                    'recruitment_period_id' => $period->id,
                    'code' => 'POS-2026-LAB-01',
                    'title' => 'Pranata Laboratorium / Analis Medis',
                    'unit' => 'Instalasi Laboratorium Patologi Klinik',
                    'employment_type' => 'PKWT',
                    'quota' => 2,
                    'location' => 'RSI Fatimah Banyuwangi',
                    'description' => 'Melakukan pengambilan spesimen biologis, pemeriksaan hematologi, kimia klinik, imunoserologi, dan mikrobiologi sesuai standar akreditasi.',
                    'responsibilities' => "1. Melakukan sampling/flebotomi darah pasien rawat jalan dan rawat inap.\n2. Menjalankan quality control harian alat analyzer laboratorium.\n3. Melaporkan nilai kritis hasil laboratorium ke dokter pengirim.",
                    'open_at' => now()->subDays(5),
                    'close_at' => now()->addDays(20),
                    'status' => JobVacancyStatus::OPEN,
                ],
                'qualifications' => [
                    ['label' => 'Pendidikan Formal', 'value' => 'D3 / D4 Teknologi Laboratorium Medis (TLM) / Analis Kesehatan', 'sort_order' => 1],
                    ['label' => 'IPK', 'value' => 'Minimal 3.00', 'sort_order' => 2],
                    ['label' => 'STR', 'value' => 'STR Pranata Laboratorium Kesehatan aktif', 'sort_order' => 3],
                    ['label' => 'Keahlian', 'value' => 'Terampil tindakan flebotomi bayi/dewasa dan pengoperasian analyzer otomatis', 'sort_order' => 4],
                    ['label' => 'Usia', 'value' => 'Maksimal 28 tahun', 'sort_order' => 5],
                ],
                'documents' => [
                    ['name' => 'Surat Lamaran', 'description' => 'Ditujukan kepada Direktur RSI Fatimah Banyuwangi', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 1],
                    ['name' => 'Curriculum Vitae (CV)', 'description' => 'Riwayat hidup lengkap', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 2],
                    ['name' => 'Ijazah & Transkrip TLM', 'description' => 'Scan ijazah D3/D4 Analis Kesehatan', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 3],
                    ['name' => 'STR Pranata Lab', 'description' => 'STR aktif KTKI', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 4],
                    ['name' => 'KTP', 'description' => 'Scan KTP elektronik', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 5],
                    ['name' => 'Sertifikat Pelatihan Flebotomi', 'description' => 'Pelatihan pengambilan sampel darah', 'is_required' => false, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 6],
                ],
                'stages' => [
                    ['name' => 'Pendaftaran Online', 'description' => 'Pengisian berkas online', 'sort_order' => 1, 'start_at' => now()->subDays(5), 'end_at' => now()->addDays(20)],
                    ['name' => 'Seleksi Administrasi', 'description' => 'Verifikasi ijazah dan STR pranata laboratorium', 'sort_order' => 2, 'start_at' => now()->addDays(21), 'end_at' => now()->addDays(23)],
                    ['name' => 'Uji Keterampilan Flebotomi & Laboratorium', 'description' => 'Tes praktik pengambilan sampel darah dan pembuatan preparat', 'sort_order' => 3, 'start_at' => now()->addDays(24), 'end_at' => now()->addDays(26)],
                    ['name' => 'Wawancara User & Al-Islam', 'description' => 'Wawancara kepala instalasi laboratorium dan komitmen islami', 'sort_order' => 4, 'start_at' => now()->addDays(27), 'end_at' => now()->addDays(29)],
                    ['name' => 'Medical Check Up (MCU)', 'description' => 'Pemeriksaan kesehatan bebas penyakit menular', 'sort_order' => 5, 'start_at' => now()->addDays(30), 'end_at' => now()->addDays(32)],
                ],
            ],

            // 6. Staff IT & Administrator SIMRS
            [
                'vacancy' => [
                    'recruitment_period_id' => $period->id,
                    'code' => 'POS-2026-IT-01',
                    'title' => 'Staff IT & Administrator SIMRS',
                    'unit' => 'Instalasi Teknologi Informasi',
                    'employment_type' => 'PKWT',
                    'quota' => 1,
                    'location' => 'RSI Fatimah Banyuwangi',
                    'description' => 'Mengelola infrastruktur server, jaringan rumah sakit, pemeliharaan basis data SIMRS, serta integrasi layanan SatuSehat & BPJS Kesehatan.',
                    'responsibilities' => "1. Pemeliharaan software SIMRS, database MySQL/PostgreSQL, dan backup harian.\n2. Troubleshoot perangkat keras, printer rekam medis, dan jaringan LAN/WiFi RS.\n3. Mengembangkan API integrasi SatuSehat Kemenkes dan bridging BPJS V-Claim.",
                    'open_at' => now()->subDays(5),
                    'close_at' => now()->addDays(20),
                    'status' => JobVacancyStatus::OPEN,
                ],
                'qualifications' => [
                    ['label' => 'Pendidikan Formal', 'value' => 'S1 / D4 Teknik Informatika / Sistem Informasi / Ilmu Komputer', 'sort_order' => 1],
                    ['label' => 'IPK', 'value' => 'Minimal 3.00', 'sort_order' => 2],
                    ['label' => 'Kemampuan Pemrograman', 'value' => 'Menguasai PHP (Laravel), SQL, JavaScript, RESTful API', 'sort_order' => 3],
                    ['label' => 'Infrastruktur Jaringan', 'value' => 'Memahami Mikrotik, Linux Server, dan troubleshooting jaringan LAN', 'sort_order' => 4],
                    ['label' => 'Nilai Tambah', 'value' => 'Memiliki pengalaman integrasi SIMRS / BPJS / SatuSehat', 'sort_order' => 5],
                    ['label' => 'Usia', 'value' => 'Maksimal 30 tahun', 'sort_order' => 6],
                ],
                'documents' => [
                    ['name' => 'Surat Lamaran', 'description' => 'Ditujukan kepada Direktur RSI Fatimah Banyuwangi', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 1],
                    ['name' => 'Curriculum Vitae (CV) & Portofolio IT', 'description' => 'Cantumkan tautan GitHub / proyek aplikasi yang pernah dibangun', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 2],
                    ['name' => 'Ijazah & Transkrip Akademik', 'description' => 'Scan ijazah S1/D4 bidang IT', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 3],
                    ['name' => 'KTP', 'description' => 'Scan KTP elektronik', 'is_required' => true, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 4],
                    ['name' => 'Sertifikat Keahlian IT / Jaringan', 'description' => 'Sertifikasi keahlian IT (MTCNA, CCNA, Database, Web Dev)', 'is_required' => false, 'allowed_mimes' => 'application/pdf', 'max_size_kb' => 2048, 'sort_order' => 5],
                ],
                'stages' => [
                    ['name' => 'Pendaftaran Online & Review Portofolio', 'description' => 'Pemeriksaan berkas dan portofolio coding/proyek pelamar', 'sort_order' => 1, 'start_at' => now()->subDays(5), 'end_at' => now()->addDays(20)],
                    ['name' => 'Seleksi Administrasi', 'description' => 'Verifikasi keabsahan data pendidikan', 'sort_order' => 2, 'start_at' => now()->addDays(21), 'end_at' => now()->addDays(23)],
                    ['name' => 'Tes Logika SQL & Live Coding Integrasi API', 'description' => 'Uji keterampilan query database relasional dan pembuatan API Laravel', 'sort_order' => 3, 'start_at' => now()->addDays(24), 'end_at' => now()->addDays(26)],
                    ['name' => 'Wawancara Teknis IT & Al-Islam', 'description' => 'Bedah arsitektur sistem SIMRS dan tes membaca Al-Qur\'an', 'sort_order' => 4, 'start_at' => now()->addDays(27), 'end_at' => now()->addDays(29)],
                    ['name' => 'Medical Check Up (MCU)', 'description' => 'Pemeriksaan kesehatan fisik calon pegawai IT', 'sort_order' => 5, 'start_at' => now()->addDays(30), 'end_at' => now()->addDays(32)],
                ],
            ],
        ];

        foreach ($vacanciesData as $item) {
            $vacancy = JobVacancy::updateOrCreate(
                ['code' => $item['vacancy']['code']],
                $item['vacancy']
            );

            // Kualifikasi dinamis
            foreach ($item['qualifications'] as $qualification) {
                VacancyQualification::updateOrCreate(
                    [
                        'job_vacancy_id' => $vacancy->id,
                        'label' => $qualification['label'],
                    ],
                    [
                        'value' => $qualification['value'],
                        'sort_order' => $qualification['sort_order'],
                    ]
                );
            }

            // Persyaratan berkas dokumen
            foreach ($item['documents'] as $doc) {
                VacancyDocumentRequirement::updateOrCreate(
                    [
                        'job_vacancy_id' => $vacancy->id,
                        'name' => $doc['name'],
                    ],
                    [
                        'description' => $doc['description'],
                        'is_required' => $doc['is_required'],
                        'allowed_mimes' => $doc['allowed_mimes'],
                        'max_size_kb' => $doc['max_size_kb'],
                        'sort_order' => $doc['sort_order'],
                    ]
                );
            }

            // Tahapan seleksi dinamis
            foreach ($item['stages'] as $stage) {
                VacancySelectionStage::updateOrCreate(
                    [
                        'job_vacancy_id' => $vacancy->id,
                        'name' => $stage['name'],
                    ],
                    [
                        'description' => $stage['description'],
                        'sort_order' => $stage['sort_order'],
                        'start_at' => $stage['start_at'],
                        'end_at' => $stage['end_at'],
                    ]
                );
            }
        }
    }
}
