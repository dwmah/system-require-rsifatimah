# Rencana Implementasi Full-Stack Sistem Rekrutmen RSI Fatimah Banyuwangi

Implementasi sistem informasi rekrutmen karyawan berbasis **Laravel 13 + MySQL + Tailwind CSS & Inter Font** yang mengacu pada requirement bisnis **`TODO.md`** dan sistem desain visual **`base_template.zip`**.

---

## User Review Required

> [!IMPORTANT]
> **Keputusan Arsitektur & Kebijakan Data:**
> 1. **Sistem Desain Visual:** Menggunakan Tailwind CSS v4 dengan token warna identitas RSI Fatimah (`#0179B5`, Inter typography, clean card layout, responsive sidebar) untuk menjamin 100% konsistensi antara sisi Pelamar dan Admin Panel.
> 2. **Keamanan Dokumen:** Berkas lamaran, ijazah, STR, dan dokumen pendukung disimpan di storage privat (`storage/app/private/recruitment_documents`) dan hanya dapat diakses melalui controller terautentikasi (Policy check: pelamar pemilik berkas atau Admin HRD/SDI).
> 3. **Kebijakan Siklus Akun (User Lifecycle):**
>    - **Pelamar Lolos:** Akun dinonaktifkan (`is_active = false`) agar tidak dapat login ke portal pendaftaran kembali, namun seluruh data profil dan dokumen dipertahankan utuh untuk arsip kepegawaian baru.
>    - **Pelamar Tidak Lolos:** Ketika pelamar telah membuka & mengonfirmasi pengumuman penolakan (`announcement_seen_at`), data akun dan dokumen dapat dibersihkan secara aman (atomic cleanup) atau melalui trigger admin / scheduled command.
> 4. **Akun Admin Default (Seeder):** Akan dibuatkan seeder untuk akun Super User / Admin HRD (`admin@rsifatimah.com` / `password123`) dengan role `admin`.

---

## Proposed Changes

### 1. Database & Migrasi

#### [NEW] `database/migrations/2026_09_12_000003_add_role_and_admin_fields_to_users_table.php`
- Menambahkan kolom `role` (`'applicant'`, `'admin'`) dengan default `'applicant'` dan index.
- Menambahkan helper method pada `User` model: `isAdmin()`, `isApplicant()`.

#### [NEW] `database/migrations/2026_09_12_000004_add_verification_and_rejection_to_job_applications_table.php`
- Menambahkan kolom:
  - `rejection_reason` (text, nullable)
  - `announcement_seen_at` (timestamp, nullable)
  - `verified_at` (timestamp, nullable)
  - `verified_by` (foreignId users, nullable)
  - `current_stage_id` (foreignId vacancy_selection_stages, nullable)

#### [NEW] `database/migrations/2026_09_12_000005_create_recruitment_notifications_table.php`
- Menyimpan notifikasi in-app untuk pelamar dan admin (title, message, type, url, read_at, timestamps).

---

### 2. Autentikasi, Middleware, & Authorization

#### [NEW] `app/Http/Middleware/EnsureUserIsAdmin.php`
- Memastikan user yang login memiliki role `admin`. Mencegah pelamar mengakses `/admin/*` (403 Forbidden).

#### [NEW] `app/Http/Middleware/EnsureUserIsApplicant.php`
- Memastikan user yang login memiliki role `applicant`.

#### [MODIFY] [bootstrap/app.php](file:///c:/Users/Admin/Downloads/system-require-rsifatimah/bootstrap/app.php)
- Mendaftarkan alias middleware `'admin'` dan `'applicant'`.

---

### 3. Layanan & Business Logic Backend

#### [NEW] `app/Services/Recruitment/ApplicationVerificationService.php`
- Logika verifikasi dokumen per item (Lengkap/Valid vs Tidak Lengkap/Invalid).
- Approve kelulusan berkas administrasi -> update status menjadi `passed_administration` / `in_selection`, trigger email & notifikasi.
- Reject berkas dengan catatan alasan penolakan wajib -> update status menjadi `rejected`, trigger email & notifikasi.
- Perpindahan tahapan seleksi (Tes Tulis, Wawancara, MCU, Final Lulus).

