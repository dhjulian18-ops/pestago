@extends('layouts.app')
@section('title', $event->name . ' — PestaGo')

@section('content')
<div class="uq-event-detail-page">
    <div class="container" style="max-width:1120px;padding:0 16px;">

        {{-- Breadcrumb Navigation --}}
        <nav class="uq-breadcrumb">
            <a href="{{ route('customer.events.index') }}">
                <i class="bi bi-arrow-left"></i> Daftar Acara
            </a>
            <span class="sep">/</span>
            <span class="current">{{ $event->name }}</span>
        </nav>

        {{-- Event Overview Header --}}
        <div class="uq-event-card">
            <div class="uq-event-card-top">
                <div class="uq-event-main-info">
                    <div class="uq-event-tag-row">
                        <span class="uq-event-type-badge">{{ strtoupper($event->event_type) }}</span>
                        @if($event->status === 'planning')
                            <span class="uq-status-pill planning">Dalam Perencanaan</span>
                        @elseif($event->status === 'confirmed')
                            <span class="uq-status-pill confirmed">Terkonfirmasi</span>
                        @else
                            <span class="uq-status-pill">{{ ucfirst($event->status) }}</span>
                        @endif
                    </div>
                    <h1 class="uq-event-title">{{ $event->name }}</h1>
                    <div class="uq-event-meta-list">
                        <span><i class="bi bi-calendar3"></i> {{ $event->event_date?->translatedFormat('d F Y') }}</span>
                        @if($event->event_time)
                            <span><i class="bi bi-clock"></i> {{ date('H:i', strtotime($event->event_time)) }} WIB</span>
                        @endif
                        @if($event->location_city)
                            <span><i class="bi bi-geo-alt"></i> {{ $event->location_city }}</span>
                        @endif
                        @if($event->guest_count)
                            <span><i class="bi bi-people"></i> {{ number_format($event->guest_count, 0, ',', '.') }} Tamu</span>
                        @endif
                    </div>
                </div>

                <div class="uq-event-actions">
                    <a href="{{ route('customer.events.edit', $event) }}" class="uq-btn-edit">
                        <i class="bi bi-pencil"></i> Edit Acara
                    </a>
                </div>
            </div>

            {{-- Metric Strip --}}
            <div class="uq-metrics-strip">
                <div class="uq-metric-item">
                    <span class="label">Target Budget</span>
                    <strong class="value">{{ $event->budget ? 'Rp ' . number_format($event->budget, 0, ',', '.') : 'Fleksibel' }}</strong>
                </div>
                <div class="uq-metric-item">
                    <span class="label">Lokasi</span>
                    <strong class="value text-truncate">{{ $event->location_city ?? ($event->location_address ?? '-') }}</strong>
                </div>
                <div class="uq-metric-item">
                    <span class="label">Vendor Dipilih</span>
                    @php
                        $cartCountForEvent = \App\Models\Cart::where('user_id', auth()->id())->where('event_id', $event->id)->count();
                        $bookedCountForEvent = $event->orders->flatMap->items->count();
                    @endphp
                    <strong class="value highlight">{{ $cartCountForEvent }} di Keranjang · {{ $bookedCountForEvent }} Dipesan</strong>
                </div>
            </div>
        </div>

        {{-- Sleek Step Progress Tracker --}}
        <div class="uq-stepper-box">
            <div class="uq-stepper-label">
                <span>Alur Pembuatan Acara</span>
                <span class="step-num">Langkah 2 dari 4</span>
            </div>
            <div class="uq-stepper-bar-wrap">
                <div class="uq-stepper-bar-fill" style="width: 50%;"></div>
            </div>
            <div class="uq-stepper-steps">
                <div class="uq-step done">
                    <span class="dot">✓</span>
                    <span class="text">1. Data Acara</span>
                </div>
                <div class="uq-step active">
                    <span class="dot">2</span>
                    <span class="text">2. Pilih Vendor</span>
                </div>
                <div class="uq-step">
                    <span class="dot">3</span>
                    <span class="text">3. Checkout</span>
                </div>
                <div class="uq-step">
                    <span class="dot">4</span>
                    <span class="text">4. Selesai</span>
                </div>
            </div>
        </div>

        {{-- Active Cart Floating Alert --}}
        @if($cartCountForEvent > 0)
        <div class="uq-cart-alert">
            <div class="uq-cart-alert-left">
                <div class="uq-cart-alert-icon">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div>
                    <strong>{{ $cartCountForEvent }} Layanan Terpilih untuk Acara Ini</strong>
                    <p>Anda bisa menambah vendor lain atau langsung melanjutkan ke checkout.</p>
                </div>
            </div>
            <a href="{{ route('customer.cart') }}" class="uq-btn-to-cart">
                Lihat Keranjang & Checkout <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        @endif

        {{-- Recommendations Header --}}
        <div class="uq-section-header">
            <div>
                <span class="uq-kicker">REKOMENDASI VENDOR</span>
                <h2 class="uq-section-title">Vendor Sesuai Pilihan Anda</h2>
            </div>
            <a href="{{ route('services.index', ['event_id' => $event->id]) }}" class="uq-link-all">
                Semua Vendor &rarr;
            </a>
        </div>

        {{-- Vendor Recommendations Grid --}}
        @php
            $cartServiceIds = \App\Models\Cart::where('user_id', auth()->id())->where('event_id', $event->id)->pluck('service_id')->toArray();
        @endphp

        @if($recommendations->count() > 0)
        <div class="uq-services-grid">
            @foreach($recommendations as $service)
            @php $isInCart = in_array($service->id, $cartServiceIds); @endphp
            <div class="uq-service-card {{ $isInCart ? 'is-selected' : '' }}">
                {{-- Media Thumbnail --}}
                <div class="uq-service-thumb">
                    @if($service->primaryImage)
                        <img src="{{ asset('storage/' . $service->primaryImage->image_path) }}" alt="{{ $service->name }}" loading="lazy">
                    @else
                        <div class="uq-thumb-placeholder">
                            <i class="bi bi-camera"></i>
                            <span>{{ $service->category->name ?? 'Layanan' }}</span>
                        </div>
                    @endif

                    @if($service->category)
                    <span class="uq-cat-badge">{{ $service->category->name }}</span>
                    @endif

                    <span class="uq-rating-pill">
                        <i class="bi bi-star-fill"></i> {{ number_format($service->rating_avg ?: 4.9, 1) }}
                    </span>
                </div>

                {{-- Card Content --}}
                <div class="uq-service-body">
                    <div class="uq-vendor-author">
                        <span class="uq-author-avatar">
                            {{ strtoupper(substr($service->vendorProfile->business_name ?? 'V', 0, 1)) }}
                        </span>
                        <span class="uq-author-name">{{ $service->vendorProfile->business_name }}</span>
                    </div>

                    <h3 class="uq-service-title">
                        <a href="{{ route('services.show', array_filter([$service->slug, 'event_id' => $event->id])) }}">
                            {{ $service->name }}
                        </a>
                    </h3>

                    <div class="uq-service-pricing">
                        <span class="price-label">Mulai dari</span>
                        <div class="price-val">Rp {{ number_format($service->price, 0, ',', '.') }}</div>
                    </div>

                    <div class="uq-card-actions">
                        <a href="{{ route('services.show', array_filter([$service->slug, 'event_id' => $event->id])) }}" class="uq-btn-detail">
                            Detail
                        </a>

                        @if($isInCart)
                            <div class="uq-btn-added">
                                <i class="bi bi-check2-circle"></i> Dipilih
                            </div>
                        @else
                            <form action="{{ route('customer.cart.add') }}" method="POST" style="margin:0;flex:1;">
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                <input type="hidden" name="event_id" value="{{ $event->id }}">
                                <input type="hidden" name="booking_date" value="{{ $event->event_date?->format('Y-m-d') }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="uq-btn-add">
                                    <i class="bi bi-plus-lg"></i> + Tambah
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Category Exploration Strip --}}
        <div class="uq-category-strip-box">
            <div class="uq-strip-title">
                <i class="bi bi-grid-fill"></i> Tambah Kebutuhan Vendor Lain untuk Acara Ini
            </div>
            <div class="uq-category-scroll">
                @php
                    $allCategories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
                @endphp
                @foreach($allCategories as $cat)
                    <a href="{{ route('services.index', ['category' => $cat->slug, 'event_id' => $event->id]) }}" class="uq-cat-chip">
                        <i class="bi bi-plus"></i> {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>

        @else
        <div class="uq-empty-box">
            <i class="bi bi-search"></i>
            <h3>Belum Ada Vendor Spesifik</h3>
            <p>Jelajahi vendor catering, dekorasi, dokumentasi, dan MUA di katalog utama kami.</p>
            <a href="{{ route('services.index', ['event_id' => $event->id]) }}" class="uq-btn-explore">
                Cari Vendor Sekarang
            </a>
        </div>
        @endif

        {{-- Booked Services (If Any) --}}
        @if($event->orders->count() > 0)
        <div class="uq-booked-orders-box">
            <div class="uq-booked-header">
                <h3><i class="bi bi-bag-check-fill text-primary"></i> Pesanan Aktif untuk Acara Ini</h3>
                <span class="count">{{ $event->orders->count() }} Pesanan</span>
            </div>

            <div class="uq-orders-list">
                @foreach($event->orders as $ord)
                <div class="uq-order-row">
                    <div class="uq-order-top">
                        <div>
                            <strong>Order #{{ $ord->order_number }}</strong>
                            <span class="date">{{ $ord->created_at->translatedFormat('d M Y, H:i') }}</span>
                            <span class="pay-badge {{ $ord->payment_type }}">
                                {{ $ord->payment_type === 'tunai' ? '💵 Tunai' : '💳 Escrow' }}
                            </span>
                        </div>
                        <div class="actions">
                            <a href="{{ route('customer.orders.invoice', $ord) }}" target="_blank" class="uq-btn-invoice">
                                <i class="bi bi-receipt"></i> Invoice
                            </a>
                            <a href="{{ route('customer.orders.show', $ord) }}" class="uq-btn-track">
                                Pantau Progres &rarr;
                            </a>
                        </div>
                    </div>

                    <div class="uq-order-items">
                        @foreach($ord->items as $it)
                        <div class="uq-item-sub">
                            <div>
                                <div class="item-name">{{ $it->service_name_snapshot }}</div>
                                <div class="item-vendor">Vendor: <strong>{{ $it->vendorProfile->business_name ?? '-' }}</strong> · Tgl: {{ $it->booking_date ? \Carbon\Carbon::parse($it->booking_date)->translatedFormat('d M Y') : '-' }}</div>
                            </div>
                            <div class="item-price">
                                Rp {{ number_format($it->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Bottom Footer Action --}}
        <div class="uq-bottom-bar">
            <a href="{{ route('customer.events.index') }}" class="uq-back-link">
                &larr; Kembali ke Daftar Acara
            </a>
            <div class="uq-bottom-btns">
                <a href="{{ route('services.index', ['event_id' => $event->id]) }}" class="uq-btn-cat-more">
                    <i class="bi bi-search"></i> Cari Kategori Lain
                </a>
                <a href="{{ route('customer.cart') }}" class="uq-btn-view-cart">
                    <i class="bi bi-cart"></i> Lihat Keranjang Saya &rarr;
                </a>
            </div>
        </div>

    </div>
</div>

@push('styles')
<style>
/* =============================================
   EVENT DETAIL & RECOMMENDATIONS — CLEAN MODERN
   ============================================= */
.uq-event-detail-page {
    padding-top: calc(var(--navbar-height, 128px) + 20px);
    padding-bottom: 80px;
    background: #f8fafc;
    min-height: 100vh;
}

.uq-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 16px;
}
.uq-breadcrumb a {
    color: #64748b;
    text-decoration: none;
    font-weight: 600;
}
.uq-breadcrumb a:hover {
    color: #0f172a;
}
.uq-breadcrumb .sep {
    color: #cbd5e1;
}
.uq-breadcrumb .current {
    color: #0f172a;
    font-weight: 700;
}

/* Event Overview Card */
.uq-event-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.uq-event-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
}
.uq-event-main-info {
    flex: 1;
    min-width: 240px;
}
.uq-event-tag-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.uq-event-type-badge {
    display: inline-block;
    padding: 3px 8px;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    background: #0f172a;
    color: #ffffff;
    border-radius: 4px;
}
.uq-status-pill {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #475569;
}
.uq-status-pill.planning {
    background: #eff6ff;
    color: #2563eb;
}
.uq-status-pill.confirmed {
    background: #ecfdf5;
    color: #059669;
}
.uq-event-title {
    font-size: 22px;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: -0.3px;
    margin: 0 0 8px;
}
.uq-event-meta-list {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 12.5px;
    color: #64748b;
    flex-wrap: wrap;
}
.uq-event-meta-list span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.uq-event-meta-list i {
    color: #0f172a;
}
.uq-btn-edit {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 36px;
    padding: 0 14px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #0f172a;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.15s;
}
.uq-btn-edit:hover {
    background: #f8fafc;
    border-color: #94a3b8;
}

