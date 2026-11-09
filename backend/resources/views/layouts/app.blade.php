<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PestaGo') — Platform Vendor Acara #1 Indonesia</title>
    <meta name="description" content="@yield('meta_description', 'PestaGo — Platform marketplace vendor acara terlengkap di Indonesia. Temukan venue, catering, dekorasi, fotografer, dan hiburan untuk pernikahan, ulang tahun, wisuda, dan acara spesial Anda.')">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    {{-- App CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}">
    <style>
        body.is-admin-panel {
            --navbar-height: 64px;
        }
        body.is-admin-panel .dashboard {
            padding-top: 0;
            min-height: calc(100vh - 64px);
        }
        @media (min-width: 769px) {
            .dashboard {
                display: flex !important;
                align-items: stretch !important;
                overflow: visible !important;
            }
            .sidebar {
                position: -webkit-sticky !important;
                position: sticky !important;
                top: var(--navbar-height, 56px) !important;
                height: calc(100vh - var(--navbar-height, 56px)) !important;
                max-height: calc(100vh - var(--navbar-height, 56px)) !important;
                overflow-y: auto !important;
                flex-shrink: 0 !important;
                z-index: 90 !important;
            }
            body.is-admin-panel .sidebar {
                top: 64px !important;
                height: calc(100vh - 64px) !important;
                max-height: calc(100vh - 64px) !important;
            }
        }
        .admin-topbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            height: 64px;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }
        .admin-topbar-inner {
            width: 100%;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
    <script>
        (function() {
            function syncNavbarHeight() {
                var header = document.getElementById('mainHeader');
                if (header) {
                    var toprow = header.querySelector('.pestago-mobile-toprow');
                    var row2 = header.querySelector('.pestago-mobile-row2');
                    var h;
                    if (window.innerWidth <= 640 && toprow && row2) {
                        h = Math.round(toprow.offsetHeight + row2.offsetHeight);
                    } else {
                        h = Math.round(header.getBoundingClientRect().height);
                    }
                    if (h > 40) {
                        var px = h + 'px';
                        document.documentElement.style.setProperty('--navbar-height', px);
                        document.documentElement.style.setProperty('--pestago-nav-height', px);
                    }
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', syncNavbarHeight);
            } else {
                syncNavbarHeight();
            }
            window.addEventListener('load', syncNavbarHeight);
            window.addEventListener('resize', syncNavbarHeight);
            window.addEventListener('orientationchange', syncNavbarHeight);
        })();
    </script>
    @stack('styles')
</head>
<body class="{{ request()->is('admin*') ? 'is-admin-panel' : '' }}">
    @if(request()->is('admin*'))
        {{-- Admin Minimal Topbar (Hanya Logo & Akun, Tanpa Navbar E-Commerce) --}}
        <header class="admin-topbar" id="adminHeader">
            <div class="admin-topbar-inner">
                {{-- Left: Brand Logo + Admin Panel Badge --}}
                <div style="display:flex;align-items:center;gap:12px;">
                    <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;text-decoration:none;" title="PestaGo Admin">
                        <img src="{{ asset('images/pgtam.png') }}" alt="PestaGo" style="height:32px;object-fit:contain;">
                    </a>
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:6px;background:#fef2f2;color:#d81a24;font-size:11px;font-weight:700;letter-spacing:0.5px;border:1px solid #fee2e2;">
                        <i class="bi bi-shield-check"></i> ADMIN PANEL
                    </span>
                </div>

                {{-- Right: Akun Admin Profil --}}
                <div style="display:flex;align-items:center;gap:10px;">
                    {{-- Admin Notification Center Dropdown --}}
                    <div class="nav-dropdown" style="position:relative;" id="adminNotifDropdownWrapper">
                        <button type="button" class="admin-sound-toggle-btn" id="adminNotifBtn" style="position:relative;" title="Pusat Notifikasi Admin">
                            <i class="bi bi-bell" style="font-size:16px;color:#111111;"></i>
                            <span id="topbarBadge-notifications" class="uq-badge-red" style="position:absolute;top:-5px;right:-5px;min-width:18px;height:18px;font-size:10px;padding:0 4px;border-radius:9px;background:#D81A24;color:#fff;font-weight:800;display:none;">0</span>
                        </button>
                        <div class="nav-dropdown-menu" id="adminNotifMenu" style="right:0;left:auto;width:340px;max-width:calc(100vw - 32px);padding:0;margin-top:8px;box-shadow:0 12px 30px rgba(0,0,0,0.15);border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;display:none;position:absolute;background:#fff;z-index:9999;">
                            <div style="padding:12px 16px;background:#fafafa;border-bottom:1px solid #ebebeb;display:flex;align-items:center;justify-content:space-between;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <strong style="font-size:13px;color:#111111;font-weight:800;">Notifikasi &amp; Antrean</strong>
                                    <span id="adminNotifTotalBadge" style="background:#fee2e2;color:#dc2626;font-size:11px;font-weight:700;padding:1px 6px;border-radius:10px;">0 pending</span>
                                </div>
                                <button type="button" id="adminSoundToggleBtn" onclick="toggleAdminNotificationSound()" style="background:none;border:none;cursor:pointer;padding:2px 6px;font-size:13px;color:#555;display:flex;align-items:center;gap:4px;" title="Beralih Suara Notifikasi">
                                    <i class="bi bi-bell-fill" style="color:#16a34a;"></i>
                                </button>
                            </div>
                            <div id="adminNotifList" style="max-height:360px;overflow-y:auto;">
                                <div style="padding:24px 16px;text-align:center;color:#888888;font-size:12px;">
                                    <i class="bi bi-check2-circle" style="font-size:24px;color:#16a34a;display:block;margin-bottom:6px;"></i>
                                    Memuat notifikasi antrean...
                                </div>
                            </div>
                            <div style="padding:8px 16px;background:#fafafa;border-top:1px solid #ebebeb;text-align:center;">
                                <a href="{{ route('admin.dashboard') }}" style="font-size:11.5px;font-weight:700;color:#D81A24;text-decoration:none;">
                                    Buka Ringkasan Dashboard &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    @php $adminUnreadMessages = auth()->user()->unreadMessagesCount(); @endphp
                    @if(auth()->user()->hasPermission('chats'))
                        <a href="{{ route('admin.chats.index') }}" title="Live Chat & CS" style="display:inline-flex;align-items:center;justify-content:center;position:relative;width:38px;height:38px;border:1px solid #e5e7eb;background:#ffffff;border-radius:4px;color:#111111;text-decoration:none;">
                            <i class="bi bi-chat-dots" style="font-size:16px;"></i>
                            <span id="topbarBadge-chats" style="position:absolute;top:-4px;right:-4px;background:#D81A24;color:#ffffff;font-size:10px;font-weight:700;padding:1px 5px;border-radius:10px;line-height:1.2;{{ $adminUnreadMessages > 0 ? '' : 'display:none;' }}">{{ $adminUnreadMessages }}</span>
                        </a>
                    @endif

                    <div class="nav-dropdown" style="position:relative;">
                        <button class="emox-signin-btn" id="userDropdownBtn" style="background:#f9fafb;border:1px solid #e5e7eb;cursor:pointer;display:flex;align-items:center;gap:10px;padding:5px 12px;border-radius:4px;transition:all 0.2s;">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" style="width:32px;height:32px;border-radius:2px;object-fit:cover;border:1px solid #e5e5e5;flex-shrink:0;">
                            @else
                                <span class="user-avatar" style="width:32px;height:32px;border-radius:2px;font-size:13px;font-weight:700;background:#d81a24;color:#ffffff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                            @endif
                            <div style="text-align:left;line-height:1.2;">
                                <span class="user-name" style="font-weight:600;font-size:13px;color:#111827;display:block;">{{ auth()->user()->name ?? 'Admin PestaGo' }}</span>
                                <span style="font-size:10.5px;color:#6b7280;font-weight:500;">{{ auth()->user()->admin_role_title ?? (auth()->user()->role === 'super_admin' ? 'Super Administrator' : 'Staff Admin') }}</span>
                            </div>
                            <i class="bi bi-chevron-down" style="font-size:11px;color:#6b7280;margin-left:4px;"></i>
                        </button>
                        <div class="nav-dropdown-menu" id="userDropdownMenu" style="right:0;left:auto;min-width:210px;padding:6px 0;margin-top:6px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1),0 8px 10px -6px rgba(0,0,0,0.1);border:1px solid #e5e7eb;border-radius:10px;">
                            <div style="padding:10px 16px;border-bottom:1px solid #f3f4f6;">
                                <strong style="font-size:13px;color:#111827;display:block;">{{ auth()->user()->name ?? 'Admin PestaGo' }}</strong>
                                <span style="font-size:11.5px;color:#6b7280;">{{ auth()->user()->email ?? 'admin@pestago.id' }}</span>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item" style="padding:9px 16px;display:flex;align-items:center;gap:10px;font-size:13px;color:#374151;text-decoration:none;"><i class="bi bi-person-gear"></i> Profil Saya</a>
                            <a href="{{ route('home') }}" target="_blank" class="dropdown-item" style="padding:9px 16px;display:flex;align-items:center;gap:10px;font-size:13px;color:#374151;text-decoration:none;"><i class="bi bi-globe"></i> Kunjungi Web</a>
                            <div class="dropdown-divider" style="margin:4px 0;border-top:1px solid #f3f4f6;"></div>
                            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;padding:9px 16px;display:flex;align-items:center;gap:10px;font-size:13px;color:#dc2626;"><i class="bi bi-box-arrow-right"></i> Keluar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    @else
        @php
            $isTransparentNavbar = false;
            $cartCount = auth()->check() ? auth()->user()->carts()->count() : 0;
            $wishlistNavCount = auth()->check() ? auth()->user()->wishlists()->count() : 0;
            $userWishlistIds = auth()->check() ? auth()->user()->wishlists()->pluck('service_id')->toArray() : [];
        @endphp

        {{-- PestaGo Uniqlo-Style Clean Navbar --}}
        <header class="pestago-navbar {{ $isTransparentNavbar ? 'navbar-transparent' : 'navbar-solid' }}" id="mainHeader">
            <div class="pestago-nav-container">

                {{-- MOBILE TOP ROW: Logo kiri + Icons kanan (sejajar 1 baris di mobile) --}}
                <div class="pestago-mobile-toprow">

                {{-- Left: Logo + Desktop Nav menu --}}
                <div class="pestago-nav-left">
                    <a href="{{ route('home') }}" class="pestago-brand" title="PestaGo">
                        <img src="{{ asset('images/pgput.png') }}" alt="PestaGo" class="pestago-logo-img logo-white">
                        <img src="{{ asset('images/pgtam.png') }}" alt="PestaGo" class="pestago-logo-img logo-dark">
                    </a>

                    {{-- Desktop nav menu --}}
                    <nav class="pestago-nav-menu" aria-label="Menu Utama">

                        {{-- SEMUA LAYANAN — Mega Dropdown --}}
                        <div class="pestago-nav-dropdown-wrap">
                            <a href="{{ route('services.index') }}"
                               class="pestago-nav-link pestago-nav-has-dropdown {{ request()->is('services') ? 'active' : '' }}"
                               id="navAllServicesBtn">
                                SEMUA LAYANAN <i class="bi bi-chevron-down" style="font-size:9px;margin-left:3px;vertical-align:middle;"></i>
                            </a>
                            <div class="pestago-nav-mega-dropdown" id="navMegaDropdown">
                                <div class="pestago-mega-inner">
                                    <a href="{{ route('services.index', ['category' => 'gedung-venue']) }}" class="pestago-mega-item {{ request('category') === 'gedung-venue' ? 'active' : '' }}">
                                        <span class="mega-item-icon"><i class="bi bi-building"></i></span>
                                        <span>Gedung &amp; Venue</span>
                                    </a>
                                    <a href="{{ route('services.index', ['category' => 'catering']) }}" class="pestago-mega-item {{ request('category') === 'catering' ? 'active' : '' }}">
                                        <span class="mega-item-icon"><i class="bi bi-cup-hot"></i></span>
                                        <span>Catering &amp; Kuliner</span>
                                    </a>
                                    <a href="{{ route('services.index', ['category' => 'dekorasi-makeup']) }}" class="pestago-mega-item {{ request('category') === 'dekorasi-makeup' ? 'active' : '' }}">
                                        <span class="mega-item-icon"><i class="bi bi-flower1"></i></span>
                                        <span>Dekorasi &amp; Makeup</span>
                                    </a>
                                    <a href="{{ route('services.index', ['category' => 'dokumentasi']) }}" class="pestago-mega-item {{ request('category') === 'dokumentasi' ? 'active' : '' }}">
                                        <span class="mega-item-icon"><i class="bi bi-camera"></i></span>
                                        <span>Dokumentasi</span>
                                    </a>
                                    <a href="{{ route('services.index', ['category' => 'hiburan-entertainment']) }}" class="pestago-mega-item {{ request('category') === 'hiburan-entertainment' ? 'active' : '' }}">
                                        <span class="mega-item-icon"><i class="bi bi-music-note-beamed"></i></span>
                                        <span>Hiburan &amp; Musik</span>
                                    </a>
                                    <a href="{{ route('services.index', ['category' => 'busana-aksesoris']) }}" class="pestago-mega-item {{ request('category') === 'busana-aksesoris' ? 'active' : '' }}">
                                        <span class="mega-item-icon"><i class="bi bi-gem"></i></span>
                                        <span>Busana &amp; Aksesoris</span>
                                    </a>
                                    <a href="{{ route('services.index', ['category' => 'perlengkapan-acara']) }}" class="pestago-mega-item {{ request('category') === 'perlengkapan-acara' ? 'active' : '' }}">
                                        <span class="mega-item-icon"><i class="bi bi-boxes"></i></span>
                                        <span>Perlengkapan Acara</span>
                                    </a>
                                    <a href="{{ route('services.index', ['filter' => 'promo']) }}" class="pestago-mega-item pestago-mega-promo {{ request('filter') === 'promo' ? 'active' : '' }}">
                                        <span class="mega-item-icon"><i class="bi bi-tag-fill"></i></span>
                                        <span>Promo &amp; Diskon</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Link tambahan — bersih, tidak duplikasi kategori --}}
                        <a href="{{ route('about') }}" class="pestago-nav-link {{ request()->routeIs('about') ? 'active' : '' }}">TENTANG</a>
                        <a href="{{ route('register.vendor') }}" class="pestago-nav-link {{ request()->routeIs('register.vendor') ? 'active' : '' }}">JADI VENDOR</a>
                        <a href="{{ route('contact') }}" class="pestago-nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">KONTAK</a>
                    </nav>
                </div>

                {{-- Right: Search Pill + 4 Icons (Heart, Person, Cart, Hamburger) --}}
                <div class="pestago-nav-right-wrap">
                    {{-- Search Bar — tersembunyi di mobile (dipindah ke row 2) --}}
                    <div class="pestago-nav-search">
                        <form action="{{ route('services.index') }}" method="GET" class="pestago-search-form">
                            <input type="text" name="q" value="{{ request('q') }}" class="pestago-search-input" placeholder="What are you looking for?" aria-label="Cari layanan">
                            <button type="submit" class="pestago-search-btn" aria-label="Cari">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>

                    {{-- 4 Action Icons (Gambar 1 style) --}}
                    <div class="pestago-nav-icons">
                        {{-- 1. Heart (Layanan Favorit Saya / Wishlist) --}}
                        <a href="{{ auth()->check() ? route('customer.wishlist') : route('login') }}" class="pestago-icon-btn pestago-wishlist-nav-btn" title="Layanan Favorit Saya" aria-label="Layanan Favorit Saya" style="position:relative;">
                            <i class="bi bi-heart"></i>
                            @if($wishlistNavCount > 0)
                                <span class="pestago-cart-badge pestago-wishlist-badge" style="background:#D81A24;">{{ $wishlistNavCount }}</span>
                            @endif
                        </a>

                        {{-- 2. Person (Akun / Profil) — Langsung ke Dashboard / Login --}}
                        @auth
                            @php
                                $userDashboardUrl = match(auth()->user()->role) {
                                    'vendor' => route('vendor.dashboard'),
                                    'admin', 'super_admin' => route('admin.dashboard'),
                                    default => route('customer.dashboard'),
                                };
                                $unreadChatNav = auth()->user()->unreadMessagesCount();
                            @endphp
                            <a href="{{ $userDashboardUrl }}" class="pestago-icon-btn" title="Akun & Dashboard Saya" aria-label="Akun & Dashboard Saya" style="position:relative;">
                                <i class="bi bi-person"></i>
                                @if($unreadChatNav > 0)
                                    <span style="position:absolute;top:2px;right:2px;width:8px;height:8px;background:#D81A24;border-radius:50%;border:1.5px solid #ffffff;"></span>
                                @endif
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="pestago-icon-btn" title="Masuk ke Akun" aria-label="Masuk ke Akun">
                                <i class="bi bi-person"></i>
                            </a>
                        @endauth

                        {{-- 3. Cart (Keranjang Belanja) --}}
                        <a href="{{ auth()->check() && auth()->user()->role === 'customer' ? route('customer.cart') : (auth()->check() ? route('services.index') : route('login')) }}" class="pestago-icon-btn pestago-cart-btn" title="Keranjang Belanja" aria-label="Keranjang Belanja">
                            <i class="bi bi-cart2"></i>
                            @if($cartCount > 0)
                                <span class="pestago-cart-badge">{{ $cartCount }}</span>
                            @endif
                        </a>

                        {{-- 4. Hamburger Menu (Slide Drawer) --}}
                        <button type="button" class="pestago-icon-btn pestago-drawer-trigger" id="btnOpenDrawer" title="Menu Lengkap" aria-label="Buka Menu Lengkap" style="position:relative;">
                            <i class="bi bi-list"></i>
                            @if(auth()->check() && ($unreadChatNav ?? 0) > 0)
                                <span style="position:absolute;top:4px;right:4px;width:7px;height:7px;background:#D81A24;border-radius:50%;border:1.5px solid #ffffff;"></span>
                            @endif
                        </button>
                    </div>
                </div>

                {{-- Tutup pestago-mobile-toprow --}}
                </div>{{-- /.pestago-mobile-toprow --}}

            </div>{{-- /.pestago-nav-container --}}

            {{-- MOBILE ROW 2: Search + Nav links (hanya tampil di mobile ≤640px) --}}
            <div class="pestago-mobile-row2">
                {{-- Search bar --}}
                <div class="pestago-nav-search">
                    <form action="{{ route('services.index') }}" method="GET" class="pestago-search-form">
                        <input type="text" name="q" value="{{ request('q') }}" class="pestago-search-input" placeholder="Cari layanan..." aria-label="Cari layanan">
                        <button type="submit" class="pestago-search-btn" aria-label="Cari">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
                {{-- Nav links mobile --}}
                <nav class="pestago-nav-menu" aria-label="Menu Mobile">
                    <button type="button"
                            class="pestago-nav-link pestago-mobile-services-btn {{ request()->is('services') ? 'active' : '' }}"
                            id="mobileAllServicesBtn"
                            aria-expanded="false"
                            aria-controls="mobileServicesPanel">
                        SEMUA LAYANAN <i class="bi bi-chevron-down"></i>
                    </button>
                    <a href="{{ route('about') }}" class="pestago-nav-link {{ request()->routeIs('about') ? 'active' : '' }}">TENTANG</a>
                    <a href="{{ route('register.vendor') }}" class="pestago-nav-link {{ request()->routeIs('register.vendor') ? 'active' : '' }}">JADI VENDOR</a>
                    <a href="{{ route('contact') }}" class="pestago-nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">KONTAK</a>
                </nav>
            </div>

            {{-- Mobile dropdown SEMUA LAYANAN --}}
            <div class="pestago-mobile-services" id="mobileServicesPanel" hidden>
                <a href="{{ route('services.index', ['category' => 'gedung-venue']) }}" class="pestago-mobile-service-item {{ request('category') === 'gedung-venue' ? 'active' : '' }}">
                    <i class="bi bi-building"></i><span>Gedung &amp; Venue</span>
                </a>
                <a href="{{ route('services.index', ['category' => 'catering']) }}" class="pestago-mobile-service-item {{ request('category') === 'catering' ? 'active' : '' }}">
                    <i class="bi bi-cup-hot"></i><span>Catering &amp; Kuliner</span>
                </a>
                <a href="{{ route('services.index', ['category' => 'dekorasi-makeup']) }}" class="pestago-mobile-service-item {{ request('category') === 'dekorasi-makeup' ? 'active' : '' }}">
                    <i class="bi bi-flower1"></i><span>Dekorasi &amp; Makeup</span>
                </a>
                <a href="{{ route('services.index', ['category' => 'dokumentasi']) }}" class="pestago-mobile-service-item {{ request('category') === 'dokumentasi' ? 'active' : '' }}">
                    <i class="bi bi-camera"></i><span>Dokumentasi</span>
                </a>
                <a href="{{ route('services.index', ['category' => 'hiburan-entertainment']) }}" class="pestago-mobile-service-item {{ request('category') === 'hiburan-entertainment' ? 'active' : '' }}">
                    <i class="bi bi-music-note-beamed"></i><span>Hiburan &amp; Musik</span>
                </a>
                <a href="{{ route('services.index', ['category' => 'busana-aksesoris']) }}" class="pestago-mobile-service-item {{ request('category') === 'busana-aksesoris' ? 'active' : '' }}">
                    <i class="bi bi-gem"></i><span>Busana &amp; Aksesoris</span>
                </a>
                <a href="{{ route('services.index', ['category' => 'perlengkapan-acara']) }}" class="pestago-mobile-service-item {{ request('category') === 'perlengkapan-acara' ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i><span>Perlengkapan Acara</span>
                </a>
                <a href="{{ route('services.index', ['filter' => 'promo']) }}" class="pestago-mobile-service-item is-promo {{ request('filter') === 'promo' ? 'active' : '' }}">
                    <i class="bi bi-tag"></i><span>Promo &amp; Diskon</span>
                </a>
                <a href="{{ route('services.index') }}" class="pestago-mobile-service-all">Lihat semua layanan</a>
            </div>
        </header>

        {{-- Mobile Category Strip (hidden — digantikan row2) --}}
        @if(!request()->is('admin*'))
        <nav class="pestago-mobile-cats" id="mobileCatStrip" aria-label="Kategori Mobile">
            <a href="{{ route('services.index') }}" class="pestago-mobile-cat-link {{ request()->is('services') && !request('category') && !request('filter') ? 'active' : '' }}">SEMUA</a>
            <a href="{{ route('services.index', ['category' => 'gedung-venue']) }}" class="pestago-mobile-cat-link {{ request('category') === 'gedung-venue' ? 'active' : '' }}">VENUE</a>
            <a href="{{ route('services.index', ['category' => 'catering']) }}" class="pestago-mobile-cat-link {{ request('category') === 'catering' ? 'active' : '' }}">CATERING</a>
            <a href="{{ route('services.index', ['category' => 'dekorasi-makeup']) }}" class="pestago-mobile-cat-link {{ request('category') === 'dekorasi-makeup' ? 'active' : '' }}">DEKORASI</a>
            <a href="{{ route('services.index', ['category' => 'dokumentasi']) }}" class="pestago-mobile-cat-link {{ request('category') === 'dokumentasi' ? 'active' : '' }}">FOTO&amp;VIDEO</a>
            <a href="{{ route('services.index', ['filter' => 'promo']) }}" class="pestago-mobile-cat-link promo-link {{ request('filter') === 'promo' ? 'active' : '' }}">PROMO</a>
        </nav>
        @endif

        {{-- Slide-out Drawer --}}
        <div class="pestago-drawer-backdrop" id="pestagoDrawerBackdrop"></div>
        <div class="pestago-drawer" id="pestagoDrawer" aria-hidden="true">
            <div class="pestago-drawer-header">
                <a href="{{ route('home') }}" class="pestago-drawer-logo">
                    <img src="{{ asset('images/pgtam.png') }}" alt="PestaGo">
                </a>
                <button type="button" class="pestago-drawer-close" id="btnCloseDrawer" aria-label="Tutup Menu">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="pestago-drawer-body">
                {{-- 1. TOP SECTION: Foto Profil & Nama User (Hanya jika login, posisikan di paling atas di atas lokasi acara) --}}
                @auth
                @php
                    $uDrawer = auth()->user();
                    $unreadDrawer = $uDrawer->unreadMessagesCount();
                @endphp
                <div style="padding:12px 14px;background:#fafafa;border:1px solid #eeeeee;border-radius:0;display:flex;align-items:center;gap:12px;margin:4px 20px 12px;width:calc(100% - 40px);box-sizing:border-box;">
                    @if($uDrawer->avatar_url)
                        <img src="{{ $uDrawer->avatar_url }}" alt="{{ $uDrawer->name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #ffffff;box-shadow:0 1px 4px rgba(0,0,0,0.08);flex-shrink:0;">
                    @else
                        <div style="width:44px;height:44px;font-size:16px;background:#111111;color:#ffffff;display:flex;align-items:center;justify-content:center;font-weight:900;border-radius:50%;flex-shrink:0;letter-spacing:0.02em;">
                            {{ strtoupper(substr($uDrawer->name, 0, 1)) }}
                        </div>
                    @endif
                    <div style="overflow:hidden;line-height:1.25;flex:1;">
                        <div style="font-weight:800;font-size:14.5px;color:#111111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $uDrawer->name }}
                        </div>
                        <div style="font-size:10px;font-weight:800;letter-spacing:0.06em;color:#888888;text-transform:uppercase;margin-top:3px;">
                            @if($uDrawer->role === 'super_admin') Super Admin
                            @elseif($uDrawer->role === 'admin') Admin Staff
                            @elseif($uDrawer->role === 'vendor') Vendor
                            @else Customer @endif
                        </div>
                    </div>
                    <a href="{{ route('profile.edit') }}" title="Pengaturan Profil" style="color:#666666;font-size:16px;text-decoration:none;padding:6px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-gear"></i>
                    </a>
                </div>
                @endauth

                {{-- 2. Lokasi Acara (Di bawah profil) --}}
                <button type="button" class="pestago-drawer-location" id="emoxLocationBtn" title="Ubah lokasi acara" style="margin-bottom:12px;">
                    <i class="bi bi-geo-alt"></i>
                    <span>
                        <small>Lokasi acara</small>
                        <strong id="navActiveLocationText">Jakarta Selatan</strong>
                    </span>
                    <em>Ubah</em>
                </button>

                {{-- 3. Section LAYANAN (Posisikan di atas button Dashboard) --}}
                <div class="pestago-drawer-section" style="border-top:1px solid #eeeeee;padding-top:10px;">
                    <button type="button" id="btnToggleDrawerServices" style="display:flex;align-items:center;justify-content:space-between;width:100%;padding:6px 0 8px;background:none;border:none;cursor:pointer;font-family:inherit;text-align:left;">
                        <span class="pestago-drawer-section-title" style="margin:0;font-size:11px;font-weight:700;letter-spacing:0.08em;color:#888888;text-transform:uppercase;">LAYANAN</span>
                        <i class="bi bi-chevron-down" id="iconToggleDrawerServices" style="font-size:13px;color:#555555;transition:transform 0.25s ease;"></i>
                    </button>
                    <nav class="pestago-drawer-menu" id="drawerServicesMenu" style="display:none;margin-top:2px;">
                        <a href="{{ route('services.index', ['category' => 'gedung-venue']) }}" class="pestago-drawer-item">Gedung &amp; Venue</a>
                        <a href="{{ route('services.index', ['category' => 'catering']) }}" class="pestago-drawer-item">Catering &amp; Kuliner</a>
                        <a href="{{ route('services.index', ['category' => 'dekorasi-makeup']) }}" class="pestago-drawer-item">Dekorasi &amp; Makeup</a>
                        <a href="{{ route('services.index', ['category' => 'busana-aksesoris']) }}" class="pestago-drawer-item">Busana &amp; Aksesoris</a>
                        <a href="{{ route('services.index', ['category' => 'dokumentasi']) }}" class="pestago-drawer-item">Dokumentasi</a>
                        <a href="{{ route('services.index', ['category' => 'hiburan-entertainment']) }}" class="pestago-drawer-item">Hiburan &amp; Musik</a>
                        <a href="{{ route('services.index', ['category' => 'perlengkapan-acara']) }}" class="pestago-drawer-item">Perlengkapan Acara</a>
                        <a href="{{ route('services.index', ['filter' => 'promo']) }}" class="pestago-drawer-item is-promo">Promo &amp; Diskon</a>
                        <a href="{{ route('services.index') }}" class="pestago-drawer-item" style="font-weight:700;color:#111111;">Lihat Semua Layanan &rarr;</a>
                    </nav>
                </div>

                {{-- 4. Section PestaGo (Posisikan di atas button Dashboard) --}}
                <div class="pestago-drawer-section" style="margin-top:6px;padding-bottom:12px;border-bottom:1px solid #eeeeee;">
                    <p class="pestago-drawer-section-title">PestaGo</p>
                    <nav class="pestago-drawer-menu">
                        <a href="{{ route('how-it-works') }}" class="pestago-drawer-item {{ request()->routeIs('how-it-works') ? 'active' : '' }}">Cara Pemesanan</a>
                        <a href="{{ route('about') }}" class="pestago-drawer-item {{ request()->routeIs('about') ? 'active' : '' }}">Tentang kami</a>
                        <a href="{{ route('contact') }}" class="pestago-drawer-item {{ request()->routeIs('contact') ? 'active' : '' }}">Hubungi kami</a>
                        <a href="{{ route('register.vendor') }}" class="pestago-drawer-item {{ request()->routeIs('register.vendor') ? 'active' : '' }}">Jadi vendor</a>
                    </nav>
                </div>

                {{-- 5. Section Menu Dashboard & Akun (Di bawah Layanan & PestaGo) --}}
                @auth
                <div class="pestago-drawer-section" style="margin-top:12px;">
                    <p class="pestago-drawer-section-title">Aktivitas &amp; Dashboard</p>
                    <nav class="pestago-drawer-menu">
                        @if(in_array($uDrawer->role, ['super_admin', 'admin']))
                            <a href="{{ route('admin.dashboard') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-speedometer2" style="font-size:15px;color:#111111;"></i> Dashboard Admin</span>
                                <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                            </a>
                            <a href="{{ route('admin.chats.index') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-headset" style="font-size:15px;color:#ed1d24;"></i> Live Chat Support</span>
                                <span style="display:flex;align-items:center;gap:6px;">
                                    @if($unreadDrawer > 0)
                                        <span class="badge badge-danger" style="background:#D81A24;color:#ffffff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;">{{ $unreadDrawer }}</span>
                                    @endif
                                    <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                                </span>
                            </a>
                        @elseif($uDrawer->role === 'vendor')
                            <a href="{{ route('vendor.dashboard') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-speedometer2" style="font-size:15px;color:#111111;"></i> Dashboard Vendor</span>
                                <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                            </a>
                            <a href="{{ route('vendor.services.index') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-box-seam" style="font-size:15px;color:#111111;"></i> Layanan Saya</span>
                                <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                            </a>
                            <a href="{{ route('vendor.orders') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-bag-check" style="font-size:15px;color:#111111;"></i> Pesanan Masuk</span>
                                <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                            </a>
                            <a href="{{ route('chat.index') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-chat-dots" style="font-size:15px;color:#111111;"></i> Pesan Masuk</span>
                                <span style="display:flex;align-items:center;gap:6px;">
                                    @if($unreadDrawer > 0)
                                        <span class="badge badge-danger" style="background:#D81A24;color:#ffffff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;">{{ $unreadDrawer }}</span>
                                    @endif
                                    <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                                </span>
                            </a>
                            <form action="{{ route('chat.support') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="pestago-drawer-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;font-size:13.5px;font-weight:600;color:#111111;font-family:inherit;">
                                    <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-headset" style="font-size:15px;color:#ed1d24;"></i> Hubungi Customer Care</span>
                                    <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('customer.dashboard') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-speedometer2" style="font-size:15px;color:#111111;"></i> Dashboard</span>
                                <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                            </a>
                            <a href="{{ route('customer.wishlist') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-heart" style="font-size:15px;color:#111111;"></i> Favorit Saya</span>
                                <span style="display:flex;align-items:center;gap:6px;">
                                    @if(($wishlistNavCount ?? 0) > 0)
                                        <span class="badge" style="background:#D81A24;color:#ffffff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;">{{ $wishlistNavCount }}</span>
                                    @endif
                                    <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                                </span>
                            </a>
                            <a href="{{ route('customer.events.index') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-calendar-event" style="font-size:15px;color:#111111;"></i> Acara Saya</span>
                                <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                            </a>
                            <a href="{{ route('customer.orders.index') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-bag-check" style="font-size:15px;color:#111111;"></i> Pesanan Saya</span>
                                <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                            </a>
                            <a href="{{ route('chat.index') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-chat-dots" style="font-size:15px;color:#111111;"></i> Pesan Masuk</span>
                                <span style="display:flex;align-items:center;gap:6px;">
                                    @if($unreadDrawer > 0)
                                        <span class="badge badge-danger" style="background:#D81A24;color:#ffffff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;">{{ $unreadDrawer }}</span>
                                    @endif
                                    <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                                </span>
                            </a>
                            <form action="{{ route('chat.support') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="pestago-drawer-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;font-size:13.5px;font-weight:600;color:#111111;font-family:inherit;">
                                    <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-headset" style="font-size:15px;color:#ed1d24;"></i> Hubungi Customer Care</span>
                                    <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                                </button>
                            </form>
                        @endif

                        <div style="margin-top:6px;padding-top:2px;">
                            <a href="{{ route('profile.edit') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-person-gear" style="font-size:15px;color:#111111;"></i> Pengaturan Profil</span>
                                <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                            </a>
                            <a href="{{ route('notifications.index') }}" class="pestago-drawer-item" style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid #f5f5f5;text-decoration:none;font-size:13.5px;font-weight:600;color:#111111;">
                                <span style="display:flex;align-items:center;gap:10px;"><i class="bi bi-bell" style="font-size:15px;color:#111111;"></i> Notifikasi</span>
                                <i class="bi bi-chevron-right" style="font-size:10px;color:#cccccc;"></i>
                            </a>
                        </div>

                        <div style="margin-top:10px;padding-top:4px;">
                            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="pestago-drawer-item" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:10px;color:#ed1d24;font-weight:800;letter-spacing:0.06em;text-transform:uppercase;padding:11px 0;font-size:12px;font-family:inherit;">
                                    <i class="bi bi-box-arrow-right" style="font-size:14px;color:#ed1d24;"></i> Keluar dari Akun
                                </button>
                            </form>
                        </div>
                    </nav>
                </div>
                @endauth

                @guest
                {{-- 5. Section AKUN untuk Tamu (Posisikan di bawah PestaGo, Uniqlo Clean Style tanpa radius) --}}
                <div class="pestago-drawer-section" style="margin-top:14px;padding-top:14px;border-top:1px solid #eeeeee;">
                    <p class="pestago-drawer-section-title" style="margin-bottom:12px;font-size:11px;font-weight:700;letter-spacing:0.08em;color:#888888;text-transform:uppercase;">AKUN</p>
                    <div style="display:flex;gap:8px;">
                        <a href="{{ route('login') }}" class="pestago-drawer-cta" style="flex:1;">Masuk</a>
                        <a href="{{ route('register') }}" class="pestago-drawer-cta-ghost" style="flex:1;">Daftar</a>
                    </div>
                </div>
                @endguest
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if(session('success') || session('error') || session('warning') || session('info'))
    <div class="container" style="padding-top:1rem;">
        @foreach(['success','error','warning','info'] as $type)
            @if(session($type))
                <div class="alert alert-{{ $type }}">
                    <i class="bi bi-{{ $type === 'success' ? 'check-circle' : ($type === 'error' ? 'exclamation-triangle' : ($type === 'warning' ? 'exclamation-circle' : 'info-circle')) }}"></i>
                    {{ session($type) }}
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif
        @endforeach
    </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="container" style="padding-top:1rem;">
        <div class="alert alert-error">
            <i class="bi bi-exclamation-triangle"></i>
            <div>
                <strong>Terdapat kesalahan:</strong>
                <ul style="margin:0.5rem 0 0 1rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Content --}}
    <main id="mainContent">
        @yield('content')
    </main>

    {{-- Footer --}}
    @if(!request()->is('admin*') && !View::hasSection('hide_footer'))
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="{{ route('home') }}" class="footer-logo">
                        <img src="{{ asset('images/pgput.png') }}" alt="PestaGo" style="height:32px;object-fit:contain;">
                    </a>
                    <p class="footer-desc">Platform marketplace vendor acara terlengkap di Indonesia. Temukan dan pesan vendor terbaik untuk acara spesial Anda.</p>
                    <div class="footer-socials">
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-tiktok"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Layanan</h4>
                    <a href="{{ route('services.index', ['category' => 'gedung-venue']) }}">Gedung & Venue</a>
                    <a href="{{ route('services.index', ['category' => 'catering']) }}">Catering</a>
                    <a href="{{ route('services.index', ['category' => 'dekorasi-makeup']) }}">Dekorasi & Makeup</a>
                    <a href="{{ route('services.index', ['category' => 'dokumentasi']) }}">Dokumentasi</a>
                    <a href="{{ route('services.index', ['category' => 'hiburan-entertainment']) }}">Hiburan</a>
                </div>
                <div class="footer-col">
                    <h4>Perusahaan</h4>
                    <a href="{{ route('about') }}">Tentang Kami</a>
                    <a href="{{ route('contact') }}">Hubungi Kami</a>
                    <a href="#">Karir</a>
                    <a href="#">Blog</a>
                </div>
                <div class="footer-col">
                    <h4>Bantuan</h4>
                    <a href="{{ route('how-it-works') }}">Cara Pemesanan</a>
                    <a href="#">FAQ</a>
                    <a href="#">Kebijakan Privasi</a>
                    <a href="#">Syarat & Ketentuan</a>
                    <a href="{{ route('register.vendor') }}">Daftar Jadi Vendor</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} PestaGo. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>
    @endif

    @if(!request()->is('admin*'))
    {{-- MODAL PILIH LOKASI ACARA --}}
    <div id="modalSelectLocation" class="pestago-loc-modal-overlay" style="display: none;">
        <div class="pestago-loc-modal-dialog">
            <div class="pestago-loc-modal-header">
                <div>
                    <h3 class="pestago-loc-modal-title"><i class="bi bi-geo-alt-fill"></i> Atur Lokasi Acara Anda</h3>
                    <p class="pestago-loc-modal-desc">Geser pin di peta atau cari daerah untuk akurasi lokasi vendor terdekat</p>
                </div>
                <button type="button" class="btn-close-modal" id="btnCloseLocModal" aria-label="Tutup">&times;</button>
            </div>

            <div class="pestago-loc-modal-body">
                {{-- Banner Lokasi Terpilih --}}
                <div class="current-active-loc-box" id="currentActiveLocBox">
                    <div class="current-loc-top-row">
                        <span class="current-loc-label">Lokasi Terpilih</span>
                        <span class="badge-loc-type" id="modalLocTypeBadge"><i class="bi bi-shield-check"></i> Aktif</span>
                    </div>
                    <div class="current-loc-main-row">
                        <div class="current-loc-pin-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div class="current-loc-text">
                            <div class="current-loc-title" id="modalCurrentLocTitle">Memuat lokasi...</div>
                            <div class="current-loc-coords" id="modalCurrentCoords">Lat: -, Lng: -</div>
                        </div>
                    </div>
                </div>

                {{-- Input Pencarian Real-Time Autocomplete --}}
                <div class="loc-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="inputSearchLocCity" placeholder="Ketik nama gedung, jalan, kecamatan, atau kota..." autocomplete="off">
                    <div id="searchSuggestionsBox" class="search-suggestions-box" style="display: none;"></div>
                </div>

                {{-- Interactive Leaflet Pin Map --}}
                <div class="loc-map-wrapper">
                    <div class="loc-map-header">
                        <span><i class="bi bi-geo-alt-fill" style="color:#D81A24;"></i> Geser Pin ke Titik Acara:</span>
                        <button type="button" class="btn-map-gps-shortcut" id="btnDetectGps" title="Ambil titik GPS real-time dari browser">
                            <span id="gpsBtnIcon"><i class="bi bi-crosshair"></i></span>
                            <span id="gpsBtnLabel">Deteksi GPS Saya</span>
                        </button>
                    </div>
                    <div id="locPickerMap"></div>
                    <div class="loc-map-hint">
                        <i class="bi bi-info-circle"></i>
                        <span>Klik peta atau geser pin di atas untuk menetapkan titik lokasi secara presisi.</span>
                    </div>
                </div>

                {{-- Tombol Konfirmasi Lokasi --}}
                <button type="button" class="btn-confirm-loc" id="btnConfirmLoc">
                    <i class="bi bi-check-circle-fill"></i> Terapkan &amp; Gunakan Lokasi Ini
                </button>

                <div class="loc-divider"><span>atau pilih kota populer</span></div>

                {{-- Grid Kota Populer --}}
                <div class="loc-cities-grid" id="popularCitiesGrid">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    {{-- App JS --}}
    <script src="{{ asset('js/app.js') }}"></script>

    {{-- Accurate Geolocation & Real-Time Location Manager --}}
    <script>
    (function() {
        const POPULAR_INDONESIA_CITIES = [
            // Jabodetabek
            { name: "Jakarta Selatan", province: "DKI Jakarta", lat: -6.2615, lng: 106.8106 },
            { name: "Jakarta Pusat", province: "DKI Jakarta", lat: -6.1818, lng: 106.8223 },
            { name: "Jakarta Barat", province: "DKI Jakarta", lat: -6.1683, lng: 106.7589 },
            { name: "Jakarta Timur", province: "DKI Jakarta", lat: -6.2250, lng: 106.9004 },
            { name: "Jakarta Utara", province: "DKI Jakarta", lat: -6.1214, lng: 106.7741 },
            { name: "Tangerang", province: "Banten", lat: -6.1783, lng: 106.6319 },
            { name: "Tangerang Selatan", province: "Banten", lat: -6.2889, lng: 106.7179 },
            { name: "Bekasi", province: "Jawa Barat", lat: -6.2383, lng: 106.9756 },
            { name: "Depok", province: "Jawa Barat", lat: -6.4025, lng: 106.7942 },
            { name: "Bogor", province: "Jawa Barat", lat: -6.5971, lng: 106.8060 },
            // Jawa Barat
            { name: "Bandung", province: "Jawa Barat", lat: -6.9175, lng: 107.6191 },
            { name: "Cimahi", province: "Jawa Barat", lat: -6.8723, lng: 107.5422 },
            { name: "Cirebon", province: "Jawa Barat", lat: -6.7320, lng: 108.5523 },
            { name: "Karawang", province: "Jawa Barat", lat: -6.3051, lng: 107.3023 },
            // Jawa Tengah & DIY
            { name: "Semarang", province: "Jawa Tengah", lat: -6.9667, lng: 110.4167 },
            { name: "Solo (Surakarta)", province: "Jawa Tengah", lat: -7.5755, lng: 110.8243 },
            { name: "Yogyakarta", province: "DIY", lat: -7.7956, lng: 110.3695 },
            // Jawa Timur
            { name: "Surabaya", province: "Jawa Timur", lat: -7.2575, lng: 112.7521 },
            { name: "Malang", province: "Jawa Timur", lat: -7.9666, lng: 112.6326 },
            { name: "Sidoarjo", province: "Jawa Timur", lat: -7.4478, lng: 112.7183 },
            // Luar Jawa
            { name: "Denpasar (Bali)", province: "Bali", lat: -8.6705, lng: 115.2126 },
            { name: "Badung / Kuta", province: "Bali", lat: -8.7230, lng: 115.1725 },
            { name: "Medan", province: "Sumatera Utara", lat: 3.5952, lng: 98.6722 },
            { name: "Palembang", province: "Sumatera Selatan", lat: -2.9909, lng: 104.7565 },
            { name: "Makassar", province: "Sulawesi Selatan", lat: -5.1477, lng: 119.4327 }
        ];

        let locMap = null;
        let locMarker = null;
        let pendingLocation = null;
        let searchTimeout = null;

        // Haversine formula in KM
        window.calculatePestagoDistance = function(lat1, lon1, lat2, lon2) {
            if (lat1 === undefined || lon1 === undefined || lat2 === undefined || lon2 === undefined) return null;
            const R = 6371; // Earth radius in KM
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a =
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        };

        window.getPestagoLocation = function() {
            const city = localStorage.getItem('pestago_user_city') || "Bandung";
            const lat = parseFloat(localStorage.getItem('pestago_user_lat')) || -6.9175;
            const lng = parseFloat(localStorage.getItem('pestago_user_lng')) || 107.6191;
            const isGps = localStorage.getItem('pestago_is_gps') === 'true';
            const source = localStorage.getItem('pestago_loc_source') || 'default';
            return { city, lat, lng, isGps, source };
        };

        window.setPestagoLocation = function(city, lat, lng, isGps = false, source = 'manual') {
            localStorage.setItem('pestago_user_city', city);
            localStorage.setItem('pestago_user_lat', lat);
            localStorage.setItem('pestago_user_lng', lng);
            localStorage.setItem('pestago_is_gps', isGps ? 'true' : 'false');
            localStorage.setItem('pestago_loc_source', source);
            localStorage.setItem('pestago_loc_set', 'true');

            // Update navbar text
            const navText = document.getElementById('navActiveLocationText');
            if (navText) navText.textContent = city;

            // Update modal active banner
            updateModalActiveBanner(city, lat, lng, isGps);

            // Trigger distance recalculation on cards
            window.updatePestagoCardsDistance();

            // Dispatch global event
            window.dispatchEvent(new CustomEvent('pestagoLocationChanged', { detail: { city, lat, lng, isGps, source } }));
        };

        function updateModalActiveBanner(city, lat, lng, isGps) {
            const titleEl = document.getElementById('modalCurrentLocTitle');
            const coordsEl = document.getElementById('modalCurrentCoords');
            const badgeEl = document.getElementById('modalLocTypeBadge');

            if (titleEl) titleEl.textContent = city;
            if (coordsEl) coordsEl.textContent = `Lat: ${parseFloat(lat).toFixed(4)}, Lng: ${parseFloat(lng).toFixed(4)}`;
            if (badgeEl) {
                if (isGps) {
                    badgeEl.innerHTML = '<i class="bi bi-crosshair"></i> GPS Akurat';
                    badgeEl.style.background = '#dcfce7';
                    badgeEl.style.color = '#15803d';
                } else {
                    badgeEl.innerHTML = '<i class="bi bi-pin-map-fill"></i> Titik Terpilih';
                    badgeEl.style.background = '#fee2e2';
                    badgeEl.style.color = '#D81A24';
                }
            }
        }

        // Recalculate distance on any cards present on page
        window.updatePestagoCardsDistance = function() {
            const userLoc = window.getPestagoLocation();
            const cards = document.querySelectorAll('.service-card[data-lat][data-lng], .ecom-card[data-lat][data-lng]');

            cards.forEach(card => {
                const cardLat = parseFloat(card.dataset.lat);
                const cardLng = parseFloat(card.dataset.lng);

                if (!isNaN(cardLat) && !isNaN(cardLng)) {
                    const distKm = window.calculatePestagoDistance(userLoc.lat, userLoc.lng, cardLat, cardLng);
                    card.dataset.distance = distKm !== null ? distKm.toFixed(2) : '9999';

                    const badge = card.querySelector('.service-dist-badge, .ecom-card-dist');
                    if (badge && distKm !== null) {
                        const distNumEl = badge.querySelector('.dist-num');
                        if (distNumEl) {
                            distNumEl.textContent = distKm < 1 ? (distKm * 1000).toFixed(0) + ' m' : distKm.toFixed(1) + ' km';
                        }
                        badge.style.display = 'inline-flex';
                    }
                }
            });

            // Automatically sort nearestServiceGrid ascending by distance
            const nearestGrid = document.getElementById('nearestServiceGrid');
            if (nearestGrid) {
                const nCards = Array.from(nearestGrid.querySelectorAll('.ecom-card'));
                nCards.sort((a, b) => {
                    const dA = parseFloat(a.dataset.distance) || 9999;
                    const dB = parseFloat(b.dataset.distance) || 9999;
                    return dA - dB;
                });
                nCards.forEach(c => nearestGrid.appendChild(c));
            }
        };

        // Real Reverse Geocoding with fallback (Nominatim + BigDataCloud)
        async function getRealLocationName(lat, lng) {
            // 1. Try OpenStreetMap Nominatim for street & subdistrict precision
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=id`);
                if (res.ok) {
                    const data = await res.json();
                    const addr = data.address || {};
                    const road = addr.road || addr.building || addr.amenity || '';
                    const sub = addr.suburb || addr.village || addr.quarter || addr.city_district || '';
                    const city = addr.city || addr.town || addr.county || addr.state || '';
                    const cleanCity = city.replace(/^Kota\s+|^Kabupaten\s+/i, '');

                    if (road && sub && cleanCity) {
                        return `${road}, ${sub}, ${cleanCity}`;
                    }
                    if (sub && cleanCity && sub !== cleanCity) {
                        return `${sub}, ${cleanCity}`;
                    }
                    if (cleanCity) return cleanCity;
                    if (sub) return sub;
                    if (data.display_name) {
                        return data.display_name.split(',').slice(0, 2).join(', ').trim();
                    }
                }
            } catch (e) {
                console.warn('Nominatim reverse geocode error:', e);
            }

            // 2. Try BigDataCloud
            try {
                const res2 = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=id`);
                if (res2.ok) {
                    const data2 = await res2.json();
                    const locality = data2.locality || '';
                    const city = data2.city || data2.principalSubdivision || '';
                    if (locality && city && locality !== city) {
                        return `${locality}, ${city}`;
                    }
                    if (city) return city;
                    if (locality) return locality;
                }
            } catch (e2) {
                console.warn('BigDataCloud reverse geocode error:', e2);
            }

            // 3. Fallback to nearest major city
            let closest = POPULAR_INDONESIA_CITIES[0];
            let minDist = 999999;
            POPULAR_INDONESIA_CITIES.forEach(c => {
                const d = window.calculatePestagoDistance(lat, lng, c.lat, c.lng);
                if (d < minDist) {
                    minDist = d;
                    closest = c;
                }
            });
            return closest.name;
        }

        // Initialize or Update Leaflet Map in Modal
        function initOrUpdateLocMap(lat, lng) {
            if (!window.L) return;
            const mapContainer = document.getElementById('locPickerMap');
            if (!mapContainer) return;

            const customPinHtml = `
                <div style="
                    width: 30px;
                    height: 30px;
                    background: #D81A24;
                    border: 2px solid #ffffff;
                    border-radius: 50% 50% 50% 0;
                    transform: rotate(-45deg);
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: grab;
                ">
                    <i class="bi bi-geo-alt-fill" style="transform: rotate(45deg); color: #fff; font-size: 13px;"></i>
                </div>
            `;

            const pinIcon = L.divIcon({
                className: 'pestago-map-pin',
                html: customPinHtml,
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });

            if (!locMap) {
                locMap = L.map('locPickerMap', {
                    zoomControl: true,
                    scrollWheelZoom: true
                }).setView([lat, lng], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(locMap);

                locMarker = L.marker([lat, lng], {
                    draggable: true,
                    icon: pinIcon
                }).addTo(locMap);

                // Handle pin drag end
                locMarker.on('dragend', async function(e) {
                    const pos = e.target.getLatLng();
                    await onMapPinMoved(pos.lat, pos.lng);
                });

                // Handle click on map
                locMap.on('click', async function(e) {
                    locMarker.setLatLng(e.latlng);
                    await onMapPinMoved(e.latlng.lat, e.latlng.lng);
                });
            } else {
                locMap.setView([lat, lng], 14);
                if (locMarker) locMarker.setLatLng([lat, lng]);
            }

            setTimeout(() => {
                if (locMap) locMap.invalidateSize();
            }, 250);
        }

        // When pin is moved or map is clicked
        async function onMapPinMoved(lat, lng) {
            const titleEl = document.getElementById('modalCurrentLocTitle');
            const coordsEl = document.getElementById('modalCurrentCoords');
            const badgeEl = document.getElementById('modalLocTypeBadge');

            if (coordsEl) coordsEl.textContent = `Lat: ${parseFloat(lat).toFixed(4)}, Lng: ${parseFloat(lng).toFixed(4)}`;
            if (titleEl) titleEl.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" style="width:0.8rem;height:0.8rem;margin-right:6px;"></span> Mengambil alamat presisi...';

            const realName = await getRealLocationName(lat, lng);
            if (titleEl) titleEl.textContent = realName;
            if (badgeEl) {
                badgeEl.innerHTML = '<i class="bi bi-pin-map-fill"></i> Titik Terpilih';
                badgeEl.style.background = '#fee2e2';
                badgeEl.style.color = '#D81A24';
            }

            pendingLocation = {
                city: realName,
                lat: lat,
                lng: lng,
                isGps: false,
                source: 'manual'
            };
        }

        // Render cities list
        function renderCitiesList(filterQuery = '') {
            const grid = document.getElementById('popularCitiesGrid');
            if (!grid) return;
            const current = window.getPestagoLocation();
            const q = filterQuery.toLowerCase().trim();

            grid.innerHTML = '';
            const filtered = POPULAR_INDONESIA_CITIES.filter(c => 
                c.name.toLowerCase().includes(q) || c.province.toLowerCase().includes(q)
            );

            if (filtered.length === 0) {
                grid.innerHTML = '<div style="grid-column: span 2; text-align:center; padding:12px; color:#94a3b8; font-size:0.8rem;">Ketik alamat spesifik di kotak pencarian di atas</div>';
                return;
            }

            filtered.forEach(item => {
                const isSelected = item.name.toLowerCase() === current.city.toLowerCase();
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `loc-city-pill ${isSelected ? 'active' : ''}`;
                btn.innerHTML = `
                    <span><i class="bi bi-geo-alt"></i> ${item.name}</span>
                    <i class="bi ${isSelected ? 'bi-check-circle-fill' : 'bi-chevron-right'}"></i>
                `;
                btn.onclick = function() {
                    pendingLocation = {
                        city: item.name,
                        lat: item.lat,
                        lng: item.lng,
                        isGps: false,
                        source: 'manual'
                    };
                    updateModalActiveBanner(item.name, item.lat, item.lng, false);
                    if (locMap && locMarker) {
                        locMap.flyTo([item.lat, item.lng], 13, { duration: 0.8 });
                        locMarker.setLatLng([item.lat, item.lng]);
                    }
                };
                grid.appendChild(btn);
            });
        }

        // Live Search Autocomplete via Nominatim
        async function handleLiveSearch(query) {
            const suggestionsBox = document.getElementById('searchSuggestionsBox');
            if (!suggestionsBox) return;

            const q = query.trim();
            if (q.length < 3) {
                suggestionsBox.style.display = 'none';
                suggestionsBox.innerHTML = '';
                return;
            }

            suggestionsBox.style.display = 'block';
            suggestionsBox.innerHTML = '<div style="padding:12px; color:#94a3b8; font-size:0.8rem; text-align:center;"><span class="spinner-border spinner-border-sm" role="status" style="width:0.8rem;height:0.8rem;margin-right:6px;"></span> Mencari lokasi akurat...</div>';

            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&countrycodes=id&limit=6&accept-language=id`);
                if (!res.ok) throw new Error('Search failed');
                const results = await res.json();

                if (results.length === 0) {
                    suggestionsBox.innerHTML = '<div style="padding:12px; color:#94a3b8; font-size:0.8rem; text-align:center;">Tidak ada hasil ditemukan</div>';
                    return;
                }

                suggestionsBox.innerHTML = '';
                results.forEach(item => {
                    const lat = parseFloat(item.lat);
                    const lon = parseFloat(item.lon);
                    const parts = item.display_name.split(',');
                    const mainTitle = parts[0].trim();
                    const subTitle = parts.slice(1, 4).join(',').trim();

                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.innerHTML = `
                        <i class="bi bi-geo-alt-fill text-primary"></i>
                        <div style="flex:1;">
                            <span class="suggestion-item-main">${mainTitle}</span>
                            <span class="suggestion-item-sub">${subTitle}</span>
                        </div>
                    `;
                    div.onclick = function() {
                        const finalName = `${mainTitle}, ${parts[1] ? parts[1].trim() : ''}`.replace(/,\s*$/, '');
                        pendingLocation = {
                            city: finalName,
                            lat: lat,
                            lng: lon,
                            isGps: false,
                            source: 'manual'
                        };
                        updateModalActiveBanner(finalName, lat, lon, false);
                        suggestionsBox.style.display = 'none';

                        if (locMap && locMarker) {
                            locMap.flyTo([lat, lon], 15, { duration: 0.9 });
                            locMarker.setLatLng([lat, lon]);
                        }
                    };
                    suggestionsBox.appendChild(div);
                });
            } catch (err) {
                suggestionsBox.innerHTML = '<div style="padding:10px; color:#ef4444; font-size:0.78rem; text-align:center;">Gagal memuat hasil pencarian</div>';
            }
        }

        // Open & Close modal
        function openLocationModal() {
            const modal = document.getElementById('modalSelectLocation');
            if (modal) {
                const cur = window.getPestagoLocation();
                pendingLocation = { ...cur };
                updateModalActiveBanner(cur.city, cur.lat, cur.lng, cur.isGps);
                renderCitiesList();
                modal.style.display = 'flex';

                // Initialize or refresh map
                setTimeout(() => {
                    initOrUpdateLocMap(cur.lat, cur.lng);
                }, 100);

                const searchInput = document.getElementById('inputSearchLocCity');
                if (searchInput) {
                    searchInput.value = '';
                    const suggestionsBox = document.getElementById('searchSuggestionsBox');
                    if (suggestionsBox) suggestionsBox.style.display = 'none';
                }
            }
        }

        function closeLocationModal() {
            const modal = document.getElementById('modalSelectLocation');
            if (modal) modal.style.display = 'none';
            const suggestionsBox = document.getElementById('searchSuggestionsBox');
            if (suggestionsBox) suggestionsBox.style.display = 'none';
        }

        // GPS Geolocation Handler with High Accuracy
        function handleGpsDetection(manualClick = false) {
            const gpsLabel = document.getElementById('gpsBtnLabel');
            const gpsIcon = document.getElementById('gpsBtnIcon');
            if (!navigator.geolocation) {
                if (manualClick) alert('Browser Anda tidak mendukung Geolocation.');
                return;
            }

            if (gpsLabel) gpsLabel.textContent = 'Mendeteksi...';
            if (gpsIcon) gpsIcon.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" style="width:0.75rem;height:0.75rem;"></span>';

            navigator.geolocation.getCurrentPosition(
                async function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    const accuracy = Math.round(pos.coords.accuracy || 0);

                    if (gpsLabel) gpsLabel.textContent = `Akurasi ±${accuracy}m`;

                    // Real reverse geocode to get exact administrative area
                    const realName = await getRealLocationName(lat, lng);

                    pendingLocation = {
                        city: realName,
                        lat: lat,
                        lng: lng,
                        isGps: true,
                        source: 'gps'
                    };

                    updateModalActiveBanner(realName, lat, lng, true);

                    if (locMap && locMarker) {
                        locMap.flyTo([lat, lng], 16, { duration: 1.0 });
                        locMarker.setLatLng([lat, lng]);
                    }

                    if (gpsLabel) gpsLabel.textContent = 'GPS Terdeteksi';
                    if (gpsIcon) gpsIcon.innerHTML = '<i class="bi bi-check-lg"></i>';

                    setTimeout(() => {
                        if (gpsLabel) gpsLabel.textContent = 'Deteksi GPS Saya';
                        if (gpsIcon) gpsIcon.innerHTML = '<i class="bi bi-crosshair"></i>';
                    }, 2500);

                    // If automatically triggered on first visit, apply directly
                    if (!manualClick) {
                        window.setPestagoLocation(realName, lat, lng, true, 'gps');
                    }
                },
                function(err) {
                    if (gpsIcon) gpsIcon.innerHTML = '<i class="bi bi-exclamation-triangle"></i>';
                    if (gpsLabel) {
                        if (err.code === 1) {
                            gpsLabel.textContent = 'Izin Diblokir';
                        } else {
                            gpsLabel.textContent = 'GPS Gagal';
                        }
                    }
                    setTimeout(() => {
                        if (gpsLabel) gpsLabel.textContent = 'Deteksi GPS Saya';
                        if (gpsIcon) gpsIcon.innerHTML = '<i class="bi bi-crosshair"></i>';
                    }, 3000);
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial navbar text
            const saved = window.getPestagoLocation();
            const navText = document.getElementById('navActiveLocationText');
            if (navText) navText.textContent = saved.city;

            // Recalculate distances on load
            window.updatePestagoCardsDistance();

            // Navbar location button click
            const locBtn = document.getElementById('emoxLocationBtn');
            if (locBtn) locBtn.addEventListener('click', openLocationModal);

            // Close button
            const closeBtn = document.getElementById('btnCloseLocModal');
            if (closeBtn) closeBtn.addEventListener('click', closeLocationModal);

            // Modal overlay click outside
            const modal = document.getElementById('modalSelectLocation');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeLocationModal();
                });
            }

            // Confirm Location Button
            const btnConfirm = document.getElementById('btnConfirmLoc');
            if (btnConfirm) {
                btnConfirm.addEventListener('click', function() {
                    if (pendingLocation) {
                        window.setPestagoLocation(
                            pendingLocation.city,
                            pendingLocation.lat,
                            pendingLocation.lng,
                            pendingLocation.isGps,
                            pendingLocation.source || 'manual'
                        );
                    }
                    closeLocationModal();
                });
            }

            // GPS button in modal
            const gpsBtn = document.getElementById('btnDetectGps');
            if (gpsBtn) {
                gpsBtn.addEventListener('click', function() {
                    handleGpsDetection(true);
                });
            }

            // Search city input with live debounce
            const searchInput = document.getElementById('inputSearchLocCity');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    clearTimeout(searchTimeout);
                    const val = e.target.value;
                    renderCitiesList(val);
                    searchTimeout = setTimeout(() => {
                        handleLiveSearch(val);
                    }, 300);
                });
            }

            // If user hasn't explicitly set location manually, try GPS in background
            const locSource = localStorage.getItem('pestago_loc_source');
            if (!locSource || locSource === 'default') {
                setTimeout(() => handleGpsDetection(false), 800);
            }
        });
    })();

    // PestaGo Navbar Scroll Handler & Offcanvas Drawer Controls
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.getElementById('mainHeader');
            const isHomepage = navbar && navbar.classList.contains('navbar-transparent');

            const mobileCatStrip = document.getElementById('mobileCatStrip');
            let navSolid = !!(navbar && navbar.classList.contains('navbar-scrolled'));

            // Hysteresis: avoid flicker around the threshold (patah-patah)
            function handleNavScroll() {
                if (!navbar || !isHomepage) return;
                const y = window.scrollY || window.pageYOffset || 0;
                if (!navSolid && y > 64) {
                    navSolid = true;
                    navbar.classList.add('navbar-scrolled');
                    navbar.classList.remove('navbar-transparent');
                    if (mobileCatStrip) mobileCatStrip.classList.add('cats-solid');
                } else if (navSolid && y < 20) {
                    navSolid = false;
                    navbar.classList.remove('navbar-scrolled');
                    navbar.classList.add('navbar-transparent');
                    if (mobileCatStrip) mobileCatStrip.classList.remove('cats-solid');
                }
            }

            if (isHomepage) {
                let ticking = false;
                window.addEventListener('scroll', function() {
                    if (ticking) return;
                    ticking = true;
                    requestAnimationFrame(function() {
                        handleNavScroll();
                        ticking = false;
                    });
                }, { passive: true });
                handleNavScroll();
            }

            // Drawer Toggle
            const btnOpen = document.getElementById('btnOpenDrawer');
            const btnClose = document.getElementById('btnCloseDrawer');
            const drawer = document.getElementById('pestagoDrawer');
            const backdrop = document.getElementById('pestagoDrawerBackdrop');

            function openDrawer() {
                if (drawer && backdrop) {
                    drawer.classList.add('active');
                    backdrop.classList.add('active');
                    drawer.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeDrawer() {
                if (drawer && backdrop) {
                    drawer.classList.remove('active');
                    backdrop.classList.remove('active');
                    drawer.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }
            }

            if (btnOpen) btnOpen.addEventListener('click', openDrawer);
            if (btnClose) btnClose.addEventListener('click', closeDrawer);
            if (backdrop) backdrop.addEventListener('click', closeDrawer);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && drawer && drawer.classList.contains('active')) {
                    closeDrawer();
                }
            });

            // Admin Topbar Dropdown Toggle (jika di halaman admin)
            const adminUserBtn = document.querySelector('.admin-topbar #userDropdownBtn');
            const adminUserMenu = document.getElementById('userDropdownMenu');
            if (adminUserBtn && adminUserMenu) {
                adminUserBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    adminUserMenu.classList.toggle('show');
                });
                document.addEventListener('click', function() {
                    adminUserMenu.classList.remove('show');
                });
            }

            // Drawer Layanan Accordion Toggle (Ikon di samping teks Layanan)
            const btnToggleServices = document.getElementById('btnToggleDrawerServices');
            const drawerServicesMenu = document.getElementById('drawerServicesMenu');
            const iconToggleServices = document.getElementById('iconToggleDrawerServices');

            if (btnToggleServices && drawerServicesMenu) {
                btnToggleServices.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isHidden = drawerServicesMenu.style.display === 'none' || drawerServicesMenu.style.display === '';
                    if (isHidden) {
                        drawerServicesMenu.style.display = 'flex';
                        if (iconToggleServices) iconToggleServices.style.transform = 'rotate(180deg)';
                    } else {
                        drawerServicesMenu.style.display = 'none';
                        if (iconToggleServices) iconToggleServices.style.transform = 'rotate(0deg)';
                    }
                });
            }

            // Mega Dropdown "SEMUA LAYANAN" — hover desktop, click-outside close
            const megaBtn = document.getElementById('navAllServicesBtn');
            const megaMenu = document.getElementById('navMegaDropdown');
            if (megaBtn && megaMenu) {
                document.addEventListener('click', function(e) {
                    if (!megaMenu.contains(e.target) && !megaBtn.contains(e.target)) {
                        megaMenu.classList.remove('open');
                    }
                });
                megaBtn.addEventListener('click', function(e) {
                    if (window.innerWidth < 960) {
                        e.preventDefault();
                        megaMenu.classList.toggle('open');
                    }
                });
            }

            // Mobile SEMUA LAYANAN panel
            const mobileBtn = document.getElementById('mobileAllServicesBtn');
            const mobilePanel = document.getElementById('mobileServicesPanel');
            function closeMobileServices() {
                if (!mobilePanel || !mobileBtn) return;
                mobilePanel.classList.remove('open');
                mobilePanel.hidden = true;
                mobileBtn.classList.remove('is-open');
                mobileBtn.setAttribute('aria-expanded', 'false');
            }
            function toggleMobileServices(e) {
                if (!mobilePanel || !mobileBtn) return;
                e.preventDefault();
                e.stopPropagation();
                const willOpen = !mobilePanel.classList.contains('open');
                if (willOpen) {
                    mobilePanel.hidden = false;
                    mobilePanel.classList.add('open');
                    mobileBtn.classList.add('is-open');
                    mobileBtn.setAttribute('aria-expanded', 'true');
                } else {
                    closeMobileServices();
                }
            }
            if (mobileBtn && mobilePanel) {
                mobileBtn.addEventListener('click', toggleMobileServices);
                document.addEventListener('click', function(e) {
                    if (!mobilePanel.contains(e.target) && !mobileBtn.contains(e.target)) {
                        closeMobileServices();
                    }
                });
                window.addEventListener('scroll', closeMobileServices, { passive: true });
            }

            // Global Wishlist Interactive AJAX Toggle
            window.PESTAGO_USER_LOGGED_IN = {{ auth()->check() ? 'true' : 'false' }};
            window.PESTAGO_WISHLIST_TOGGLE_URL = "{{ route('wishlist.toggle') }}";
            window.PESTAGO_LOGIN_URL = "{{ route('login') }}";

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.ecom-card-wishlist');
                if (!btn) return;

                e.preventDefault();
                e.stopPropagation();

                if (!window.PESTAGO_USER_LOGGED_IN) {
                    window.location.href = window.PESTAGO_LOGIN_URL;
                    return;
                }

                let serviceId = btn.getAttribute('data-service-id');
                if (!serviceId) {
                    const card = btn.closest('.ecom-card');
                    if (card) {
                        serviceId = card.getAttribute('data-id');
                    }
                }

                if (!serviceId) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                btn.style.opacity = '0.5';

                fetch(window.PESTAGO_WISHLIST_TOGGLE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ service_id: serviceId })
                })
                .then(res => res.json())
                .then(data => {
                    btn.style.opacity = '1';
                    if (data.unauthenticated) {
                        window.location.href = data.redirect || window.PESTAGO_LOGIN_URL;
                        return;
                    }
                    if (data.success) {
                        // Update all heart buttons for this service on screen
                        const targetButtons = document.querySelectorAll(`.ecom-card[data-id="${serviceId}"] .ecom-card-wishlist, .ecom-card-wishlist[data-service-id="${serviceId}"]`);
                        targetButtons.forEach(b => {
                            const icon = b.querySelector('i');
                            if (data.is_favorite) {
                                b.classList.add('active');
                                if (icon) icon.className = 'bi bi-heart-fill';
                            } else {
                                b.classList.remove('active');
                                if (icon) icon.className = 'bi bi-heart';
                            }
                        });

                        // Update navbar wishlist badges
                        document.querySelectorAll('.pestago-wishlist-badge').forEach(badge => {
                            badge.textContent = data.count;
                            badge.style.display = data.count > 0 ? '' : 'none';
                        });
                    }
                })
                .catch(err => {
                    btn.style.opacity = '1';
                    console.error('Error toggling wishlist:', err);
                });
            });
        });
    })();
    </script>
    @stack('scripts')
    @if(request()->is('admin*') && auth()->check())
        @include('admin._live_notifications')
    @endif
</body>
</html>
