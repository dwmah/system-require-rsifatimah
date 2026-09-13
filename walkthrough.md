# Sistem Informasi Rekrutmen Karyawan RSI Fatimah Banyuwangi - Walkthrough

Implementasi sistem rekrutmen karyawan full-stack untuk **RSI Fatimah Banyuwangi** telah selesai secara menyeluruh, mencakup modul pelamar eksternal, panel administrasi Tim HRD/SDI, verifikasi berkas atomik, timeline seleksi dinamis, notifikasi dual-channel, hingga manajemen siklus hidup akun (*pass deactivation* & *rejected cleanup*).

---

## 1. Ringkasan Implementasi

### A. Database & Schema
- **Role User**: Menambahkan kolom `role` (`applicant`, `admin`) dengan default `applicant` dan indeks performa.
- **Verifikasi & Penolakan**: Menambahkan kolom `current_stage_id`, `rejection_reason`, `announcement_seen_at`, `verified_at`, `verified_by` pada tabel `job_applications`.
- **Notifikasi In-App**: Tabel `recruitment_notifications` untuk riwayat notifikasi pelamar.
- **Seeders**: Akun default admin SDI (`admin@rsifatimah.com` / `password123`) serta data formasi rekrutmen awal.

### B. Otentikasi & Otorisasi
- **Role-Based Routing**: Middleware `EnsureUserIsAdmin` (`admin`) dan `EnsureUserIsApplicant` (`applicant`).
- **Pemisahan Jalur Login**: Admin diarahkan langsung ke `/admin/dashboard`, pelamar diarahkan ke `/portal`.
- **Date-Driven Lock**: Akses unggah berkas, perubahan data lamaran, dan submit terkunci otomatis saat melewati batas waktu periode (`registration_end_at`).

### C. Portal Pelamar (Applicant Portal)
- **Dashboard Ringkasan**: Pengumuman kelulusan administrasi/tahapan dengan banner hijau/merah interaktif, progress kelengkapan profil, dan status stepper.
- **Workspace Lamaran (`/portal/lamaran`)**:
  - Unggah berkas dokumen dinamis sesuai persyaratan formasi lowongan (PDF/JPG, validasi ukuran & format).
  - Indikator status verifikasi per dokumen (*Valid*, *Tidak Valid*, *Menunggu Verifikasi*).
  - Tombol Submit Lamaran (aktif hanya jika semua dokumen wajib terunggah dan periode masih buka).
- **Pengumuman Interaktif & Konfirmasi**: Tombol konfirmasi pengumuman hasil seleksi untuk mencatat `announcement_seen_at`.
- **Pusat Notifikasi (`/portal/notifikasi`)**: Notifikasi real-time in-app yang tersinkronisasi.

### D. Panel SDI / HRD Admin (`/admin`)
- **Dashboard SDI (`/admin/dashboard`)**: Metrik ringkasan (total pelamar, belum diverifikasi, lolos berkas, formasi aktif).
- **Manajemen Formasi & Kuota (`/admin/vacancies`)**: CRUD formasi lowongan, kuota, persyaratan berkas dinamis, kualifikasi, dan tahapan seleksi.
- **Verifikasi Berkas & Pelamar (`/admin/applications`)**:
  - Filter berdasarkan lowongan dan status verifikasi.
  - Detail berkas pelamar dengan viewer/download aman.
  - Validasi individual berkas (*Valid* / *Tidak Valid*).
  - Aksi keputusan final: **Lolos Seleksi Administrasi** atau **Tolak Lamaran** (wajib menyertakan alasan penolakan).
- **Timeline & Tahapan Seleksi (`/admin/timelines`)**: Pengaturan progres pelamar dari satu tahapan seleksi ke tahapan berikutnya.
- **Manajemen Siklus Hidup Akun (`/admin/lifecycle`)**:
  - **Pelamar Lolos (Arsip)**: Penonaktifan akun (`is_active = false`) agar akses portal ditutup setelah penetapan status akhir.
  - **Pelamar Tidak Lolos (Pembersihan)**: Penghapusan atomik akun & file berkas tersimpan secara aman setelah pengumuman dibaca.

### E. Layanan Notifikasi & Console Commands
- **Dual-Channel Notification**: Email HTML bertema branding RSI Fatimah Banyuwangi (`#0179B5`) + In-app notification.
- **Artisan Commands**:
  - `php artisan recruitment:check-deadlines`: Otomatisasi penutupan periode & lowongan yang kedaluwarsa.
  - `php artisan recruitment:cleanup-rejected`: Pembersihan berkas pelamar yang ditolak setelah periode retensi.

---

## 2. Hasil Pengujian (Test Results)

Seluruh test suite Laravel & PHPUnit berjalan **100% Hijau (Passed)** tanpa error.

```text
Tests:    73 passed (313 assertions)
Duration: 6.64s
```

### Rincian Modul Test yang Lolos:
1. `RecruitmentFullWorkflowTest`: 6 tests, 29 assertions (Verifikasi end-to-end admin verification, locking, rejection reason, progress tahapan).
2. `ApplicantAuthenticationTest`: 15 tests (Login pelamar, login admin, proteksi akun nonaktif, reset password).
3. `ApplicantProfileTest`: 16 tests (Pendidikan, STR, sertifikasi, pengalaman, kalkulasi kelengkapan profil).
4. `PublicRecruitmentPortalTest`: 12 tests (Halaman lowongan publik, filter pencarian, pengalihan saat rekrutmen ditutup).
5. `RegisteredUserControllerTest`: 5 tests (Registrasi pelamar dengan nomor registrasi unik).

---

## 3. Akun Akses Default

| Peran | Email | Password | URL Masuk |
|---|---|---|---|
| **Admin SDI / HRD** | `admin@rsifatimah.com` | `password123` | `/masuk` &rarr; Redirect ke `/admin/dashboard` |
| **Pelamar Baru** | Registrasi mandiri | Sesuai input | `/daftar` / `/masuk` &rarr; Redirect ke `/portal` |
