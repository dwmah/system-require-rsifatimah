# Talent Pool Document Focus & Force Delete Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menampilkan fokus berkas (CV, Portofolio, Dokumen) pada akun pelamar yang diarsipkan (soft delete) di tabel lifecycle, menambahkan fitur hapus permanen (force delete) akun & file fisik, serta menjadikan CV dan Portofolio sebagai persyaratan dokumen default di pemberkasan lowongan.

**Architecture:**
- Menambahkan metode `forceDeleteApplicant(User $user)` pada `UserLifecycleService` yang menghapus file fisik dokumen pelamar di storage disk dan memanggil `forceDelete()` pada User/Application.
- Menambahkan endpoint `DELETE /admin/lifecycle/{user}/force` pada `AdminLifecycleController` dan `routes/web.php`.
- Memperbarui `admin/lifecycle/index.blade.php`: untuk akun berstatus soft delete (archived), kolom menampilkan shortcut akses/preview berkas dokumen (CV & Portofolio), serta tombol aksi "Hapus Permanen".
- Memperbarui default dokumen pada `AdminVacancyController` (create/store) dan preset formulir create/edit vacancy agar CV dan Portofolio selalu masuk sebagai dokumen default.

**Tech Stack:** Laravel 11, Blade, Tailwind CSS, Alpine.js, PHPUnit.

---

### Task 1: Force Delete Service & Controller Backend

**Files:**
- Modify: `app/Services/Lifecycle/UserLifecycleService.php`
- Modify: `app/Http/Controllers/Admin/AdminLifecycleController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/UserLifecycleForceDeleteTest.php`

- [ ] **Step 1: Write feature test for force delete**
- [ ] **Step 2: Run test to verify it fails**
- [ ] **Step 3: Implement `forceDeleteApplicant` in `UserLifecycleService` and controller route**
- [ ] **Step 4: Run test to verify it passes**
- [ ] **Step 5: Commit changes**

---

### Task 2: Default CV & Portofolio di Persyaratan Lowongan

**Files:**
- Modify: `app/Http/Controllers/Admin/AdminVacancyController.php`
- Modify: `resources/views/admin/vacancies/create.blade.php`
- Modify: `resources/views/admin/vacancies/edit.blade.php`
- Test: `tests/Feature/DefaultVacancyDocumentsTest.php`

- [ ] **Step 1: Write test for vacancy default documents having CV and Portofolio**
- [ ] **Step 2: Implement default documents update in Controller & Blade views**
- [ ] **Step 3: Run test to verify it passes**
- [ ] **Step 4: Commit changes**

---

### Task 3: Tampilan Fokus Berkas & Tombol Hapus Permanen di Lifecycle View

**Files:**
- Modify: `resources/views/admin/lifecycle/index.blade.php`
- Test: `tests/Feature/UserLifecycleSoftDeleteTest.php`

- [ ] **Step 1: Update view with CV/Portofolio quick preview and Force Delete button for archived users**
- [ ] **Step 2: Run all lifecycle tests**
- [ ] **Step 3: Commit changes and push to origin**