/* Metric Strip */
.uq-metrics-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
}
.uq-metric-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
}
.uq-metric-item .label {
    display: block;
    font-size: 10.5px;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.5px;
}
.uq-metric-item .value {
    display: block;
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    margin-top: 2px;
}
.uq-metric-item .value.highlight {
    color: #2563eb;
}
.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Stepper */
.uq-stepper-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.uq-stepper-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.uq-stepper-label .step-num {
    color: #ef4444;
}
.uq-stepper-bar-wrap {
    height: 6px;
    background: #f1f5f9;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 12px;
}
.uq-stepper-bar-fill {
    height: 100%;
    background: #0f172a;
    border-radius: 3px;
}
.uq-stepper-steps {
    display: flex;
    justify-content: space-between;
    gap: 8px;
}
.uq-step {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    color: #94a3b8;
    font-weight: 600;
}
.uq-step.done {
    color: #059669;
}
.uq-step.done .dot {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}
.uq-step.active {
    color: #0f172a;
    font-weight: 800;
}
.uq-step.active .dot {
    background: #0f172a;
    color: #ffffff;
}
.uq-step .dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 800;
}

/* Floating Cart Alert */
.uq-cart-alert {
    background: #0f172a;
    color: #ffffff;
    border-radius: 10px;
    padding: 14px 20px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.uq-cart-alert-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.uq-cart-alert-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #ffffff;
    flex-shrink: 0;
}
.uq-cart-alert strong {
    display: block;
    font-size: 14px;
    font-weight: 800;
}
.uq-cart-alert p {
    margin: 2px 0 0;
    font-size: 12px;
    color: #cbd5e1;
}
.uq-btn-to-cart {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 38px;
    padding: 0 16px;
    background: #ffffff;
    color: #0f172a;
    font-size: 12.5px;
    font-weight: 800;
    text-decoration: none;
    border-radius: 6px;
    white-space: nowrap;
}

