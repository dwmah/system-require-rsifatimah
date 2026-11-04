<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Perbarui nama tahapan di vacancy_selection_stages
        DB::table('vacancy_selection_stages')
            ->where('name', 'Masa Orientasi Magang Dokter Umum Baru (atau Karyawan Baru)')
            ->update(['name' => 'Masa Orientasi & Pembekalan Karyawan Baru']);

        DB::table('vacancy_selection_stages')
            ->where('name', 'Seleksi test tulis, test skill, Test Wawancara')
            ->update(['name' => 'Seleksi Tes Tertulis, Keterampilan, dan Wawancara']);

        DB::table('vacancy_selection_stages')
            ->whereIn('name', ['Seleksi test psikologi dan test kesehatan', 'Seleksi test kesehatan'])
            ->update(['name' => 'Seleksi Psikotes dan Pemeriksaan Kesehatan (MCU)']);

        // 2. Perbarui stages_data pada selection_stage_templates
        $templates = DB::table('selection_stage_templates')->get();
        foreach ($templates as $tpl) {
            if ($tpl->stages_data) {
                $stagesData = json_decode($tpl->stages_data, true);
                if (is_array($stagesData)) {
                    foreach ($stagesData as &$s) {
                        if (isset($s['name'])) {
                            $s['name'] = match ($s['name']) {
                                'Masa Orientasi Magang Dokter Umum Baru (atau Karyawan Baru)' => 'Masa Orientasi & Pembekalan Karyawan Baru',
                                'Seleksi test tulis, test skill, Test Wawancara' => 'Seleksi Tes Tertulis, Keterampilan, dan Wawancara',
                                'Seleksi test psikologi dan test kesehatan', 'Seleksi test kesehatan' => 'Seleksi Psikotes dan Pemeriksaan Kesehatan (MCU)',
                                default => $s['name'],
                            };
                        }
                    }
                    unset($s);
                    DB::table('selection_stage_templates')
                        ->where('id', $tpl->id)
                        ->update(['stages_data' => json_encode($stagesData)]);
                }
            }
        }

        // 3. Sinkronisasi tahun dan batas akhir pendaftaran pada recruitment_periods id 1
        DB::table('recruitment_periods')
            ->where('id', 1)
            ->where('registration_end_at', 'like', '2027-%')
            ->update(['registration_end_at' => '2026-11-07 23:59:59']);

        // 4. Perbarui application_snapshots yang sudah terlanjur menyimpan nama lama dalam vacancy_data
        $snapshots = DB::table('application_snapshots')->get();
        foreach ($snapshots as $snap) {
            if ($snap->vacancy_data) {
                $vData = json_decode($snap->vacancy_data, true);
                if (is_array($vData) && ! empty($vData['selection_stages']) && is_array($vData['selection_stages'])) {
                    $hasChange = false;
                    foreach ($vData['selection_stages'] as &$st) {
                        if (isset($st['name'])) {
                            $oldName = $st['name'];
                            $st['name'] = match ($oldName) {
                                'Masa Orientasi Magang Dokter Umum Baru (atau Karyawan Baru)' => 'Masa Orientasi & Pembekalan Karyawan Baru',
                                'Seleksi test tulis, test skill, Test Wawancara' => 'Seleksi Tes Tertulis, Keterampilan, dan Wawancara',
                                'Seleksi test psikologi dan test kesehatan', 'Seleksi test kesehatan' => 'Seleksi Psikotes dan Pemeriksaan Kesehatan (MCU)',
                                default => $st['name'],
                            };
                            if ($st['name'] !== $oldName) {
                                $hasChange = true;
                            }
                        }
                    }
                    unset($st);

                    if ($hasChange) {
                        DB::table('application_snapshots')
                            ->where('id', $snap->id)
                            ->update(['vacancy_data' => json_encode($vData)]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert nama tahapan jika di-rollback
        DB::table('vacancy_selection_stages')
            ->where('name', 'Masa Orientasi & Pembekalan Karyawan Baru')
            ->update(['name' => 'Masa Orientasi Magang Dokter Umum Baru (atau Karyawan Baru)']);

        DB::table('vacancy_selection_stages')
            ->where('name', 'Seleksi Tes Tertulis, Keterampilan, dan Wawancara')
            ->update(['name' => 'Seleksi test tulis, test skill, Test Wawancara']);

        DB::table('vacancy_selection_stages')
            ->where('name', 'Seleksi Psikotes dan Pemeriksaan Kesehatan (MCU)')
            ->update(['name' => 'Seleksi test psikologi dan test kesehatan']);
    }
};

