@extends('layouts.app')
@section('title', 'Pembayaran Berhasil — PestaGo')

@section('content')
<div class="uq-success-page">
    <div class="uq-success-container">
        {{-- Flow Stepper --}}
        <div class="uq-flow-stepper">
            <div class="uq-flow-step completed">
                <span class="uq-flow-step-num"><i class="bi bi-check"></i></span>
                <span>Pesanan</span>
            </div>
            <div class="uq-flow-line completed"></div>
            <div class="uq-flow-step completed">
                <span class="uq-flow-step-num"><i class="bi bi-check"></i></span>
                <span>Pembayaran</span>
            </div>
            <div class="uq-flow-line completed"></div>
            <div class="uq-flow-step active">
                <span class="uq-flow-step-num">3</span>
                <span>Selesai</span>
            </div>
        </div>

        {{-- Success Content --}}
        <div class="uq-success-content">
            <div class="uq-success-check">
                <i class="bi bi-check-lg"></i>
            </div>

            <h1 class="uq-success-title">Pembayaran Berhasil</h1>
            <p class="uq-success-subtitle">Terima kasih, pesanan Anda telah dikonfirmasi</p>

            {{-- Order Details --}}
            <div class="uq-success-details">
                <div class="uq-success-row">
                    <span>No. Pesanan</span>
                    <span>#{{ $order->order_number }}</span>
                </div>
                <div class="uq-success-row">
                    <span>Total Dibayar</span>
                    <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                @if($order->payment && $order->payment->payment_method)
                <div class="uq-success-row">
                    <span>Metode</span>
                    <span>{{ strtoupper($order->payment->payment_method) }}</span>
                </div>
                @endif
                @if($order->payment && $order->payment->paid_at)
                <div class="uq-success-row">
                    <span>Waktu Bayar</span>
                    <span>{{ $order->payment->paid_at->translatedFormat('d M Y, H:i') }} WIB</span>
                </div>
                @endif
                @if($order->event)
                <div class="uq-success-row">
                    <span>Acara</span>
                    <span>{{ $order->event->name }}</span>
                </div>
                @endif
            </div>

            {{-- Ordered Items --}}
            @if($order->items->count() > 0)
            <div style="text-align:left;margin-bottom:32px;">
                <div class="uq-section-label">Layanan Dipesan</div>
                @foreach($order->items as $item)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f5f5f5;font-size:13px;">
                    <div>
                        <div style="font-weight:600;color:#111111;">{{ $item->service_name_snapshot }}</div>
                        <div style="font-size:11px;color:#999999;margin-top:2px;">{{ $item->vendorProfile->business_name ?? '-' }}</div>
                    </div>
                    <div style="font-weight:700;color:#111111;white-space:nowrap;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Next Step Info --}}
            <div style="text-align:left;padding:16px;border:1px solid #e5e5e5;margin-bottom:32px;font-size:13px;color:#666666;line-height:1.6;">
                <strong style="color:#111111;display:block;margin-bottom:6px;">Langkah Selanjutnya</strong>
                Vendor akan mengkonfirmasi pesanan Anda dalam 1×24 jam. Anda bisa memantau status pesanan dan berkomunikasi langsung dengan vendor melalui fitur chat.
            </div>

            {{-- Actions --}}
            <div class="uq-success-actions">
                <a href="{{ route('customer.orders.show', $order) }}" class="uq-btn-submit">
                    Lihat Detail Pesanan
                </a>
                <a href="{{ route('services.index') }}" class="uq-btn-outline-dark">
                    Cari Layanan Lainnya
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