/* Section Header */
.uq-section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
}
.uq-kicker {
    display: block;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #ef4444;
}
.uq-section-title {
    font-size: 18px;
    font-weight: 900;
    color: #0f172a;
    margin: 2px 0 0;
    letter-spacing: -0.3px;
}
.uq-link-all {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    text-decoration: none;
}
.uq-link-all:hover {
    text-decoration: underline;
}

/* Services Grid */
.uq-services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.uq-service-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.uq-service-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    border-color: #cbd5e1;
}
.uq-service-card.is-selected {
    border-color: #86efac;
}

/* Thumbnail */
.uq-service-thumb {
    position: relative;
    width: 100%;
    aspect-ratio: 4/3;
    background: #f1f5f9;
    overflow: hidden;
}
.uq-service-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.25s;
}
.uq-service-card:hover .uq-service-thumb img {
    transform: scale(1.02);
}
.uq-thumb-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    color: #94a3b8;
    gap: 6px;
}
.uq-thumb-placeholder i {
    font-size: 32px;
    opacity: 0.6;
}
.uq-thumb-placeholder span {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
}

.uq-cat-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(15, 23, 42, 0.88);
    backdrop-filter: blur(4px);
    color: #ffffff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.5px;
    padding: 3px 8px;
    border-radius: 4px;
    text-transform: uppercase;
}
.uq-rating-pill {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(4px);
    color: #0f172a;
    font-size: 11.5px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.uq-rating-pill i {
    color: #f59e0b;
    font-size: 10px;
}

/* Card Body */
.uq-service-body {
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.uq-vendor-author {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 6px;
}
.uq-author-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #0f172a;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 9.5px;
    font-weight: 800;
}
.uq-author-name {
    font-size: 11.5px;
    color: #64748b;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.uq-service-title {
    font-size: 14.5px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.35;
    margin: 0 0 10px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.uq-service-title a {
    color: #0f172a;
    text-decoration: none;
}
.uq-service-title a:hover {
    color: #2563eb;
}

.uq-service-pricing {
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}
.uq-service-pricing .price-label {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    font-weight: 700;
    color: #94a3b8;
}
.uq-service-pricing .price-val {
    font-size: 16px;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: -0.3px;
    margin-top: 1px;
}

/* Card Buttons */
.uq-card-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}
.uq-btn-detail {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 38px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.15s;
}
.uq-btn-detail:hover {
    background: #f8fafc;
    border-color: #94a3b8;
}
.uq-btn-add {
    width: 100%;
    height: 38px;
    background: #0f172a;
    border: none;
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 800;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    transition: background 0.15s;
}
.uq-btn-add:hover {
    background: #1e293b;
}
.uq-btn-added {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    height: 38px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #059669;
    font-size: 12px;
    font-weight: 800;
    border-radius: 6px;
}

/* Category Strip */
.uq-category-strip-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.uq-strip-title {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #475569;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.uq-category-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.uq-category-scroll::-webkit-scrollbar {
    display: none;
}
.uq-cat-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 16px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    border-radius: 20px;
    white-space: nowrap;
    transition: all 0.15s;
}
.uq-cat-chip:hover {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}
.uq-cat-chip i {
    color: #ef4444;
}
.uq-cat-chip:hover i {
    color: #ffffff;
}

