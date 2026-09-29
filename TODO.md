RANCANGAN SISTEM INFORMASI REKRUTMEN KARYAWAN

RSI Fatimah Banyuwangi — Dokumen Spesifikasi Kebutuhan Sistem & Alur Kerja
Target Pengguna	Pelamar Kerja (Eksternal) & Tim HRD/SDI (Super User / Admin)
Tim Pengembang / PIC	Tim IT RSI Fatimah (Konsultasi: Mas Wisnu & Mas Bima)
Status Dokumen	Draft Rancangan Awal (Untuk Penyusunan Flowchart & Validasi)

1. Latar Belakang & Tujuan
Sistem ini dirancang untuk memodernisasi dan mendigitalisasi proses penerimaan tenaga kerja di RSI Fatimah Banyuwangi. Sistem rekrutmen ini akan diintegrasikan dengan portal web resmi rumah sakit, memungkinkan pelamar mengirimkan dokumen secara mandiri, serta memfasilitasi tim SDI (Sumber Daya Insani / HRD) dalam menyeleksi, memvalidasi berkas, dan mempublikasikan status kelulusan peserta secara terstruktur sesuai standar regulasi dan akreditasi rumah sakit (KARS/Kemenkes).
2. Konsep Desain & Antarmuka (UI/UX)
• Identitas Visual / Warna Tema: Didominasi warna Biru Khas RSI Fatimah sebagai warna identitas korporat rumah sakit pada komponen navbar, header, tombol utama, dan indikator status (Dengan font khas dari fatimah sendiri yaitu Tahoma dengan ukuran yang agak besar untuk memudahkan pengeliahtan pada para ADMIN dan USER).
• Integrasi Menu Website: Menambahkan menu baru yaitu 'Rekrutmen' pada website utama (berdampingan dengan menu Diklat/Profil). Menu Rekrutmen memiliki dua submenu utama:
a. Informasi Pendaftaran: Halaman terbuka untuk publik berisi jadwal rekrutmen, formasi lowongan yang dibuka, kualifikasi/syarat umum, serta dashboard timeline seleksi dan statistik (jumlah kebutuhan vs pelamar).
b. Pendaftaran Pelamar: Pintu gerbang (login page & registrasi akun) khusus pelamar untuk mengakses dashboard mandiri, mengunggah berkas, dan memantau status seleksi.
3. Manajemen Hak Akses (User Roles)
A. Pelamar (Job Seeker / Public User)
Hak akses pelamar mencakup fungsionalitas berikut:
• Membuat akun pendaftaran baru selama periode pendaftaran masih dibuka.
• Melakukan login ke portal mandiri menggunakan akun yang telah dibuat.
• Memilih jenis formasi tenaga yang dilamar (misal: Perawat, Bidan, Dokter Umum, Apoteker, dll.).
• Mengunggah berkas persyaratan wajib dan opsional sesuai kualifikasi yang ditentukan.
• Memantau perkembangan tahapan seleksi secara visual melalui timeline tracker.
• Menerima pengumuman kelulusan (Lolos / Tidak Lolos beserta catatan alasan penolakan jika berkas tidak lengkap).
B. Super User / Admin HRD (Tim SDI)
Hak akses pengelola sistem mencakup fungsionalitas berikut:
• Mengatur dan menentukan batas waktu (timeline) seleksi berbasis kalender (tanggal awal, batas upload, verifikasi, hingga pengumuman).
• Melihat daftar seluruh pelamar terkelompok berdasarkan formasi yang dilamar.
• Memeriksa kelengkapan berkas lamaran peserta secara online.
• Memberikan status validasi berkas: Setujui (Approve) atau Tolak (Reject).
• Menginput catatan/alasan penolakan (misal: 'Ijazah belum dilegalisir' atau 'STR kedaluwarsa') bila berkas tidak memenuhi syarat.
• Kebijakan Siklus & Penghapusan Akun Pengguna (User Lifecycle Management):
  - Pelamar Lolos: Akun dinonaktifkan (disable/deactivate status) oleh Admin SDI tanpa menghapus datanya, sehingga seluruh berkas, riwayat kualifikasi, dan biodata pelamar tetap tersimpan aman sebagai arsip data kepegawaian/karyawan baru.
  - Pelamar Tidak Lolos: Akun dan berkas pelamar akan terhapus otomatis dari sistem segera setelah pelamar membuka pengumuman dan mengetahui bahwa dirinya tidak lolos (atau dapat dipicu hapus permanen/hard delete oleh sistem), menjaga ruang penyimpanan database tetap efisien dan melindungi privasi.
