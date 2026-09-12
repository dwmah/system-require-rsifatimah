RANCANGAN SISTEM INFORMASI REKRUTMEN KARYAWAN

RSI Fatimah Banyuwangi — Dokumen Spesifikasi Kebutuhan Sistem & Alur Kerja
Target Pengguna	Pelamar Kerja (Eksternal) & Tim HRD/SDI (Super User / Admin)
Tim Pengembang / PIC	Tim IT RSI Fatimah (Konsultasi: Mas Wisnu & Mas Bima)
Status Dokumen	Draft Rancangan Awal (Untuk Penyusunan Flowchart & Validasi)

1. Latar Belakang & Tujuan
Sistem ini dirancang untuk memodernisasi dan mendigitalisasi proses penerimaan tenaga kerja di RSI Fatimah Banyuwangi. Sistem rekrutmen ini akan diintegrasikan dengan portal web resmi rumah sakit, memungkinkan pelamar mengirimkan dokumen secara mandiri, serta memfasilitasi tim SDI (Sumber Daya Insani / HRD) dalam menyeleksi, memvalidasi berkas, dan mempublikasikan status kelulusan peserta secara terstruktur sesuai standar regulasi dan akreditasi rumah sakit (KARS/Kemenkes).
2. Konsep Desain & Antarmuka (UI/UX)
• Identitas Visual / Warna Tema: Didominasi warna Biru Khas RSI Fatimah sebagai warna identitas korporat rumah sakit pada komponen navbar, header, tombol utama, dan indikator status.
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

