# Pemilihan RT/RW - Sistem Voting (PHP + MySQL)

Langkah cepat untuk menjalankan sistem ini di mesin lokal (Windows):

1. Import database

   - Jalankan MySQL/MariaDB dan import file SQL:

```bash
mysql -u root -p < "database/pemilihan_RTRW.sql"
```

2. Konfigurasi koneksi database

   - Buka file `backend/db.php` dan sesuaikan variabel `$DB_HOST`, `$DB_NAME`, `$DB_USER`, `$DB_PASS` jika perlu.

3. Menjalankan server PHP built-in (untuk development)

```bash
cd "c:\proyek kel 7"
php -S localhost:8000
```

4. Akses aplikasi

   - Voting (frontend): http://localhost:8000/index.html
   - Hasil (admin): http://localhost:8000/admin.html

Catatan keamanan:

- Sistem ini dibuat sebagai contoh. Untuk produksi, tambahkan autentikasi admin, validasi input lebih ketat, proteksi CSRF, dan HTTPS.
