# 🎨 Frontend Assets — PestaGo

Folder ini menyimpan sumber aset frontend yang digunakan pada platform PestaGo:

```text
frontend/
└── assets/
    ├── css/
    │   └── app.css      # Design system custom (Plus Jakarta Sans, warna merah & kuning, micro-animations)
    ├── js/
    │   └── app.js       # Logika interaktivitas client-side (navbar, modal, peta Leaflet, notifications)
    ├── images/          # Aset grafis & placeholder ilustrasi
    └── icons/           # Ikon UI
```

## Integrasi dengan Backend Laravel
Aset-aset di dalam folder ini disinkronkan ke direktori `backend/public/css/` dan `backend/public/js/` agar dilayani secara optimal oleh web server Apache melalui Laravel Blade (`asset('css/app.css')` dan `asset('js/app.js')`).
