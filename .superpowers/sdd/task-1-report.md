# Task 1 Report: Fix Bug Sinkronisasi Tahapan Seleksi Baru di Portal User (No.6)

## Overview
Ketika admin menambahkan tahapan seleksi baru pada formasi/lowongan (`VacancySelectionStage`) setelah pelamar mengirimkan lamaran, tahapan baru tersebut sebelumnya tidak muncul pada portal pelamar. Hal ini dikarenakan `SelectionTrackingService::tracking()` hanya mengiterasi tahapan dari `snapshot->vacancy_data['selection_stages']`.

## Perubahan yang Dilakukan
1. **Tests (`tests/Feature/SelectionStageSyncTest.php`)**:
   - Menambahkan feature test untuk skenario:
     - Lowongan dibuat dengan 2 tahapan awal.
     - Pelamar mengirimkan lamaran sehingga snapshot terbuat berisi 2 tahapan.
     - Admin menambahkan tahapan ke-3 (`VacancySelectionStage`).
     - Verifikasi pemanggilan `SelectionTrackingService::tracking()` menyertakan tahapan ke-3 dengan status `waiting` ("Menunggu"), `applicant_message = null`, dan `published_at = null`.
     - Verifikasi route portal tracking `/portal/lamaran/{jobApplication}/tracking` menampilkan tahapan ke-3.
2. **Reconciliation Logic (`app/Services/Recruitment/SelectionTrackingService.php`)**:
   - Mengambil live selection stages via `$application->jobVacancy->selectionStages()->orderBy('sort_order')->get()`.
   - Melakukan rekonsiliasi/merge antara data tahapan dari snapshot dan tahapan live: setiap tahapan live yang ID-nya belum ada di snapshot diintegrasikan ke array stages.
   - Mengurutkan tahapan berdasarkan `sort_order`.
   - Menggunakan `$liveStagesById` untuk me-resolve model live stage secara tepat berdasarkan primary key ID.
   - Memastikan perlindungan keamanan: status unpublished tetap `waiting`/`Menunggu`, field internal `note` dan `decided_at` tidak bocor.
3. **Penyesuaian Test (`tests/Feature/ApplicantDashboardUiUxTest.php`)**:
   - Memperbarui test dashboard timeline untuk memastikan tahapan baru live disinkronkan dan muncul berdampingan dengan tahapan snapshot yang dibekukan.

## Hasil Verifikasi Test
- `vendor/bin/phpunit tests/Feature/SelectionStageSyncTest.php`: PASSED (1 test, 14 assertions)
- `vendor/bin/phpunit tests/Feature/AdminVacancyCustomDocumentsAndTimelineTest.php`: PASSED (4 tests, 25 assertions)
- `vendor/bin/phpunit tests/Feature/ApplicantSelectionTrackingTest.php`: PASSED (23 tests, 137 assertions)
- `vendor/bin/phpunit tests/Feature/ApplicantDashboardUiUxTest.php`: PASSED (12 tests, 90 assertions)
- Total 40 tests, 266 assertions lulus 100%.
- Format code lolos pengecekan `vendor/bin/pint --dirty --format agent`.
