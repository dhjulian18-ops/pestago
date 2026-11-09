# MASTER PROMPT — PEMBANGUNAN WEBSITE PESTAGO

## 1. PERAN DAN TUJUAN

Bertindaklah sebagai Senior Laravel Developer, Full-Stack Web Developer, UI/UX Designer, Database Architect, dan Software Tester.

Saya ingin membangun **PestaGo**, sebuah website marketplace penyewaan dan pemesanan layanan untuk berbagai acara, seperti pernikahan, ulang tahun, wisuda, seminar, khitanan, dan acara lainnya.

Saya akan memberikan file PDF **Konsep Lengkap PestaGo**. Baca dan pahami seluruh isi PDF tersebut sebelum mulai mengembangkan website.

PDF menjadi acuan utama untuk fitur, alur customer, vendor, super admin, pemesanan, pembayaran, pembatalan, dan sistem lokasi.

Hasil akhir yang saya inginkan adalah website yang benar-benar berfungsi, terhubung ke database, mempunyai tampilan profesional, dan memiliki kode yang rapi serta mudah dikembangkan.

**Jangan hanya membuat desain frontend atau prototype statis.** Implementasikan frontend, backend, database, autentikasi, validasi, dan seluruh fungsi sesuai konsep.

## 2. TEKNOLOGI WAJIB

Gunakan teknologi berikut:

- **Backend:** Laravel dengan PHP.
- **Database:** MySQL atau MariaDB yang dijalankan melalui XAMPP.
- **Frontend:** Blade Template Laravel, HTML5, CSS3, dan JavaScript.
- **CSS framework:** Bootstrap 5 atau Tailwind CSS. Pilih salah satu dan gunakan secara konsisten.
- **Interaktivitas:** JavaScript, AJAX, atau Fetch API jika diperlukan.
- **Peta lokasi:** Google Maps atau OpenStreetMap dengan Leaflet, disesuaikan dengan kebutuhan dan konfigurasi yang tersedia.
- **Web server lokal:** Apache melalui XAMPP.
- **Database management:** phpMyAdmin melalui XAMPP.
- **Dependency management:** Composer.

Prioritaskan website Laravel yang dapat dijalankan secara lokal menggunakan XAMPP. Jangan menggunakan Flutter, React Native, Expo, atau membuat frontend sebagai proyek aplikasi mobile terpisah.

Gunakan versi Laravel dan PHP yang kompatibel dengan lingkungan yang tersedia. Periksa versi PHP dan Composer terlebih dahulu sebelum menentukan dependensi.

## 3. STRUKTUR FOLDER WAJIB

Nama folder utama adalah `pestagodemo`.

Di dalam folder tersebut, pisahkan kode frontend dan backend menjadi dua folder utama: `frontend` dan `backend`.

Struktur yang diinginkan:

```text
pestagodemo/
│
├── frontend/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── images/
│   │   └── icons/
│   ├── README.md
│   └── ...
│
├── backend/
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   ├── public/
│   ├── resources/
│   │   └── views/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   ├── .env.example
│   ├── artisan
│   ├── composer.json
│   └── README.md
│
├── docs/
│   ├── arsitektur.md
│   ├── struktur-database.md
│   └── panduan-instalasi.md
│
└── README.md
```

### Ketentuan struktur

1. Folder utama proyek harus tetap bernama `pestagodemo`.
2. Folder `frontend/` khusus untuk aset, stylesheet, JavaScript, gambar, ikon, dan file frontend yang memang perlu dipisahkan.
3. Folder `backend/` berisi proyek Laravel lengkap, termasuk Blade, controller, model, migration, middleware, route, dan konfigurasi backend.
4. Template Blade tetap disimpan di `backend/resources/views/`, karena Blade merupakan bagian dari Laravel.
5. File CSS, JavaScript, gambar, dan aset frontend dapat disimpan di `frontend/`, kemudian diintegrasikan dengan Laravel melalui konfigurasi aset yang sesuai.
6. Jangan membuat dua proyek Laravel yang terpisah.
7. Jangan memindahkan folder inti Laravel ke luar `backend/`.
8. Jangan menaruh semua kode di satu file besar.
9. Jangan membuat struktur folder tambahan yang tidak diperlukan.
10. Jika struktur tersebut membutuhkan konfigurasi tambahan agar Laravel dapat melayani aset frontend dengan benar, implementasikan konfigurasi itu dan dokumentasikan cara kerjanya.

