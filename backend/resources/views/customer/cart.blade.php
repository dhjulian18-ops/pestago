@extends('layouts.app')
@section('title', 'Keranjang — PestaGo')

@section('content')
<div class="dashboard">
    @include('customer._sidebar', ['active' => 'cart'])

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Keranjang Belanja</h1>
            <p class="dashboard-subtitle">Tinjau daftar layanan vendor yang Anda pilih sebelum melakukan pemesanan</p>
        </div>

        @php $allCarts = $carts->flatten(); @endphp

        @if($allCarts->count() > 0)

        {{-- Group by Event --}}
        @foreach($carts as $eventId => $eventCarts)
        @php
            $event = $eventCarts->first()->event;
            $eventTotal = $totals[$eventId] ?? 0;
        @endphp
        <div class="uq-cart-event-group">
            {{-- Event Header --}}
            <div class="uq-cart-event-header">
                <div class="uq-cart-event-info">
                    <i class="bi bi-calendar-event"></i>
                    <div>
                        <strong>{{ $event->name ?? 'Tanpa Acara' }}</strong>
                        <span>{{ $event ? $event->event_type . ' · ' . ($event->event_date ? $event->event_date->translatedFormat('d M Y') : '') : '' }}</span>
                    </div>
                </div>
                <div class="uq-cart-event-badge">
                    {{ $eventCarts->count() }} layanan
                </div>
            </div>

            {{-- Cart Items --}}
            <form id="checkoutForm-{{ $eventId }}" action="{{ route('customer.cart.checkout') }}" method="POST">
                @csrf
                <div class="uq-cart-items">
                    @foreach($eventCarts as $cart)
                    <div class="uq-cart-item">
                        <div class="uq-cart-item-check">
                            <input type="checkbox" name="cart_ids[]" value="{{ $cart->id }}" checked
                                   class="uq-checkbox">
                        </div>

                        <div class="uq-cart-item-img">
                            @if($cart->service->primaryImage)
                                <img src="{{ asset('storage/' . $cart->service->primaryImage->image_path) }}" alt="{{ $cart->service->name }}">
                            @else
                                <div class="uq-cart-img-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </div>

                        <div class="uq-cart-item-details">
                            <a href="{{ route('services.show', $cart->service->slug) }}" class="uq-cart-item-name">
                                {{ $cart->service->name }}
                            </a>
                            <div class="uq-cart-item-vendor">
                                {{ $cart->service->vendorProfile->business_name ?? '' }}
                            </div>
                            @if($cart->booking_date)
                            <div class="uq-cart-item-date">
                                <i class="bi bi-calendar3"></i> {{ \Carbon\Carbon::parse($cart->booking_date)->translatedFormat('d M Y') }}
                            </div>
                            @endif
                        </div>

                        <div class="uq-cart-item-price">
                            <div class="uq-cart-price-main">
                                Rp {{ number_format($cart->price_snapshot * $cart->quantity, 0, ',', '.') }}
                            </div>
                            <div class="uq-cart-price-unit">
                                {{ $cart->quantity }}× Rp {{ number_format($cart->price_snapshot, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="uq-cart-item-action">
                            <button type="submit" form="delete-cart-{{ $cart->id }}"
                                    class="uq-cart-delete-btn"
                                    title="Hapus dari keranjang"
                                    onclick="return confirm('Hapus layanan ini dari keranjang?')">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </form>

            {{-- Hidden Delete Forms --}}
            @foreach($eventCarts as $cart)
            <form id="delete-cart-{{ $cart->id }}" action="{{ route('customer.cart.remove', $cart) }}" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
            @endforeach

            {{-- Add More Services to this Event Action Bar --}}
            <div style="padding:14px 20px;background:#fcfcfc;border-top:1px dashed #e5e5e5;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div style="font-size:12.5px;color:#555555;">
                    <i class="bi bi-bag-plus text-primary"></i> Ingin menambah vendor lain (catering, dekorasi, hiburan) untuk acara ini?
                </div>
                <a href="{{ $event ? route('customer.events.show', $event) : route('services.index') }}" style="display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 16px;background:#ffffff;border:1px solid #111111;color:#111111;font-size:12px;font-weight:800;text-decoration:none;">
                    <i class="bi bi-plus-lg"></i> Tambah Layanan untuk Acara Ini
                </a>
            </div>

            {{-- Event Summary + Checkout Button --}}
            <div class="uq-cart-event-summary">
                <div class="uq-cart-summary-row">
                    <span>Subtotal ({{ $eventCarts->count() }} layanan)</span>
                    <span class="uq-cart-summary-value">Rp {{ number_format($eventTotal, 0, ',', '.') }}</span>
                </div>
                <div class="uq-cart-summary-row">
                    <span>Biaya Platform ({{ $platformFeePercentage }}%)</span>
                    <span class="uq-cart-summary-value">Rp {{ number_format($eventTotal * ($platformFeePercentage / 100), 0, ',', '.') }}</span>
                </div>
                <div class="uq-cart-summary-total">
                    <span>Total Bayar</span>
                    <span class="uq-cart-total-price">Rp {{ number_format($eventTotal * (1 + $platformFeePercentage / 100), 0, ',', '.') }}</span>
                </div>
                <button type="submit" form="checkoutForm-{{ $eventId }}" class="uq-cart-checkout-btn">
                    Lanjut ke Checkout
                </button>
                <a href="{{ $event ? route('customer.events.show', $event) : route('services.index') }}" style="display:block;text-align:center;height:42px;line-height:40px;border:1px solid #111111;background:#ffffff;color:#111111;font-size:12.5px;font-weight:800;text-decoration:none;margin-top:8px;">
                    + Cari & Tambah Vendor Lain untuk Acara Ini
                </a>
                <div class="uq-cart-secure-badge">
                    <i class="bi bi-shield-check"></i> Transaksi Aman & Terverifikasi
                </div>
            </div>
        </div>
        @endforeach

        @else
        {{-- Empty Cart --}}
        <div class="uq-cart-empty">
            <div class="uq-cart-empty-icon"><i class="bi bi-cart3"></i></div>
            <h3>Keranjang Masih Kosong</h3>
            <p>Belum ada layanan yang ditambahkan. Jelajahi vendor terbaik kami untuk kebutuhan acara Anda.</p>
            <a href="{{ route('services.index') }}" class="uq-cart-browse-btn">
                <i class="bi bi-search"></i> Cari Vendor
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
/* =============================================
   CART PAGE — UNIQLO STYLE, FULLY RESPONSIVE
   ============================================= */
.uq-cart-event-group {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 20px;
}

.uq-cart-event-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #fafafa;
    border-bottom: 1px solid #eeeeee;
    gap: 12px;
}

.uq-cart-event-info {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.uq-cart-event-info > i {
    font-size: 1.25rem;
    color: #D81A24;
    flex-shrink: 0;
}

.uq-cart-event-info > div {
    min-width: 0;
}

.uq-cart-event-info strong {
    display: block;
    font-size: 0.95rem;
    font-weight: 700;
    color: #111111;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.uq-cart-event-info span {
    display: block;
    font-size: 0.78rem;
    color: #767676;
    margin-top: 2px;
}

.uq-cart-event-badge {
    flex-shrink: 0;
    font-size: 0.75rem;
    font-weight: 600;
    color: #111111;
    background: #f0f0f0;
    padding: 4px 10px;
    border-radius: 4px;
}

/* Cart Items */
.uq-cart-items {
    display: flex;
    flex-direction: column;
}

.uq-cart-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.15s ease;
}

.uq-cart-item:last-child {
    border-bottom: none;
}

.uq-cart-item:hover {
    background: #fafafa;
}

.uq-cart-item-check {
    flex-shrink: 0;
}

.uq-checkbox {
    width: 18px;
    height: 18px;
    accent-color: #111111;
    cursor: pointer;
}

.uq-cart-item-img {
    width: 72px;
    height: 72px;
    border-radius: 4px;
    overflow: hidden;
    background: #f5f5f5;
    flex-shrink: 0;
    border: 1px solid #eeeeee;
}

.uq-cart-item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.uq-cart-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #aaaaaa;
    font-size: 1.25rem;
}

.uq-cart-item-details {
    flex: 1;
    min-width: 0;
}

.uq-cart-item-name {
    font-weight: 700;
    color: #111111;
    font-size: 0.92rem;
    text-decoration: none;
    display: block;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.uq-cart-item-name:hover {
    text-decoration: underline;
}

.uq-cart-item-vendor {
    font-size: 0.8rem;
    color: #666666;
    margin-bottom: 2px;
}

.uq-cart-item-date {
    font-size: 0.78rem;
    color: #767676;
}

.uq-cart-item-price {
    text-align: right;
    flex-shrink: 0;
}

.uq-cart-price-main {
    font-size: 0.95rem;
    font-weight: 800;
    color: #111111;
}

.uq-cart-price-unit {
    font-size: 0.75rem;
    color: #888888;
}

.uq-cart-item-action {
    flex-shrink: 0;
}

.uq-cart-delete-btn {
    border: none;
    background: none;
    color: #999999;
    font-size: 1.1rem;
    padding: 8px;
    cursor: pointer;
    border-radius: 4px;
    transition: color 0.2s, background 0.2s;
}

.uq-cart-delete-btn:hover {
    color: #D81A24;
    background: #fef2f2;
}

/* Event Summary */
.uq-cart-event-summary {
    padding: 20px;
    background: #fafafa;
    border-top: 1px solid #eeeeee;
}

.uq-cart-summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.86rem;
    color: #666666;
    margin-bottom: 8px;
}

.uq-cart-summary-value {
    font-weight: 600;
    color: #111111;
}

.uq-cart-summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    margin-top: 12px;
    border-top: 1px solid #dddddd;
    margin-bottom: 16px;
}