4. Skema & Spesifikasi Berkas Unggahan
Pada halaman form upload berkas, pelamar wajib melengkapi dokumen berikut sebelum dapat menyimpan (submit) data:
No	Nama Dokumen	Sifat	Keterangan Validasi
1	Surat Lamaran Kerja	Wajib (*)	Ditujukan ke Direktur RSI Fatimah Banyuwangi
2	Ijazah Terakhir	Wajib (*)	Scan ijazah legalisir asli sesuai kualifikasi
3	Sertifikat Keahlian / STR	Wajib (*)	Wajib bagi formasi nakes (STR aktif / sertifikat kompetensi)
4	SKCK (Kepolisian)	Opsional	Dapat disusulkan pada tahap berikutnya jika belum tersedia
5	Rekomendasi Profesi / Ormas	Opsional	Rekomendasi organisasi profesi atau persyarikatan (Muhammadiyah)
6	Dokumen Pendukung Lain	Opsional	MCU, sertifikat seminar/pelatihan tambahan, dll.
5. Logika Timeline Tracker & Otomasi Sistem
Sistem menerapkan kontrol waktu berbasis tanggal (Date-Driven Lock) yang mengatur hak akses fungsionalitas secara otomatis:
• Tahap 1: Pengumuman & Buka Pendaftaran: Tombol registrasi akun dan tombol upload berkas terbuka otomatis sesuai tanggal mulai.
• Tahap 2: Batas Akhir Submit Dokumen: Sistem menghitung mundur batas waktu (timer). Begitu melewati pukul 23:59 pada tanggal batas akhir, form input otomatis terkunci (disabled) dan pendaftaran ditutup.
• Tahap 3: Verifikasi Berkas (Tim SDI): Admin memverifikasi berkas pelamar. Status verifikasi diupdate menjadi 'Lengkap' atau 'Tidak Lengkap'.
• Tahap 4: Pengumuman Seleksi Administrasi: Dashboard pelamar menampilkan banner status kelulusan besar di bagian atas.
• Tahap 5: Seleksi Lanjutan: Peserta yang lolos melanjutkan ke tahap Tes Tulis, Wawancara, MCU, dan Pengumuman Akhir.
Skema Pewarnaan Indikator Tahapan (Timeline Bullets / Steps):
– Abu-abu (Inactive): Tahapan belum dimulai / menunggu jadwal tiba.
– Hijau (Active / In Progress): Tahapan yang sedang aktif berlangsung.
– Merah / Terkunci (Closed / Expired): Batas waktu unggah berkas telah berakhir (tombol submit disabled).
6. Alur Kerja Sistem (Flowchart Logic)
Berikut adalah alur logika yang menjadi acuan pembuatan diagram alir (Flowchart) rekrutmen:
[MULAI]
   │
   ▼
Cek Tanggal Pendaftaran vs Periode Aktif
   ├─► [Di Luar Jadwal] ──► Tombol Registrasi & Upload Dikunci (Disabled)
   └─► [Dalam Jadwal]
           │
           ▼
     Pelamar Registrasi Akun Baru
           │
           ▼
     Login ke Dashboard Pelamar
           │
           ▼
     Pilih Jenis Formasi Tenaga (Dokter / Perawat / Bidan / dll.)
           │
           ▼
     Unggah Dokumen (Validasi Dokumen Wajib: Surat, Ijazah, STR)
           │
           ▼
     Klik [Simpan / Final Submit]
           │
           ▼
     Admin SDI: Verifikasi Kelengkapan Dokumen
           │
           ├─► [Lengkap / Disetujui]
           │         │
           │         ▼
           │   Dashboard Pelamar: Banner Hijau "Anda Lolos Tahap Administrasi"
           │   Kirim Notifikasi Tembusan via Email
           │   Pelamar Berhak Mengikuti Tahap Tes / MCU Berikutnya
           │
           └─► [Tidak Lengkap / Ditolak]
                     │
                     ▼
               Admin Mengisi Catatan Alasan Penolakan
               Dashboard Pelamar: Banner Merah "Belum Lolos" + Rincian Alasan
   			│
   			▼