**Penting:** Laravel tetap menjadi satu aplikasi web yang utuh. Pemisahan frontend dan backend merupakan pemisahan struktur kode, bukan dua server atau dua aplikasi yang tidak saling terhubung.

## 4. ATURAN SEBELUM MEMULAI

Sebelum menulis kode:

1. Periksa direktori kerja dan struktur folder yang sudah ada.
2. Baca seluruh PDF konsep PestaGo.
3. Periksa versi PHP, Composer, Laravel, dan ketersediaan XAMPP.
4. Tentukan struktur folder yang kompatibel dengan Laravel.
5. Rancang tabel database beserta relasinya.
6. Tentukan route, controller, model, middleware, dan alur autentikasi.
7. Buat daftar halaman berdasarkan role pengguna.
8. Implementasikan fitur secara bertahap.
9. Jalankan pengujian setelah menyelesaikan setiap bagian utama.

Jangan menghapus atau menimpa file yang sudah ada tanpa memeriksanya terlebih dahulu.

Jika ada detail yang belum dijelaskan dalam PDF, tentukan solusi yang masuk akal, dokumentasikan asumsi, dan lanjutkan pengerjaan.

## 5. DESAIN WEBSITE

Buat website marketplace yang profesional, modern, rapi, menarik, dan mudah digunakan.

Gunakan prinsip desain berikut:

- Identitas visual PestaGo dengan warna merah sebagai warna utama dan kuning sebagai aksen.
- Latar belakang netral yang nyaman dibaca.
- Jangan menggunakan ungu sebagai warna utama.
- Desain tidak boleh terlalu polos, terlalu ramai, atau penuh warna.
- Gunakan tipografi yang konsisten, ikon kategori ilustratif, gambar layanan yang relevan, dan kartu vendor yang menarik.
- Pastikan tampilan responsif di desktop, laptop, tablet, dan HP.
- Buat navigasi yang jelas dan konsisten.
- Gunakan komponen UI yang dapat dipakai kembali.
- Buat halaman loading, pesan error, empty state, notifikasi, dan konfirmasi tindakan yang sesuai.
- Semua tombol, formulir, filter, navigasi, dan menu harus memiliki fungsi nyata.

Landing page harus ditampilkan terlebih dahulu, bukan langsung mengarahkan pengunjung ke halaman login.

## 6. FITUR WEBSITE

Implementasikan seluruh fitur yang tercantum dalam PDF, termasuk bagian-bagian berikut.

### A. Landing page

- Hero section.
- Kategori layanan.
- Cara kerja PestaGo.
- Vendor unggulan.
- Promo atau poster promosi.
- Testimoni.
- Tentang kami.
- Kontak.
- Tombol Mulai Rencanakan.
- Tombol Login dan Register.

Data vendor, kategori, promo, dan testimoni yang dikelola melalui sistem harus ditampilkan dari database jika relevan. Jangan mengisi seluruh halaman dengan data statis yang tidak terhubung ke backend.

### B. Customer

- Registrasi dan login.
- Dashboard customer.
- Profil pengguna.
- Membuat acara baru.
- Memilih jenis acara.
- Mengisi tanggal, lokasi, anggaran, dan jumlah tamu.
- Memilih kebutuhan layanan acara.
- Melihat rekomendasi vendor berdasarkan kategori, lokasi, dan anggaran.
- Melihat detail layanan.
- Melihat foto, harga, fasilitas, alamat, lokasi peta, dan ulasan vendor.
- Menambahkan layanan ke keranjang acara.
- Melihat rincian harga.
- Checkout beberapa layanan dari vendor yang berbeda.
- Melihat status pesanan.
- Mengajukan pembatalan sesuai aturan.
- Melihat riwayat pesanan dan pembayaran.
- Chat customer dan vendor jika tercantum dalam PDF.
- Memberikan ulasan setelah pesanan memenuhi persyaratan.
- Menerima notifikasi.

