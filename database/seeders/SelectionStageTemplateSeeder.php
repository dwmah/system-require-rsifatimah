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
                'name' => 'Pendaftaran Peserta Rekrutmen Dan Seleksi Administrasi',
                'description' => 'Pendaftaran online, unggah dokumen persyaratan, verifikasi keabsahan berkas oleh tim SDI, dan pengumuman kelulusan administrasi tahap 1.',
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addDays(7)->format('Y-m-d'),
                'announcement_date' => now()->addDays(9)->format('Y-m-d'),
            ],
            [
                'sequence_order' => 2,
                'name' => 'Seleksi test tulis, test skill, Test Wawancara',
                'description' => 'Ujian kompetensi tulis bidang, tes keterampilan klinis/teknis, wawancara kompetensi dan etika islami, serta pengumuman kelulusan tahap 2.',
                'start_date' => now()->addDays(10)->format('Y-m-d'),
                'end_date' => now()->addDays(15)->format('Y-m-d'),
                'announcement_date' => now()->addDays(17)->format('Y-m-d'),
            ],
            [
                'sequence_order' => 3,
                'name' => 'Seleksi test psikologi dan test kesehatan',
                'description' => 'Pemeriksaan psikotes lanjutan dan Medical Check-Up (MCU) kesehatan fisik bebas narkoba di RS serta pengumuman akhir tahap 3.',
                'start_date' => now()->addDays(18)->format('Y-m-d'),
                'end_date' => now()->addDays(22)->format('Y-m-d'),
                'announcement_date' => now()->addDays(24)->format('Y-m-d'),
            ],
            [
                'sequence_order' => 4,
                'name' => 'Masa Orientasi Magang Dokter Umum Baru (atau Karyawan Baru)',
                'description' => 'Pembekalan nilai Al-Islam & Kemuhammadiyahan, patient safety, SOP Rumah Sakit, dan penugasan orientasi kerja.',
                'start_date' => now()->addDays(25)->format('Y-m-d'),
                'end_date' => now()->addDays(32)->format('Y-m-d'),
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