[SELESAI / AKHIR SELEKSI]
   │
   ▼
Manajemen Akun Pasca-Seleksi:
  ├─► [Pelamar Lolos] ──────► Admin Menonaktifkan Akun (Data & Berkas Dipertahankan untuk Arsip Kepegawaian)
  └─► [Pelamar Tidak Lolos] ─► Akun & Berkas Terhapus Otomatis Setelah Pelamar Mengetahui Status Tidak Lolos

7. Rencana Infrastruktur & Koordinasi Teknis
• Lingkungan Pengembangan (Development): Sistem dirancang dan diuji coba terlebih dahulu di lingkungan server lokal (localhost).
• Lingkungan Produksi (Production / Cloud): Disarankan menggunakan Cloud Hosting dengan kapasitas penyimpanan yang memadai agar portal rekrutmen dapat diakses secara stabil oleh banyak pelamar secara bersamaan.
• Pengelolaan Database & File Storage: Penyimpanan dokumen PDF/gambar pelamar disiapkan dengan struktur folder yang rapi dan terenkripsi. Akun pelamar dapat dibersihkan/dihapus secara berkala pasca-rekrutmen.
• Koordinasi Tim: Penyesuaian basis kode (source code) dan integrasi dengan SIMRS/Web utama dikoordinasikan langsung dengan Mas Wisnu dan Mas Bima.
 
8. Diagram Alir Detail (Swimlane Flowchart)
Diagram alir berikut menggunakan model Swimlane (tiga jalur peran: Pelamar, Sistem Otomatis, dan Tim SDI) untuk menggambarkan pembagian tanggung jawab dan perpindahan data secara transparan:
 
Gambar 1: Alur Kerja Sistem Informasi Rekrutmen Karyawan RSI Fatimah Banyuwangi (Eraser.io Flowchart)

9. Panduan Penjelasan Flowchart kepada Pimpinan (Executive Briefing)
Saat mempresentasikan rancangan alur kerja ini kepada atasan / pimpinan manajemen, gunakan 5 poin kunci penjelasan terstruktur berikut:
1. Kendali Otomatis Berbasis Waktu (Date-Driven Lock)
Jelaskan bahwa sistem ini tidak lagi memerlukan penutupan manual oleh staf. Tanggal pendaftaran, batas akhir unggah berkas, hingga masa verifikasi dikunci otomatis oleh sistem server sesuai kalender seleksi. Begitu batas waktu habis, tombol submit otomatis dinonaktifkan (disabled), menjamin kepatuhan jadwal sesuai standar akreditasi KARS.
2. Portal Akun Mandiri bagi Pelamar (Self-Service Dashboard)
Tekankan bahwa pelamar memiliki akun login pribadi untuk memantau status berkasnya secara transparan kapan saja tanpa perlu menelepon pihak rumah sakit. Ini mengurangi beban administratif tim SDI secara signifikan.
3. Validasi Berkas Ketat & Transparansi Alasan Penolakan
Sistem menyaring kelengkapan berkas wajib (Surat Lamaran, Ijazah, STR). Jika berkas tidak memenuhi syarat, Admin SDI dapat langsung menginput catatan penolakan yang akan terbaca di akun pelamar (misalnya: 'STR telah habis masa berlaku' atau 'Ijazah belum terlegalisir'). Hal ini mencegah komplain dan meningkatkan akuntabilitas proses seleksi.
4. Notifikasi Ganda (Dual-Channel Notification)
Selain banner status visual (Hijau untuk lolos, Merah untuk tidak lolos) pada dashboard web, sistem juga mengirimkan tembusan notifikasi kelulusan dan jadwal tes langsung ke alamat email masing-masing pelamar.
5. Kebijakan Retensi & Deaktivasi Akun Terpisah (User Lifecycle Management)
Jelaskan kepada pimpinan bahwa sistem menerapkan aturan retensi data yang selektif dan rapi:
• Bagi Pelamar yang Lolos: Akun dinonaktifkan (deactivated) sehingga tidak bisa login kembali ke portal seleksi, tetapi seluruh data profil dan berkas dokumennya dipertahankan utuh di database MySQL sebagai arsip calon karyawan baru.
• Bagi Pelamar yang Tidak Lolos: Akun dan file berkasnya langsung dihapus otomatis setelah yang bersangkutan mengakses dan mengetahui pengumuman status penolakannya. Hal ini menjaga kebersihan server, menghemat kuota hosting, serta menjaga privasi dokumen pelamar yang gugur.
10. Catatan Teknis & Spesifikasi Teknologi (Tech Stack)
Untuk memastikan pengembangan berjalan terstandarisasi, efisien, dan mudah dirawat (maintainable), proyek pengembangan Sistem Rekrutmen RSI Fatimah Banyuwangi ini disepakati menggunakan arsitektur teknologi berikut:
• Framework Backend: Laravel (PHP): Menggunakan framework Laravel sebagai fondasi utama aplikasi. Laravel dipilih karena memiliki ekosistem keamanan yang kuat (proteksi CSRF, enkripsi password, dan SQL injection prevention), sistem autentikasi pengguna yang matang, manajemen basis data (Eloquent ORM) yang terstruktur, serta kemudahan dalam integrasi antrean pengiriman email notifikasi.
• Framework Frontend & UI: Bootstrap: Antarmuka pengguna (User Interface) dibangun menggunakan Bootstrap. Hal ini menjamin tampilan website sepenuhnya responsif (ramah diakses melalui smartphone, tablet, maupun desktop PC), memudahkan penerapan komponen formulir upload, timeline tracker, modal popup, serta konsistensi warna tema Biru RSI Fatimah.
• Basis Data & Manajemen Penyimpanan (MySQL): Sistem menggunakan MySQL (Relational Database Management System) sebagai basis data utama yang dikelola melalui Eloquent ORM Laravel. MySQL dipilih karena performanya yang stabil, sangat kompatibel dengan server web dan hosting RSI Fatimah, serta mudah dipelihara oleh tim IT internal. Penyimpanan berkas PDF fisik (lamaran, ijazah, STR) dikelola melalui direktori Storage Link Laravel, sedangkan tabel MySQL menyimpan data akun, relasi formasi, riwayat status, dan log verifikasi berkas.
• Kesesuaian dengan Tim IT Internal: Pemilihan stack Laravel + Bootstrap memudahkan koordinasi teknis dan kesinambungan pemeliharaan bersama Mas Bima dan Mas Wisnu, mengingat kedua framework ini merupakan standar umum pengembangan web di lingkungan RSI Fatimah.