/* Empty Box */
.uq-empty-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 48px 20px;
    text-align: center;
    margin-bottom: 24px;
}
.uq-empty-box i {
    font-size: 32px;
    color: #94a3b8;
    margin-bottom: 12px;
    display: block;
}
.uq-empty-box h3 {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 6px;
}
.uq-empty-box p {
    font-size: 13px;
    color: #64748b;
    max-width: 420px;
    margin: 0 auto 16px;
}
.uq-btn-explore {
    display: inline-block;
    height: 40px;
    line-height: 40px;
    padding: 0 20px;
    background: #0f172a;
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 800;
    text-decoration: none;
    border-radius: 6px;
}

/* Booked Box */
.uq-booked-orders-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}
.uq-booked-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
}
.uq-booked-header h3 {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.uq-booked-header .count {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
}
.uq-orders-list {
    display: grid;
    gap: 12px;
}
.uq-order-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px;
}
.uq-order-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e2e8f0;
}
.uq-order-top strong {
    font-size: 13.5px;
    color: #0f172a;
}
.uq-order-top .date {
    font-size: 12px;
    color: #64748b;
    margin-left: 6px;
}
.pay-badge {
    font-size: 11px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 4px;
    text-transform: uppercase;
    margin-left: 6px;
}
.pay-badge.tunai {
    background: #e0f2fe;
    color: #0369a1;
}
.pay-badge.non_tunai {
    background: #ecfdf5;
    color: #059669;
}
.uq-btn-invoice {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    padding: 5px 10px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    text-decoration: none;
}
.uq-btn-track {
    font-size: 12px;
    font-weight: 800;
    color: #ffffff;
    background: #0f172a;
    padding: 5px 12px;
    border-radius: 4px;
    text-decoration: none;
}
.uq-item-sub {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
    padding: 8px 12px;
    border: 1px solid #f1f5f9;
    border-radius: 6px;
    margin-bottom: 6px;
}
.uq-item-sub:last-child {
    margin-bottom: 0;
}
.item-name {
    font-weight: 700;
    font-size: 13px;
    color: #0f172a;
}
.item-vendor {
    font-size: 11.5px;
    color: #64748b;
}
.item-price {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
}

