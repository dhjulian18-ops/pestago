@extends('layouts.app')
@section('title', 'Invoice ' . $order->invoice_number . ' — PestaGo')

@section('content')
<div style="padding-top:calc(var(--navbar-height, 128px) + 24px);padding-bottom:60px;">
    <div class="container" style="max-width:800px;margin:0 auto;">

        {{-- Action Bar --}}
        <div class="uq-inv-actions" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <a href="{{ route('customer.orders.show', $order) }}" style="font-size:0.85rem;color:#767676;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Kembali ke Detail Pesanan
            </a>
            <button onclick="window.print()" class="uq-inv-print-btn">
                <i class="bi bi-printer"></i> Cetak Invoice
            </button>
        </div>

        {{-- Invoice Card --}}
        <div class="uq-inv-card">
            {{-- Header --}}
            <div class="uq-inv-header">
                <div>
                    <img src="{{ asset('images/pgtam.png') }}" alt="PestaGo" style="height:30px;object-fit:contain;margin-bottom:8px;">
                    <div style="font-size:0.78rem;color:#767676;line-height:1.5;">
                        PestaGo — Platform Vendor Acara<br>
                        Indonesia
                    </div>
                </div>
                <div style="text-align:right;">
                    <h1 style="font-size:1.5rem;font-weight:800;color:#111111;margin:0 0 4px;letter-spacing:-0.02em;">INVOICE</h1>
                    <div style="font-size:0.88rem;font-weight:700;color:#D81A24;">{{ $order->invoice_number }}</div>
                    <div style="font-size:0.78rem;color:#767676;margin-top:4px;">
                        Tanggal: {{ $order->created_at->translatedFormat('d M Y') }}
                    </div>
                </div>
            </div>

            {{-- Customer & Event Info --}}
            <div class="uq-inv-info-grid">
                <div class="uq-inv-info-box">
                    <div class="uq-inv-info-label">Ditagihkan kepada</div>
                    <div class="uq-inv-info-value">{{ $order->user->name }}</div>
                    <div class="uq-inv-info-sub">{{ $order->user->email }}</div>
                    @if($order->user->phone)
                    <div class="uq-inv-info-sub">{{ $order->user->phone }}</div>
                    @endif
                </div>
                @if($order->event)
                <div class="uq-inv-info-box">
                    <div class="uq-inv-info-label">Acara</div>
                    <div class="uq-inv-info-value">{{ $order->event->name }}</div>
                    <div class="uq-inv-info-sub">{{ $order->event->event_type }} · {{ $order->event->event_date?->translatedFormat('d M Y') }}</div>
                    @if($order->event->location_city)
                    <div class="uq-inv-info-sub"><i class="bi bi-geo-alt"></i> {{ $order->event->location_city }}</div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Order Info Badge --}}
            <div class="uq-inv-meta-row">
                <div class="uq-inv-meta-item">
                    <span class="uq-inv-meta-label">No. Pesanan</span>
                    <span class="uq-inv-meta-value">{{ $order->order_number }}</span>
                </div>
                <div class="uq-inv-meta-item">
                    <span class="uq-inv-meta-label">Metode Bayar</span>
                    <span class="uq-inv-meta-value">{{ $order->payment_type === 'tunai' ? 'Tunai' : 'Non-Tunai' }}</span>
                </div>
                <div class="uq-inv-meta-item">
                    <span class="uq-inv-meta-label">Status</span>
                    <span class="uq-inv-meta-value">
                        @php
                            $statusLabels = [
                                'pending_payment' => 'Menunggu Pembayaran',
                                'payment_processing' => 'Verifikasi Pembayaran',
                                'paid' => 'Lunas',
                                'partially_confirmed' => 'Sebagian Dikonfirmasi',
                                'confirmed' => 'Dikonfirmasi',
                                'in_progress' => 'Berlangsung',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                'refunded' => 'Dikembalikan',
                            ];
                        @endphp
                        {{ $statusLabels[$order->status] ?? $order->status }}
                    </span>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="uq-inv-table-wrap">
                <table class="uq-inv-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Layanan</th>
                            <th>Vendor</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">Harga</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <strong>{{ $item->service_name_snapshot }}</strong>
                                @if($item->booking_date)
                                <div style="font-size:0.75rem;color:#888;">
                                    <i class="bi bi-calendar3"></i> {{ \Carbon\Carbon::parse($item->booking_date)->translatedFormat('d M Y') }}
                                </div>
                                @endif
                            </td>
                            <td>{{ $item->vendorProfile->business_name ?? '-' }}</td>
                            <td style="text-align:center;">{{ $item->quantity }}</td>
                            <td style="text-align:right;">Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}</td>
                            <td style="text-align:right;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="uq-inv-totals">
                <div class="uq-inv-total-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="uq-inv-total-row">
                    <span>Biaya Platform ({{ $platformFeePercentage }}%)</span>
                    <span>Rp {{ number_format($order->platform_fee, 0, ',', '.') }}</span>
                </div>
                <div class="uq-inv-total-row uq-inv-grand-total">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="uq-inv-footer">
                <p>Terima kasih telah menggunakan PestaGo untuk acara Anda. Invoice ini digenerate secara otomatis dan sah tanpa tanda tangan.</p>
                <p style="margin-top:8px;font-weight:600;">PestaGo — Platform Vendor Acara #1 Indonesia</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('hide_footer', 'true')