11. Rancangan yang Sudah Diimplementasikan (Status Pengembangan per 28 September 2026)

Berikut adalah daftar rancangan dan fitur yang sudah berhasil diimplementasikan dalam sistem:

A. Modul Autentikasi & Manajemen Akun
   • Halaman Registrasi Pelamar Baru (/daftar) — dengan auto-link ke formasi lowongan yang dipilih
   • Halaman Login (/masuk) — mendukung login dengan email, dilengkapi toggle show/hide password
   • Fitur Lupa Password (/lupa-password) — kirim link reset via email
   • Reset Password (/reset-password/{token}) — form reset aman dengan token
   • Rate Limiting pada registrasi (throttle:registration) dan password reset (throttle:password-reset-link)
   • Role-based Middleware: middleware `applicant` untuk pelamar, `admin` untuk Tim SDI
   • Login tetap tersedia meskipun periode rekrutmen publik ditutup

B. Portal Publik (Halaman Terbuka untuk Umum)
   • Halaman Utama / Landing Page — listing seluruh lowongan aktif dengan statistik pelamar
   • Detail Lowongan (/lowongan/{id}) — kualifikasi, persyaratan dokumen, timeline seleksi
   • Panduan Alur Pendaftaran (/panduan/alur-pendaftaran) — step-by-step guide visual
   • Panduan Persyaratan Umum (/panduan/persyaratan) — daftar berkas & ketentuan
   • Halaman Rekrutmen Ditutup (/rekrutmen-ditutup) — tampil otomatis saat periode berakhir
   • Middleware `recruitment.open` — mengunci akses publik saat tidak ada periode aktif

C. Dashboard & Profil Pelamar (Self-Service Portal)
   • Dashboard Beranda (/portal) — ringkasan status lamaran, progres tahapan, notifikasi
   • Profil Lengkap (/portal/profil) — biodata pribadi, alamat KTP, alamat domisili, kontak
   • Upload Foto Profil — dengan validasi ukuran & tipe file, replace/delete foto lama
   • CRUD Riwayat Pendidikan — institusi, program studi, tahun lulus, IPK, akreditasi
   • CRUD Lisensi / STR — nomor STR, masa berlaku, status aktif
   • CRUD Sertifikasi — nama sertifikat, penerbit, tanggal
   • CRUD Pengalaman Kerja — posisi, perusahaan, durasi kerja
   • Profile Completeness Service — tracking persentase kelengkapan profil pelamar