.uq-cart-summary-total > span:first-child {
    font-size: 0.88rem;
    font-weight: 700;
    color: #111111;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.uq-cart-total-price {
    font-size: 1.3rem;
    font-weight: 800;
    color: #D81A24;
}

.uq-cart-checkout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 48px;
    padding: 12px 24px;
    background: #111111;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s ease;
    letter-spacing: 0.02em;
}

.uq-cart-checkout-btn:hover {
    background: #333333;
}

.uq-cart-secure-badge {
    font-size: 0.75rem;
    color: #888888;
    text-align: center;
    margin-top: 10px;
}

.uq-cart-secure-badge i {
    color: #22c55e;
}

/* Empty Cart */
.uq-cart-empty {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    padding: 56px 24px;
    text-align: center;
    border-radius: 6px;
}

.uq-cart-empty-icon {
    font-size: 2.5rem;
    color: #888888;
    margin-bottom: 12px;
}

.uq-cart-empty h3 {
    font-size: 1.15rem;
    font-weight: 800;
    color: #111111;
    margin: 0 0 6px;
}

.uq-cart-empty p {
    font-size: 0.86rem;
    color: #666666;
    margin: 0 auto 18px;
    max-width: 380px;
}

.uq-cart-browse-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #111111;
    color: #ffffff;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    transition: background 0.2s ease;
}

.uq-cart-browse-btn:hover {
    background: #333333;
    color: #ffffff;
}

/* ===== RESPONSIVE MOBILE ===== */
@media (max-width: 640px) {
    .uq-cart-event-header {
        padding: 14px 16px;
    }

    .uq-cart-item {
        flex-wrap: wrap;
        gap: 12px;
        padding: 14px 16px;
    }

    .uq-cart-item-img {
        width: 56px;
        height: 56px;
    }

    .uq-cart-item-details {
        flex: 1;
        min-width: 0;
    }

    .uq-cart-item-name {
        font-size: 0.88rem;
        white-space: normal;
        overflow: visible;
    }

    .uq-cart-item-price {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-left: 30px; /* align with content after checkbox */
        text-align: left;
    }

    .uq-cart-price-main {
        font-size: 0.9rem;
    }

    .uq-cart-item-action {
        position: absolute;
        right: 16px;
        top: 14px;
    }

    .uq-cart-item {
        position: relative;
    }

    .uq-cart-event-summary {
        padding: 16px;
    }

    .uq-cart-total-price {
        font-size: 1.15rem;
    }
}
</style>
@endpush
