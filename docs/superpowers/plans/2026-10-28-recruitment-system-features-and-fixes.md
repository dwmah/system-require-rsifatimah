# Implementation Plan: Recruitment System Revisions, Features & Bug Fixing

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan konfigurasi template timeline seleksi terpadu dengan switch aktif/tutup periode, soft delete akun pelamar tidak lolos untuk menjaga arsip CV/Portofolio, dukungan custom berkas video .mp4, upload lampiran hasil tes (PDF/JPG/PNG) di setiap tahapan seleksi beserta tampilannya di portal pelamar, CMS dinamis panduan alur & persyaratan, dan perbaikan sinkronisasi tahapan seleksi baru di timeline user.

**Architecture:** 
1. Database schema updates: soft deletes on `users`, migration for `selection_stage_templates` and `recruitment_guides`, and attachment fields on `application_stage_histories`.
2. Services update: `SelectionTrackingService` dynamically merges live stages with snapshot stages, ensuring newly created stages render seamlessly for applicants. `ApplicationDocumentService` handles MP4 validation and storage.
3. Controller & Views: Admin timeline converted to template-based timeline with active toggle switch; Admin application show modal supports stage attachment upload and note; Guides controller connects to dynamic DB content; Portal views display stage attachments and updated deadlines.

**Tech Stack:** Laravel 11/12, PHP 8.3, Blade, Alpine.js, Tailwind CSS, PHPUnit.

---

### Task 1: Fix Bug - Sinkronisasi Tahapan Seleksi Baru di Portal User (No.6)
**Files:**
- Modify: `app/Services/Recruitment/SelectionTrackingService.php`
- Modify: `app/Services/Portal/ApplicantDashboardService.php`
- Test: `tests/Feature/SelectionStageSyncTest.php`

- [ ] **Step 1: Buat Feature Test untuk mereproduksi bug tahapan seleksi baru**
- [ ] **Step 2: Jalankan test dan pastikan gagal**
- [ ] **Step 3: Update `SelectionTrackingService` agar menggabungkan tahapan live dari `JobVacancy` dengan snapshot**
- [ ] **Step 4: Update `ApplicantDashboardService` agar konsisten**
- [ ] **Step 5: Jalankan test kembali dan pastikan lulus**
- [ ] **Step 6: Commit**

---

### Task 2: Siklus Akun & Pengarsipan Pelamar Tidak Lolos (Soft Delete) (No.2)
**Files:**
- Create: `database/migrations/2026_10_28_000001_add_soft_deletes_to_users_table.php`
- Modify: `app/Models/User.php`
- Modify: `app/Services/Lifecycle/UserLifecycleService.php`
- Modify: `app/Http/Controllers/Admin/AdminLifecycleController.php`
- Modify: `resources/views/admin/lifecycle/index.blade.php`
- Test: `tests/Feature/UserLifecycleSoftDeleteTest.php`

- [ ] **Step 1: Tulis migration `softDeletes` untuk tabel `users` dan jalankan migrate**
- [ ] **Step 2: Tambahkan trait `SoftDeletes` pada model `User`**
- [ ] **Step 3: Tulis failing test untuk cleanup rejected applicant yang mempertahankan berkas CV/Portofolio dan soft delete user**
- [ ] **Step 4: Update `UserLifecycleService` agar tidak menghapus fisik file dokumen CV/Portofolio dan menggunakan `$user->delete()` sebagai soft delete**
- [ ] **Step 5: Update filter dan tampilan di `AdminLifecycleController` dan Blade**
- [ ] **Step 6: Jalankan test dan pastikan lulus**
- [ ] **Step 7: Commit**

---

### Task 3: Dukungan Format Video (.mp4) pada Custom Berkas Lowongan (No.3)
**Files:**
- Modify: `app/Services/Recruitment/ApplicationDocumentService.php`
- Modify: `resources/views/admin/vacancies/create.blade.php`
- Modify: `resources/views/admin/vacancies/edit.blade.php`
- Modify: `resources/views/portal/applications/documents.blade.php`
- Modify: `resources/views/admin/applications/show.blade.php`
- Test: `tests/Feature/VacancyVideoUploadRequirementTest.php`

- [ ] **Step 1: Tulis failing test upload dokumen video format .mp4**
- [ ] **Step 2: Update `ApplicationDocumentService` (tambahkan MIME `video/mp4` dan mapping ekstensi `mp4`)**
- [ ] **Step 3: Update UI Form Tambah/Edit Lowongan Admin untuk opsi tipe file video MP4**
- [ ] **Step 4: Update UI Unggah Berkas Portal Pelamar agar menerima video/mp4 dan menampilkan ukuran sesuai kebutuhan**
- [ ] **Step 5: Tambahkan pemutar video `<video controls>` pada modal preview berkas di Admin & Portal**
- [ ] **Step 6: Jalankan test dan pastikan lulus**
- [ ] **Step 7: Commit**

---