### C. Vendor

- Registrasi vendor.
- Mengisi nama usaha, kategori, alamat, nomor HP, dan dokumen verifikasi.
- Status verifikasi vendor: menunggu, disetujui, atau ditolak.
- Vendor yang belum disetujui tidak boleh menerima booking aktif.
- Dashboard vendor.
- CRUD layanan milik vendor sendiri.
- Upload foto layanan.
- Pengaturan harga, deskripsi, fasilitas, kapasitas, alamat, dan titik lokasi.
- Pengelolaan pesanan masuk.
- Menerima atau menolak pesanan.
- Kalender booking.
- Chat customer jika tersedia.
- Pengelolaan profil.
- Pendapatan dan riwayat transaksi.
- Review pelanggan.

### D. Super Admin

- Dashboard statistik.
- Mengelola customer.
- Mengelola vendor.
- Memverifikasi atau menolak vendor.
- Mengelola kategori layanan.
- Mengelola pesanan.
- Mengelola pembayaran dan refund.
- Mengelola promo dan konten landing page sesuai kebutuhan.
- Mengelola laporan sistem.
- Melihat aktivitas penting dan status transaksi.

Gunakan hanya tiga role utama: `customer`, `vendor`, dan `super_admin`. Terapkan middleware dan pemeriksaan izin di backend untuk mencegah akses yang tidak sah.

## 7. FITUR LOKASI DAN PETA — WAJIB

Fitur lokasi adalah salah satu fitur penting PestaGo dan tidak boleh diabaikan.

Setiap layanan yang membutuhkan lokasi harus mempunyai data alamat dan koordinat yang dapat digunakan untuk menampilkan peta.

Contoh pada layanan MUA:

- Nama layanan MUA.
- Foto layanan.
- Harga.
- Deskripsi.
- Fasilitas.
- Rating dan ulasan.
- Alamat lengkap.
- Latitude.
- Longitude.
- Peta lokasi vendor.
- Tombol Lihat Peta.
- Tombol Petunjuk Arah.

### Perilaku peta

1. Peta menampilkan lokasi vendor berdasarkan koordinat yang tersimpan di database.
2. Marker menunjukkan titik lokasi layanan.
3. Peta dapat diperbesar, diperkecil, dan digeser.
4. Customer dapat membuka lokasi pada layanan peta eksternal untuk melihat rute.
5. Jika lokasi perangkat diizinkan oleh pengguna, website dapat membantu menentukan lokasi customer.
6. Jika izin lokasi ditolak, customer tetap dapat mencari berdasarkan kota, kecamatan, alamat, atau lokasi yang dipilih secara manual.
7. Gunakan koordinat sebenarnya yang dimasukkan vendor. Jangan mengarang koordinat atau selalu menggunakan satu titik yang sama untuk semua vendor.
8. Validasi koordinat dan alamat ketika vendor menambahkan atau mengedit layanan.
9. Lokasi digunakan untuk membantu mengurutkan rekomendasi vendor berdasarkan kedekatan, jika koordinat tersedia.
10. Tangani layanan yang belum memiliki koordinat dengan menampilkan alamat dan keterangan bahwa titik peta belum tersedia.

### Implementasi teknis peta

Gunakan Leaflet dengan OpenStreetMap sebagai pilihan awal agar dapat diuji di lingkungan lokal tanpa langsung membutuhkan Google Maps API key.

Simpan koordinat dalam kolom database yang sesuai, misalnya `latitude` dan `longitude`.

Pastikan atribusi penyedia peta ditampilkan dengan benar. Jangan menganggap layanan tile peta publik memiliki kapasitas tanpa batas; dokumentasikan ketergantungan layanan peta dan opsi konfigurasi jika aplikasi akan dipublikasikan.