D. Alur Lamaran Pekerjaan (Workflow Lengkap)
   • Pilih Formasi Lowongan — dropdown formasi aktif sesuai periode rekrutmen
   • Draft Lamaran — inisialisasi lamaran dengan status Draft, belum bisa disubmit
   • Upload Berkas Persyaratan — upload per jenis dokumen (wajib/opsional), preview & download
   • Review Sebelum Submit (/portal/lamaran/{id}/review) — rangkuman final seluruh data & berkas
   • Final Submit — validasi dokumen wajib, lock lamaran, kirim notifikasi dual-channel
   • Auto-Lock setelah Submit — form upload otomatis disabled, data tidak bisa diubah
   • Pembatalan Draft Lamaran — batalkan draft + hapus akun sesuai kebijakan lifecycle
   • Kebijakan 1 Formasi per Periode — setiap pelamar hanya boleh melamar 1 formasi per periode
   • Application Snapshot — snapshot data pada saat submit untuk audit trail

E. Manajemen Berkas & Dokumen Persyaratan
   • Konfigurasi Persyaratan per Lowongan (VacancyDocumentRequirement)
   • Tipe Berkas: Wajib (*) dan Opsional sesuai tabel spesifikasi TODO.md Poin 4
   • Dukungan: Surat Lamaran, Ijazah, STR/Sertifikat Keahlian, SKCK, Rekomendasi Profesi, Dokumen Pendukung
   • Validasi file: tipe (PDF/gambar), ukuran maksimal, format
   • Preview dokumen dalam browser & download file asli
   • Admin: Download ZIP seluruh berkas per pelamar

F. Panel Admin SDI — Verifikasi Berkas & Manajemen Lamaran
   • Dashboard Admin (/admin/dashboard) — statistik rekrutmen, grafik pelamar per formasi
   • Daftar Seluruh Lamaran (/admin/applications) — filter berdasarkan formasi, status, dan pencarian
   • Detail Lamaran (/admin/applications/{id}) — panel biodata lengkap + berkas dokumen + timeline seleksi
   • Verifikasi per-Dokumen — status Valid / Invalid per item berkas + catatan verifikasi
   • Auto-Approve Administrasi — otomatis loloskan jika semua dokumen wajib berstatus Valid
   • Approve / Reject Keseluruhan — approve atau tolak administrasi secara bulk
   • Catatan Alasan Penolakan — wajib diisi saat reject (contoh: "STR kedaluwarsa", "Ijazah belum dilegalisir")
   • Download ZIP Semua Berkas — unduh seluruh dokumen pelamar dalam satu file ZIP

G. Timeline Tracker & Tahapan Seleksi Lanjutan
   • Tahapan Seleksi Kustom per Lowongan (VacancySelectionStage) — Admin menentukan urutan tahap
   • Riwayat Progres per Tahap (ApplicationStageHistory) — status, catatan, tanggal keputusan
   • Pewarnaan Indikator Tahapan:
     – Abu-abu (Inactive/Pending): Tahapan belum dimulai
     – Biru/Sky (In Progress): Sedang berjalan
     – Hijau (Passed): Tahapan lolos
     – Merah (Failed/Rejected): Tidak lolos
     – Amber (Draft): Draft lamaran
   • Auto-Advance ke Tahap Berikutnya — otomatis pindah ke tahap selanjutnya setelah lolos
   • Halaman Tracking Pelamar (/portal/lamaran/{id}/tracking) — visualisasi timeline step-by-step
   • Admin Update Tahapan — Tes Tulis, Wawancara, MCU, Pengumuman Akhir
   • Validasi Kuota Formasi — cegah melebihi kuota saat lolos tahap akhir
   • Reset / Rollback Tahapan — admin bisa mengembalikan status ke tahap sebelumnya

