# REMINDER.md — Project Development Context

## 1. Identitas dan Pembagian Developer

Project ini dikerjakan secara bersamaan oleh **2 developer** menggunakan AI coding agent Antigravity.

### Developer 1 — Admin Side

**Account:** `bheeluckzz@gmail.com`
**Developer:** `bheeluckzz`
**Role:** Full Stack Developer
**Primary Responsibility:** **Admin Side**

Developer ini bertanggung jawab terutama terhadap:

* Admin Dashboard
* Admin Authentication
* Admin Authorization
* Admin CRUD
* Admin Management
* Admin API
* Backend logic untuk kebutuhan Admin
* Database yang berkaitan dengan fitur Admin
* Integrasi frontend dan backend Admin

---

### Developer 2 — User Side

**Account:** `ahmadidawam48@gmail.com`
**Primary Responsibility:** **User Side**

Developer ini bertanggung jawab terutama terhadap:

* User Interface
* User Experience
* User Flow
* User Authentication
* User Features
* User API integration
* Frontend User
* Backend/User logic yang berkaitan dengan kebutuhan User

---

## 2. Aturan Kolaborasi

Project ini adalah **satu project yang dikerjakan oleh dua developer**, bukan dua project yang terpisah.

Karena itu, setiap perubahan harus mempertimbangkan pekerjaan developer lainnya.

Struktur tanggung jawab utama:

```text
bheeluckzz@gmail.com
        │
        └── Admin Side
             ├── Admin Frontend
             ├── Admin Backend
             ├── Admin API
             └── Admin Database Logic


ahmadidawam48@gmail.com
        │
        └── User Side
             ├── User Frontend
             ├── User Backend
             ├── User API
             └── User Flow
```

Namun batas tersebut **bukan batas mutlak**.

Jika suatu fitur membutuhkan perubahan lintas bagian, developer diperbolehkan melakukan perubahan pada bagian developer lainnya selama perubahan tersebut memang diperlukan.

---

## 3. Aturan Utama Antar Developer

### Jangan menghapus atau merombak pekerjaan developer lain tanpa alasan

Jika menemukan kode yang dibuat atau sedang digunakan oleh developer lain:

* Jangan langsung menghapus.
* Jangan mengganti architecture tanpa alasan.
* Jangan melakukan refactor besar hanya karena memiliki preferensi coding yang berbeda.
* Jangan mengubah behavior yang sudah berjalan tanpa mempertimbangkan dampaknya.
* Pahami terlebih dahulu tujuan kode tersebut.

Jika memang diperlukan perubahan, lakukan perubahan seminimal mungkin dan tetap pertahankan functionality yang sudah ada.

---

## 4. Shared Code

Beberapa bagian project kemungkinan digunakan oleh **Admin dan User sekaligus**.

Contohnya:

```text
Database
API
Authentication
Authorization
Models
Services
Utilities
Types / Interfaces
Shared Components
Validation
Configuration
```

Bagian tersebut harus dianggap sebagai **shared code**.

Jika mengubah shared code:

1. Periksa siapa saja yang menggunakan kode tersebut.
2. Periksa Admin Side.
3. Periksa User Side.
4. Periksa API yang terkait.
5. Periksa database.
6. Pastikan perubahan tidak menyebabkan breaking change.

---

## 5. Jika Developer Admin Mengubah User Side

Developer dengan akun:

`bheeluckzz@gmail.com`

fokus pada Admin Side.

Namun developer tersebut **BOLEH mengubah User Side** jika perubahan tersebut diperlukan untuk:

* Sinkronisasi dengan Admin.
* Perubahan API.
* Perubahan database.
* Perubahan authentication.
* Perubahan authorization.
* Perubahan data structure.
* Perubahan shared component.
* Perubahan business logic.
* Memperbaiki bug yang berdampak lintas sistem.
* Menjaga compatibility antara Admin dan User.

Contoh:

```text
Admin mengubah status recruitment
             ↓
       Backend / API
             ↓
     Database berubah
             ↓
User harus menampilkan status terbaru
```

Dalam kondisi seperti ini, perubahan pada User Side **diperbolehkan dan memang diperlukan**.

---

## 6. Jika Developer User Mengubah Admin Side

Hal yang sama berlaku untuk developer:

`ahmadidawam48@gmail.com`

Walaupun fokusnya adalah User Side, developer tersebut dapat menyentuh Admin Side apabila perubahan User membutuhkan penyesuaian pada:

* API
* Database
* Authentication
* Authorization
* Shared components
* Backend logic
* Shared services
* Data structure

Namun tetap hindari perubahan yang tidak berhubungan dengan kebutuhan fitur.

---

## 7. Shared API Rule

API merupakan salah satu bagian paling penting dalam project ini karena dapat digunakan oleh Admin maupun User.

Sebelum mengubah endpoint:

```text
GET
POST
PUT
PATCH
DELETE
```

periksa terlebih dahulu:

```text
Siapa yang menggunakan endpoint?
        ↓
Admin?
User?
Keduanya?
        ↓
Apakah response berubah?
        ↓
Apakah request berubah?
        ↓
Apakah validation berubah?
        ↓
Apakah authorization berubah?
```

Jangan melakukan perubahan API yang menyebabkan salah satu sisi mengalami error tanpa melakukan penyesuaian terhadap consumer API tersebut.

---

## 8. Shared Database Rule

Database digunakan bersama oleh Admin dan User.

Karena itu, perubahan database harus dipikirkan secara keseluruhan.

Sebelum mengubah:

* Table
* Column
* Foreign Key
* Relationship
* Enum
* Status
* Constraint
* Migration

periksa terlebih dahulu apakah struktur tersebut digunakan oleh Admin, User, atau keduanya.

### Contoh

Jika Admin memiliki fitur:

```text
Update Recruitment Status
```

dan User memiliki:

```text
View Recruitment Status
```

maka field:

```text
recruitment.status
```

merupakan **shared data**.

Jangan mengubah struktur atau value field tersebut hanya dari perspektif Admin.

---

## 9. Git / Version Control Awareness

Karena project dikerjakan oleh dua developer, selalu anggap bahwa:

> **Kode dapat berubah karena pekerjaan developer lain.**

Jika menemukan perubahan yang tidak dibuat dalam pekerjaan saat ini:

* Jangan langsung menganggapnya sebagai error.
* Jangan langsung menghapusnya.
* Jangan melakukan rollback sembarangan.
* Periksa konteks perubahan terlebih dahulu.
* Pertimbangkan bahwa perubahan tersebut mungkin berasal dari developer lainnya.

Jika menggunakan Git:

```text
Pull / Sync
    ↓
Review perubahan
    ↓
Implementasi
    ↓
Test
    ↓
Commit
    ↓
Push
```

Hindari melakukan force push atau tindakan Git yang berpotensi menghapus pekerjaan developer lain kecuali memang sudah disepakati.

---

## 10. Conflict Prevention

Untuk mengurangi konflik antar developer:

### Developer Admin

Prioritaskan file/folder yang berhubungan dengan:

```text
Admin
Dashboard
Admin Controllers
Admin Components
Admin Pages
Admin Services
Admin API
```

### Developer User

Prioritaskan:

```text
User
Public Pages
User Components
User Pages
User Services
User API Integration
```

### Shared

Untuk:

```text
Models
Database
Migrations
API
Authentication
Authorization
Shared Components
Shared Services
Types
Utilities
```

harus lebih berhati-hati karena kemungkinan digunakan oleh kedua sisi.

---

## 11. Jangan Membatasi Perubahan Berdasarkan Ownership Folder

Jangan menggunakan aturan:

> "Ini folder User, jadi developer Admin tidak boleh menyentuhnya."

Aturan yang benar:

