@extends('layouts.app')
@section('title', (!empty($isDirectCheckout) ? 'Checkout Langsung' : 'Checkout Pesanan') . ' — PestaGo')

@section('content')
    <div class="uq-checkout-wrapper">
        <div class="container uq-checkout-container">
            <div class="uq-checkout-head">
                @if(!empty($isDirectCheckout))
                    @php
                        $targetService = $service ?? ($carts->first()?->service ?? null);
                        $backUrl = $targetService ? route('services.show', $targetService->slug ?? $targetService->id) : route('customer.cart');
                    @endphp
                    <a href="{{ $backUrl }}" class="uq-checkout-back-link">
                        <i class="bi bi-arrow-left"></i> Kembali ke Detail Layanan
                    </a>
                    <div class="uq-checkout-title-wrap">
                        <h1 class="uq-checkout-title">Checkout Pesanan Langsung</h1>
                        <span class="uq-checkout-badge">1 Layanan · Langsung Bayar</span>
                    </div>
                    <p class="uq-checkout-desc">Lengkapi data diri dan detail acara Anda di bawah ini untuk melanjutkan ke
                        pembayaran.</p>
                @else
                    <a href="{{ route('customer.cart') }}" class="uq-checkout-back-link">
                        <i class="bi bi-arrow-left"></i> Kembali ke Keranjang
                    </a>
                    <div class="uq-checkout-title-wrap">
                        <h1 class="uq-checkout-title">Checkout Pesanan</h1>
                        <span class="uq-checkout-badge" style="background:#111111;">{{ $carts->count() }} Layanan</span>
                    </div>
                    <p class="uq-checkout-desc">Konfirmasi data pemesan, jadwal acara, dan pilih metode pembayaran Anda.</p>
                @endif
            </div>

            <form action="{{ route('customer.cart.process') }}" method="POST" id="checkoutMainForm">
                @csrf
                @if(!empty($isDirectCheckout))
                    <input type="hidden" name="is_direct" value="1">
                    <input type="hidden" name="direct_service_id" value="{{ $service->id }}">
                    <input type="hidden" name="quantity" value="{{ $carts->first()->quantity ?? 1 }}">
                    <input type="hidden" name="booking_date" value="{{ $carts->first()->booking_date }}">
                @else
                    @foreach($carts as $cart)
                        <input type="hidden" name="cart_ids[]" value="{{ $cart->id }}">
                    @endforeach
                @endif
                <input type="hidden" name="event_id" value="{{ $event?->id }}">

                <div class="uq-checkout-grid">
                    <div class="uq-checkout-main-col">

                        {{-- 1. Data Pemesan & Kontak --}}
                        <div class="uq-checkout-card">
                            <div class="uq-card-header">
                                <h3 class="uq-checkout-card-title"><i class="bi bi-person-badge"></i> Data Pemesan & Kontak
                                </h3>
                                <span class="uq-card-subtitle">Identitas untuk konfirmasi vendor</span>
                            </div>
                            <div class="uq-form-grid-2">
                                <div>
                                    <label class="uq-label-clean">Nama Lengkap Pemesan <span class="uq-req">*</span></label>
                                    <input type="text" name="customer_name" class="uq-input-clean"
                                        value="{{ old('customer_name', auth()->user()->name) }}" placeholder="Nama Anda"
                                        required>
                                </div>
                                <div>
                                    <label class="uq-label-clean">Nomor WhatsApp / HP <span class="uq-req">*</span></label>
                                    <input type="tel" name="customer_phone" class="uq-input-clean"
                                        value="{{ old('customer_phone', auth()->user()->phone) }}"
                                        placeholder="Contoh: 081234567890" required>
                                </div>
                            </div>
                            <div style="margin-top:14px;">
                                <label class="uq-label-clean">Alamat Email Terdaftar</label>
                                <input type="email" class="uq-input-clean" value="{{ auth()->user()->email }}" disabled
                                    style="background:#f5f5f5;color:#777777;cursor:not-allowed;">
                                <span style="font-size:0.75rem;color:#888888;margin-top:5px;display:block;">Bukti invoice
                                    dan verifikasi pesanan akan dikirimkan otomatis ke email ini.</span>
                            </div>
                        </div>

                        {{-- 2. Data Pelaksanaan Acara --}}
                        <div class="uq-checkout-card">
                            <div class="uq-card-header">
                                <h3 class="uq-checkout-card-title"><i class="bi bi-calendar2-event"></i> Data Pelaksanaan
                                    Acara</h3>
                                <span class="uq-card-subtitle">Jadwal & lokasi kehadiran vendor</span>
                            </div>
                            <div class="uq-form-grid-event">
                                <div>
                                    <label class="uq-label-clean">Nama Acara <span class="uq-req">*</span></label>
                                    <input type="text" name="event_name" class="uq-input-clean"
                                        value="{{ old('event_name', $event?->name ?? 'Acara Saya') }}"
                                        placeholder="Contoh: Pernikahan Dika & Sarah" required>
                                </div>
                                <div>
                                    <label class="uq-label-clean">Tanggal Pelaksanaan <span class="uq-req">*</span></label>
                                    @php
                                        $defaultEventDate = $event?->event_date ? $event->event_date->format('Y-m-d') : ($carts->first()?->booking_date ? \Carbon\Carbon::parse($carts->first()->booking_date)->format('Y-m-d') : date('Y-m-d'));
                                    @endphp
                                    <input type="date" name="event_date" class="uq-input-clean"
                                        value="{{ old('event_date', $defaultEventDate) }}" min="{{ date('Y-m-d') }}"
                                        required>
                                </div>
                            </div>
                            <div class="uq-form-grid-loc" style="margin-top:14px;">
                                <div>
                                    <label class="uq-label-clean">Kota Pelaksanaan <span class="uq-req">*</span></label>
                                    <input type="text" name="location_city" class="uq-input-clean"
                                        value="{{ old('location_city', $event?->location_city ?? '') }}"
                                        placeholder="Contoh: Subang / Bandung" required>
                                </div>
                                <div>
                                    <label class="uq-label-clean">Alamat Lengkap / Nama Venue</label>
                                    <input type="text" name="location_address" class="uq-input-clean"
                                        value="{{ old('location_address', $event?->location_address ?? '') }}"
                                        placeholder="Contoh: Gedung Wisma Haji, Jl. Veteran No. 12">
                                </div>
                            </div>
                        </div>

                        {{-- 3. Layanan yang Dipesan --}}
                        <div class="uq-checkout-card">
                            <div class="uq-card-header">
                                <h3 class="uq-checkout-card-title"><i class="bi bi-box-seam"></i> Layanan yang Dipesan</h3>
                                <span class="uq-card-subtitle">{{ $carts->count() }} Layanan</span>
                            </div>
                            <div style="display:flex;flex-direction:column;gap:14px;">
                                @foreach($carts as $cart)
                                    <div class="uq-checkout-item-row">
                                        @if($cart->service->thumbnail_url)
                                            <img src="{{ $cart->service->thumbnail_url }}" alt="{{ $cart->service->name }}"
                                                class="uq-checkout-item-img">
                                        @else
                                            <div class="uq-checkout-item-img-placeholder">
                                                <i class="bi bi-image" style="font-size:1.4rem;"></i>
                                            </div>
                                        @endif
                                        <div class="uq-checkout-item-info">
                                            <div class="uq-checkout-item-title">{{ $cart->service->name }}</div>
                                            <div class="uq-checkout-item-vendor">
                                                <i class="bi bi-shop"></i> {{ $cart->service->vendorProfile->business_name }}
                                                @if($cart->service->category)
                                                    <span class="uq-tag-cat">{{ $cart->service->category->name }}</span>
                                                @endif
                                            </div>
                                            <div class="uq-checkout-item-meta">
                                                Jumlah: <strong>{{ $cart->quantity }} unit</strong>
                                                @if($cart->booking_date)
                                                    · Jadwal:
                                                    <strong>{{ \Carbon\Carbon::parse($cart->booking_date)->translatedFormat('d M Y') }}</strong>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="uq-checkout-item-price">
                                            Rp {{ number_format($cart->price_snapshot * $cart->quantity, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 4. Tipe Pembayaran: Tunai vs Non-Tunai --}}
                        <div class="uq-checkout-card">
                            <div class="uq-card-header">
                                <h3 class="uq-checkout-card-title"><i class="bi bi-credit-card"></i> Tipe Pembayaran</h3>
                                <span class="uq-card-subtitle">Pilih metode pembayaran</span>
                            </div>
                            <div class="uq-payment-type-grid">
                                <label class="uq-payment-type-option" id="payTypeNonTunai">
                                    <input type="radio" name="payment_type" value="non_tunai" checked
                                        onchange="togglePaymentType()">
                                    <div class="uq-payment-type-content">
                                        <i class="bi bi-qr-code" style="font-size:1.4rem;"></i>
                                        <strong>Non-Tunai</strong>
                                        <span>QRIS (Semua Digital Payment)</span>
                                    </div>
                                </label>
                                <label class="uq-payment-type-option" id="payTypeTunai">
                                    <input type="radio" name="payment_type" value="tunai" onchange="togglePaymentType()">
                                    <div class="uq-payment-type-content">
                                        <i class="bi bi-cash-coin" style="font-size:1.4rem;"></i>
                                        <strong>Tunai</strong>
                                        <span>Bayar langsung ke vendor</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Metode Pembayaran (Non-Tunai) - QRIS Instan --}}
                        <div class="uq-checkout-card" id="paymentMethodSection">
                            <h3 class="uq-checkout-card-title" style="margin-bottom:12px;">Metode Pembayaran Non-Tunai</h3>
                            <div class="uq-qris-box">
                                <input type="radio" name="payment_method" value="qris" checked
                                    style="accent-color:#111111;width:18px;height:18px;cursor:pointer;flex-shrink:0;">
                                <div class="uq-qris-icon-wrap">
                                    <i class="bi bi-qr-code-scan" style="font-size:22px;color:#D81A24;"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <strong
                                        style="display:block;font-size:0.92rem;color:#111111;font-weight:800;line-height:1.3;">QRIS
                                        (Quick Response Code Indonesian Standard)</strong>
                                    <span
                                        style="font-size:0.78rem;color:#666666;line-height:1.4;display:block;margin-top:2px;">GoPay,
                                        OVO, DANA, ShopeePay, BCA, Mandiri, BRI, BNI & Semua M-Banking</span>
                                </div>
                                <span class="uq-instan-badge">Instan</span>
                            </div>
                        </div>

                        {{-- Catatan Janjian (Tunai) --}}
                        <div class="uq-checkout-card" id="cashMeetingSection" style="display:none;">
                            <h3 class="uq-checkout-card-title"><i class="bi bi-people"></i> Catatan Janjian Ketemuan</h3>
                            <p style="font-size:0.82rem;color:#666666;margin:0 0 10px;">
                                Tuliskan catatan ketemuan untuk vendor (tempat, waktu, detail DP, dll.)
                            </p>
                            <textarea name="cash_meeting_notes" class="uq-input-clean" rows="3"
                                placeholder="Contoh: Bisa ketemuan di lokasi acara hari Sabtu jam 10.00 untuk konfirmasi & DP 50%..."></textarea>
                            <div
                                style="font-size:0.75rem;color:#b45309;margin-top:8px;display:flex;align-items:center;gap:6px;">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                Pembayaran tunai tidak menggunakan sistem escrow. Anda akan berkomunikasi langsung dengan
                                vendor.
                            </div>
                        </div>

                        {{-- 5. Catatan Pesanan --}}
                        <div class="uq-checkout-card">
                            <label class="uq-label-clean" style="margin-bottom:8px;">
                                Catatan Tambahan untuk Vendor (Opsional)
                            </label>
                            <textarea name="notes" class="uq-input-clean" rows="3"
                                placeholder="Tuliskan instruksi atau preferensi khusus untuk vendor..."></textarea>
                        </div>
                    </div>

                    {{-- Summary Box --}}
                    <div class="uq-checkout-summary-col">
                        <div class="uq-checkout-summary">
                            <h3
                                style="font-size:1.05rem;font-weight:800;color:#111111;margin:0 0 16px;letter-spacing:-0.01em;">
                                Rincian Pembayaran
                            </h3>
                            <div
                                style="display:flex;flex-direction:column;gap:10px;font-size:0.86rem;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #eeeeee;">
                                <div style="display:flex;justify-content:space-between;color:#666666;">
                                    <span>Subtotal ({{ $carts->count() }} layanan)</span>
                                    <span style="font-weight:600;color:#111111;">Rp
                                        {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;color:#666666;">
                                    <span>Biaya Platform ({{ $platformFeePercentage }}%)</span>
                                    <span style="font-weight:600;color:#111111;">Rp
                                        {{ number_format($platformFee, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;">
                                <span
                                    style="font-size:0.85rem;font-weight:700;color:#111111;text-transform:uppercase;letter-spacing:0.04em;">Total
                                    Bayar</span>
                                <span style="font-size:1.35rem;font-weight:800;color:#D81A24;">Rp
                                    {{ number_format($total, 0, ',', '.') }}</span>
                            </div>

                            <button type="submit" class="uq-checkout-submit-btn">
                                <i class="bi bi-lock-fill"></i> <span id="submitBtnText">Proses & Bayar Sekarang</span>
                            </button>
                            <div
                                style="font-size:0.75rem;color:#888888;text-align:center;margin-top:12px;display:flex;align-items:center;justify-content:center;gap:6px;">
                                <i class="bi bi-shield-check" style="font-size:0.95rem;color:#16a34a;"></i> Pembayaran aman
                                & terverifikasi sistem
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .uq-checkout-wrapper {
            padding-top: calc(var(--navbar-height, 128px) + 32px);
            padding-bottom: 80px;
            background: #fafafa;
            min-height: 100vh;
        }

        .uq-checkout-container {
            max-width: 980px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .uq-checkout-head {
            margin-bottom: 24px;
        }

        .uq-checkout-back-link {
            font-size: 0.82rem;
            font-weight: 700;
            color: #666666;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            transition: color 0.15s ease;
        }

        .uq-checkout-back-link:hover {
            color: #111111;
        }

        .uq-checkout-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .uq-checkout-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: #111111;
            margin: 0;
            letter-spacing: -0.02em;
            line-height: 1.25;
        }

        .uq-checkout-badge {
            font-size: 0.72rem;
            font-weight: 800;
            background: #D81A24;
            color: #ffffff;
            padding: 4px 10px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border-radius: 0 !important;
        }

        .uq-checkout-desc {
            font-size: 0.84rem;
            color: #666666;
            margin: 6px 0 0;
        }

        .uq-checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 24px;
            align-items: start;
        }

        .uq-checkout-main-col {
            display: flex;
            flex-direction: column;
            gap: 18px;
            min-width: 0;
        }

        .uq-checkout-card {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 0 !important;
            padding: 22px;
        }

        .uq-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
            flex-wrap: wrap;
            gap: 6px;
        }

        .uq-checkout-card-title {
            font-size: 0.98rem;
            font-weight: 800;
            color: #111111;
            margin: 0;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .uq-card-subtitle {
            font-size: 0.75rem;
            color: #888888;
        }

        .uq-label-clean {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #444444;
            text-transform: uppercase;
            margin-bottom: 6px;
            display: block;
        }

        .uq-req {
            color: #D81A24;
        }

        .uq-input-clean {
            width: 100%;
            border: 1px solid #d4d4d4;
            border-radius: 0 !important;
            padding: 10px 14px;
            font-size: 0.88rem;
            color: #111111;
            background: #ffffff;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.15s ease;
        }

        .uq-input-clean:focus {
            border-color: #111111;
            box-shadow: 0 0 0 1px #111111;
        }

        /* Form Grids - Responsif 1 Kolom di Layar Kecil */
        .uq-form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .uq-form-grid-event {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 14px;
        }

        .uq-form-grid-loc {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 14px;
        }

        /* Items Row */
        .uq-checkout-item-row {
            display: flex;
            gap: 14px;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 1px solid #f2f2f2;
        }

        .uq-checkout-item-img {
            width: 68px;
            height: 68px;
            object-fit: cover;
            border: 1px solid #e5e5e5;
            border-radius: 0 !important;
            flex-shrink: 0;
        }

        .uq-checkout-item-img-placeholder {
            width: 68px;
            height: 68px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888888;
            flex-shrink: 0;
            border: 1px solid #e5e5e5;
            border-radius: 0 !important;
        }

        .uq-checkout-item-info {
            flex: 1;
            min-width: 0;
        }

        .uq-checkout-item-title {
            font-weight: 700;
            color: #111111;
            font-size: 0.92rem;
            line-height: 1.35;
        }

        .uq-checkout-item-vendor {
            font-size: 0.8rem;
            color: #555555;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .uq-tag-cat {
            font-size: 0.72rem;
            background: #f0f0f0;
            color: #333333;
            padding: 1px 6px;
            border-radius: 0 !important;
        }

        .uq-checkout-item-meta {
            font-size: 0.76rem;
            color: #777777;
            margin-top: 3px;
        }

        .uq-checkout-item-price {
            text-align: right;
            font-weight: 800;
            color: #111111;
            font-size: 0.95rem;
            white-space: nowrap;
        }

        /* Payment Type Selector */
        .uq-payment-type-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .uq-payment-type-option {
            cursor: pointer;
        }

        .uq-payment-type-option input {
            display: none;
        }

        .uq-payment-type-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 16px 12px;
            border: 2px solid #e5e5e5;
            border-radius: 0 !important;
            text-align: center;
            transition: all 0.15s ease;
            background: #ffffff;
        }

        .uq-payment-type-option input:checked+.uq-payment-type-content {
            border-color: #111111;
            background: #fafafa;
        }

        .uq-payment-type-content strong {
            font-size: 0.9rem;
            color: #111111;
        }

        .uq-payment-type-content span {
            font-size: 0.75rem;
            color: #888888;
        }

        /* QRIS Box */
        .uq-qris-box {
            background: #ffffff;
            border: 2px solid #111111;
            border-radius: 0 !important;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .uq-qris-icon-wrap {
            width: 44px;
            height: 44px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 0 !important;
        }

        .uq-instan-badge {
            font-size: 0.72rem;
            font-weight: 800;
            color: #15803d;
            background: #dcfce7;
            padding: 3px 8px;
            text-transform: uppercase;
            border-radius: 0 !important;
            flex-shrink: 0;
        }

        /* Summary Box */
        .uq-checkout-summary-col {
            min-width: 0;
        }

        .uq-checkout-summary {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 0 !important;
            padding: 24px;
            position: sticky;
            top: calc(var(--navbar-height, 56px) + 20px);
        }

        .uq-checkout-submit-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 48px;
            padding: 12px;
            font-weight: 800;
            border-radius: 0 !important;
            font-size: 0.92rem;
            background: #111111;
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            transition: background 0.2s;
        }

        .uq-checkout-submit-btn:hover {
            background: #333333;
        }

        /* ====================================================================
           RESPONSIVE UNTUK MOBILE
           ==================================================================== */
        @media (max-width: 768px) {
            .uq-checkout-wrapper {
                padding-top: calc(var(--navbar-height, 140px) + 40px) !important;
                padding-bottom: 90px;
            }

            .uq-checkout-container {
                padding: 0 14px;
            }

            .uq-checkout-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .uq-checkout-summary {
                position: static;
                padding: 20px 16px;
            }

            .uq-checkout-title {
                font-size: 1.3rem;
            }

            .uq-checkout-card {
                padding: 18px 14px;
            }

            .uq-form-grid-2,
            .uq-form-grid-event,
            .uq-form-grid-loc {
                grid-template-columns: 1fr !important;
                gap: 12px;
            }

            .uq-qris-box {
                padding: 12px 14px;
                gap: 10px;
            }

            .uq-instan-badge {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .uq-payment-type-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .uq-payment-type-content {
                padding: 12px 10px;
                flex-direction: row;
                text-align: left;
                gap: 12px;
            }

            .uq-checkout-item-row {
                align-items: flex-start;
            }

            .uq-checkout-item-price {
                font-size: 0.88rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function togglePaymentType() {
            const isTunai = document.querySelector('input[name="payment_type"][value="tunai"]').checked;
            document.getElementById('paymentMethodSection').style.display = isTunai ? 'none' : 'block';
            document.getElementById('cashMeetingSection').style.display = isTunai ? 'block' : 'none';
            document.getElementById('submitBtnText').textContent = isTunai ? 'Buat Pesanan Tunai' : 'Proses & Bayar Sekarang';
        }
    </script>
@endpush