Fitur lokasi harus terhubung ke database dan benar-benar berfungsi di halaman detail layanan, bukan hanya berupa gambar peta dekoratif.

## 8. ALUR PEMESANAN DAN PEMBAYARAN

Implementasikan alur sesuai PDF:

1. Customer membuat acara.
2. Customer memilih kebutuhan layanan.
3. Website menampilkan rekomendasi vendor.
4. Customer membuka detail layanan.
5. Customer memeriksa harga, alamat, dan lokasi peta.
6. Customer menambahkan layanan ke keranjang.
7. Customer melakukan checkout.
8. Sistem menyimpan pesanan dan detail item.
9. Vendor menerima atau menolak permintaan sesuai aturan.
10. Customer melihat perubahan status pesanan.
11. Sistem menangani pembatalan, refund, dan penyelesaian pesanan sesuai aturan yang ditetapkan.
12. Customer dapat memberikan ulasan setelah pesanan memenuhi persyaratan.

Gunakan transaksi database untuk operasi penting agar perubahan stok, pesanan, dan pembayaran tidak menghasilkan data yang tidak konsisten.

Jangan menganggap pembayaran berhasil hanya karena customer menekan tombol bayar. Gunakan status pembayaran yang jelas dan verifikasi melalui mekanisme backend.

Jika payment gateway belum memiliki kredensial, buat integrasi melalui konfigurasi yang dapat diganti dan sediakan mode pengujian yang aman. Jangan mengklaim transaksi uang sungguhan telah terintegrasi jika belum diuji.

## 9. DATABASE MYSQL

Buat migration Laravel untuk tabel yang benar-benar dibutuhkan oleh sistem.

Pertimbangkan tabel berikut:

- users
- vendor_profiles
- vendor_documents
- categories
- services
- service_images
- events
- event_requirements
- carts atau tabel keranjang yang sesuai
- orders
- order_items
- payments
- refunds
- reviews
- notifications
- conversations dan messages jika chat diimplementasikan
- promos jika diperlukan

Tambahkan tabel lain apabila memang dibutuhkan oleh konsep PDF.

Ketentuan database:

- Gunakan primary key dan foreign key yang tepat.
- Tentukan relasi antartabel.
- Gunakan migration, bukan hanya membuat tabel secara manual.
- Gunakan seeder untuk data demo.
- Terapkan validasi dan unique constraint jika dibutuhkan.
- Simpan harga menggunakan tipe data yang sesuai untuk nilai uang.
- Terapkan status pesanan dan pembayaran yang konsisten.
- Jangan menyimpan password dalam bentuk teks biasa.
- Jangan mengandalkan validasi frontend saja.

Pastikan database dapat dibuat dan dijalankan menggunakan MySQL/MariaDB melalui XAMPP.

## 10. KEAMANAN

Implementasikan:

- Hashing password menggunakan mekanisme Laravel.
- Validasi request di backend.
- Proteksi CSRF untuk formulir web.
- Middleware autentikasi dan role.
- Pembatasan akses data berdasarkan pemiliknya.
- Validasi file upload, tipe file, dan ukuran file.
- Pencegahan mass assignment yang tidak aman.
- Proteksi terhadap akses langsung ke data pengguna lain.
- Validasi harga dan total pesanan di backend.
- Pengelolaan konfigurasi rahasia melalui `.env`.
- Penanganan error tanpa membocorkan informasi sensitif.

Customer tidak boleh mengedit layanan milik vendor. Vendor hanya boleh mengelola layanan dan data yang menjadi haknya. Super admin memiliki akses sesuai kebutuhan administrasi sistem.

## 11. PENGUJIAN

Setelah implementasi, lakukan pengujian terhadap:

- Registrasi dan login.
- Hak akses setiap role.
- CRUD layanan.
- Upload foto.
- Verifikasi vendor.
- Pencarian dan filter.
- Peta dan koordinat lokasi.
- Keranjang dan checkout.
- Pesanan multi-vendor.
- Perubahan status pesanan.
- Pembatalan dan refund.
- Validasi harga.
- Database dan relasi.
- Tampilan responsif.
- Navigasi dan tombol.
- Error handling.

