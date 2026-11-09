# 🚀 Panduan Instalasi dan Menjalankan PestaGo di XAMPP Lokal

Panduan lengkap langkah demi langkah untuk menjalankan website marketplace **PestaGo** di komputer lokal menggunakan **XAMPP (Apache & MySQL/MariaDB)**.

---

## 1. Prasyarat Sistem

Pastikan perangkat Anda sudah terinstal:
- **XAMPP** (dengan Apache dan MySQL / MariaDB aktif)
- **PHP** versi >= 8.2
- **Composer** (Dependency Manager untuk PHP)
- Browser modern (Google Chrome, Firefox, Safari, Edge)

---

## 2. Langkah Instalasi

### Langkah 1: Penempatan Folder Proyek
Pastikan folder proyek berada di direktori `htdocs` XAMPP:
```bash
# macOS
/Applications/XAMPP/xamppfiles/htdocs/pestagodemo/

# Windows
C:\xampp\htdocs\pestagodemo\
```

Struktur folder terbagi menjadi:
- `backend/` : Proyek Laravel lengkap (Controllers, Blade Views, Migrations, Models, Routes)
- `frontend/` : Mirroring aset frontend (CSS, JS, gambar, ikon)
- `docs/` : Dokumentasi arsitektur, database, dan panduan

---

### Langkah 2: Menyalakan Layanan XAMPP
1. Buka aplikasi **XAMPP Control Panel** / `manager-osx`.
2. Klik tombol **Start** pada modul **Apache Web Server**.
3. Klik tombol **Start** pada modul **MySQL Database**.

---

### Langkah 3: Membuat Database MySQL
Buka browser dan akses **phpMyAdmin**:
```
http://localhost/phpmyadmin/
```
1. Klik menu **Databases** (Basis data).
2. Masukkan nama database: `pestago`.
3. Pilih collation: `utf8mb4_unicode_ci`.
4. Klik **Create** (Buat).

*Atau melalui terminal/command prompt:*
```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "CREATE DATABASE IF NOT EXISTS pestago CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

### Langkah 4: Konfigurasi File Lingkungan (.env)
Buka file `backend/.env` dan pastikan konfigurasi database sesuai dengan pengaturan default XAMPP:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pestago
DB_USERNAME=root
DB_PASSWORD=
```

---

### Langkah 5: Instalasi Dependensi Composer
Masuk ke terminal pada folder `backend/`:
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/pestagodemo/backend
composer install
```

---

### Langkah 6: Menjalankan Migrasi & Seeder Demo Data
Jalankan perintah berikut untuk membuat seluruh tabel dan mengisi data awal (akun demo, kategori, vendor, layanan, ulasan):
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

---

### Langkah 7: Menjalankan Server Aplikasi
Jalankan server pengembangan Laravel:
```bash
php artisan serve --port=8001
```

Buka peramban (browser) Anda pada alamat:
👉 **http://127.0.0.1:8001**

---

## 3. Akun Pengujian (Demo Accounts)

Sistem sudah dilengkapi akun demo untuk ketiga peran (role):

| Peran (Role) | Email | Password | Akses Halaman |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@pestago.id` | `admin123` | `/admin/dashboard` |
| **Customer 1** | `sari@email.com` | `customer123` | `/customer/dashboard` |
| **Customer 2** | `budi@email.com` | `customer123` | `/customer/dashboard` |
| **Vendor 1 (Venue)** | `vendor1@email.com` | `vendor123` | `/vendor/dashboard` |
| **Vendor 2 (Catering)** | `vendor2@email.com` | `vendor123` | `/vendor/dashboard` |
| **Vendor 3 (Dekor/MUA)**| `vendor3@email.com` | `vendor123` | `/vendor/dashboard` |
| **Vendor 4 (Busana)** | `vendor4@email.com` | `vendor123` | `/vendor/dashboard` |
| **Vendor 5 (Dokumentasi)**| `vendor5@email.com`| `vendor123` | `/vendor/dashboard` |
| **Vendor 6 (Musik/MC)** | `vendor6@email.com` | `vendor123` | `/vendor/dashboard` |

---

## 4. Troubleshooting Masalah Umum

### 1. Error: `SQLSTATE[HY000] [2002] Connection refused`
- **Penyebab**: MySQL pada XAMPP belum aktif.
- **Solusi**: Buka XAMPP Control Panel dan pastikan lampu indikator MySQL menyala hijau (Running).

### 2. Error Gambar / Bukti Pembayaran Tidak Muncul
- **Penyebab**: Symlink storage belum terbuat.
- **Solusi**: Jalankan perintah `php artisan storage:link` pada direktori `backend/`.

### 3. Peta Tidak Memuat / Blank Tile
- **Penyebab**: Koneksi internet komputer terputus saat mengambil tile OpenStreetMap publik.
- **Solusi**: Pastikan komputer terhubung ke internet saat membuka peta Leaflet.js.