> **Developer fokus pada bagiannya, tetapi boleh menyentuh bagian lain jika perubahan tersebut memang dibutuhkan oleh fitur yang sedang dikerjakan.**

Yang harus dihindari adalah perubahan yang tidak memiliki hubungan dengan pekerjaan.

---

## 12. Contoh Skenario

### Skenario 1 — Admin Recruitment

Developer Admin membuat:

```text
Admin Recruitment Management
```

Fitur tersebut membutuhkan:

```text
Admin Page
Admin Controller
API
Database
```

Tetapi ternyata User juga harus melihat status recruitment.

Maka boleh dilakukan:

```text
Admin
   ↓
Backend
   ↓
Database
   ↓
API
   ↓
User
```

Developer Admin boleh menyesuaikan User Side agar sistem tetap sinkron.

---

### Skenario 2 — User Membutuhkan API Baru

Developer User membutuhkan endpoint baru:

```text
GET /api/recruitments
```

Jika endpoint tersebut membutuhkan perubahan backend/shared API, perubahan tersebut boleh dilakukan meskipun backend tersebut sebelumnya banyak digunakan oleh Admin.

Tetapi sebelum mengubahnya, periksa dampaknya terhadap Admin.

---

## 13. Prinsip Sinkronisasi

Setiap fitur harus dipandang sebagai satu **end-to-end flow**, bukan sebagai bagian Admin atau User secara terpisah.

Gunakan pola:

```text
User / Admin
     ↓
Frontend
     ↓
API
     ↓
Backend
     ↓
Business Logic
     ↓
Database
     ↓
Backend
     ↓
API
     ↓
Frontend
```

Jika salah satu bagian berubah, periksa seluruh flow yang terdampak.

---

## 14. Prioritas Saat Terjadi Konflik

Jika terdapat perbedaan implementasi antara Admin dan User, prioritaskan:

1. Data consistency
2. API consistency
3. Database integrity
4. Authentication & authorization
5. Existing functionality
6. Maintainability
7. UI/UX
8. Developer preference

**Developer preference bukan alasan yang cukup untuk merusak implementasi yang sudah berjalan.**

---

## 15. AI Coding Agent Rules

AI yang bekerja di project ini harus memahami bahwa terdapat **dua developer**.

Sebelum melakukan perubahan besar:

```text
1. Pahami fitur yang sedang dikerjakan.
2. Tentukan apakah fitur berada di Admin atau User.
3. Cari shared code yang terdampak.
4. Periksa kemungkinan dampak terhadap developer lainnya.
5. Implementasikan perubahan seminimal mungkin.
6. Test Admin Side.
7. Test User Side jika terdampak.
8. Pastikan tidak ada functionality yang rusak.
```

Jangan menganggap kode yang tidak dibuat pada sesi saat ini sebagai kode yang boleh dihapus atau diganti.

---

# 16. Final Reminder

Project ini dikerjakan oleh:

**`bheeluckzz@gmail.com` → Admin Side**

**`ahmadidawam48@gmail.com` → User Side**

Keduanya tetap berada dalam **satu codebase dan satu sistem**.

### Ingat:

> **Fokus pekerjaan berbeda, tetapi project dan sistemnya sama.**

> **Admin dan User harus selalu sinkron.**

> **Shared code harus diperlakukan dengan hati-hati.**

> **Perubahan lintas bagian diperbolehkan jika memang diperlukan.**

> **Jangan menghapus pekerjaan developer lain hanya karena berbeda dengan implementasi yang sedang dibuat.**

> **Selalu periksa dampak perubahan terhadap seluruh sistem sebelum melakukan perubahan besar.**

> **Tujuan utama bukan hanya membuat fitur bekerja, tetapi membuat Admin Side dan User Side tetap kompatibel, stabil, dan terintegrasi.**

> **Jangan pernah sekali-kali untuk di hardcode untuk Admin side maupun User side**