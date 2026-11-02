# Task 4 Implementation Report: Upload & Publikasi Lampiran Hasil Tes di Setiap Tahapan Seleksi

## Ringkasan Pekerjaan
Fitur upload dan publikasi lampiran hasil tes di setiap tahapan seleksi telah selesai diimplementasikan. Admin HRD kini dapat mengunggah file hasil tes/pengumuman (format PDF, JPG, PNG hingga 10 MB) di setiap tahapan seleksi (bukan hanya tahap 3), baik saat mengubah status ke lolos, gugur, maupun in progress. Pelamar dapat melihat dan mengunduh berkas tersebut melalui timeline tracking hanya jika tahapan telah dipublikasikan (`isStagePublished()`), menjaga kerahasiaan dan kepatuhan anti-leak.

---

## Rincian Perubahan Kode

### 1. Database Migration
- File: `database/migrations/2026_10_28_000002_add_attachment_to_application_stage_histories_table.php`
- Kolom baru pada tabel `application_stage_histories`:
  - `attachment_path` (string, nullable)
  - `attachment_original_name` (string, nullable)
  - `attachment_mime` (string, nullable)
- Status: Telah dimigrasi ke database.

### 2. Model `ApplicationStageHistory`
- Menambahkan kolom `attachment_path`, `attachment_original_name`, `attachment_mime` ke `$fillable`.
- Menambahkan method `hasAttachment(): bool`.
- Menambahkan accessor `getAttachmentUrlAttribute(): ?string` yang secara dinamis dan aman menghasilkan route unduhan untuk admin atau pelamar.

### 3. Service `ApplicationVerificationService`
- Menambahkan parameter opsional `?UploadedFile $attachment = null` pada `updateSelectionStage()`.
- Validasi MIME type: `application/pdf`, `image/jpeg`, `image/png` dengan ukuran maks 10 MB.
- Penyimpanan file ke disk `local` pada direktori privat `stage_attachments/{application_id}/...`.
- Otomatis menghapus file lampiran lama jika ada pembaruan file baru.
- Menyimpan metadata `attachment_path`, `attachment_original_name`, dan `attachment_mime` ke `ApplicationStageHistory`.

### 4. Controller & Routing
- `AdminApplicationController`:
  - Validasi file `stage_attachment` (mimes:pdf,jpg,jpeg,png|max:10240) di method `updateStage()`.
  - Meneruskan file ke `updateSelectionStage()`.
  - Endpoint baru: `downloadStageAttachment()` via route `admin.applications.stages.download-attachment` (`GET /admin/applications/{application}/stages/{history}/attachment`).
- `ApplicationTrackingController`:
  - Endpoint baru: `downloadStageAttachment()` via route `portal.applications.stages.download-attachment` (`GET /portal/lamaran/{jobApplication}/stages/{history}/attachment`).
  - Proteksi otorisasi kepemilikan lamaran (`Gate::authorize('view', $jobApplication)`).
  - Proteksi kerahasiaan publikasi: verifikasi `isStagePublished($history)` sebelum mengizinkan download.
- `routes/web.php`:
  - Mendaftarkan kedua route untuk admin dan portal pelamar.

### 5. UI Admin (`resources/views/admin/applications/show.blade.php`)
- Form modal `stageModalOpen`:
  - Menambahkan atribut `enctype="multipart/form-data"`.
  - Menambahkan input file `stage_attachment` dengan accept `.pdf,.jpg,.jpeg,.png` beserta label dan keterangan ukuran maks 10 MB.
- Timeline tahapan admin:
  - Menampilkan badge/link unduh lampiran: `📎 Lihat Lampiran Hasil / Dokumen (nama file)` jika ada lampiran.

### 6. Timeline Tracking Pelamar
- `app/Services/Recruitment/SelectionTrackingService.php`:
  - Menyertakan metadata `has_attachment`, `attachment_name`, `attachment_url`, dan `attachment_mime` hanya jika tahapan valid dan telah dipublikasikan (`isStagePublished()`).
- `resources/views/portal/applications/tracking.blade.php`:
  - Menampilkan card lampiran hasil seleksi dan tombol `Lihat / Unduh Lampiran Hasil Tes (PDF/Gambar)` ketika `has_attachment` bernilai true.

---

## Verifikasi & Pengujian
- File test baru: `tests/Feature/StageResultUploadTest.php`
  - `test_admin_can_update_stage_with_pdf_attachment`: Berhasil (upload PDF, file tersimpan di storage, database terisi, admin dapat mengunduh).
  - `test_admin_can_update_stage_with_jpg_image_attachment`: Berhasil (upload JPG tersimpan dan MIME terdeteksi image/jpeg).
  - `test_applicant_can_see_and_download_attachment_when_published`: Berhasil (pelamar melihat card & tombol download saat status dipublikasi).
  - `test_applicant_cannot_see_or_download_attachment_when_unpublished`: Berhasil (pelamar tidak dapat melihat link dan dicegah 403 Forbidden saat belum dipublikasikan).
  - `test_other_applicant_cannot_download_stage_attachment`: Berhasil (pelamar lain dicegah 403 Forbidden).
  - `test_disallowed_file_types_are_rejected_by_validation`: Berhasil (file .exe, .docx, dan file >10MB ditolak oleh validasi).
- Hasil PHPUnit: `6 tests, 36 assertions, all passed`.
- Hasil Pint: Seluruh kode bersih sesuai format standar PSR / Laravel Pint.

---
**Status**: DONE
