@extends('layouts.app')
@section('title', 'Cari Layanan Vendor — PestaGo')
@section('meta_description', 'Temukan dan bandingkan vendor terbaik untuk pernikahan, ulang tahun, dan acara spesial Anda. Filter berdasarkan kategori, lokasi, harga, dan rating.')

@section('content')
<div class="uq-services-page">
    <div class="container">
        {{-- Vendor Banner (Jika difilter berdasarkan vendor spesifik) --}}
        @if(isset($activeVendor) && $activeVendor)
            <div class="uq-vendor-banner">
                <div class="uq-vendor-info">
                    <div class="uq-vendor-avatar">
                        {{ strtoupper(substr($activeVendor->business_name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="uq-vendor-title-row">
                            <h1 class="uq-vendor-name">{{ $activeVendor->business_name }}</h1>
                            <i class="bi bi-patch-check-fill uq-vendor-check" title="Vendor Terverifikasi PestaGo"></i>
                            <span class="uq-vendor-tag">Vendor Mitra Resmi</span>
                        </div>
                        <p class="uq-vendor-meta">
                            <i class="bi bi-geo-alt"></i> {{ $activeVendor->city ?? 'Indonesia' }}
                            <span class="uq-dot-sep">&bull;</span>
                            Kategori: {{ $activeVendor->category->name ?? 'Vendor' }}
                            <span class="uq-dot-sep">&bull;</span>
                            <strong>{{ $services->total() }}</strong> Layanan Tersedia
                        </p>
                    </div>
                </div>
                <a href="{{ route('services.index') }}" class="uq-btn-back">
                    <i class="bi bi-arrow-left"></i> Semua Layanan
                </a>
            </div>
        @else
            {{-- Clean UNIQLO Page Header --}}
            <div class="uq-page-header">
                <span class="uq-badge-kicker">KATALOG VENDOR ACARA</span>
                <h1 class="uq-page-title">Cari Layanan Vendor</h1>
                <p class="uq-page-subtitle">Temukan dan bandingkan ribuan vendor terbaik terverifikasi untuk acara impian Anda</p>
            </div>
        @endif

        @if(request('event_id'))
            @php $ctxEvent = \App\Models\Event::where('user_id', auth()->id())->find(request('event_id')); @endphp
            @if($ctxEvent)
                <div style="background:#111111;color:#ffffff;padding:14px 20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    <div>
                        <span style="font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#ed1d24;">MENAMBAHKAN LAYANAN UNTUK ACARA:</span>
                        <div style="font-size:16px;font-weight:900;color:#ffffff;margin-top:2px;">
                            <i class="bi bi-calendar-event"></i> {{ $ctxEvent->name }} · {{ $ctxEvent->event_date?->translatedFormat('d M Y') }}
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <a href="{{ route('customer.events.show', $ctxEvent) }}" style="display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 14px;background:#ffffff;color:#111111;font-size:12px;font-weight:800;text-decoration:none;">
                            &larr; Kembali ke Acara
                        </a>
                        <a href="{{ route('customer.cart') }}" style="display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 14px;background:#ed1d24;color:#ffffff;font-size:12px;font-weight:800;text-decoration:none;">
                            <i class="bi bi-cart"></i> Lihat Keranjang
                        </a>
                    </div>
                </div>
            @endif
        @endif

        {{-- Sponsored Category Banner (Clean UNIQLO minimal) --}}
        @if(isset($categoryAds) && $categoryAds->isNotEmpty())
            <div class="uq-cat-ads-wrap">
                @foreach($categoryAds as $catAd)
                    <div class="uq-cat-ad-card">
                        <div class="uq-cat-ad-content">
                            <span class="uq-ad-pill">IKLAN MITRA</span>
                            <h3 class="uq-cat-ad-title">{{ $catAd->title }}</h3>
                            <p class="uq-cat-ad-desc">{{ Str::limit($catAd->vendorProfile->description ?? '', 120) }}</p>
                        </div>
                        <a href="{{ $catAd->vendorProfile ? route('services.index', ['vendor' => $catAd->vendorProfile->slug]) : route('services.index') }}" class="uq-btn-ad-visit">
                            Kunjungi Vendor <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- UNIQLO Clean Filter Bar (No Glow, Clean Borders, Minimal) --}}
        <form action="{{ route('services.index') }}" method="GET" class="uq-filter-bar">
            @if(request('event_id'))
                <input type="hidden" name="event_id" value="{{ request('event_id') }}">
            @endif
            <div class="uq-filter-row">
                {{-- Keyword Search --}}
                <div class="uq-filter-field uq-field-search">
                    <label class="uq-filter-label" for="filterSearch">PENCARIAN</label>
                    <div class="uq-input-icon-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" id="filterSearch" name="search" class="uq-filter-input" value="{{ request('search') ?: request('q') }}" placeholder="Cari nama layanan atau vendor...">
                    </div>
                </div>

                {{-- Kategori --}}
                <div class="uq-filter-field">
                    <label class="uq-filter-label" for="filterCategory">KATEGORI</label>
                    <select id="filterCategory" name="category" class="uq-filter-select">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Kota --}}
                <div class="uq-filter-field">
                    <label class="uq-filter-label" for="filterCity">KOTA</label>
                    <div class="uq-input-icon-wrap">
                        <i class="bi bi-geo-alt"></i>
                        <input type="text" id="filterCity" name="city" class="uq-filter-input" value="{{ request('city') }}" placeholder="Semua kota (cth: Jakarta)">
                    </div>
                </div>

                {{-- Urutkan --}}
                <div class="uq-filter-field">
                    <label class="uq-filter-label" for="filterSort">URUTKAN</label>
                    <select id="filterSort" name="sort" class="uq-filter-select">
                        <option value="rating" {{ ($sort ?? '') == 'rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                        <option value="price_asc" {{ ($sort ?? '') == 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="price_desc" {{ ($sort ?? '') == 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                        <option value="newest" {{ ($sort ?? '') == 'newest' ? 'selected' : '' }}>Layanan Terbaru</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="uq-filter-actions">
                    <button type="submit" class="uq-btn-filter-submit">
                        Terapkan
                    </button>
                    @if(request('search') || request('q') || request('category') || request('city') || request('sort'))
                        <a href="{{ route('services.index') }}" class="uq-btn-filter-reset" title="Reset filter">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Sponsored Search Ads (Clean Minimal) --}}
        @if(isset($sponsoredAds) && $sponsoredAds->isNotEmpty())
            <div class="uq-sponsored-section">
                <div class="uq-sponsored-head">
                    <i class="bi bi-megaphone-fill"></i>
                    <span>HASIL PENCARIAN BERSOPONSOR</span>
                </div>
                <div class="uq-sponsored-grid">
                    @foreach($sponsoredAds as $sAd)
                        <div class="uq-sponsored-card">
                            @if($sAd->poster_image)
                                <img src="{{ asset('storage/' . $sAd->poster_image) }}" alt="{{ $sAd->title }}" class="uq-sponsored-thumb">
                            @endif
                            <div class="uq-sponsored-body">
                                <span class="uq-sponsored-badge">SPONSORED</span>
                                <h4 class="uq-sponsored-title">{{ $sAd->title }}</h4>
                                <p class="uq-sponsored-vendor">{{ $sAd->vendorProfile->business_name ?? '' }}</p>
                                <a href="{{ $sAd->vendorProfile ? route('services.index', ['vendor' => $sAd->vendorProfile->slug]) : route('services.index') }}" class="uq-sponsored-link">
                                    Lihat Profil &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Results Section --}}
        @if($services->count() > 0)
            <div class="uq-results-bar">
                <p class="uq-results-count">
                    Menampilkan <strong>{{ $services->total() }}</strong> layanan vendor
                </p>
                <div class="uq-results-quicktags">
                    @if(request('category'))
                        <span class="uq-active-tag">
                            Kategori: {{ request('category') }}
                            <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}">&times;</a>
                        </span>
                    @endif
                    @if(request('city'))
                        <span class="uq-active-tag">
                            Kota: {{ request('city') }}
                            <a href="{{ request()->fullUrlWithQuery(['city' => null]) }}">&times;</a>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Service Cards Grid (PERSIS SAMA DENGAN DI LANDING PAGE: .ecom-cards-grid & .ecom-card) --}}
            <div class="ecom-cards-grid" id="serviceGrid">
                @foreach($services as $service)
                    <div class="ecom-card" data-id="{{ $service->id }}" data-lat="{{ $service->location->latitude ?? '' }}"
                        data-lng="{{ $service->location->longitude ?? '' }}" data-rating="{{ $service->rating_avg ?: 4.9 }}"
                        data-reviews="{{ $service->rating_count ?: 48 }}">
                        <div class="ecom-card-img-wrap">
                            @if($service->category)
                                <span class="ecom-card-cat-badge">{{ $service->category->name }}</span>
                            @endif
                            @php $isFav = in_array($service->id, $userWishlistIds ?? []); @endphp
                            <button type="button" class="ecom-card-wishlist {{ $isFav ? 'active' : '' }}" data-service-id="{{ $service->id }}" aria-label="Simpan ke favorit"
                                title="{{ $isFav ? 'Hapus dari favorit' : 'Simpan ke favorit' }}">
                                <i class="bi bi-heart{{ $isFav ? '-fill' : '' }}"></i>
                            </button>
                            <a href="{{ route('services.show', array_filter([$service->slug, 'event_id' => request('event_id')])) }}" style="display:contents;">
                                <img src="{{ $service->thumbnail_url }}" alt="{{ $service->name }}" class="ecom-card-img"
                                    loading="lazy">
                            </a>
                        </div>
                        <div class="ecom-card-body">
                            <h3 class="ecom-card-title">
                                <a href="{{ route('services.show', array_filter([$service->slug, 'event_id' => request('event_id')])) }}"
                                    title="{{ $service->name }}">{{ $service->name }}</a>
                            </h3>

                            <div class="ecom-card-meta">
                                <span class="ecom-card-dist" style="display:none;" title="Jarak dari lokasi acara Anda">
                                    <i class="bi bi-geo-alt-fill"></i> <span class="dist-num">0</span>
                                </span>
                                <span class="ecom-card-city">{{ $service->location->city ?? 'Indonesia' }}</span>
                            </div>

                            <div class="ecom-card-rating">
                                <span class="ecom-stars">
                                    @php $stars = round($service->rating_avg ?: 4.9); @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $stars ? '-fill' : '' }}"></i>
                                    @endfor
                                </span>
                                <span class="ecom-rating-count">({{ $service->rating_count ?: 48 }})</span>
                            </div>

                            <div class="ecom-card-price-row">
                                <span class="ecom-price-main">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                <span class="ecom-price-strike">Rp
                                    {{ number_format($service->price * 1.15, 0, ',', '.') }}</span>
                                <span class="ecom-price-unit">/{{ str_replace('per_', '', $service->price_unit) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Clean UNIQLO Pagination --}}
            <div class="uq-pagination-wrap">
                {{ $services->appends(request()->query())->links() }}
            </div>
        @else
            {{-- Clean Empty State --}}
            <div class="uq-empty-state">
                <div class="uq-empty-icon"><i class="bi bi-search"></i></div>
                <h3 class="uq-empty-title">Tidak ada layanan yang sesuai</h3>
                <p class="uq-empty-desc">Coba gunakan kata kunci lain, pilih kategori umum, atau atur ulang filter pencarian Anda.</p>
                <a href="{{ route('services.index') }}" class="uq-btn-solid">Reset Semua Filter</a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Recalculate distances based on user selected location
        if (typeof window.updatePestagoCardsDistance === 'function') {
            window.updatePestagoCardsDistance();
        }

        window.addEventListener('pestagoLocationChanged', function () {
            if (typeof window.updatePestagoCardsDistance === 'function') {
                window.updatePestagoCardsDistance();
            }
        });
    });
</script>
@endpush
