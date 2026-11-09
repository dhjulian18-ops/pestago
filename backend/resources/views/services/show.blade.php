@extends('layouts.app')
@section('title', $service->name . ' — PestaGo')
@section('meta_description', Str::limit($service->description, 160))

@section('content')
<div class="uq-pdp-page">
    <div class="uq-pdp-container">
        {{-- Smart Back Button --}}
        @php
            $eventId = request('event_id');
            if ($eventId) {
                $backUrl = route('customer.events.show', $eventId);
                $backLabel = 'Kembali ke Rekomendasi Acara';
            } elseif (url()->previous() && url()->previous() !== url()->current() && !str_contains(url()->previous(), 'login')) {
                $backUrl = url()->previous();
                $backLabel = 'Kembali';
            } else {
                $backUrl = route('services.index');
                $backLabel = 'Kembali ke Layanan';
            }
        @endphp
        <div style="margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <a href="{{ $backUrl }}" id="pdpBackLink" style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:#ffffff;border:1px solid #111111;border-radius:0 !important;font-size:12px;font-weight:700;color:#111111;text-decoration:none;text-transform:uppercase;letter-spacing:0.04em;transition:all 0.15s ease;">
                <i class="bi bi-arrow-left" style="font-size:14px;color:#D81A24;"></i>
                <span>{{ $backLabel }}</span>
            </a>
            @if($eventId)
                <span style="font-size:11px;font-weight:700;color:#111111;background:#f5f5f5;border:1px solid #111111;padding:6px 12px;border-radius:0 !important;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:0.03em;">
                    <i class="bi bi-calendar-check"></i> Mode Pilih Vendor Acara
                </span>
            @endif
        </div>

        {{-- Breadcrumb --}}
        <nav class="uq-pdp-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Beranda</a>
            <span>/</span>
            <a href="{{ route('services.index') }}">Layanan</a>
            <span>/</span>
            <a href="{{ route('services.index', ['category' => $service->category->slug ?? '']) }}">{{ $service->category->name ?? 'Kategori' }}</a>
            <span>/</span>
            <span class="uq-breadcrumb-current">{{ $service->name }}</span>
        </nav>

        {{-- Mobile Top Header (Muncul di Mobile agar judul dan harga langsung terlihat sebelum galeri) --}}
        <div class="uq-pdp-mobile-header">
            <div class="uq-pdp-category-row">
                <span class="uq-pdp-category">{{ $service->category->name ?? 'Layanan' }}</span>
                @if($service->is_featured)
                    <span class="uq-pdp-badge-featured"><i class="bi bi-star-fill"></i> Unggulan</span>
                @endif
            </div>
            <h1 class="uq-pdp-title">{{ $service->name }}</h1>
            <div class="uq-pdp-meta-row">
                <span class="uq-pdp-rating">
                    <i class="bi bi-star-fill"></i> {{ number_format($service->rating_avg, 1) }}
                    <span class="uq-pdp-review-count">({{ $service->rating_count }} ulasan)</span>
                </span>
                @if($service->location)
                    <span class="uq-pdp-meta-dot">·</span>
                    <span><i class="bi bi-geo-alt"></i> {{ $service->location->city }}</span>
                @endif
                @if($service->capacity_max)
                    <span class="uq-pdp-meta-dot">·</span>
                    <span><i class="bi bi-people"></i> {{ $service->capacity_min ?? 1 }}–{{ $service->capacity_max }} orang</span>
                @endif
            </div>
            <div class="uq-pdp-price-wrap">
                <div class="uq-pdp-price">Rp {{ number_format($service->price, 0, ',', '.') }}</div>
                <div class="uq-pdp-price-unit">/{{ str_replace('per_', '', $service->price_unit) }}</div>
            </div>
        </div>

        <div class="uq-pdp-layout">
            {{-- Left Column: Gallery, Description, Facilities, Calendar, Map, Reviews --}}
            <div class="uq-pdp-main">
                {{-- Interactive Multi-Image Gallery --}}
                @php
                    $galleryImages = [];
                    if ($service->images->count() > 0) {
                        foreach($service->images as $img) {
                            $galleryImages[] = [
                                'url' => asset('storage/' . $img->image_path),
                                'caption' => $img->caption ?? $service->name
                            ];
                        }
                    } else {
                        $galleryImages[] = [
                            'url' => $service->thumbnail_url,
                            'caption' => $service->name
                        ];
                    }
                @endphp

                <div class="service-gallery-wrap">
                    <div class="service-gallery-main">
                        <img id="mainGalleryImage" src="{{ $galleryImages[0]['url'] }}" alt="{{ $galleryImages[0]['caption'] }}">
                        
                        @if(count($galleryImages) > 1)
                        <button type="button" class="gallery-nav-btn prev" id="galleryPrevBtn" aria-label="Foto sebelumnya" title="Sebelumnya">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="gallery-nav-btn next" id="galleryNextBtn" aria-label="Foto berikutnya" title="Berikutnya">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <div class="gallery-counter-badge" id="galleryCounter">
                            <i class="bi bi-camera"></i> <span id="galleryCurrentIdx">1</span> / {{ count($galleryImages) }}
                        </div>
                        @endif

                        @if(!empty($galleryImages[0]['caption']))
                        <div class="gallery-caption-overlay" id="galleryCaptionText">
                            {{ $galleryImages[0]['caption'] }}
                        </div>
                        @endif
                    </div>

                    @if(count($galleryImages) > 1)
                    <div class="service-gallery-thumbs" id="galleryThumbsTrack">
                        @foreach($galleryImages as $index => $gImg)
                        <button type="button" class="gallery-thumb-btn {{ $index === 0 ? 'active' : '' }}" 
                                data-index="{{ $index }}" 
                                data-url="{{ $gImg['url'] }}" 
                                data-caption="{{ $gImg['caption'] }}"
                                title="Lihat foto {{ $index + 1 }}">
                            <img src="{{ $gImg['url'] }}" alt="{{ $gImg['caption'] }}">
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Deskripsi Layanan --}}
                <div class="uq-pdp-section">
                    <h2 class="uq-pdp-section-title">
                        <i class="bi bi-file-text"></i> Deskripsi Layanan
                    </h2>
                    <div class="uq-pdp-description">
                        {!! nl2br(e($service->description)) !!}
                    </div>
                </div>

                {{-- Facilities --}}
                @if($service->facilities)
                <div class="uq-pdp-section">
                    <h2 class="uq-pdp-section-title">
                        <i class="bi bi-check2-all"></i> Fasilitas & Yang Termasuk
                    </h2>
                    <div class="uq-pdp-facilities-grid">
                        @foreach($service->facilities as $facility)
                        <div class="uq-pdp-facility-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ $facility }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- KALENDER KETERSEDIAAN JADWAL LAYANAN --}}
                <div class="uq-pdp-section" id="serviceCalendarCard">
                    <div class="uq-pdp-cal-head">
                        <div>
                            <h2 class="uq-pdp-section-title" style="border-bottom:none;padding-bottom:0;margin-bottom:4px;">
                                <i class="bi bi-calendar-check"></i> Kalender Ketersediaan Jadwal
                            </h2>
                            <p class="uq-pdp-subtext">
                                Pilih tanggal untuk melihat jadwal kosong atau tanggal yang sudah dipesan customer lain.
                            </p>
                        </div>
                        {{-- Legend --}}
                        <div class="cal-legend-row">
                            <span class="cal-legend-item"><span class="cal-dot available"></span> Tersedia</span>
                            <span class="cal-legend-item"><span class="cal-dot booked"></span> Terisi</span>
                            <span class="cal-legend-item"><span class="cal-dot selected"></span> Dipilih</span>
                        </div>
                    </div>

                    {{-- Interactive Calendar Container --}}
                    <div class="pestago-calendar-widget">
                        <div class="cal-header">
                            <button type="button" class="btn-cal-nav" id="btnPrevMonth" aria-label="Bulan sebelumnya">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <div class="cal-month-title" id="calMonthTitle">Memuat kalender...</div>
                            <button type="button" class="btn-cal-nav" id="btnNextMonth" aria-label="Bulan berikutnya">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>

                        <div class="cal-weekdays">
                            <div>Min</div>
                            <div>Sen</div>
                            <div>Sel</div>
                            <div>Rab</div>
                            <div>Kam</div>
                            <div>Jum</div>
                            <div>Sab</div>
                        </div>

                        <div class="cal-days-grid" id="calDaysGrid">
                            <!-- Populated dynamically by JS -->
                        </div>
                    </div>

                    {{-- Notice if Available Date Selected --}}
                    <div class="cal-selected-notice" id="calSelectedNotice" style="display:none;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;font-size:1.3rem;"></i>
                            <div>
                                <div style="font-size:0.85rem;font-weight:700;color:#111111;" id="calNoticeDateText">-</div>
                                <div style="font-size:0.75rem;color:#16a34a;">Tanggal ini <strong>TERSEDIA</strong> dan otomatis diterapkan pada form pemesanan!</div>
                            </div>
                        </div>
                        <button type="button" class="uq-btn-primary" style="height:36px;line-height:32px;padding:0 16px;font-size:12px;width:auto;border-radius:18px;" id="btnScrollToBooking">
                            Lanjut Pesan <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>

                    {{-- Notice if Booked Date Clicked --}}
                    <div class="cal-booked-notice" id="calBookedNotice" style="display:none;">
                        <i class="bi bi-x-circle-fill" style="color:#dc2626;font-size:1.3rem;flex-shrink:0;"></i>
                        <div>
                            <div style="font-size:0.85rem;font-weight:700;color:#b91c1c;" id="calBookedDateText">Tanggal Sudah Terisi</div>
                            <div style="font-size:0.75rem;color:#dc2626;">Maaf, vendor sudah memiliki jadwal acara yang terkonfirmasi pada tanggal ini. Silakan pilih tanggal lain yang bertanda hijau.</div>
                        </div>
                    </div>
                </div>

                {{-- Location Map --}}
                @if($service->location && $service->location->latitude && $service->location->longitude)
                <div class="uq-pdp-section">
                    <h2 class="uq-pdp-section-title">
                        <i class="bi bi-geo-alt"></i> Lokasi Vendor
                    </h2>
                    <p style="font-size:13.5px;color:#555555;margin-bottom:14px;line-height:1.6;">
                        {{ $service->location->address }}<br>
                        <strong>{{ $service->location->city }}, {{ $service->location->province }}</strong>
                    </p>
                    <div class="map-container" style="border:1px solid #e5e5e5;border-radius:0;overflow:hidden;">
                        <div id="serviceMap" style="height:320px;"></div>
                    </div>
                    <div style="display:flex;gap:12px;margin-top:14px;flex-wrap:wrap;">
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $service->location->latitude }},{{ $service->location->longitude }}" target="_blank" rel="noopener noreferrer" class="uq-btn-primary" style="height:40px;line-height:36px;font-size:12px;padding:0 18px;width:auto;border-radius:20px;">
                            <i class="bi bi-compass"></i> Petunjuk Arah
                        </a>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $service->location->latitude }},{{ $service->location->longitude }}" target="_blank" rel="noopener noreferrer" class="uq-btn-outline" style="height:40px;line-height:36px;font-size:12px;padding:0 18px;width:auto;border-radius:20px;">
                            <i class="bi bi-box-arrow-up-right"></i> Buka di Google Maps
                        </a>
                    </div>
                </div>
                @endif

                {{-- Reviews --}}
                @if($service->reviews->count() > 0)
                <div class="uq-pdp-section">
                    <h2 class="uq-pdp-section-title">
                        <i class="bi bi-star"></i> Review Pelanggan ({{ $service->rating_count }})
                    </h2>
                    @foreach($service->reviews as $review)
                    <div class="uq-pdp-review-item">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                            <span class="uq-pdp-review-avatar">{{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}</span>
                            <div>
                                <div style="font-weight:700;font-size:13.5px;color:#111111;">{{ $review->user->name ?? 'Pengguna' }}</div>
                                <div style="font-size:11.5px;color:#767676;">{{ $review->created_at->translatedFormat('d M Y') }}</div>
                            </div>
                            <div style="margin-left:auto;color:#FBBF24;font-size:13px;">
                                @for($i = 0; $i < $review->rating; $i++)<i class="bi bi-star-fill"></i>@endfor
                            </div>
                        </div>
                        <p style="font-size:13.5px;color:#444444;line-height:1.7;margin:0;">{{ $review->comment }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Right Column: Sticky Sidebar with Details, Booking Form, Vendor Info --}}
            <div class="uq-pdp-sidebar" id="bookingSection">
                <div class="uq-pdp-card">
                    {{-- Desktop Header --}}
                    <div class="uq-pdp-desktop-header">
                        <div class="uq-pdp-category-row">
                            <span class="uq-pdp-category">{{ $service->category->name ?? 'Layanan' }}</span>
                            @if($service->is_featured)
                                <span class="uq-pdp-badge-featured"><i class="bi bi-star-fill"></i> Unggulan</span>
                            @endif
                        </div>
                        <h1 class="uq-pdp-title">{{ $service->name }}</h1>
                        <div class="uq-pdp-meta-row">
                            <span class="uq-pdp-rating">
                                <i class="bi bi-star-fill"></i> {{ number_format($service->rating_avg, 1) }}
                                <span class="uq-pdp-review-count">({{ $service->rating_count }} ulasan)</span>
                            </span>
                            @if($service->location)
                                <span class="uq-pdp-meta-dot">·</span>
                                <span><i class="bi bi-geo-alt"></i> {{ $service->location->city }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Price Display --}}
                    <div class="uq-pdp-price-box">
                        <div class="uq-pdp-price">
                            Rp {{ number_format($service->price, 0, ',', '.') }}
                        </div>
                        <div class="uq-pdp-price-unit">/{{ str_replace('per_', '', $service->price_unit) }}</div>
                    </div>

                    {{-- Vendor Profile Card --}}
                    <div class="uq-pdp-vendor-box">
                        <span class="uq-pdp-vendor-avatar">{{ strtoupper(substr($service->vendorProfile->business_name ?? 'V', 0, 1)) }}</span>
                        <div style="min-width:0;flex:1;">
                            <div class="uq-pdp-vendor-name">{{ $service->vendorProfile->business_name }}</div>
                            <div class="uq-pdp-vendor-rating">
                                <i class="bi bi-star-fill"></i>
                                {{ number_format($service->vendorProfile->rating_avg, 1) }}
                                · {{ $service->vendorProfile->rating_count }} review
                            </div>
                        </div>
                        <span class="uq-pdp-verified-badge"><i class="bi bi-patch-check-fill"></i> Verified</span>
                    </div>

                    {{-- Booking Form --}}
                    @auth
                        @if(auth()->user()->role === 'customer')
                        <form action="{{ route('customer.cart.add') }}" method="POST" class="uq-pdp-booking-form" id="pdpBookingForm">
                            @csrf
                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                            <input type="hidden" name="redirect_to_cart" value="1">

                            @php
                                $selectedEventId = request('event_id') ?? (isset($userEvents) && $userEvents->count() === 1 ? $userEvents->first()->id : null);
                                $prefillDate = null;
                                if ($selectedEventId && isset($userEvents)) {
                                    $ev = $userEvents->firstWhere('id', $selectedEventId);
                                    if ($ev && $ev->event_date) {
                                        $prefillDate = $ev->event_date->format('Y-m-d');
                                    }
                                }
                            @endphp

                            @if(isset($userEvents) && $userEvents->count() > 0)
                            <div class="uq-form-group">
                                <label class="uq-form-label"><i class="bi bi-calendar-event"></i> Untuk Acara</label>
                                <select name="event_id" id="eventSelectInput" class="uq-form-input" style="height:44px;background:#fff;cursor:pointer;border-radius:0 !important;" onchange="updateEventDate(this)">
                                    @foreach($userEvents as $ev)
                                        <option value="{{ $ev->id }}" data-date="{{ $ev->event_date ? $ev->event_date->format('Y-m-d') : '' }}" {{ $selectedEventId == $ev->id ? 'selected' : '' }}>
                                            {{ $ev->name }} ({{ $ev->event_date ? $ev->event_date->translatedFormat('d M Y') : 'Belum ada tanggal' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @elseif(request('event_id'))
                                <input type="hidden" name="event_id" value="{{ request('event_id') }}">
                            @endif

                            <div class="uq-form-group">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                    <label class="uq-form-label" style="margin:0;">Tanggal Acara <span style="color:#D81A24;">*</span></label>
                                    <a href="#serviceCalendarCard" style="font-size:12px;color:#111111;font-weight:700;text-decoration:underline;">
                                        <i class="bi bi-calendar3"></i> Cek Kalender
                                    </a>
                                </div>
                                <input type="date" name="booking_date" id="bookingDateInput" value="{{ old('booking_date', $prefillDate) }}" class="uq-form-input" min="{{ date('Y-m-d') }}" onchange="handleDateInputChange(this)" oninput="handleDateInputChange(this)" style="border-radius:0 !important;" required>
                                
                                {{-- Alert Tanggal Terisi / Penuh --}}
                                <div id="dateBookedAlert" style="display:none;background:#fef2f2;border:1.5px solid #dc2626;color:#991b1b;padding:9px 12px;font-size:12px;font-weight:700;margin-top:6px;line-height:1.4;">
                                    <i class="bi bi-x-octagon-fill" style="color:#dc2626;margin-right:4px;"></i>
                                    <span id="dateBookedAlertMsg">Tanggal ini SUDAH TERISI / PENUH. Tidak dapat dipesan.</span>
                                </div>
                            </div>

                            <div class="uq-form-group">
                                <label class="uq-form-label">Catatan Pemesanan <span class="uq-label-optional">(opsional)</span></label>
                                <textarea name="notes" id="bookingNotesInput" class="uq-form-input" rows="2" style="height:auto;padding:10px 14px;resize:vertical;border-radius:0 !important;" placeholder="Catatan khusus atau detail acara..."></textarea>
                            </div>

                            {{-- Unified Uniqlo CTA Buttons --}}
                            <div style="display:flex;flex-direction:column;gap:10px;margin-top:18px;">
                                {{-- Tombol 1: PESAN LANGSUNG --}}
                                <button type="button" class="uq-btn-buy-direct" onclick="submitDirectOrder()">
                                    <i class="bi bi-lightning-charge-fill"></i> PESAN LANGSUNG
                                </button>

                                {{-- Tombol 2: TAMBAH KE KERANJANG --}}
                                <button type="button" class="uq-btn-add-cart" onclick="submitAddToCart()">
                                    <i class="bi bi-cart-plus"></i> TAMBAH KE KERANJANG
                                </button>

                                {{-- Baris Tombol Pendukung: Chat Vendor & Favorit --}}
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:2px;">
                                    <button type="button" class="uq-btn-subaction" onclick="document.getElementById('chatVendorModal').style.display='flex'">
                                        <i class="bi bi-chat-dots"></i> Chat Vendor
                                    </button>
                                    @php $isFavShow = in_array($service->id, $userWishlistIds ?? []); @endphp
                                    <button type="button" class="uq-btn-subaction ecom-card-wishlist {{ $isFavShow ? 'active' : '' }}" data-service-id="{{ $service->id }}" title="{{ $isFavShow ? 'Hapus dari favorit' : 'Simpan ke favorit' }}">
                                        <i class="bi bi-heart{{ $isFavShow ? '-fill' : '' }}"></i>
                                        <span>{{ $isFavShow ? 'Tersimpan' : 'Favorit' }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <script>
                            function updateEventDate(select) {
                                const selectedOption = select.options[select.selectedIndex];
                                const eventDate = selectedOption.getAttribute('data-date');
                                const input = document.getElementById('bookingDateInput');
                                if (eventDate && input) {
                                    input.value = eventDate;
                                    handleDateInputChange(input);
                                }
                            }
                        </script>
                        @endif
                    @else
                    <a href="{{ route('login') }}" class="uq-btn-primary">
                        MASUK UNTUK MEMESAN
                    </a>
                    @endauth

                    {{-- Cancellation Policy --}}
                    @if($service->cancellation_policy)
                    <div class="uq-pdp-policy-box">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <strong>Kebijakan Pembatalan:</strong><br>
                            {{ $service->cancellation_policy }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Similar Services --}}
        @if($similarServices->count() > 0)
        <div style="margin-top:48px;">
            <h2 class="uq-pdp-section-title" style="font-size:18px;margin-bottom:20px;">
                <i class="bi bi-collection"></i> Layanan Serupa
            </h2>
            <div class="service-grid">
                @foreach($similarServices as $similar)
                <div class="service-card" style="border-radius:0;border:1px solid #e5e5e5;box-shadow:none;">
                    <div class="service-card-img" style="border-radius:0;">
                        <img src="{{ $similar->thumbnail_url }}" alt="{{ $similar->name }}" loading="lazy">
                        <span class="service-badge" style="border-radius:0;background:#111111;color:#fff;">{{ $similar->category->name ?? 'Layanan' }}</span>
                        <div class="service-rating-badge" style="border-radius:12px;">
                            <i class="bi bi-star-fill"></i>
                            <span>{{ number_format($similar->rating_avg ?: 4.9, 1) }}</span>
                        </div>
                    </div>
                    <div class="service-card-body">
                        <div class="service-card-vendor">
                            <span class="service-card-vendor-avatar" style="border-radius:50%;background:#111;color:#fff;">{{ strtoupper(substr($similar->vendorProfile->business_name ?? 'P', 0, 1)) }}</span>
                            <span class="service-card-vendor-name">{{ $similar->vendorProfile->business_name }}</span>
                            <i class="bi bi-patch-check-fill service-vendor-verified" title="Terverifikasi"></i>
                        </div>
                        <h3 class="service-card-title">
                            <a href="{{ route('services.show', $similar->slug) }}">{{ $similar->name }}</a>
                        </h3>
                        @if($similar->location)
                        <div class="service-card-location">
                            <i class="bi bi-geo-alt"></i> <span>{{ $similar->location->city }}</span>
                        </div>
                        @endif
                        <div class="service-card-footer">
                            <div class="service-price-block">
                                <span class="service-price-label">Mulai dari</span>
                                <div class="service-price">
                                    Rp {{ number_format($similar->price, 0, ',', '.') }}
                                    <span class="service-price-unit">/{{ str_replace('per_', '', $similar->price_unit) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('services.show', $similar->slug) }}" class="service-card-btn" style="border-radius:16px;">
                                Detail <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Mobile Sticky Action Bar --}}
<div class="uq-pdp-sticky-bar" id="pdpStickyBar">
    <div class="uq-sticky-price-wrap">
        <span class="uq-sticky-price-label">Mulai dari</span>
        <div class="uq-sticky-price-val">Rp {{ number_format($service->price, 0, ',', '.') }}</div>
        <div class="uq-sticky-price-unit">/{{ str_replace('per_', '', $service->price_unit) }}</div>
    </div>
    <div class="uq-sticky-btns-wrap">
        <button type="button" class="uq-sticky-cart-btn" onclick="submitAddToCart()" title="Tambah ke Keranjang">
            <i class="bi bi-cart-plus"></i>
            <span class="uq-sticky-cart-text">+ Keranjang</span>
        </button>
        <button type="button" class="uq-sticky-buy-btn" id="mobileBookBtn" onclick="submitDirectOrder()">
            <i class="bi bi-lightning-charge-fill"></i>
            <span>Pesan Langsung</span>
        </button>
    </div>
</div>

{{-- Chat Modal --}}
@auth
    @if(auth()->user()->role === 'customer')
    <div id="chatVendorModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(2px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
        <div class="uq-pdp-card" style="width:100%;max-width:480px;background:#ffffff;padding:0;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.15);">
            <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="font-size:14px;font-weight:700;color:#111111;margin:0;display:flex;align-items:center;gap:8px;">
                    <i class="bi bi-chat-dots"></i> Hubungi {{ $service->vendorProfile->business_name }}
                </h3>
                <button type="button" style="background:none;border:none;font-size:18px;color:#767676;cursor:pointer;padding:4px;display:flex;align-items:center;" onclick="document.getElementById('chatVendorModal').style.display='none'">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form action="{{ route('chat.start') }}" method="POST">
                @csrf
                <input type="hidden" name="vendor_profile_id" value="{{ $service->vendor_profile_id }}">
                <div style="padding:20px;">
                    <div class="uq-form-group">
                        <label class="uq-form-label">Tanya tentang layanan <strong>{{ $service->name }}</strong>:</label>
                        <textarea name="message" class="uq-form-input" rows="4" style="height:auto;padding:12px;resize:vertical;" placeholder="Halo, saya ingin bertanya tentang ketersediaan dan detail layanan ini..." required></textarea>
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
                        <button type="button" class="uq-btn-outline" style="height:40px;line-height:36px;width:auto;padding:0 20px;font-size:13px;border-radius:20px;" onclick="document.getElementById('chatVendorModal').style.display='none'">Batal</button>
                        <button type="submit" class="uq-btn-primary" style="height:40px;line-height:38px;width:auto;padding:0 20px;font-size:13px;border-radius:20px;"><i class="bi bi-send-fill"></i> Kirim Pesan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
@endauth
@endsection

@push('styles')
<style>
/* Uniqlo Clean PDP Buttons */
.uq-btn-buy-direct {
    width: 100%;
    height: 46px;
    font-size: 13px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #D81A24;
    border: 1.5px solid #D81A24;
    color: #ffffff;
    border-radius: 0 !important;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s ease;
    box-sizing: border-box;
}
.uq-btn-buy-direct:hover {
    background: #b91c1c;
    border-color: #b91c1c;
}

.uq-btn-add-cart {
    width: 100%;
    height: 44px;
    font-size: 12.5px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #ffffff;
    border: 1.5px solid #111111;
    color: #111111;
    border-radius: 0 !important;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.15s ease;
    box-sizing: border-box;
}
.uq-btn-add-cart:hover {
    background: #111111;
    color: #ffffff;
}

.uq-btn-subaction {
    height: 40px;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: #ffffff;
    border: 1px solid #d4d4d4;
    color: #333333;
    border-radius: 0 !important;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.15s ease;
    text-decoration: none;
    box-sizing: border-box;
}
.uq-btn-subaction:hover {
    border-color: #111111;
    color: #111111;
    background: #fafafa;
}

/* Mobile Sticky Action Bar */
.uq-pdp-sticky-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 1px solid #e5e5e5;
    padding: 10px 14px;
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    box-shadow: 0 -4px 16px rgba(0,0,0,0.06);
    box-sizing: border-box;
    width: 100%;
}
.uq-sticky-price-wrap {
    min-width: 0;
    flex-shrink: 0;
    line-height: 1.2;
}
.uq-sticky-price-label {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    color: #888888;
    letter-spacing: 0.04em;
    display: block;
}
.uq-sticky-price-val {
    font-size: 14px;
    font-weight: 800;
    color: #111111;
    white-space: nowrap;
    letter-spacing: -0.02em;
}
.uq-sticky-price-unit {
    font-size: 10px;
    color: #777777;
    white-space: nowrap;
}
.uq-sticky-btns-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    justify-content: flex-end;
    min-width: 0;
}
.uq-sticky-cart-btn {
    height: 40px;
    padding: 0 12px;
    font-size: 11.5px;
    font-weight: 700;
    border-radius: 0 !important;
    border: 1.5px solid #111111;
    color: #111111;
    background: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    white-space: nowrap;
    cursor: pointer;
    flex-shrink: 0;
    font-family: inherit;
    box-sizing: border-box;
    transition: all 0.15s ease;
}
.uq-sticky-cart-btn:hover {
    background: #f5f5f5;
}
.uq-sticky-buy-btn {
    height: 40px;
    padding: 0 14px;
    font-size: 11.5px;
    font-weight: 800;
    border-radius: 0 !important;
    background: #D81A24;
    border: 1.5px solid #D81A24;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    white-space: nowrap;
    cursor: pointer;
    flex: 1;
    max-width: 170px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    font-family: inherit;
    box-sizing: border-box;
    transition: background 0.15s ease;
}
.uq-sticky-buy-btn:hover {
    background: #b91c1c;
}