H. Notifikasi Ganda (Dual-Channel Notification)
   • In-App Notification — pusat notifikasi di portal pelamar (/portal/notifikasi)
   • Email Notification — 3 jenis email otomatis:
     1. ApplicationSubmittedMail — konfirmasi lamaran berhasil dikirim
     2. ApplicationVerificationResultMail — hasil verifikasi berkas (Lolos/Tidak Lolos)
     3. SelectionStageUpdatedMail — update tahapan seleksi & jadwal tes
   • Mark as Read / Mark All as Read — tandai notifikasi sudah dibaca
   • Template email profesional dengan branding RSI Fatimah

I. Manajemen Siklus Akun (User Lifecycle Management)
   • Deactivate Akun Pelamar Lolos — admin menonaktifkan akun, data tetap tersimpan sebagai arsip kepegawaian
   • Reactivate Akun — admin mengaktifkan kembali akun jika diperlukan
   • Acknowledge Announcement — pelamar tidak lolos wajib konfirmasi melihat pengumuman
   • Auto-Cleanup Pelamar Tidak Lolos — akun & berkas dihapus otomatis setelah pelamar konfirmasi
   • Batch Cleanup — hapus massal seluruh pelamar rejected yang sudah konfirmasi
   • Halaman Lifecycle Admin (/admin/lifecycle) — daftar pelamar lolos (arsip) & tidak lolos (antrean cleanup)
   • Export Biodata ke Excel (.xlsx) — unduh rekapitulasi data pelamar diterima dengan format profesional
   • Filter Lifecycle — pencarian, filter formasi, status lamaran, dan status akun

J. Manajemen Lowongan (Admin CRUD Lengkap)
   • Buat Lowongan Baru (/admin/jobs/create) — judul, deskripsi, kuota, persyaratan, tahapan seleksi
   • Edit Lowongan (/admin/jobs/{id}/edit) — ubah data lowongan yang sudah dibuat
   • Detail Lowongan (/admin/jobs/{id}) — lihat statistik pendaftar per formasi
   • Toggle Publish/Unpublish — buka/tutup lowongan untuk publik
   • Hapus Lowongan — hapus formasi yang tidak jadi dibuka
   • Kualifikasi Kustom per Lowongan (VacancyQualification) — konfigurasi syarat per formasi
   • Document Requirements Kustom — tentukan berkas wajib/opsional per lowongan
   • Tahapan Seleksi Kustom — tentukan urutan tahapan (Pendaftaran → Berkas → Tes → MCU → Final)

K. Manajemen Timeline & Kalender Seleksi
   • Halaman Timeline Admin (/admin/timelines) — kelola periode rekrutmen
   • CRUD Periode Rekrutmen (RecruitmentPeriod) — tanggal mulai, batas upload, verifikasi, pengumuman
   • Date-Driven Lock — form otomatis terkunci saat melewati batas waktu (sesuai TODO Poin 5 Tahap 2)
   • Status Periode: Mendatang, Aktif (Terbuka), Ditutup, Selesai

L. Identitas Visual & Desain UI/UX
   • Warna Tema Biru RSI Fatimah (#0179B5) — navbar, header, tombol utama, badge status
   • Font Tahoma — font utama sesuai permintaan (ukuran agak besar untuk kemudahan baca admin & user)
   • Layout Responsive — mendukung smartphone, tablet, dan desktop
   • Sidebar Navigasi Portal Pelamar — menu: Beranda, Lamaran, Profil, Notifikasi, Panduan
   • Navbar Admin Terpisah — layout admin dengan sidebar menu yang berbeda dari portal pelamar
   • Logo RSI Fatimah — ditampilkan pada navbar dan halaman autentikasi
   • Design System — komponen reusable (button, badge, card, modal, form) yang konsisten
   • Dark/Light Badge Status — indikator visual per status lamaran
   • Tailwind CSS — framework CSS utility-first untuk styling konsisten dan cepat (menggantikan Bootstrap)

M. Infrastruktur Teknis & Keamanan
   • Proteksi CSRF — seluruh form dilindungi token CSRF Laravel
   • Enkripsi Password — menggunakan bcrypt hashing
   • Middleware Auth & Role — pembatasan akses berdasarkan autentikasi dan peran
   • Rate Limiting — mencegah brute force pada login, registrasi, dan password reset
   • Storage Link — file berkas dikelola melalui Laravel Storage dengan path terenkripsi
   • SQLite untuk Testing — database ringan untuk automated test tanpa perlu MySQL
   • 223 Automated Tests (PHPUnit) — 20 file test feature untuk validasi alur bisnis
   • Vite Build System — asset bundling modern untuk CSS dan JavaScript