@push('styles')
<style>
.uq-inv-print-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #111111;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    font-family: inherit;
}
.uq-inv-print-btn:hover { background: #333333; }

.uq-inv-card {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    overflow: hidden;
}

.uq-inv-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 28px 28px 20px;
    border-bottom: 2px solid #111111;
}

.uq-inv-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    padding: 20px 28px;
}

.uq-inv-info-label {
    font-size: 0.7rem;
    font-weight: 700;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 6px;
}

.uq-inv-info-value {
    font-size: 0.95rem;
    font-weight: 700;
    color: #111111;
}

.uq-inv-info-sub {
    font-size: 0.8rem;
    color: #666666;
    margin-top: 2px;
}

.uq-inv-meta-row {
    display: flex;
    gap: 16px;
    padding: 12px 28px;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap;
}

.uq-inv-meta-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.uq-inv-meta-label {
    font-size: 0.7rem;
    color: #999999;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.04em;
}

.uq-inv-meta-value {
    font-size: 0.82rem;
    font-weight: 700;
    color: #111111;
}

.uq-inv-table-wrap {
    padding: 0 28px;
    overflow-x: auto;
}

.uq-inv-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.uq-inv-table thead th {
    padding: 12px 8px;
    font-size: 0.72rem;
    font-weight: 700;
    color: #888888;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid #e5e5e5;
    text-align: left;
}

.uq-inv-table tbody td {
    padding: 12px 8px;
    border-bottom: 1px solid #f0f0f0;
    color: #333333;
    vertical-align: top;
}

.uq-inv-totals {
    padding: 16px 28px 20px;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.uq-inv-total-row {
    display: flex;
    justify-content: space-between;
    width: 260px;
    font-size: 0.85rem;
    color: #555555;
}

.uq-inv-total-row span:last-child {
    font-weight: 600;
    color: #111111;
}

.uq-inv-grand-total {
    padding-top: 10px;
    margin-top: 4px;
    border-top: 2px solid #111111;
    font-size: 1rem;
    font-weight: 800;
}

.uq-inv-grand-total span:last-child {
    color: #D81A24;
    font-weight: 800;
}

.uq-inv-footer {
    padding: 20px 28px;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
    font-size: 0.78rem;
    color: #888888;
    text-align: center;
}

@media print {
    .uq-inv-actions, .pestago-navbar, .pestago-mobile-row2, .pestago-mobile-cats, .footer { display: none !important; }
    .uq-inv-card { border: none; box-shadow: none; }
    body { padding: 0; margin: 0; }
}

@media (max-width: 640px) {
    .uq-inv-header { flex-direction: column; gap: 16px; padding: 20px 16px 16px; }
    .uq-inv-header > div:last-child { text-align: left; }
    .uq-inv-info-grid { grid-template-columns: 1fr; gap: 16px; padding: 16px; }
    .uq-inv-meta-row { padding: 12px 16px; }
    .uq-inv-table-wrap { padding: 0 16px; }
    .uq-inv-totals { padding: 16px; align-items: stretch; }
    .uq-inv-total-row { width: 100%; }
    .uq-inv-footer { padding: 16px; }
}
</style>
@endpush