@media (max-width: 380px) {
    .uq-pdp-sticky-bar {
        padding: 8px 10px;
        gap: 8px;
    }
    .uq-sticky-cart-text {
        display: none;
    }
    .uq-sticky-cart-btn {
        padding: 0 10px;
    }
    .uq-sticky-buy-btn {
        padding: 0 8px;
        font-size: 11px;
    }
}
</style>
@endpush

@push('scripts')
<script>
    const bookedDates = @json($bookedDates ?? []);

    function isDateBooked(dateStr) {
        if (!dateStr) return false;
        return bookedDates.includes(dateStr);
    }

    function handleDateInputChange(input) {
        if (!input) return false;
        const val = input.value;
        const alertBox = document.getElementById('dateBookedAlert');
        const alertMsg = document.getElementById('dateBookedAlertMsg');
        
        if (val && isDateBooked(val)) {
            input.style.border = '2px solid #dc2626';
            input.style.background = '#fff5f5';
            if (alertBox && alertMsg) {
                alertMsg.innerHTML = 'Tanggal <strong>' + val + '</strong> SUDAH TERISI / PENUH. Tidak dapat dipesan. Silakan pilih tanggal lain yang bertanda "Tersedia" di kalender.';
                alertBox.style.display = 'block';
            }
            return false;
        } else {
            input.style.border = '';
            input.style.background = '';
            if (alertBox) {
                alertBox.style.display = 'none';
            }
            return true;
        }
    }

    function scrollToBookingSection() {
        const target = document.getElementById('bookingSection');
        const input = document.getElementById('bookingDateInput');
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        if (input) {
            setTimeout(() => {
                input.focus();
            }, 500);
        }
    }

    function scrollToCalendarSection() {
        const cal = document.getElementById('serviceCalendarCard');
        if (cal) {
            cal.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function submitAddToCart() {
        const form = document.getElementById('pdpBookingForm');
        const dateInput = document.getElementById('bookingDateInput');

        @guest
            window.location.href = "{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}";
            return;
        @endguest

        if (!form) {
            scrollToBookingSection();
            return;
        }

        if (!dateInput || !dateInput.value) {
            scrollToBookingSection();
            if (dateInput) {
                dateInput.style.border = '2px solid #D81A24';
                dateInput.focus();
                alert('Silakan tentukan Tanggal Acara terlebih dahulu.');
            }
            return;
        }

        if (isDateBooked(dateInput.value)) {
            handleDateInputChange(dateInput);
            scrollToBookingSection();
            alert('Tanggal ' + dateInput.value + ' sudah terisi/penuh! Silakan pilih tanggal lain yang tersedia di kalender ketersediaan.');
            return;
        }

        form.action = "{{ route('customer.cart.add') }}";
        const redirectInput = document.getElementById('redirectToCartInput');
        if (redirectInput) {
            redirectInput.value = '1';
        }
        form.submit();
    }

    function submitDirectOrder() {
        const form = document.getElementById('pdpBookingForm');
        const dateInput = document.getElementById('bookingDateInput');
        const mobileBtn = document.getElementById('mobileBookBtn');

        @guest
            window.location.href = "{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}";
            return;
        @endguest

        if (!form) {
            scrollToBookingSection();
            return;
        }

        if (!dateInput || !dateInput.value) {
            scrollToBookingSection();
            if (dateInput) {
                dateInput.style.border = '2px solid #D81A24';
                dateInput.focus();
                alert('Silakan tentukan Tanggal Acara terlebih dahulu untuk Pesan Langsung.');
            }
            return;
        }

        if (isDateBooked(dateInput.value)) {
            handleDateInputChange(dateInput);
            scrollToBookingSection();
            alert('Tanggal ' + dateInput.value + ' sudah terisi/penuh! Silakan pilih tanggal lain yang bertanda hijau di kalender ketersediaan.');
            return;
        }

        if (mobileBtn) {
            mobileBtn.disabled = true;
            mobileBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyiapkan...';
        }

        form.action = "{{ route('customer.direct-checkout') }}";
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initial check for prefilled date
        const initBookingInput = document.getElementById('bookingDateInput');
        if (initBookingInput && initBookingInput.value) {
            handleDateInputChange(initBookingInput);
        }

        // Multi-Image Gallery Controls
        const thumbs = document.querySelectorAll('.gallery-thumb-btn');
        const mainImg = document.getElementById('mainGalleryImage');
        const captionText = document.getElementById('galleryCaptionText');
        const currentIdxEl = document.getElementById('galleryCurrentIdx');
        const prevBtn = document.getElementById('galleryPrevBtn');
        const nextBtn = document.getElementById('galleryNextBtn');

        let currentIndex = 0;
        const totalImages = thumbs.length;

        function setGalleryImage(index) {
            if (index < 0) index = totalImages - 1;
            if (index >= totalImages) index = 0;
            currentIndex = index;

            const targetThumb = thumbs[currentIndex];
            if (!targetThumb || !mainImg) return;

            const newUrl = targetThumb.getAttribute('data-url');
            const newCaption = targetThumb.getAttribute('data-caption');

            // Smooth opacity transition
            mainImg.style.opacity = '0.4';
            setTimeout(() => {
                mainImg.src = newUrl;
                if (captionText) captionText.textContent = newCaption;
                if (currentIdxEl) currentIdxEl.textContent = currentIndex + 1;
                mainImg.style.opacity = '1';
            }, 120);

            thumbs.forEach((t, i) => {
                t.classList.toggle('active', i === currentIndex);
            });

            targetThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }

        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const idx = parseInt(this.getAttribute('data-index'), 10);
                setGalleryImage(idx);
            });
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                setGalleryImage(currentIndex - 1);
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                setGalleryImage(currentIndex + 1);
            });
        }

        // Keyboard arrow navigation
        document.addEventListener('keydown', function(e) {
            if (totalImages <= 1) return;
            if (e.key === 'ArrowLeft') setGalleryImage(currentIndex - 1);
            if (e.key === 'ArrowRight') setGalleryImage(currentIndex + 1);
        });
    });
