@extends('layouts.app')
@section('title', 'Admin Dashboard — PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'dashboard'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
                <h1 class="dashboard-title"><i class="bi bi-shield-check" style="color:var(--primary);"></i> Admin Dashboard</h1>
                <p class="dashboard-subtitle">Kelola dan pantau seluruh operasional platform PestaGo</p>
            </div>
            <div style="display:flex;gap:10px;">
                <a href="{{ route('admin.maps.index') }}" class="btn btn-outline-primary" style="font-size:13px;padding:8px 16px;">
                    <i class="bi bi-geo-alt"></i> Peta Sebaran Vendor &amp; Layanan
                </a>
            </div>
        </div>

        {{-- Stats Grid (8 Kartu Lengkap & Profesional) --}}
        <div class="stats-grid" style="margin-bottom:var(--space-8);">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['total_customers'] }}</div>
                    <div class="stat-label">Customer Terdaftar</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pink"><i class="bi bi-shop"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['active_vendors'] }}/{{ $stats['total_vendors'] }}</div>
                    <div class="stat-label">Vendor Aktif</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['pending_vendors'] }}</div>
                    <div class="stat-label">Vendor Pending</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['published_services'] }}</div>
                    <div class="stat-label">Layanan Aktif</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-bag-check"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['total_orders'] }}</div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="stat-value">Rp {{ number_format($stats['platform_commission'], 0, ',', '.') }}</div>
                    <div class="stat-label">Komisi Platform</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-tag"></i></div>
                <div>
                    <div class="stat-value">{{ \App\Models\Promo::where('is_active', true)->count() }}</div>
                    <div class="stat-label">Promo Aktif</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="bi bi-megaphone"></i></div>
                <div>
                    <div class="stat-value">{{ \App\Models\Advertisement::where('review_status', 'approved')->count() }}</div>
                    <div class="stat-label">Iklan Berjalan</div>
                </div>
            </div>
            <div class="stat-card" style="border-left:3px solid #D81A24;">
                <div class="stat-icon red"><i class="bi bi-cash-stack" style="color:#D81A24;"></i></div>
                <div>
                    <div class="stat-value" style="color:#D81A24;">{{ $stats['pending_withdrawals'] ?? 0 }}</div>
                    <div class="stat-label">Pencairan Pending</div>
                </div>
            </div>
        </div>

        {{-- Permintaan Pencairan Saldo Vendor Menunggu Transfer Manual (Uniqlo Clean) --}}
        @if(isset($pendingWithdrawals) && $pendingWithdrawals->count() > 0)
        <div style="background:#ffffff;border:1px solid #e5e5e5;border-left:4px solid #D81A24;padding:20px 24px;margin-bottom:28px;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                <div>
                    <div style="font-size:11px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;color:#D81A24;display:flex;align-items:center;gap:6px;">
                        <i class="bi bi-exclamation-octagon-fill"></i> Perlu Tindakan Transfer Segera
                    </div>
                    <h2 style="font-size:16px;font-weight:900;color:#111111;margin:4px 0 0;letter-spacing:-0.01em;">
                        Permintaan Penarikan Saldo Vendor Menunggu Transfer Manual ({{ $pendingWithdrawals->count() }})
                    </h2>
                </div>
                <a href="{{ route('admin.wallets.index', ['status' => 'pending']) }}" style="font-size:12px;font-weight:800;color:#111111;text-decoration:underline;">
                    Kelola Semua di Menu Saldo →
                </a>
            </div>

            <div class="table-wrapper" style="border:1px solid #eeeeee;">
                <table class="table" style="margin:0;">
                    <thead>
                        <tr style="background:#fcfcfc;">
                            <th>Kode TRX / Waktu</th>
                            <th>Vendor &amp; Pemilik</th>
                            <th>Nominal Penarikan</th>
                            <th>Rekening Tujuan Transfer</th>
                            <th>Catatan</th>
                            <th style="text-align:right;">Aksi Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingWithdrawals as $txn)
                        @php
                            $vendor = $txn->wallet->vendorProfile;
                        @endphp
                        <tr>
                            <td>
                                <strong style="font-size:12px;color:#111111;">{{ $txn->transaction_number }}</strong>
                                <div style="font-size:11px;color:#888888;">{{ $txn->created_at->translatedFormat('d M Y, H:i') }}</div>
                            </td>
                            <td>
                                <strong style="color:#111111;font-size:13px;">{{ $vendor->business_name ?? '-' }}</strong>
                                <div style="font-size:11.5px;color:#666666;">{{ $vendor->user->name ?? '-' }}</div>
                                <div style="font-size:11px;color:#888888;">{{ $vendor->phone ?: $vendor->user->email }}</div>
                            </td>
                            <td>
                                <strong style="font-size:15px;font-weight:900;color:#D81A24;">Rp {{ number_format($txn->amount, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:6px 10px;font-size:12px;">
                                    <div style="font-weight:800;color:#0f172a;text-transform:uppercase;display:flex;align-items:center;justify-content:space-between;">
                                        <span>{{ $vendor->bank_name ?? 'BANK' }}</span>
                                        <button type="button" onclick="copyAccountNumber('{{ $vendor->bank_account_number }}', this)" style="background:none;border:none;color:#D81A24;font-size:10.5px;font-weight:700;cursor:pointer;padding:0;">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                    <div style="font-family:monospace;font-size:13px;font-weight:800;color:#D81A24;letter-spacing:0.5px;margin:2px 0;">
                                        {{ $vendor->bank_account_number ?? '-' }}
                                    </div>
                                    <div style="font-size:11px;color:#475569;">a/n {{ $vendor->bank_account_holder ?? '-' }}</div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size:12px;color:#555555;">{{ $txn->description }}</span>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex;gap:6px;">
                                    <button type="button" class="btn btn-sm" style="background:#16a34a;color:#ffffff;border:none;font-weight:800;font-size:11px;padding:6px 12px;border-radius:0;" onclick="openApproveModal({{ $txn->id }}, '{{ $txn->transaction_number }}', '{{ number_format($txn->amount, 0, ',', '.') }}', '{{ strtoupper($vendor->bank_name ?? '-') }} - {{ $vendor->bank_account_number ?? '-' }} a/n {{ $vendor->bank_account_holder ?? '-' }}')">
                                        <i class="bi bi-check-lg"></i> Konfirmasi Transfer
                                    </button>
                                    <button type="button" class="btn btn-sm" style="background:#ffffff;border:1px solid #dc2626;color:#dc2626;font-weight:800;font-size:11px;padding:6px 12px;border-radius:0;" onclick="openRejectModal({{ $txn->id }}, '{{ $txn->transaction_number }}', '{{ number_format($txn->amount, 0, ',', '.') }}')">
                                        <i class="bi bi-x-lg"></i> Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Pending Vendors --}}
        @if($pendingVendors->count() > 0)
        <div class="mb-8">
            <h2 style="font-size:var(--font-size-lg);font-weight:700;margin-bottom:var(--space-4);">
                <i class="bi bi-shield-exclamation text-primary"></i> Vendor Menunggu Verifikasi
            </h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr><th>Nama Usaha</th><th>Pemilik</th><th>Kategori</th><th>Kota</th><th>Tanggal Daftar</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($pendingVendors as $v)
                        <tr>
                            <td><strong>{{ $v->business_name }}</strong></td>
                            <td>{{ $v->user->name }}<br><span class="text-muted" style="font-size:var(--font-size-xs);">{{ $v->user->email }}</span></td>
                            <td>{{ $v->category->name ?? '-' }}</td>
                            <td>{{ $v->city }}</td>
                            <td>{{ $v->created_at->translatedFormat('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.vendors.show', $v) }}" class="btn btn-sm btn-primary">Review</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.vendors', ['status' => 'pending']) }}" class="btn btn-sm btn-ghost">Lihat Semua Pending →</a>
            </div>
        </div>
        @endif

        {{-- Recent Orders --}}
        <div>
            <h2 style="font-size:var(--font-size-lg);font-weight:700;margin-bottom:var(--space-4);">
                <i class="bi bi-clock-history text-primary"></i> Pesanan Terbaru
            </h2>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Pembayaran</th><th>Status</th><th>Tanggal</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr>
                            <td style="font-weight:600;">{{ $order->order_number }}</td>
                            <td>{{ $order->user->name ?? '-' }}</td>
                            <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $pStatus = [
                                        'pending' => ['Pending', 'warning'],
                                        'processing' => ['Processing', 'info'],
                                        'paid' => ['Paid', 'success'],
                                        'failed' => ['Failed', 'danger'],
                                    ];
                                    $ps = $pStatus[$order->payment->status ?? ''] ?? ['—', 'gray'];
                                @endphp
                                <span class="badge badge-{{ $ps[1] }}">{{ $ps[0] }}</span>
                            </td>
                            <td>
                                @php
                                    $oStatus = [
                                        'pending_payment' => ['Menunggu Bayar', 'warning'],
                                        'payment_processing' => ['Verifikasi', 'info'],
                                        'paid' => ['Dibayar', 'info'],
                                        'confirmed' => ['Dikonfirmasi', 'primary'],
                                        'completed' => ['Selesai', 'success'],
                                        'cancelled' => ['Batal', 'danger'],
                                    ];
                                    $os = $oStatus[$order->status] ?? [$order->status, 'gray'];
                                @endphp
                                <span class="badge badge-{{ $os[1] }}">{{ $os[0] }}</span>
                            </td>
                            <td>{{ $order->created_at->translatedFormat('d M Y') }}</td>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-ghost">Detail</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('admin.wallets._withdrawal_modals')
@endsection