#### [NEW] `app/Services/Lifecycle/UserLifecycleService.php`
- Deaktivasi akun pelamar yang lolos (arsip tetap utuh).
- Pembersihan aman (atomic cleanup) data & berkas fisik pelamar yang tidak lolos setelah pengumuman dilihat.

#### [NEW] `app/Services/Notification/RecruitmentNotificationService.php`
- Mengirimkan dual-channel notification: database notification (in-app) + email mailable notification.

#### [NEW] `app/Mail/ApplicationSubmittedMail.php`
#### [NEW] `app/Mail/ApplicationVerificationResultMail.php`
#### [NEW] `app/Mail/SelectionStageUpdatedMail.php`
- Template email HTML bergaya profesional RSI Fatimah untuk registrasi, submit berkas, hasil verifikasi, dan undangan tes/interview.

---

### 4. Controller Pelamar (Applicant Portal)

#### [NEW] `app/Http/Controllers/Portal/ApplicantApplicationController.php`
- `index`: Menampilkan workspace lamaran aktif, ringkasan formasi, timeline status interaktif, banner status (Lolos / Belum Lolos + Alasan).
- `apply`: Memilih formasi lowongan kerja yang dibuka selama periode aktif.
- `storeApply`: Inisialisasi data lamaran pelamar pada formasi terkait.
- `submit`: Final submit berkas dengan validasi kelengkapan dokumen wajib (Surat Lamaran, Ijazah, STR nakes) dan penguncian form (Date-driven Lock).
- `acknowledgeAnnouncement`: Konfirmasi pelamar telah melihat pengumuman penolakan.

#### [NEW] `app/Http/Controllers/Portal/ApplicantDocumentController.php`
- `store`: Upload dokumen persyaratan dengan validasi tipe berkas (PDF/JPG/PNG, max 2MB-5MB), pembuatan nama file unik yang aman, dan penyimpanan privat.
- `destroy`: Hapus dokumen jika lamaran belum difinalisasi.
- `download`: Download / stream dokumen dengan proteksi hak akses pemilik atau admin.

#### [NEW] `app/Http/Controllers/Portal/ApplicantNotificationController.php`
- `index`: Halaman notifikasi pelamar.
- `markAsRead`: Tandai notifikasi telah dibaca.

---

### 5. Controller Admin (HRD / SDI Panel)

#### [NEW] `app/Http/Controllers/Admin/AdminDashboardController.php`
- Menampilkan metrik lengkap: total lowongan, lowongan aktif, lowongan ditutup, total pelamar, pelamar baru, menunggu verifikasi, lolos administrasi, tidak lolos, tahap tes, interview, diterima, serta log aktivitas verifikasi terbaru.

#### [NEW] `app/Http/Controllers/Admin/AdminVacancyController.php`
- CRUD Lowongan: List lowongan dengan search/filter, form tambah lowongan (posisi, departemen, kuota, kualifikasi, tahapan, persyaratan dokumen), detail lowongan & pelamar, edit lowongan, publish/unpublish/close.

#### [NEW] `app/Http/Controllers/Admin/AdminApplicationController.php`
- List seluruh lamaran pelamar dengan multi-filter (formasi, status verifikasi, tanggal daftar), search nama/email/nomor registrasi.
- **Halaman Verifikasi Utama (`/admin/applications/{id}`)**:
  - Biodata lengkap, pendidikan, lisensi/STR, sertifikat, dan pengalaman kerja.
  - Preview & download berkas dokumen pelamar.
  - Checklist validasi dokumen & verifikasi status berkas (Lolos / Tidak Lolos dengan catatan alasan penolakan wajib).
  - Update status tahapan seleksi (Tes Tulis, Wawancara, MCU, Lolos Akhir).

#### [NEW] `app/Http/Controllers/Admin/AdminTimelineController.php`
- Pengaturan periode rekrutmen dan jadwal tahapan seleksi terpadu.

#### [NEW] `app/Http/Controllers/Admin/AdminLifecycleController.php`
- Manajemen siklus akun: Arsip pelamar lolos & pembersihan data pelamar tidak lolos.