</script>

@if($service->location && $service->location->latitude && $service->location->longitude)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initMap(
            'serviceMap',
            {{ $service->location->latitude }},
            {{ $service->location->longitude }},
            15,
            '<strong>{{ $service->vendorProfile->business_name }}</strong><br>{{ $service->location->address }}'
        );
    });
</script>
@endif

{{-- Availability Calendar Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let viewYear = today.getFullYear();
    let viewMonth = today.getMonth();
    let selectedDateStr = null;

    const monthTitleEl = document.getElementById('calMonthTitle');
    const daysGridEl = document.getElementById('calDaysGrid');
    const prevBtn = document.getElementById('btnPrevMonth');
    const nextBtn = document.getElementById('btnNextMonth');

    const selectedNotice = document.getElementById('calSelectedNotice');
    const noticeDateText = document.getElementById('calNoticeDateText');
    const bookedNotice = document.getElementById('calBookedNotice');
    const bookedDateText = document.getElementById('calBookedDateText');
    const bookingInput = document.getElementById('bookingDateInput');
    const scrollBtn = document.getElementById('btnScrollToBooking');

    function renderCalendar() {
        if (!daysGridEl || !monthTitleEl) return;

        monthTitleEl.textContent = `${monthNames[viewMonth]} ${viewYear}`;

        // Disable prev button if current month/year is <= today's month/year
        if (viewYear === today.getFullYear() && viewMonth <= today.getMonth()) {
            if (prevBtn) {
                prevBtn.disabled = true;
                prevBtn.style.opacity = '0.35';
                prevBtn.style.cursor = 'not-allowed';
            }
        } else {
            if (prevBtn) {
                prevBtn.disabled = false;
                prevBtn.style.opacity = '1';
                prevBtn.style.cursor = 'pointer';
            }
        }

        daysGridEl.innerHTML = '';

        // First day of month (0 = Sunday)
        const firstDay = new Date(viewYear, viewMonth, 1).getDay();
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

        // Empty cells for days before the 1st
        for (let i = 0; i < firstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'cal-day-cell empty';
            daysGridEl.appendChild(emptyCell);
        }

        // Days in the month
        for (let day = 1; day <= daysInMonth; day++) {
            const cellDate = new Date(viewYear, viewMonth, day);
            cellDate.setHours(0, 0, 0, 0);

            // Format YYYY-MM-DD
            const mStr = String(viewMonth + 1).padStart(2, '0');
            const dStr = String(day).padStart(2, '0');
            const dateStr = `${viewYear}-${mStr}-${dStr}`;

            const isPast = cellDate < today;
            const isToday = cellDate.getTime() === today.getTime();
            const isBooked = isDateBooked(dateStr);
            const isSelected = selectedDateStr === dateStr;

            const cell = document.createElement('div');
            cell.className = 'cal-day-cell';
            cell.dataset.date = dateStr;

            if (isToday) cell.classList.add('today');

            if (isPast) {
                cell.classList.add('past');
                cell.innerHTML = `<span>${day}</span><span class="cal-status-sub">-</span>`;
            } else if (isBooked) {
                cell.classList.add('booked');
                cell.innerHTML = `<span>${day}</span><span class="cal-status-sub">Terisi</span>`;
                cell.title = `Tanggal ${day} ${monthNames[viewMonth]} ${viewYear}: SUDAH TERISI (Tidak dapat dipesan)`;
                cell.onclick = function() {
                    if (selectedNotice) selectedNotice.style.display = 'none';
                    if (bookedNotice) {
                        bookedDateText.innerHTML = `Tanggal ${day} ${monthNames[viewMonth]} ${viewYear} SUDAH TERISI / PENUH`;
                        bookedNotice.style.display = 'flex';
                    }
                    if (bookingInput) {
                        bookingInput.value = dateStr;
                        handleDateInputChange(bookingInput);
                    }
                };
            } else {
                cell.classList.add('available');
                if (isSelected) cell.classList.add('selected');
                cell.innerHTML = `<span>${day}</span><span class="cal-status-sub">Tersedia</span>`;
                cell.title = `Tanggal ${day} ${monthNames[viewMonth]} ${viewYear}: TERSEDIA (Klik untuk pesan)`;
                cell.onclick = function() {
                    selectedDateStr = dateStr;

                    // Update active state in grid
                    document.querySelectorAll('.cal-day-cell').forEach(c => c.classList.remove('selected'));
                    cell.classList.add('selected');

                    // Update feedback notice
                    if (bookedNotice) bookedNotice.style.display = 'none';
                    if (selectedNotice) {
                        noticeDateText.textContent = `Tanggal Terpilih: ${day} ${monthNames[viewMonth]} ${viewYear} (Tersedia)`;
                        selectedNotice.style.display = 'flex';
                    }

                    // Auto-fill booking date input
                    if (bookingInput) {
                        bookingInput.value = dateStr;
                        handleDateInputChange(bookingInput);
                        bookingInput.style.borderColor = '#16a34a';
                        bookingInput.style.boxShadow = '0 0 0 2px rgba(22, 163, 74, 0.2)';
                        setTimeout(() => {
                            bookingInput.style.borderColor = '';
                            bookingInput.style.boxShadow = '';
                        }, 1200);
                    }
                };
            }

            daysGridEl.appendChild(cell);
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            viewMonth--;
            if (viewMonth < 0) {
                viewMonth = 11;
                viewYear--;
            }
            renderCalendar();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            viewMonth++;
            if (viewMonth > 11) {
                viewMonth = 0;
                viewYear++;
            }
            renderCalendar();
        });
    }

    if (scrollBtn) {
        scrollBtn.addEventListener('click', () => {
            if (bookingInput) {
                bookingInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                bookingInput.focus();
            }
        });
    }

    // Initial render
    renderCalendar();
});
</script>
@endpush