/* Bottom Bar */
.uq-bottom-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 20px 0;
    border-top: 1px solid #e2e8f0;
}
.uq-back-link {
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    text-decoration: none;
}
.uq-back-link:hover {
    color: #0f172a;
}
.uq-bottom-btns {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.uq-btn-cat-more {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 40px;
    padding: 0 16px;
    background: #ffffff;
    border: 1px solid #0f172a;
    color: #0f172a;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    border-radius: 6px;
}
.uq-btn-view-cart {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 40px;
    padding: 0 20px;
    background: #0f172a;
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 800;
    text-decoration: none;
    border-radius: 6px;
}

/* Mobile Responsive Optimization */
@media (max-width: 640px) {
    .uq-event-card {
        padding: 16px;
    }
    .uq-event-title {
        font-size: 19px;
    }
    .uq-metrics-strip {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .uq-services-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }
    .uq-stepper-steps .text {
        display: none;
    }
    .uq-stepper-steps .uq-step.active .text {
        display: inline;
    }
    .uq-cart-alert {
        flex-direction: column;
        align-items: stretch;
    }
    .uq-btn-to-cart {
        justify-content: center;
    }
    .uq-bottom-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .uq-bottom-btns {
        flex-direction: column;
    }
    .uq-btn-cat-more,
    .uq-btn-view-cart {
        justify-content: center;
    }
}
</style>
@endpush
@endsection
