# Laporan Eksekusi SDD Task 5: Pengelolaan Konten Panduan di Dashboard Admin (CMS Panduan)

## Status: DONE

### Ringkasan Pekerjaan:
Telah berhasil diimplementasikan fitur CMS / editor pengelolaan konten panduan di Dashboard Admin (SDI) RSI Fatimah Banyuwangi:
1. **Migration & Model:**
   - Migration `database/migrations/2026_10_28_000003_create_recruitment_guides_table.php` membuat tabel `recruitment_guides` dengan kolom `id`, `type`, `title`, `slug`, `subtitle`, `content` (JSON structured data), `sort_order`, `is_active`, dan `timestamps`.
   - Enum `app/Enums/RecruitmentGuideType.php` dengan case `FLOW = 'flow'` dan `REQUIREMENTS = 'requirements'`.
   - Model `app/Models/RecruitmentGuide.php` dengan fillable, cast array untuk content, boolean untuk is_active, serta scope `active()`, `flow()`, dan `requirements()`.
   - Seeder `database/seeders/RecruitmentGuideSeeder.php` yang mengisi konten default alur pendaftaran (8 langkah) dan persyaratan umum (6 poin ketentuan), serta didaftarkan di `DatabaseSeeder` dan dijalankan saat migration.

2. **Controller Admin & Routes:**
   - Dibuat controller `app/Http/Controllers/Admin/AdminGuideController.php` dengan method:
     - `index()`: menampilkan daftar panduan rekrutmen.
     - `edit()`: menampilkan formulir editor konten panduan.
     - `update()`: menyimpan pembaruan judul, subjudul, prioritas urutan, status aktif, dan daftar item/tahapan langkah panduan.
     - `store()`: menambahkan bagian panduan baru dengan slug generator otomatis.
     - `toggleActive()`: mengaktifkan atau menonaktifkan status publikasi panduan secara cepat.
   - Rute terdaftar di `routes/web.php` di bawah prefix `/admin` dengan middleware `auth` dan `admin`.
   - Ditambahkan menu navigasi "Kelola Panduan" pada sidebar admin (`resources/views/components/navigation/admin-sidebar.blade.php`) dan drawer mobile admin (`resources/views/components/layouts/admin.blade.php`).

3. **Views Admin:**
   - `resources/views/admin/guides/index.blade.php`: dashboard kelola panduan dengan kartu status alur pendaftaran & persyaratan umum, penghitung poin/tahapan, toggle status aktif, tombol pratinjau live portal publik, dan modal tambah panduan.
   - `resources/views/admin/guides/edit.blade.php`: formulir editor dinamis berbasis Tailwind RSI Fatimah dan Alpine.js repeater yang memungkinkan admin menambah, menyusun urutan (naik/turun), menghapus, dan mengedit nomor urut, judul, dan isi petunjuk setiap langkah panduan.

4. **Sinkronisasi Publik & Portal:**
   - Diperbarui `app/Http/Controllers/Public/GuideController.php`:
     - Method `flow()` mengambil record panduan aktif bertipe `flow` dari database `RecruitmentGuide`.
     - Method `requirements()` mengambil record panduan aktif bertipe `requirements` dari database `RecruitmentGuide`.
   - Diperbarui `resources/views/public/guides/flow.blade.php` & `resources/views/public/guides/requirements.blade.php` agar merender data dinamis dari database, dengan fallback default apabila data belum tersedia.

5. **Pengujian (Tests):**
   - Dibuat suite test komprehensif di `tests/Feature/AdminRecruitmentGuideCmsTest.php` mencakup:
     - Proteksi akses guest & non-admin (403/redirect).
     - Admin dapat melihat halaman kelola panduan dan menu sidebar.
     - Admin dapat melihat form edit panduan.
     - Admin dapat memperbarui konten panduan alur pendaftaran dan persyaratan umum.
     - Perubahan langsung tersinkronisasi dan tampil di halaman publik `/panduan/alur-pendaftaran` dan `/panduan/persyaratan`.
     - Admin dapat mengubah toggle status aktif/nonaktif panduan.
     - Admin dapat menambahkan bagian panduan baru.
   - Seluruh 10 unit/feature test lolos 100% (43 assertions).
   - Formatter Laravel Pint telah dijalankan bersih dengan opsi `--format agent`.
