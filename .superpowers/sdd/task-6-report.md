# Task 6 Implementation Report: Timeline Seleksi, Template Tahapan Seleksi & Switch Buka/Tutup Periode

## Objective
Implementasi fitur sesuai permintaan:
1. Template tahapan seleksi reusable seragam sesuai Foto 2:
   - Tahap 1: Pendaftaran Peserta Rekrutmen Dan Seleksi Administrasi (Tanggal Pendaftaran s/d Batas Akhir Submit; Tanggal Seleksi Administrasi/Verifikasi Berkas s/d Pengumuman lolos test tahap-1)
   - Tahap 2: Seleksi test tulis, test skill, Test Wawancara (Timeline Tes s/d Pengumuman lolos test tahap-2)
   - Tahap 3: Seleksi test psikologi dan test kesehatan (Timeline Tes s/d Pengumuman lolos test tahap-3)
   - Tahap 4: Masa Orientasi Magang Dokter Umum Baru (atau Karyawan Baru) (Timeline Magang/Orientasi)
2. Switch toggle buka/tutup periode rekrutmen untuk kemudahan admin.
3. Sinkronisasi template ke seluruh formasi lowongan pada periode aktif dan dropdown "Gunakan Template Timeline Seleksi" pada form create/edit lowongan.
4. Validasi batas waktu submit dokumen di sisi server & indikator visual penutupan pengumpulan berkas di portal pelamar.

## Implementation Details
1. **Database Migration & Models:**
   - Migration `database/migrations/2026_10_28_000004_create_selection_stage_templates_table.php`.
   - Model `app/Models/SelectionStageTemplate.php` dengan `$fillable` dan casts (`is_default` => 'boolean', `stages_data` => 'array').
   - Seeder `database/seeders/SelectionStageTemplateSeeder.php` dengan 4 tahapan standar sesuai Foto 2.
2. **Switch Buka/Tutup Periode:**
   - Endpoint `POST /admin/timelines/{period}/toggle-status` di `AdminTimelineController::toggleStatus`.
   - UI switch toggle di `resources/views/admin/timelines/index.blade.php`.
3. **Template Timeline Seleksi & Terapkan ke Lowongan:**
   - CRUD template tahapan seleksi di `AdminTimelineController` (`storeTemplate`, `updateTemplate`, `destroyTemplate`).
   - Endpoint aksi mass-sync `POST /admin/timelines/templates/{template}/apply-to-vacancies` untuk menyinkronkan tahapan formasi lowongan ke database.
   - Dropdown "Gunakan Template" pada form pembukaan lowongan (`create.blade.php` dan `edit.blade.php`) dengan auto-fill instan via AlpineJS.
4. **Validasi Batas Waktu & Indikator Visual Berkas:**
   - Helper `JobApplication::isSubmissionDeadlinePassed()`.
   - Validasi fail-closed di `ApplicationDocumentService::uploadOrReplace()` melemparkan `DomainException("Batas waktu pengumpulan berkas telah berakhir.")`.
   - Banner visual jelas & nonaktifkan tombol upload di `resources/views/portal/applications/documents.blade.php` dengan badge status "Batas pengumpulan berakhir".
5. **Testing & Code Formatting:**
   - Feature Test: `tests/Feature/SelectionTimelineAndTemplateTest.php` (4 test methods, 23 assertions, 100% pass).
   - Laravel Pint dijalankan dan semua file PHP berstandar rapi.

## Status: DONE