Perbaiki error yang ditemukan. Jangan menyatakan fitur telah berhasil jika belum diuji.

## 12. PANDUAN INSTALASI DAN MENJALANKAN WEBSITE

Sertakan README yang menjelaskan langkah-langkah untuk menjalankan PestaGo di komputer lokal menggunakan XAMPP.

Minimal dokumentasikan:

1. Menempatkan folder proyek di lokasi kerja yang sesuai.
2. Menyalakan Apache dan MySQL di XAMPP.
3. Membuat database melalui phpMyAdmin.
4. Mengatur file `.env`.
5. Menjalankan instalasi dependensi Composer.
6. Menjalankan migration dan seeder.
7. Mengatur penyimpanan gambar dan izin direktori jika diperlukan.
8. Menjalankan aplikasi menggunakan konfigurasi lokal yang benar.
9. Membuka website melalui browser.
10. Menguji akun demo untuk customer, vendor, dan super admin.
11. Mengatasi masalah koneksi database dan error konfigurasi yang umum.

Jangan meletakkan folder proyek Laravel secara sembarangan di dalam document root lalu menganggap seluruh direktori backend aman diakses publik. Atur document root atau virtual host agar hanya direktori `backend/public` yang menjadi pintu masuk web, atau gunakan konfigurasi pengembangan lokal yang aman dan terdokumentasi.

Jangan pernah memasukkan password asli, API key pribadi, atau kredensial sensitif ke repository.

## 13. STANDAR HASIL AKHIR

PestaGo dinyatakan selesai untuk tahap awal apabila:

- Struktur proyek berada di dalam `pestagodemo`.
- Folder `frontend` dan `backend` terpisah dengan jelas.
- Backend Laravel berjalan.
- Database MySQL dapat dihubungkan melalui XAMPP.
- Landing page tampil dengan baik.
- Registrasi dan login berfungsi.
- Hak akses customer, vendor, dan super admin diterapkan.
- Vendor dapat mengelola layanan sesuai izin.
- Detail layanan menampilkan alamat dan peta lokasi yang sesuai.
- Customer dapat mencari layanan dan membuat pesanan.
- Alur status pesanan dapat diuji.
- Fitur inti tidak sekadar tampilan statis.
- Error penting sudah diperiksa dan diperbaiki.
- Dokumentasi instalasi dan pengujian tersedia.

## 14. CARA BEKERJA

Kerjakan secara berurutan:

**Tahap 1:** Analisis PDF, lingkungan kerja, dan struktur proyek.

**Tahap 2:** Siapkan struktur folder dan proyek Laravel.

**Tahap 3:** Siapkan konfigurasi MySQL/XAMPP, migration, model, dan seeder.

**Tahap 4:** Bangun autentikasi, role, middleware, dan dashboard masing-masing pengguna.

**Tahap 5:** Bangun frontend, landing page, komponen UI, kategori, dan halaman layanan.

**Tahap 6:** Implementasikan lokasi, peta, pencarian, filter, dan rekomendasi vendor.

**Tahap 7:** Implementasikan keranjang, checkout, pesanan, pembayaran, pembatalan, dan refund sesuai cakupan PDF.

**Tahap 8:** Implementasikan fitur vendor dan super admin.

**Tahap 9:** Lakukan pengujian, perbaikan, dan pengecekan keamanan.

**Tahap 10:** Lengkapi README, dokumentasi database, dan panduan menjalankan website.

Jangan berhenti setelah membuat struktur folder atau halaman awal. Lanjutkan sampai fitur inti selesai sesuai kemampuan lingkungan kerja.

Jika ada bagian yang belum dapat diimplementasikan karena keterbatasan akses atau konfigurasi, jelaskan secara spesifik apa yang belum selesai, penyebabnya, dan langkah yang dibutuhkan untuk menyelesaikannya.

**Prioritas utama: kesesuaian dengan PDF, struktur folder yang rapi, desain profesional, fungsi nyata, integrasi Laravel–MySQL, dan fitur lokasi yang benar-benar bekerja.**