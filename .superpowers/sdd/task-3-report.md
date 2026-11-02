# Task 3 Report: Dukungan Format Video (.mp4) pada Custom Berkas Lowongan

## Status
DONE

## Ringkasan Perubahan
1. **Service Backend (`app/Services/Recruitment/ApplicationDocumentService.php`)**:
   - `mimeToExtension`: Ditambahkan mapping `'video/mp4' => 'mp4'`.
   - `canonicalizeMimeToken`: Ditambahkan mapping token `'mp4'`, `'video/mp4' => 'video/mp4'`.
   - Validasi upload `validateFile()` secara otomatis mengizinkan file berformat `video/mp4` sesuai requirement lowongan dan menampilkan ekstensi `MP4` pada pesan error human-readable jika format tidak cocok.
   - Ekstensi file yang disimpan dan filename yang disanitasi mempertahankan format `.mp4`.

2. **Admin UI Vacancy Management (`resources/views/admin/vacancies/create.blade.php` & `edit.blade.php`)**:
   - Menambahkan opsi tipe file: Dokumen PDF (.pdf), Gambar (.jpg, .png), Dokumen/Gambar (.pdf, .jpg, .png), dan Video MP4 (.mp4).
   - Menambahkan opsi pilihan batas ukuran berkas (2 MB, 5 MB, 10 MB, 20 MB, 50 MB).
   - Menyesuaikan batas ukuran file default menjadi 20 MB (20480 KB) jika opsi Video MP4 dipilih.

3. **Portal Pelamar Berkas UI (`resources/views/portal/applications/documents.blade.php`)**:
   - Menampilkan badge `MP4 Video` jika berkas persyaratan memiliki allowed MIME video MP4.
   - Input file accept attribute disesuaikan dinamis sesuai `allowed_mimes` requirement.
   - Menyediakan modal pratinjau dokumen untuk pelamar dengan tag `<video controls>` dan tombol "Download Video" bila berkas bertipe MP4.

4. **Admin Show Application UI (`resources/views/admin/applications/show.blade.php`)**:
   - Menambahkan pengecekan `is_video` untuk berkas video MP4.
   - Viewer dokumen interaktif menampilkan tag `<video controls>` dengan `<source src="..." type="video/mp4">` dan tombol "Download Video".
   - Viewer modal bundle dokumen juga mendukung video player MP4 responsif.

5. **Pengujian & Verifikasi (`tests/Feature/VacancyVideoUploadRequirementTest.php`)**:
   - `test_admin_can_create_vacancy_with_video_mp4_requirement`: Verifikasi admin membuat formasi dengan berkas MP4.
   - `test_applicant_can_upload_mp4_video_and_metadata_saved_correctly`: Verifikasi pelamar mengunggah video MP4, berkas tersimpan aman di disk privat dengan UUID `.mp4`.
   - `test_applicant_uploading_non_mp4_file_is_rejected_with_validation_error`: Verifikasi penolakan file non-mp4 (misal PDF) jika syarat hanya video MP4.
   - `test_admin_and_applicant_can_access_video_preview_and_download`: Verifikasi hak akses preview, download, dan rendering player video di sisi admin dan portal pelamar.
   - Seluruh test (4 tests, 32 assertions) lulus 100%. Code style lolos Laravel Pint.
