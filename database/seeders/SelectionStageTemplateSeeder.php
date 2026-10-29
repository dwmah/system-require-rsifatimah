<?php

namespace Database\Seeders;

use App\Models\SelectionStageTemplate;
use Illuminate\Database\Seeder;

class SelectionStageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultStages = [
            [
                'sequence_order' => 1,
                'name' => 'Pendaftaran Peserta Rekrutmen',
                'description' => 'Pendaftaran online akun peserta rekrutmen dan verifikasi kelayakan biodata data diri pelamar.',
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addDays(5)->format('Y-m-d'),
                'announcement_date' => now()->addDays(6)->format('Y-m-d'),
            ],
            [
                'sequence_order' => 2,
                'name' => 'Seleksi Administrasi',
                'description' => 'Pemeriksaan dan verifikasi keabsahan seluruh berkas dokumen persyaratan (CV, Portofolio, Ijazah, STR, dll) oleh tim SDI serta pengumuman hasil kelulusan berkas administrasi.',
                'start_date' => now()->addDays(6)->format('Y-m-d'),
                'end_date' => now()->addDays(10)->format('Y-m-d'),
                'announcement_date' => now()->addDays(11)->format('Y-m-d'),
            ],
            [
                'sequence_order' => 3,
                'name' => 'Seleksi test tulis, test skill, Test Wawancara',
                'description' => 'Ujian kompetensi tulis bidang, tes keterampilan klinis/teknis, wawancara kompetensi dan etika islami, serta pengumuman kelulusan tahap berikutnya.',
                'start_date' => now()->addDays(12)->format('Y-m-d'),
                'end_date' => now()->addDays(17)->format('Y-m-d'),
                'announcement_date' => now()->addDays(19)->format('Y-m-d'),
            ],
            [
                'sequence_order' => 4,
                'name' => 'Seleksi test psikologi dan test kesehatan',
                'description' => 'Pemeriksaan psikotes lanjutan dan Medical Check-Up (MCU) kesehatan fisik bebas narkoba di RS serta pengumuman hasil kelulusan tahap psikologi dan kesehatan.',
                'start_date' => now()->addDays(20)->format('Y-m-d'),
                'end_date' => now()->addDays(24)->format('Y-m-d'),
                'announcement_date' => now()->addDays(26)->format('Y-m-d'),
            ],
            [
                'sequence_order' => 5,
                'name' => 'Masa Orientasi Magang Dokter Umum Baru (atau Karyawan Baru)',
                'description' => 'Pembekalan nilai Al-Islam & Kemuhammadiyahan, patient safety, SOP Rumah Sakit, dan penugasan orientasi kerja.',
                'start_date' => now()->addDays(27)->format('Y-m-d'),
                'end_date' => now()->addDays(34)->format('Y-m-d'),
                'announcement_date' => null,
            ],
        ];

        SelectionStageTemplate::updateOrCreate(
            ['name' => 'Template Standar RSI Fatimah'],
            [
                'is_default' => true,
                'stages_data' => $defaultStages,
            ]
        );
    }
}
