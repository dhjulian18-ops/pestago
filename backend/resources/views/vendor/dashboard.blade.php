@extends('layouts.app')
@section('title', 'Dashboard Vendor — PestaGo')

@section('content')
<div class="dashboard">
    @include('vendor._sidebar', ['active' => 'dashboard'])

    <div class="dashboard-content">
        {{-- Uniqlo Minimal Vendor Header --}}
        <div class="uq-vendor-header">
            <div class="uq-vendor-meta">
                <span class="uq-vendor-badge-top">PANEL MITRA VENDOR PESTAGO</span>
                <div>
                    @php
                        $vstatus = [
                            'incomplete' => ['Persyaratan Belum Lengkap', '#92400e', '#fef3c7', '#f59e0b'],
                            'pending' => ['Menunggu Verifikasi', '#1e40af', '#dbeafe', '#3b82f6'],
                            'needs_revision' => ['Perlu Revisi Dokumen', '#9a3412', '#ffedd5', '#ea580c'],
                            'rejected' => ['Pendaftaran Ditolak', '#991b1b', '#fee2e2', '#dc2626'],
                            'active' => ['Aktif & Terverifikasi', '#166534', '#dcfce7', '#16a34a'],
                            'suspended' => ['Ditangguhkan', '#991b1b', '#fee2e2', '#dc2626'],
                        ];
                        $vs = $vstatus[$vendor->status] ?? [$vendor->status, '#374151', '#f3f4f6', '#9ca3af'];
                    @endphp
                    <span style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:800;color:{{ $vs[0] === 'Aktif & Terverifikasi' ? '#15803d' : $vs[1] }};background:{{ $vs[2] }};padding:3px 10px;border-radius:2px;letter-spacing:0.02em;">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $vs[3] }};"></span>
                        {{ $vs[0] }}
                    </span>
                </div>
            </div>

            <h1 class="uq-vendor-title">{{ $vendor->business_name ?? 'Vendor PestaGo' }}</h1>
            <p class="uq-vendor-subtitle">Ringkasan performa penjualan, pesanan masuk, dan layanan toko Anda</p>

            <div class="uq-vendor-tags">
                <span class="uq-vendor-tag">
                    <i class="bi bi-geo-alt" style="color:#d81a24;"></i> {{ $vendor->city ?? 'Indonesia' }}
                </span>
                <span class="uq-vendor-tag">
                    <i class="bi bi-tag"></i> {{ $vendor->category->name ?? 'Vendor Acara' }}
                </span>
                @if($vendor->status === 'active')
                    <a href="{{ route('services.index', ['q' => $vendor->business_name]) }}" target="_blank" class="uq-vendor-tag" style="color:#111111;font-weight:700;">
                        <i class="bi bi-box-arrow-up-right"></i> Lihat di Katalog Publik
                    </a>
                @endif
            </div>
        </div>

        {{-- Verification Status Callouts (Uniqlo Minimal Clean) --}}
        @if($vendor->status === 'incomplete')
        <div style="background:#ffffff;border:1px solid #e5e5e5;border-left:4px solid #111111;border-radius:2px;padding:18px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="flex:1;min-width:260px;">
                <div style="font-size:10px;font-weight:800;color:#888888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">
                    Verifikasi Usaha Diperlukan
                </div>
                <h3 style="font-size:15px;font-weight:800;color:#111111;margin:0 0 6px;">
                    Lengkapi Dokumen Verifikasi Toko Anda
                </h3>
                <p style="color:#666666;font-size:12.5px;margin:0;line-height:1.5;">
                    Fitur katalog layanan dan penerimaan booking masih terkunci. Silakan lengkapi alamat usaha, nomor rekening pencairan, dan foto KTP.
                </p>
            </div>
            <div>
                <a href="{{ route('vendor.verification') }}" class="uq-btn-primary" style="padding:9px 18px;font-size:12.5px;">
                    Lengkapi Sekarang <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        @elseif($vendor->status === 'needs_revision')
        <div style="background:#fffbeb;border:1px solid #fef3c7;border-left:4px solid #f59e0b;border-radius:2px;padding:18px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="flex:1;min-width:260px;">
                <div style="font-size:10px;font-weight:800;color:#b45309;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">
                    Perlu Revisi Dokumen
                </div>
                <h3 style="font-size:15px;font-weight:800;color:#92400e;margin:0 0 6px;">
                    Pengajuan Memerlukan Perbaikan Berkas
                </h3>
                <p style="color:#78350f;font-size:12.5px;margin:0;line-height:1.5;">
                    Catatan Tim Kurasi: <strong>"{{ $vendor->admin_notes ?: 'Ada berkas yang perlu diperbaiki / diunggah ulang.' }}"</strong>
                </p>
            </div>
            <div>
                <a href="{{ route('vendor.verification') }}" class="btn" style="background:#f59e0b;color:#ffffff;border:none;padding:9px 18px;font-size:12.5px;font-weight:800;border-radius:2px;">
                    Perbaiki Berkas <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        @elseif($vendor->status === 'pending')
        <div style="background:#f0f9ff;border:1px solid #e0f2fe;border-left:4px solid #0284c7;border-radius:2px;padding:18px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="flex:1;min-width:260px;">
                <div style="font-size:10px;font-weight:800;color:#0284c7;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">
                    Dalam Pemeriksaan
                </div>
                <h3 style="font-size:15px;font-weight:800;color:#0369a1;margin:0 0 6px;">
                    Berkas Sedang Diverifikasi oleh Tim Kurasi PestaGo
                </h3>
                <p style="color:#0c4a6e;font-size:12.5px;margin:0;line-height:1.5;">
                    Estimasi proses 1–3 hari kerja. Segera setelah disetujui, toko dan katalog layanan Anda akan langsung aktif menerima pesanan.
                </p>
            </div>
            <div>
                <a href="{{ route('vendor.verification') }}" class="uq-btn-secondary" style="padding:8px 16px;font-size:12px;">
                    Lihat Berkas Diajukan
                </a>
            </div>
        </div>
        @elseif($vendor->status === 'rejected')
        <div style="background:#fef2f2;border:1px solid #fee2e2;border-left:4px solid #dc2626;border-radius:2px;padding:18px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div style="flex:1;min-width:260px;">
                <div style="font-size:10px;font-weight:800;color:#dc2626;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:4px;">
                    Pendaftaran Ditolak
                </div>
                <h3 style="font-size:15px;font-weight:800;color:#991b1b;margin:0 0 6px;">
                    Pendaftaran Toko Belum Memenuhi Syarat
                </h3>
                <p style="color:#7f1d1d;font-size:12.5px;margin:0;line-height:1.5;">
                    Alasan: {{ $vendor->rejection_reason ?: ($vendor->admin_notes ?: 'Belum memenuhi standar kurasi mitra vendor PestaGo.') }}
                </p>
            </div>
            <div>
                <a href="https://wa.me/6281234567890?text=Halo%20Tim%20PestaGo%2C%20saya%20ingin%20konsultasi%20mengenai%20status%20verifikasi%20vendor%20{{ urlencode($vendor->business_name) }}" target="_blank" class="uq-btn-secondary" style="border-color:#dc2626;color:#dc2626 !important;padding:8px 16px;font-size:12px;">
                    Hubungi Customer Care
                </a>
            </div>
        </div>
        @endif

        {{-- 1. Financial & Rating Highlights --}}
        <div class="uq-vendor-highlights">
            {{-- Total Pendapatan --}}
            <a href="{{ route('vendor.revenue') }}" class="uq-vendor-highlight-card">
                <div>
                    <div class="uq-vendor-highlight-head">
                        <span class="uq-vendor-highlight-label">
                            <i class="bi bi-wallet2" style="color:#111111;font-size:14px;"></i> Total Pendapatan Bersih
                        </span>
                        <span style="font-size:11px;font-weight:700;color:#16a34a;background:#dcfce7;padding:2px 8px;border-radius:2px;">
                            Pesanan Selesai
                        </span>
                    </div>
                    <div class="uq-vendor-highlight-val">
                        Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="uq-vendor-highlight-foot">
                    <span>Dana dari seluruh pesanan sukses</span>
                    <span class="uq-vendor-highlight-link">Rincian Saldo &rarr;</span>
                </div>
            </a>

            {{-- Rating & Reputasi --}}
            <a href="{{ route('vendor.reviews') }}" class="uq-vendor-highlight-card">
                <div>
                    <div class="uq-vendor-highlight-head">
                        <span class="uq-vendor-highlight-label">
                            <i class="bi bi-star-fill" style="color:#f59e0b;font-size:13px;"></i> Reputasi &amp; Ulasan
                        </span>
                        <span style="font-size:11px;font-weight:700;color:#111111;background:#f5f5f5;padding:2px 8px;border-radius:2px;">
                            Skor Mitra
                        </span>
                    </div>
                    <div class="uq-vendor-highlight-val">
                        {{ number_format($stats['rating_avg'], 1) }}
                        <span style="font-size:14px;font-weight:600;color:#888888;">/ 5.0</span>
                    </div>
                </div>
                <div class="uq-vendor-highlight-foot">
                    <span>Penilaian kepuasan pelanggan</span>
                    <span class="uq-vendor-highlight-link">Lihat Ulasan &rarr;</span>
                </div>
            </a>
        </div>

        {{-- 2. Operational Metrics Grid (4 items on desktop, 2x2 on mobile) --}}
        <div class="uq-vendor-stats-grid">
            {{-- Pesanan Baru --}}
            <a href="{{ route('vendor.orders', ['status' => 'pending_confirmation']) }}" class="uq-vendor-stat-card">
                <div class="uq-vendor-stat-head">
                    <span class="uq-vendor-stat-label">Pesanan Baru</span>
                    <i class="bi bi-bell uq-vendor-stat-icon" style="{{ $stats['pending_orders'] > 0 ? 'color:#D81A24;' : '' }}"></i>
                </div>
                <div style="display:flex;align-items:baseline;justify-content:space-between;gap:8px;">
                    <div class="uq-vendor-stat-val" style="{{ $stats['pending_orders'] > 0 ? 'color:#D81A24;' : '' }}">
                        {{ $stats['pending_orders'] }}
                    </div>
                    @if($stats['pending_orders'] > 0)
                        <span style="font-size:9.5px;font-weight:800;background:#D81A24;color:#ffffff;padding:1px 6px;letter-spacing:0.04em;">
                            RESPON
                        </span>
                    @endif
                </div>
            </a>

            {{-- Pesanan Aktif / Berlangsung --}}
            <a href="{{ route('vendor.orders', ['status' => 'in_progress']) }}" class="uq-vendor-stat-card">
                <div class="uq-vendor-stat-head">
                    <span class="uq-vendor-stat-label">Sedang Proses</span>
                    <i class="bi bi-hourglass-split uq-vendor-stat-icon"></i>
                </div>
                <div class="uq-vendor-stat-val">
                    {{ $stats['active_orders'] }}
                </div>
            </a>

            {{-- Pesanan Selesai --}}
            <a href="{{ route('vendor.orders', ['status' => 'completed']) }}" class="uq-vendor-stat-card">
                <div class="uq-vendor-stat-head">
                    <span class="uq-vendor-stat-label">Pesanan Selesai</span>
                    <i class="bi bi-check2-circle uq-vendor-stat-icon"></i>
                </div>
                <div class="uq-vendor-stat-val">
                    {{ $stats['completed_orders'] }}
                </div>
            </a>

            {{-- Layanan Aktif --}}
            <a href="{{ route('vendor.services.index') }}" class="uq-vendor-stat-card">
                <div class="uq-vendor-stat-head">
                    <span class="uq-vendor-stat-label">Layanan Aktif</span>
                    <i class="bi bi-box-seam uq-vendor-stat-icon"></i>
                </div>
                <div class="uq-vendor-stat-val">
                    {{ $stats['published_services'] }}
                    <span style="font-size:12px;font-weight:600;color:#888888;">/ {{ $stats['total_services'] }}</span>
                </div>
            </a>
        </div>

        {{-- 3. Quick Actions Toolbar --}}
        @if($vendor->status === 'active')
        @php
            $unreadVendorChats = auth()->user()?->unreadMessagesCount() ?? 0;
        @endphp
        <div class="uq-vendor-quick-actions">
            <a href="{{ route('vendor.services.create') }}" class="uq-btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Layanan Baru
            </a>
            <div class="uq-vendor-quick-row">
                <a href="{{ route('vendor.orders') }}" class="uq-btn-secondary" style="position:relative;">
                    <i class="bi bi-bag-check"></i> Kelola Pesanan
                    @if($stats['pending_orders'] > 0)
                        <span style="font-size:9.5px;font-weight:800;background:#D81A24;color:#ffffff;padding:1px 6px;border-radius:10px;">{{ $stats['pending_orders'] }}</span>
                    @endif
                </a>
                <a href="{{ route('vendor.calendar') }}" class="uq-btn-secondary">
                    <i class="bi bi-calendar3"></i> Kalender
                </a>
                <a href="{{ route('vendor.advertisements.index') }}" class="uq-btn-secondary">
                    <i class="bi bi-megaphone"></i> Iklan &amp; Promosi
                </a>
                <a href="{{ route('chat.index') }}" class="uq-btn-secondary" style="position:relative;">
                    <i class="bi bi-chat-dots"></i> Chat
                    @if($unreadVendorChats > 0)
                        <span style="font-size:9.5px;font-weight:800;background:#D81A24;color:#ffffff;padding:1px 6px;border-radius:10px;">{{ $unreadVendorChats }}</span>
                    @endif
                </a>
            </div>
        </div>
        @endif

        {{-- 4. Pesanan Menunggu Konfirmasi (Pending Orders) --}}
        @if($pendingOrders->count() > 0)
        <div class="uq-section">
            <div class="uq-section-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <h2 class="uq-section-title">Pesanan Menunggu Konfirmasi</h2>
                    <span class="badge" style="background:#D81A24;color:#ffffff;font-size:10.5px;font-weight:800;padding:2px 8px;border-radius:2px;">
                        {{ $pendingOrders->count() }} Perlu Respon
                    </span>
                </div>
            </div>

            {{-- Desktop Table --}}
            <div class="uq-desktop-table">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Pelanggan &amp; No. Pesanan</th>
                                <th>Layanan Dipesan</th>
                                <th>Tgl Acara</th>
                                <th>Subtotal</th>
                                <th style="text-align:right;">Keputusan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingOrders as $item)
                            <tr>
                                <td>
                                    <strong style="color:#111111;display:block;">{{ $item->order->user->name ?? '-' }}</strong>
                                    <span style="font-size:11.5px;color:#767676;">{{ $item->order->order_number }}</span>
                                </td>
                                <td>
                                    <span style="color:#111111;font-weight:700;">{{ $item->service_name_snapshot }}</span>
                                </td>
                                <td style="color:#555555;font-size:13px;">
                                    {{ $item->booking_date ? \Carbon\Carbon::parse($item->booking_date)->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td>
                                    <strong style="color:#111111;font-size:14px;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex;gap:6px;justify-content:flex-end;">
                                        <form action="{{ route('vendor.orders.accept', $item) }}" method="POST" style="margin:0;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm" style="background:#111111;color:#ffffff;border:1px solid #111111;font-weight:800;font-size:12px;padding:6px 14px;border-radius:2px;cursor:pointer;">
                                                <i class="bi bi-check-lg"></i> Terima
                                            </button>
                                        </form>
                                        <form action="{{ route('vendor.orders.reject', $item) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak pesanan ini?')" style="margin:0;">
                                            @csrf
                                            <input type="hidden" name="reason" value="Tidak tersedia pada tanggal tersebut">
                                            <button type="submit" class="btn btn-sm" style="background:#ffffff;color:#D81A24;border:1px solid #fee2e2;font-weight:700;font-size:12px;padding:6px 14px;border-radius:2px;cursor:pointer;">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Order Cards --}}
            <div class="uq-mobile-cards">
                @foreach($pendingOrders as $item)
                <div class="uq-order-card">
                    <div class="uq-order-card-header">
                        <span class="uq-order-card-num">#{{ $item->order->order_number }}</span>
                        <span class="uq-order-card-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="uq-order-card-body">
                        <div class="uq-order-card-row">
                            <i class="bi bi-person"></i>
                            <strong>{{ $item->order->user->name ?? '-' }}</strong>
                        </div>
                        <div class="uq-order-card-row">
                            <i class="bi bi-box-seam"></i>
                            <span>{{ $item->service_name_snapshot }}</span>
                        </div>
                        <div class="uq-order-card-row">
                            <i class="bi bi-calendar3"></i>
                            <span>Tgl Acara: {{ $item->booking_date ? \Carbon\Carbon::parse($item->booking_date)->translatedFormat('d M Y') : '-' }}</span>
                        </div>
                    </div>
                    <div class="uq-order-card-actions">
                        <form action="{{ route('vendor.orders.accept', $item) }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="uq-order-btn-accept">
                                <i class="bi bi-check-lg"></i> Terima
                            </button>
                        </form>
                        <form action="{{ route('vendor.orders.reject', $item) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak pesanan ini?')" style="margin:0;">
                            @csrf
                            <input type="hidden" name="reason" value="Tidak tersedia pada tanggal tersebut">
                            <button type="submit" class="uq-order-btn-reject">
                                Tolak
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 5. Riwayat Pesanan Terbaru (Recent Orders) --}}
        <div class="uq-section">
            <div class="uq-section-header">
                <h2 class="uq-section-title">Riwayat Pesanan Terbaru</h2>
                @if($recentOrders->count() > 0)
                <a href="{{ route('vendor.orders') }}" class="uq-section-link">
                    Lihat Semua Pesanan &rarr;
                </a>
                @endif
            </div>

            @if($recentOrders->count() > 0)
            {{-- Desktop Table --}}
            <div class="uq-desktop-table">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Layanan</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $item)
                            <tr>
                                <td style="font-weight:700;color:#111111;">{{ $item->order->order_number ?? '-' }}</td>
                                <td style="color:#333333;">{{ $item->order->user->name ?? '-' }}</td>
                                <td style="color:#111111;font-weight:600;">{{ $item->service_name_snapshot }}</td>
                                <td><strong style="color:#111111;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                                <td>
                                    @php
                                        $iStatusMap = [
                                            'pending_confirmation' => ['Menunggu', '#92400e', '#fef3c7'],
                                            'confirmed' => ['Dikonfirmasi', '#1e40af', '#dbeafe'],
                                            'rejected' => ['Ditolak', '#991b1b', '#fee2e2'],
                                            'in_progress' => ['Berlangsung', '#0369a1', '#e0f2fe'],
                                            'completed' => ['Selesai', '#15803d', '#dcfce7'],
                                            'cancelled' => ['Dibatalkan', '#991b1b', '#fee2e2'],
                                        ];
                                        $is = $iStatusMap[$item->status] ?? ['Unknown', '#374151', '#f3f4f6'];
                                    @endphp
                                    <span style="font-size:11px;font-weight:800;color:{{ $is[1] }};background:{{ $is[2] }};padding:3px 8px;border-radius:2px;">
                                        {{ $is[0] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Order Cards --}}
            <div class="uq-mobile-cards">
                @foreach($recentOrders as $item)
                @php
                    $iStatusMap = [
                        'pending_confirmation' => ['Menunggu', '#92400e', '#fef3c7'],
                        'confirmed' => ['Dikonfirmasi', '#1e40af', '#dbeafe'],
                        'rejected' => ['Ditolak', '#991b1b', '#fee2e2'],
                        'in_progress' => ['Berlangsung', '#0369a1', '#e0f2fe'],
                        'completed' => ['Selesai', '#15803d', '#dcfce7'],
                        'cancelled' => ['Dibatalkan', '#991b1b', '#fee2e2'],
                    ];
                    $is = $iStatusMap[$item->status] ?? ['Unknown', '#374151', '#f3f4f6'];
                @endphp
                <div class="uq-order-card">
                    <div class="uq-order-card-header">
                        <span class="uq-order-card-num">#{{ $item->order->order_number ?? '-' }}</span>
                        <span style="font-size:11px;font-weight:800;color:{{ $is[1] }};background:{{ $is[2] }};padding:2px 8px;border-radius:2px;">
                            {{ $is[0] }}
                        </span>
                    </div>
                    <div class="uq-order-card-body">
                        <div class="uq-order-card-row">
                            <i class="bi bi-person"></i>
                            <strong>{{ $item->order->user->name ?? '-' }}</strong>
                        </div>
                        <div class="uq-order-card-row">
                            <i class="bi bi-box-seam"></i>
                            <span>{{ $item->service_name_snapshot }}</span>
                        </div>
                        <div class="uq-order-card-row" style="justify-content:space-between;margin-top:4px;">
                            <span style="font-size:11.5px;color:#888888;">
                                <i class="bi bi-calendar3"></i> {{ $item->created_at?->translatedFormat('d M Y') ?? '-' }}
                            </span>
                            <span style="font-size:13.5px;font-weight:900;color:#111111;">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="uq-empty-box">
                <div class="uq-empty-icon"><i class="bi bi-clock-history"></i></div>
                <h3 class="uq-empty-title">Belum Ada Riwayat Pesanan</h3>
                <p class="uq-empty-desc">
                    Pesanan baru yang masuk dari pelanggan akan otomatis dicatat dan ditampilkan di sini.
                </p>
                @if($vendor->status === 'active')
                <a href="{{ route('vendor.services.create') }}" class="uq-btn-primary" style="display:inline-flex;width:auto;padding:10px 22px;">
                    <i class="bi bi-plus-lg"></i> Tambah Layanan Sekarang
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
