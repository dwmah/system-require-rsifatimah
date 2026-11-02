# Laporan Eksekusi Task 2: Siklus Akun & Pengarsipan Pelamar Tidak Lolos (Soft Delete)

## Ringkasan Perubahan
Mekanisme siklus hidup akun pelamar yang tidak lolos seleksi sebelumnya melakukan hard delete dan menghapus berkas dokumen fisik dari server. Sesuai kebutuhan rumah sakit untuk mempertahankan berkas CV dan Portofolio sebagai talent pool, akun pelamar tidak lolos kini dialihkan ke mekanisme **Soft Delete**.

## Detail Implementasi

1. **Database Migration**
   - File: `database/migrations/2026_10_28_000001_add_soft_deletes_to_users_table.php`
   - Menambahkan kolom `deleted_at` ke tabel `users` dengan `Schema::table('users', fn($table) => $table->softDeletes())`.
   - Migration telah dieksekusi dengan sukses (`php artisan migrate`).

2. **Model**
   - File: `app/Models/User.php`:
     - Ditambahkan trait `Illuminate\Database\Eloquent\SoftDeletes`.
   - File: `app/Models/JobApplication.php` & `app/Models/ApplicantProfile.php`:
     - Menambahkan relasi `->withTrashed()` pada `belongsTo(User::class)` agar data lamaran, dokumen, dan profil tetap terhubung secara aman saat akun pelamar berstatus soft-deleted.

3. **Service Layer (`UserLifecycleService`)**
   - File: `app/Services/Lifecycle/UserLifecycleService.php`:
     - Refaktor `cleanupRejectedApplicant(User $user)`: File fisik dokumen di storage privat dan profil publik **tidak lagi dihapus**, melainkan dipertahankan secara utuh. Memanggil `$user->delete()` untuk soft-delete.
     - Penyesuaian `cleanupAllSeenRejectedApplicants()`: Hanya memproses akun pelamar yang belum di-soft-delete (`whereNull('deleted_at')`).
     - Penambahan method `isApplicantArchived(User $user): bool` untuk mengecek status soft delete pelamar.
     - Penambahan method `getArchivedTalentPoolQuery(): Builder` untuk kueri arsip talent pool pelamar tidak lolos yang akunnya telah di-soft-delete beserta dokumennya.

4. **Controller & Route & View**
   - File: `routes/web.php`:
     - Menambahkan `->withTrashed()` pada route `admin.lifecycle.cleanup`.
   - File: `app/Http/Controllers/Admin/AdminLifecycleController.php`:
     - Menyesuaikan kueri tab pelamar tidak lolos untuk mengikutsertakan soft deleted user dengan `withTrashed()`.
     - Menambahkan opsi filter `account_status=archived` (Diarsipkan / Soft Deleted).
     - Menyesuaikan notifikasi konfirmasi tindakan pengarsipan.
   - File: `resources/views/admin/lifecycle/index.blade.php`:
     - Memperbarui kartu kebijakan siklus hidup: menjelaskan kebijakan baru pengarsipan berkas CV/portofolio via soft delete.
     - Menambahkan kolom dan badge "Akun Diarsipkan / Soft Deleted" pada baris kandidat yang telah di-soft-delete.
     - Menyediakan tombol "Lihat Berkas" pada setiap baris pelamar tidak lolos agar admin tetap dapat meninjau dan mengunduh CV/dokumen pelamar kapan saja.
     - Tombol "Arsipkan Akun" otomatis hilang / tidak ditampilkan jika akun sudah berada dalam status soft-deleted.

5. **Pengujian (Automated Tests)**
   - File: `tests/Feature/UserLifecycleSoftDeleteTest.php` (5 tests, 32 assertions, 100% Passed):
     - `test_cleanup_performs_soft_delete_and_preserves_physical_files`
     - `test_admin_can_trigger_soft_delete_cleanup_via_route`
     - `test_admin_can_view_soft_deleted_applicant_in_lifecycle_and_view_documents`
     - `test_batch_cleanup_all_seen_soft_deletes_confirmed_applicants_and_keeps_files`
     - `test_talent_pool_query_and_is_archived_helper`
   - Seluruh test terkait (`AdminLifecycleExcelExportTest`, `AdminDocumentAutoApprovalAndBiodataTabTest`) tetap berjalan lulus tanpa regresi.

6. **Code Style**
   - Telah dijalankan `vendor/bin/pint --dirty --format agent` dengan hasil bersih.

## Status
DONE