### Task 4: Upload & Publikasi Lampiran Hasil Tes di Setiap Tahapan Seleksi (No.4)
**Files:**
- Create: `database/migrations/2026_10_28_000002_add_attachment_to_application_stage_histories_table.php`
- Modify: `app/Models/ApplicationStageHistory.php`
- Modify: `app/Services/Recruitment/ApplicationVerificationService.php`
- Modify: `app/Http/Controllers/Admin/AdminApplicationController.php`
- Modify: `app/Services/Recruitment/SelectionTrackingService.php`
- Modify: `resources/views/admin/applications/show.blade.php`
- Modify: `resources/views/portal/applications/tracking.blade.php`
- Test: `tests/Feature/StageResultUploadTest.php`

- [ ] **Step 1: Tulis migration penambahan kolom `attachment_path` & `attachment_original_name` pada tabel `application_stage_histories`**
- [ ] **Step 2: Tulis failing test upload hasil tes pada tahapan seleksi**
- [ ] **Step 3: Perbarui `ApplicationVerificationService` dan controller admin untuk menangani upload lampiran hasil tes (PDF, JPG, PNG)**
- [ ] **Step 4: Update modal di `admin/applications/show.blade.php` dengan input upload lampiran hasil tes di bawah catatan**
- [ ] **Step 5: Update `SelectionTrackingService` dan `tracking.blade.php` agar pelamar dapat melihat/mengunduh lampiran hasil tes**
- [ ] **Step 6: Jalankan test dan pastikan lulus**
- [ ] **Step 7: Commit**

---

### Task 5: Pengelolaan Konten Panduan di Dashboard Admin (CMS Panduan) (No.5)
**Files:**
- Create: `database/migrations/2026_10_28_000003_create_recruitment_guides_table.php`
- Create: `app/Models/RecruitmentGuide.php`
- Create: `app/Http/Controllers/Admin/AdminGuideController.php`
- Modify: `app/Http/Controllers/Public/GuideController.php`
- Create: `resources/views/admin/guides/index.blade.php`
- Modify: `resources/views/public/guides/flow.blade.php`
- Modify: `resources/views/public/guides/requirements.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/components/navigation/admin-sidebar.blade.php`
- Test: `tests/Feature/AdminRecruitmentGuideCmsTest.php`

- [ ] **Step 1: Buat migration dan model `RecruitmentGuide` (slug, title, content, type: 'flow'|'requirements', is_published)**
- [ ] **Step 2: Tulis seeder atau default migration content berdasarkan isi file guide lama**
- [ ] **Step 3: Buat `AdminGuideController` (CRUD panduan) dan rute admin**
- [ ] **Step 4: Hubungkan `Public/GuideController` ke database `RecruitmentGuide`**
- [ ] **Step 5: Perbarui view admin dan view publik panduan**
- [ ] **Step 6: Jalankan test dan pastikan lulus**
- [ ] **Step 7: Commit**

---

### Task 6: Timeline Seleksi, Template Tahapan Seleksi & Switch Buka/Tutup Periode (No.1)
**Files:**
- Create: `database/migrations/2026_10_28_000004_create_selection_stage_templates_table.php`
- Create: `app/Models/SelectionStageTemplate.php`
- Modify: `app/Http/Controllers/Admin/AdminTimelineController.php`
- Modify: `resources/views/admin/timelines/index.blade.php`
- Modify: `app/Http/Controllers/Admin/AdminVacancyController.php`
- Modify: `resources/views/admin/vacancies/create.blade.php`
- Modify: `resources/views/admin/vacancies/edit.blade.php`
- Modify: `resources/views/portal/applications/documents.blade.php`
- Test: `tests/Feature/SelectionTimelineAndTemplateTest.php`

- [ ] **Step 1: Buat migration dan model `SelectionStageTemplate` untuk menyimpan template tahapan seleksi seragam (nama tahapan, urutan, estimasi durasi/tanggal)**
- [ ] **Step 2: Seed default template sesuai gambar alur tahapan seleksi RSI Fatimah (Foto 2: 1. Pendaftaran & Seleksi Adm, 2. Tes Tulis, Skill, Wawancara, 3. Psikologi & Kesehatan, 4. Masa Orientasi Magang)**
- [ ] **Step 3: Ubah halaman `admin/timelines/index.blade.php` agar:**
  - Mengelola template tahapan seleksi global
  - Mengganti checkbox status aktif periode dengan toggle switch buka/tutup cepat
- [ ] **Step 4: Tambahkan endpoint switch cepat status periode (toggle open/close)**
- [ ] **Step 5: Tambahkan tombol "Terapkan Template Timeline" di form pembuatan/edit lowongan agar admin bisa langsung mengaplikasikan template**
- [ ] **Step 6: Tambahkan validasi & visual indicator batas pengumpulan berkas di portal pelamar**
- [ ] **Step 7: Jalankan test dan pastikan lulus**
- [ ] **Step 8: Commit**
