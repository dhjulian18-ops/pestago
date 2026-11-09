@extends('layouts.app')
@section('title', 'Dashboard Pelanggan — PestaGo')

@section('content')
<div class="dashboard">
    @include('customer._sidebar', ['active' => 'dashboard'])

    {{-- Content --}}
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Halo, {{ auth()->user()->name }}</h1>
            <p class="dashboard-subtitle">Ringkasan aktivitas acara dan pesanan Anda di PestaGo</p>
        </div>

        {{-- Uniqlo Minimal Stats Strip (1 Row, 3 Columns on Mobile & Desktop) --}}
        <div class="uq-stats-strip">
            <a href="{{ route('customer.events.index') }}" class="uq-stat-item">
                <span class="uq-stat-label"><i class="bi bi-calendar-event"></i> Total Acara</span>
                <span class="uq-stat-value">{{ $stats['total_events'] }}</span>
            </a>
            <a href="{{ route('customer.orders.index', ['status' => 'active']) }}" class="uq-stat-item">
                <span class="uq-stat-label"><i class="bi bi-hourglass-split"></i> Pesanan Aktif</span>
                <span class="uq-stat-value">{{ $stats['active_orders'] }}</span>
            </a>
            <a href="{{ route('customer.orders.index', ['status' => 'completed']) }}" class="uq-stat-item">
                <span class="uq-stat-label"><i class="bi bi-check2-circle"></i> Selesai</span>
                <span class="uq-stat-value">{{ $stats['completed_orders'] }}</span>
            </a>
        </div>

        {{-- Quick Action (Uniqlo Style) --}}
        <div class="uq-quick-actions">
            <a href="{{ route('customer.events.create') }}" class="uq-btn-primary" style="display:inline-flex;align-items:center;justify-content:center;gap:8px;">
                <i class="bi bi-plus-lg"></i> Buat Acara Baru
            </a>
        </div>

        {{-- Upcoming Events --}}
        @if($upcomingEvents->count() > 0)
        <div class="uq-section">
            <div class="uq-section-header">
                <h2 class="uq-section-title">Acara Mendatang</h2>
                <a href="{{ route('customer.events.index') }}" class="uq-section-link">Lihat Semua</a>
            </div>
            <div class="uq-events-grid">
                @foreach($upcomingEvents as $event)
                <a href="{{ route('customer.events.show', $event) }}" class="uq-event-card">
                    <div class="uq-event-top">
                        <div>
                            <div class="uq-event-type">{{ $event->event_type }}</div>
                            <div class="uq-event-name">{{ $event->name }}</div>
                        </div>
                        <span class="badge badge-primary" style="font-size:10px;border-radius:2px;">Mendatang</span>
                    </div>
                    <div class="uq-event-meta">
                        <div><i class="bi bi-calendar3"></i> {{ $event->event_date?->translatedFormat('d M Y') }}</div>
                        @if($event->location_city)
                        <div><i class="bi bi-geo-alt"></i> {{ $event->location_city }}</div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Recent Orders --}}
        <div class="uq-section">
            <div class="uq-section-header">
                <h2 class="uq-section-title">Pesanan Terbaru</h2>
                @if($recentOrders->count() > 0)
                <a href="{{ route('customer.orders.index') }}" class="uq-section-link">Semua Pesanan</a>
                @endif
            </div>

            @if($recentOrders->count() > 0)
            <div class="table-wrapper uq-table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th style="text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr>
                            <td style="font-weight:700;color:#111111;">{{ $order->order_number }}</td>
                            <td style="color:#666666;">{{ $order->created_at->translatedFormat('d M Y') }}</td>
                            <td><strong style="color:#111111;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                            <td>
                                @php
                                    $statusMap = [
                                        'pending_payment' => ['Menunggu Bayar', 'warning'],
                                        'payment_processing' => ['Verifikasi Bayar', 'info'],
                                        'paid' => ['Dibayar', 'info'],
                                        'partially_confirmed' => ['Sebagian Dikonfirmasi', 'info'],
                                        'confirmed' => ['Dikonfirmasi', 'primary'],
                                        'in_progress' => ['Berlangsung', 'info'],
                                        'completed' => ['Selesai', 'success'],
                                        'cancelled' => ['Dibatalkan', 'danger'],
                                    ];
                                    $s = $statusMap[$order->status] ?? [$order->status, 'gray'];
                                @endphp
                                <span class="badge badge-{{ $s[1] }}" style="border-radius:2px;">{{ $s[0] }}</span>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-ghost" style="border:1px solid #e5e5e5;color:#111111;background:#ffffff;border-radius:2px;font-weight:700;padding:4px 10px;">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="uq-empty-box">
                <div class="uq-empty-icon"><i class="bi bi-bag"></i></div>
                <h3 class="uq-empty-title">Belum Ada Pesanan</h3>
                <p class="uq-empty-desc">
                    Mulai cari vendor profesional untuk kebutuhan pesta atau acara Anda.
                </p>
                <a href="{{ route('services.index') }}" class="uq-btn-primary" style="display:inline-flex;width:auto;padding:10px 24px;">
                    Cari Vendor Sekarang
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
