# 🎉 PestaGo — Platform Vendor Acara Indonesia

## Panduan Menjalankan Website

### Prasyarat
- **XAMPP** dengan Apache dan MySQL aktif
- **PHP** >= 8.2
- **Composer** terinstall
- **Node.js** (opsional, untuk build assets jika diperlukan)

### Langkah Setup

#### 1. Aktifkan XAMPP
Buka XAMPP Control Panel, nyalakan **Apache** dan **MySQL**.

#### 2. Buat Database
```bash
# Via terminal
/Applications/XAMPP/xamppfiles/bin/mysql -u root -e "CREATE DATABASE pestago CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```
Atau buka **phpMyAdmin** (`http://localhost/phpmyadmin`) dan buat database `pestago`.

#### 3. Install Dependencies
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/pestagodemo/backend
composer install
```

#### 4. Setup Environment
File `.env` sudah dikonfigurasi. Pastikan koneksi database benar:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pestago
DB_USERNAME=root
DB_PASSWORD=
```

#### 5. Jalankan Migrasi & Seeder
```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

#### 6. Jalankan Server
```bash
php artisan serve --port=8001
```
Buka browser: **http://127.0.0.1:8001**

---

### Akun Demo

| Role | Email | Password |
|------|-------|----------|
| **Super Admin** | admin@pestago.id | admin123 |
| **Customer** | sari@email.com | customer123 |
| **Customer** | budi@email.com | customer123 |
| **Vendor** | vendor1@email.com | vendor123 |
| **Vendor** | vendor2@email.com | vendor123 |
| **Vendor** | vendor3@email.com | vendor123 |
| **Vendor** | vendor4@email.com | vendor123 |
| **Vendor** | vendor5@email.com | vendor123 |
| **Vendor** | vendor6@email.com | vendor123 |

---

### Struktur Folder

```
pestagodemo/
├── backend/                 # Laravel Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Auth/AuthController.php       # Login & Register
│   │   │   │   ├── HomeController.php            # Landing page
│   │   │   │   ├── ServiceController.php         # Layanan publik
│   │   │   │   ├── Customer/                     # Dashboard, Cart, Order, Event
│   │   │   │   ├── Vendor/                       # Dashboard, Services, Orders
│   │   │   │   └── Admin/                        # Admin panel lengkap
│   │   │   └── Middleware/
│   │   │       ├── RoleMiddleware.php            # Role-based access
│   │   │       └── VendorActiveMiddleware.php    # Vendor verification
│   │   └── Models/                               # 18+ Eloquent models
│   ├── database/
│   │   ├── migrations/                           # 9 migration files
│   │   └── seeders/DatabaseSeeder.php            # Demo data
│   ├── resources/views/                          # Blade templates
│   │   ├── layouts/app.blade.php                 # Master layout
│   │   ├── home/                                 # Landing, About, Contact
│   │   ├── auth/                                 # Login, Register Customer/Vendor
│   │   ├── services/                             # Index & Detail
│   │   ├── customer/                             # Dashboard, Cart, Orders, Events
│   │   ├── vendor/                               # Dashboard, Services, Orders
│   │   └── admin/                                # Dashboard, Vendors, Users, etc
│   ├── public/
│   │   ├── css/app.css                           # Design system
│   │   └── js/app.js                             # App JavaScript
│   └── routes/web.php                            # All routes
├── frontend/                # Frontend Assets (mirror)
│   └── assets/
│       ├── css/app.css
│       └── js/app.js
└── pestago/                 # Dokumentasi proyek
    ├── PROMPT_PESTAGO.md
    └── Konsep_Lengkap_PestaGo.pdf
```

---

### Fitur yang Diimplementasikan

#### 🏠 Landing Page
- Hero section dengan gradient premium & micro-animations
- Kategori layanan (7 kategori)
- Layanan unggulan dengan rating
- "Cara Kerja" 4 langkah
- Testimonial pelanggan
- CTA (Call to Action)
- Footer responsif

#### 🔐 Autentikasi
- Login dengan role-based redirect (Customer/Vendor/Admin)
- Registrasi Customer
- Registrasi Vendor (dengan profil bisnis)
- Middleware role-based access control

#### 👤 Customer Dashboard
- Statistik acara dan pesanan
- Buat & kelola acara (Event Planning)
- Cari layanan vendor (filter kategori, kota, harga, sort)
- Detail layanan dengan **peta Leaflet/OpenStreetMap**
- Keranjang belanja multi-vendor
- Checkout dengan pilihan metode pembayaran
- Upload bukti pembayaran
- Kelola pesanan (lihat, batalkan item)

#### 🏪 Vendor Dashboard
- Status verifikasi (Pending/Active/Rejected)
- Statistik: layanan aktif, pesanan, pendapatan, rating
- CRUD layanan dengan upload foto & map picker
- Kelola pesanan masuk (terima/tolak dengan alasan)

#### 🛡️ Admin Panel
- Statistik platform: users, vendor, pesanan, pendapatan
- Verifikasi vendor (Approve/Revisi/Reject)
- Manajemen users (aktifkan/nonaktifkan)
- CRUD kategori layanan
- Monitor semua pesanan
- Verifikasi pembayaran manual

#### 🗺️ Peta & Lokasi
- Leaflet.js + OpenStreetMap (gratis, tanpa API key)
- Marker lokasi vendor di halaman detail layanan
- Map picker interaktif untuk vendor saat tambah/edit layanan
- Map picker untuk customer saat buat acara

#### 💳 Sistem Pembayaran
- Multi-vendor checkout
- Platform fee 5% otomatis
- Validasi harga di backend
- Upload bukti pembayaran
- Verifikasi admin
- Notifikasi otomatis

#### 🔔 Notifikasi
- Pesanan baru untuk vendor
- Konfirmasi/penolakan pesanan untuk customer
- Status pembayaran
- Verifikasi vendor

---

### Teknologi

| Teknologi | Versi | Penggunaan |
|-----------|-------|------------|
| PHP | 8.5 | Backend runtime |
| Laravel | 13 | Web framework |
| MySQL | 8+ | Database |
| Leaflet.js | 1.9.4 | Peta interaktif |
| OpenStreetMap | — | Tile layer peta (gratis) |
| Bootstrap Icons | 1.11 | Ikon UI |
| Plus Jakarta Sans | — | Typography |
| Vanilla CSS | — | Design system custom |
| Vanilla JS | — | Interaktivitas |