---

### 6. Frontend & Blade Views

#### Desain & Layout
- [NEW] `resources/views/components/layouts/admin.blade.php`: Layout admin panel dengan navbar `#0179B5`, user dropdown, alert messages, dan drawer mobile.
- [NEW] `resources/views/components/navigation/admin-sidebar.blade.php`: Navigasi sidebar admin (Dashboard, Lowongan, Pelamar, Lamaran, Timeline Seleksi, Lifecycle Akun, Notifikasi).
- [MODIFY] `resources/views/components/layouts/applicant.blade.php`: Penyesuaian layout pelamar agar 100% konsisten dengan desain sistem.

#### Halaman Admin Panel
- [NEW] `resources/views/admin/dashboard.blade.php`
- [NEW] `resources/views/admin/vacancies/index.blade.php`
- [NEW] `resources/views/admin/vacancies/create.blade.php`
- [NEW] `resources/views/admin/vacancies/show.blade.php`
- [NEW] `resources/views/admin/vacancies/edit.blade.php`
- [NEW] `resources/views/admin/applications/index.blade.php`
- [NEW] `resources/views/admin/applications/show.blade.php` (Verification Workspace)
- [NEW] `resources/views/admin/timelines/index.blade.php`
- [NEW] `resources/views/admin/lifecycle/index.blade.php`

#### Halaman Pelamar (Applicant Portal)
- [MODIFY] `resources/views/portal/home.blade.php`: Dashboard pelamar dinamis dengan timeline tracker, countdown batas submit, banner status lolos/tidak lolos, dan tombol aksi.
- [NEW] `resources/views/portal/applications/workspace.blade.php`: Workspace pemilihan formasi, upload dokumen berkas wajib & opsional, status berkas, dan tombol final submit.
- [NEW] `resources/views/portal/notifications/index.blade.php`: Halaman inbox notifikasi pelamar.

---

### 7. Seeder & Automasi

#### [NEW] `database/seeders/AdminUserSeeder.php`
- Seeder akun Tim SDI / Admin HRD RSI Fatimah (`admin@rsifatimah.com` / `password123`).

#### [NEW] `app/Console/Commands/RecruitmentCheckDeadlinesCommand.php`
- Command otomatis untuk mengunci pendaftaran jika melewati batas waktu (Date-driven Lock).

---

## Verification Plan

### Automated Tests & CLI Checks
1. Menjalankan seluruh migrasi dan seeder:
   ```sh
   php artisan migrate --force
   php artisan db:seed --class=AdminUserSeeder
   ```
2. Validasi route listing:
   ```sh
   php artisan route:list --path=admin
   php artisan route:list --path=portal
   ```
3. Menjalankan build frontend asset Vite:
   ```sh
   npm run build
   ```

### Manual Verification
1. **Flow Pelamar:**
   - Registrasi akun pelamar baru di `/daftar`.
   - Login pelamar di `/masuk`.
   - Buka Dashboard `/portal`, lihat lowongan aktif di `/lowongan`.
   - Melamar pada posisi lowongan, upload dokumen wajib (Surat Lamaran, Ijazah, STR).
   - Melakukan Final Submit -> status berubah menjadi `Menunggu Verifikasi`, form terkunci.
2. **Flow Admin HRD:**
   - Login dengan akun admin di `/masuk`.
   - Akses `/admin/dashboard` (melihat data statistik dan metrik rekrutmen).
   - Akses `/admin/applications` (melihat daftar lamaran yang masuk).
   - Masuk ke detail lamaran `/admin/applications/{id}` -> periksa dokumen, lakukan Approve (Lolos Administrasi) atau Reject dengan catatan alasan.
   - Uji perpindahan tahapan seleksi (Tes Tulis, Wawancara, MCU, Diterima).
3. **Flow Verifikasi Akun & Lifecycle:**
   - Pelamar membuka dashboard -> melihat banner hasil verifikasi (Hijau jika lolos / Merah jika tidak lolos beserta rincian alasan penolakan).
   - Uji coba pengujian proteksi otorisasi: Pelamar dilarang mengakses `/admin/*` (403 Forbidden).
