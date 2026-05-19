## 🗳️ Sistem E-Voting RT/RW

Aplikasi e-voting untuk pemilihan RT/RW dengan fitur admin, calon, dan pemilih.

---

## 📋 Daftar Isi

1. [Fitur Utama](#fitur-utama)
2. [Struktur Sistem](#struktur-sistem)
3. [Setup Database](#setup-database)
4. [Cara Penggunaan](#cara-penggunaan)
5. [Akun Default](#akun-default)
6. [Teknologi](#teknologi)

---

## ✨ Fitur Utama

### 1. **Admin Dashboard**
- Login admin dengan username & password
- Membuat akun untuk warga pemilih
- Melihat daftar warga yang sudah terdaftar
- Melihat status voting masing-masing warga

### 2. **Voter (Pemilih)**
- Login dengan NIK & password (dibuat oleh admin)
- Melihat daftar calon RT/RW
- Melakukan voting untuk calon pilihan
- Hanya dapat melakukan voting 1 kali
- Setelah voting, tidak dapat voting lagi

### 3. **Candidate (Calon)**
- Login dengan ID calon & password
- Melihat profil calon
- Melihat perolehan suara real-time
- Dashboard auto-refresh setiap 5 detik

---

## 🏗️ Struktur Sistem

### Alur Sistem

```
SEBELUM PEMILIHAN:
1. Admin login → admin-login.html
2. Admin membuat akun warga via admin-dashboard.html
3. Admin memberikan NIK & password ke warga
4. Calon menerima ID & password dari admin

SAAT HARI H PEMILIHAN:
1. Warga login via index.html (masuk sebagai pemilih)
2. Warga memilih calon → voting direkam di database
3. Calon login via candidate-login.html
4. Calon bisa lihat suara real-time di candidate-dashboard.html
5. Admin bisa pantau progress via admin-dashboard.html
```

### File Structure

```
kelompok-7-main/
├── index.html                   (Home & Voter Login)
├── admin-login.html             (Admin Login)
├── admin-dashboard.html         (Admin Dashboard)
├── candidate-login.html         (Calon Login)
├── candidate-dashboard.html     (Calon Dashboard)
├── admin.html                   (Hasil Voting - Legacy)
├── backend/
│   ├── api.php                 (API Endpoints)
│   └── db.php                  (Database Config)
└── database/
    └── pemilihan_RTRW.sql      (Database Schema)
```

---

## 🗄️ Setup Database

### Langkah 1: Import Database

1. Buka phpMyAdmin
2. Klik "Import"
3. Pilih file `database/pemilihan_RTRW.sql`
4. Klik "Go"

### Langkah 2: Verifikasi Koneksi

Edit `backend/db.php` sesuai konfigurasi XAMPP Anda:

```php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'pemilihan_rtrw';
$DB_USER = 'root';
$DB_PASS = '';  // Default XAMPP adalah kosong
```

---

## 📖 Cara Penggunaan

### 1️⃣ ADMIN - Membuat Akun Warga

**Login Admin:**
1. Buka `http://localhost/kelompok-7-main/admin-login.html`
2. Username: `admin`
3. Password: `admin123`

**Membuat Akun Warga:**
1. Di Admin Dashboard, isi form "Buat Akun Warga"
2. NIK (wajib), Nama, Password, RT, RW
3. Klik "Buat Akun"
4. Warga akan menerima NIK & password dari admin

**Melihat Daftar Warga:**
- Tabel di bawah form menampilkan semua warga yang terdaftar
- Tampil status apakah sudah voting atau belum

---

### 2️⃣ PEMILIH (WARGA) - Melakukan Voting

**Login Pemilih:**
1. Buka `http://localhost/kelompok-7-main/`
2. Klik "Masuk sebagai Pemilih"
3. Masukkan NIK & password yang diberikan admin
4. Klik "Login"

**Melakukan Voting:**
1. Klik "Lihat Calon"
2. Lihat daftar calon dengan perolehan suara saat ini
3. Klik "Pilih" pada calon pilihan
4. Vote berhasil tercatat!
5. Anda tidak dapat voting lagi

---

### 3️⃣ CALON - Memantau Perolehan Suara

**Login Calon:**
1. Buka `http://localhost/kelompok-7-main/candidate-login.html`
2. Masukkan ID Calon (1, 2, atau 3 untuk data sample)
3. Password: `candidate123` (untuk data sample)
4. Klik "Login"

**Dashboard Calon:**
- Lihat nama calon, deskripsi, dan foto (jika ada)
- **TOTAL SUARA MASUK** - Update otomatis setiap 5 detik
- Progress bar menampilkan perolehan suara
- Tombol "Refresh" untuk refresh manual
- Tombol "Logout" untuk keluar

---

## 🔐 Akun Default

### Admin
```
Username: admin
Password: admin123
```

### Calon (Sample Data)
```
ID: 1  | Password: candidate123  | Nama: Calon Ketua RT 001
ID: 2  | Password: candidate123  | Nama: Calon Ketua RT 002
ID: 3  | Password: candidate123  | Nama: Calon Ketua RT 003
```

### Warga (Dibuat oleh Admin)
- Contoh: NIK: 3571060512900001, Password: (sesuai yang dibuat admin)

---

## 📊 Database Schema

### Tabel: `candidates`
```sql
- id (INT, Primary Key)
- name (VARCHAR)
- description (TEXT)
- password (VARCHAR) - Hashed password
- photo_url (VARCHAR)
- is_active (TINYINT) - 1 = aktif, 0 = tidak aktif
- created_at (TIMESTAMP)
```

### Tabel: `voters`
```sql
- id (INT, Primary Key)
- nik (VARCHAR, UNIQUE)
- name (VARCHAR)
- rt (VARCHAR)
- rw (VARCHAR)
- password (VARCHAR) - Hashed password
- has_voted (TINYINT) - 1 = sudah voting, 0 = belum
- voted_at (DATETIME)
- created_at (TIMESTAMP)
```

### Tabel: `votes`
```sql
- id (INT, Primary Key)
- voter_id (INT, Foreign Key)
- candidate_id (INT, Foreign Key)
- created_at (TIMESTAMP)
```

### Tabel: `admins`
```sql
- id (INT, Primary Key)
- username (VARCHAR, UNIQUE)
- password (VARCHAR) - Hashed password
- email (VARCHAR)
- created_at (TIMESTAMP)
```

---

## 🔧 API Endpoints

### Admin Endpoints
```
POST /backend/api.php?action=admin_login
  - Input: username, password
  - Output: success, admin_id

POST /backend/api.php?action=admin_create_voter
  - Input: nik, name, password, rt, rw
  - Output: success, voter_id

GET /backend/api.php?action=admin_get_voters
  - Output: Array of voters
```

### Voter Endpoints
```
POST /backend/api.php?action=voter_login
  - Input: nik, password
  - Output: success, voter_id, has_voted

POST /backend/api.php?action=submit_vote
  - Input: candidate_id
  - Output: success, message
```

### Candidate Endpoints
```
POST /backend/api.php?action=candidate_login
  - Input: candidate_id, password
  - Output: success, candidate_id

GET /backend/api.php?action=get_candidate_profile
  - Output: success, profile (name, description, votes, photo_url)
```

### Public Endpoints
```
GET /backend/api.php?action=list_candidates
  - Output: Array of candidates with votes

GET /backend/api.php?action=results
  - Output: Array of results sorted by votes DESC

GET /backend/api.php?action=logout
  - Output: success
```

---

## ⚠️ Catatan Penting

1. **Password Hashing**: Passwords disimpan dengan MD5 hash (untuk production gunakan bcrypt)
2. **Session Management**: Menggunakan PHP session (`$_SESSION`)
3. **Security**: Implementasi CSRF protection dan input validation untuk production
4. **Real-time Update**: Dashboard calon auto-refresh setiap 5 detik
5. **One Vote Per Person**: Sistem mencegah voting ganda dengan `has_voted` flag

---

## 🚀 Teknologi

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.x
- **Database**: MySQL/MariaDB
- **Server**: Apache (XAMPP)

---

## 📝 Lisensi

Proyek ini dibuat untuk keperluan akademik.

---

## 💡 Tips

- Gunakan password yang kuat saat membuat akun warga
- Admin dapat membuat akun warga sebelum hari H pemilihan
- Pastikan semua warga menerima NIK & password sebelum pemilihan dimulai
- Monitor progress voting di admin dashboard
- Calon dapat memantau suara real-time di dashboard mereka

---

**Selamat menggunakan sistem E-Voting RT/RW! 🎉**
