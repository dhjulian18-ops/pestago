@extends('layouts.app')

@section('title', 'PestaGo — Platform Vendor Acara #1 Indonesia')

@section('content')
    {{-- EMOX HERO SECTION --}}
    <section class="emox-hero" id="emoxHero">
        <div class="emox-container">
            <div class="emox-hero-grid">
                {{-- Main Left Banner: Carousel Bergeser Otomatis --}}
                {{-- Main Left Banner: Carousel Bergeser Otomatis --}}
                <div class="emox-carousel-wrapper" id="emoxHeroCarousel">
                    <div class="emox-carousel-track" id="emoxCarouselTrack">
                        {{-- Slide Iklan Vendor Aktif di Papan Utama / Besar (Jika Ada) --}}
                        @if(isset($homeCarouselAds) && $homeCarouselAds->isNotEmpty())
                            @foreach($homeCarouselAds as $adIdx => $ad)
                                @php
                                    $targetUrl = $ad->target_url ?: ($ad->vendorProfile ? route('services.index', ['vendor' => $ad->vendorProfile->slug]) : route('services.index'));
                                @endphp
                                <a href="{{ $targetUrl }}"
                                    class="emox-banner-main emox-slide emox-slide-sponsored {{ $adIdx === 0 ? 'is-active' : '' }}"
                                    data-slide="{{ $adIdx }}"
                                    title="{{ $ad->title }} — {{ $ad->vendorProfile->business_name ?? 'Vendor Mitra' }}">
                                    <div class="emox-popout-frame">
                                        <img src="{{ asset('storage/' . $ad->poster_image) }}" alt="{{ $ad->title }}"
                                            class="emox-hero-custom-poster emox-banner-pure-img" draggable="false" loading="eager"
                                            onerror="this.onerror=null; this.src='{{ asset('images/hero-wedding-package.svg') }}';">

                                        {{-- Optional Overlay jika vendor memilih tampilkan teks atau custom tombol --}}
                                        @if($ad->show_overlay_text || !empty($ad->button_text))
                                            <div class="emox-hero-poster-overlay">
                                                <div class="emox-hero-poster-info">
                                                    @if($ad->show_overlay_text)
                                                        <div class="emox-hero-poster-badge">
                                                            <i class="bi bi-patch-check-fill" style="color:#fbbf24;"></i>
                                                            <span>{{ $ad->vendorProfile->business_name ?? 'Vendor Mitra' }}</span>
                                                        </div>
                                                        <div class="emox-hero-poster-title">{{ $ad->title }}</div>
                                                        @if(!empty($ad->vendorProfile->description))
                                                            <div class="emox-hero-poster-desc">
                                                                {{ Str::limit($ad->vendorProfile->description, 110) }}
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                                @if(!empty($ad->button_text))
                                                    <div class="emox-hero-poster-action-wrap">
                                                        <span class="emox-hero-poster-action-btn">
                                                            {{ $ad->button_text }} &rarr;
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        @endif
                    </div>

                    {{-- Tombol Navigasi Panah --}}
                    @if(isset($homeCarouselAds) && $homeCarouselAds->count() > 1)
                        <button type="button" class="emox-carousel-arrow emox-carousel-prev" id="emoxHeroPrev"
                            aria-label="Slide sebelumnya">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="emox-carousel-arrow emox-carousel-next" id="emoxHeroNext"
                            aria-label="Slide berikutnya">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    @endif
                </div>

                {{-- Carousel Dots (Slide Indicators Outside / Below Carousel) --}}
                @if(isset($homeCarouselAds) && $homeCarouselAds->count() > 1)
                    <div class="emox-carousel-dots" id="emoxHeroDots">
                        @php $totalSlideCount = (isset($homeCarouselAds) ? $homeCarouselAds->count() : 0); @endphp
                        @for($i = 0; $i < $totalSlideCount; $i++)
                            <span class="emox-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}"
                                aria-label="Slide {{ $i + 1 }}"></span>
                        @endfor
                    </div>
                @endif

                {{-- Side banner dihilangkan — hero kini full width --}}
            </div>
        </div>
    </section>

    {{-- EXPLORE POPULAR CATEGORIES --}}
    <section class="emox-categories-section" id="exploreCategories">
        <div class="emox-container">
            <div class="emox-section-header">
                <h2 class="emox-section-title">Jelajahi Kategori Populer</h2>
                <a href="{{ route('services.index') }}" class="emox-view-all-link">
                    <span>Lihat Semua</span>
                    <i class="bi bi-chevron-right" style="font-size:12px;"></i>
                </a>
            </div>

            <div class="emox-categories-row-wrap">
                <div class="emox-categories-row" id="emoxCatRow">
                    {{-- 1. Gedung & Venue --}}
                    <a href="{{ route('services.index', ['category' => 'gedung-venue']) }}" class="emox-cat-item">
                        <div class="emox-cat-circle">
                            <img src="{{ asset('images/cat-venue.png') }}" alt="Gedung &amp; Venue">
                        </div>
                        <span class="emox-cat-name">Gedung &amp; Venue</span>
                    </a>

                    {{-- 2. Catering --}}
                    <a href="{{ route('services.index', ['category' => 'catering']) }}" class="emox-cat-item">
                        <div class="emox-cat-circle">
                            <img src="{{ asset('images/cat-catering.png') }}" alt="Catering">
                        </div>
                        <span class="emox-cat-name">Catering</span>
                    </a>

                    {{-- 3. Dekorasi & Makeup --}}
                    <a href="{{ route('services.index', ['category' => 'dekorasi-makeup']) }}" class="emox-cat-item">
                        <div class="emox-cat-circle">
                            <img src="{{ asset('images/cat-dekorasi.png') }}" alt="Dekorasi &amp; Makeup">
                        </div>
                        <span class="emox-cat-name">Dekorasi &amp; Makeup</span>
                    </a>

                    {{-- 4. Busana & Aksesoris --}}
                    <a href="{{ route('services.index', ['category' => 'busana-aksesoris']) }}" class="emox-cat-item">
                        <div class="emox-cat-circle">
                            <img src="{{ asset('images/cat-busana.png') }}" alt="Busana &amp; Aksesoris">
                        </div>
                        <span class="emox-cat-name">Busana &amp; Aksesoris</span>
                    </a>

                    {{-- 5. Dokumentasi --}}
                    <a href="{{ route('services.index', ['category' => 'dokumentasi']) }}" class="emox-cat-item">
                        <div class="emox-cat-circle">
                            <img src="{{ asset('images/cat-dokumentasi.png') }}" alt="Dokumentasi">
                        </div>
                        <span class="emox-cat-name">Dokumentasi</span>
                    </a>

                    {{-- 6. Hiburan & Entertainment --}}
                    <a href="{{ route('services.index', ['category' => 'hiburan-entertainment']) }}" class="emox-cat-item">
                        <div class="emox-cat-circle">
                            <img src="{{ asset('images/cat-hiburan.png') }}" alt="Hiburan &amp; Musik">
                        </div>
                        <span class="emox-cat-name">Hiburan &amp; Musik</span>
                    </a>

                    {{-- 7. Perlengkapan Acara --}}
                    <a href="{{ route('services.index', ['category' => 'perlengkapan-acara']) }}" class="emox-cat-item">
                        <div class="emox-cat-circle">
                            <img src="{{ asset('images/cat-perlengkapan.png') }}" alt="Perlengkapan Acara">
                        </div>
                        <span class="emox-cat-name">Perlengkapan Acara</span>
                    </a>
                </div>

                {{-- Floating Next Arrow Button --}}
                <button class="emox-cat-next-btn" id="emoxCatNextBtn" aria-label="Kategori berikutnya">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>

    {{-- LAYANAN TERPOPULER (LAYOUT GAMBAR 2) --}}
    @if(isset($popularServices) && $popularServices->count() > 0)
        <section class="ecom-section" style="background: #ffffff;" id="layananTerpopuler">
            <div class="container">
                <div class="ecom-section-header">
                    <h2 class="ecom-section-title">
                        Layanan Terpopuler
                    </h2>
                    <a href="{{ route('services.index', ['sort' => 'popular']) }}" class="ecom-view-all-link">
                        Lihat Semua <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

                <div class="ecom-cards-grid ecom-cards-scroll" id="popularServiceGrid">
                    @foreach($popularServices->take(5) as $service)
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
                                <a href="{{ route('services.show', $service->slug) }}" style="display:contents;">
                                    <img src="{{ $service->thumbnail_url }}" alt="{{ $service->name }}" class="ecom-card-img"
                                        loading="lazy">
                                </a>
                            </div>
                            <div class="ecom-card-body">
                                <h3 class="ecom-card-title">
                                    <a href="{{ route('services.show', $service->slug) }}"
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
            </div>
        </section>
    @endif

    {{-- LAYANAN TERDEKAT DI SEKITARMU (LAYOUT GAMBAR 2) --}}
    @if(isset($nearestServices) && $nearestServices->count() > 0)
        <section class="ecom-section" style="background: #ffffff;" id="layananTerdekat">
            <div class="container">
                <div class="ecom-section-header">
                    <h2 class="ecom-section-title">
                        Layanan Terdekat di Sekitarmu
                    </h2>
                    <a href="{{ route('services.index', ['sort' => 'nearest']) }}" class="ecom-view-all-link">
                        Lihat Semua <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

                <div class="ecom-cards-grid ecom-cards-scroll" id="nearestServiceGrid">
                    @foreach($nearestServices->take(5) as $service)
                        <div class="ecom-card" data-id="{{ $service->id }}" data-lat="{{ $service->location->latitude ?? '' }}"
                            data-lng="{{ $service->location->longitude ?? '' }}" data-rating="{{ $service->rating_avg ?: 4.9 }}"
                            data-reviews="{{ $service->rating_count ?: 48 }}" data-order="{{ $loop->index }}">
                            <div class="ecom-card-img-wrap">
                                @if($service->category)
                                    <span class="ecom-card-cat-badge">{{ $service->category->name }}</span>
                                @endif
                                @php $isFav = in_array($service->id, $userWishlistIds ?? []); @endphp
                                <button type="button" class="ecom-card-wishlist {{ $isFav ? 'active' : '' }}" data-service-id="{{ $service->id }}" aria-label="Simpan ke favorit"
                                    title="{{ $isFav ? 'Hapus dari favorit' : 'Simpan ke favorit' }}">
                                    <i class="bi bi-heart{{ $isFav ? '-fill' : '' }}"></i>
                                </button>
                                <a href="{{ route('services.show', $service->slug) }}" style="display:contents;">
                                    <img src="{{ $service->thumbnail_url }}" alt="{{ $service->name }}" class="ecom-card-img"
                                        loading="lazy">
                                </a>
                            </div>
                            <div class="ecom-card-body">
                                <h3 class="ecom-card-title">
                                    <a href="{{ route('services.show', $service->slug) }}"
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
                                        @php $stars = round($service->rating_avg ?: 4.8); @endphp
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $stars ? '-fill' : '' }}"></i>
                                        @endfor
                                    </span>
                                    <span class="ecom-rating-count">({{ $service->rating_count ?: 32 }})</span>
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
            </div>
        </section>
    @endif

    {{-- PAPAN IKLAN KHUSUS VENDOR (VENDOR PILIHAN) --}}
    <section class="vendor-billboard-section" id="vendorBillboardSection">
        <div class="emox-container">
            <div class="emox-section-header">
                <h2 class="emox-section-title">Vendor Pilihan</h2>
                <a href="{{ route('services.index') }}" class="emox-view-all-link">
                    <span>Lihat Semua Vendor</span>
                    <i class="bi bi-chevron-right" style="font-size:12px;"></i>
                </a>
            </div>

            <div class="vb-carousel-wrapper" id="vbCarouselWrapper">
                <div class="vb-carousel-viewport" id="vbViewport">
                    <div class="vb-carousel-track" id="vbTrack">
                        @if(isset($vendorBillboardAds) && $vendorBillboardAds->isNotEmpty())
                            @foreach($vendorBillboardAds as $vIdx => $vAd)
                                @php
                                    $vTargetUrl = $vAd->target_url ?: ($vAd->vendorProfile ? route('services.index', ['vendor' => $vAd->vendorProfile->slug]) : route('services.index'));
                                @endphp
                                <a href="{{ $vTargetUrl }}"
                                    class="vb-slide {{ $vIdx === 0 ? 'is-active' : '' }}"
                                    data-slide="{{ $vIdx }}"
                                    title="{{ $vAd->title }} — {{ $vAd->vendorProfile->business_name ?? 'Vendor Mitra' }}">
                                    <div class="vb-card-frame">
                                        <img src="{{ asset('storage/' . $vAd->poster_image) }}" alt="{{ $vAd->title }}"
                                            class="vb-poster-img" draggable="false" loading="lazy">

                                        @if($vAd->show_overlay_text || !empty($vAd->button_text))
                                            <div class="vb-poster-overlay">
                                                <div class="vb-poster-info">
                                                    @if($vAd->show_overlay_text)
                                                        @if($vAd->vendorProfile)
                                                            <div class="vb-poster-badge">
                                                                <i class="bi bi-patch-check-fill" style="color:#d81a24;"></i>
                                                                <span>{{ $vAd->vendorProfile->business_name }}</span>
                                                            </div>
                                                        @endif
                                                        <div class="vb-poster-title">{{ $vAd->title }}</div>
                                                    @endif
                                                </div>
                                                @if(!empty($vAd->button_text))
                                                    <div class="vb-poster-action-wrap">
                                                        <span class="vb-poster-action-btn">
                                                            {{ $vAd->button_text }} &rarr;
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        @else
                            {{-- Clean Fallback Banner Khusus Vendor Pilihan --}}
                            <div class="vb-slide is-active" data-slide="0">
                                <a href="{{ route('vendor.advertisements.create') }}" class="vb-card-frame vb-placeholder-frame" title="Pasang Iklan Vendor">
                                    <div class="vb-placeholder-content">
                                        <span class="vb-placeholder-tag">PAPAN IKLAN VENDOR PILIHAN</span>
                                        <h3 class="vb-placeholder-title">Promosikan Layanan Acara Anda di Bagian Ini</h3>
                                        <p class="vb-placeholder-desc">Dapatkan sorotan langsung dari ribuan calon customer yang sedang merencanakan pernikahan dan pesta.</p>
                                        <span class="vb-placeholder-btn">Pasang Iklan Vendor Sekarang &rarr;</span>
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tombol Navigasi Panah --}}
                @if(isset($vendorBillboardAds) && $vendorBillboardAds->count() > 1)
                    <button type="button" class="vb-arrow vb-prev" id="vbPrev" aria-label="Slide sebelumnya">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="vb-arrow vb-next" id="vbNext" aria-label="Slide berikutnya">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                @endif
            </div>

            {{-- Carousel Dots --}}
            @if(isset($vendorBillboardAds) && $vendorBillboardAds->count() > 1)
                <div class="vb-dots" id="vbDots">
                    @for($i = 0; $i < $vendorBillboardAds->count(); $i++)
                        <span class="vb-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}" aria-label="Slide {{ $i + 1 }}"></span>
                    @endfor
                </div>
            @endif
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Hero Auto-Sliding & Interactive Touch-Draggable Carousel (Infinite Seamless Loop)
            const heroCarousel = document.getElementById('emoxHeroCarousel');
            const heroTrack = document.getElementById('emoxCarouselTrack');
            const heroDotsContainer = document.getElementById('emoxHeroDots');
            const heroPrev = document.getElementById('emoxHeroPrev');
            const heroNext = document.getElementById('emoxHeroNext');

            if (!heroCarousel || !heroTrack) return;

            // Collect all real slides initially in DOM
            const originalSlides = Array.from(heroTrack.querySelectorAll('.emox-slide'));
            const N = originalSlides.length;
            if (N === 0) return;

            // Ensure dot count matches original slide count N exactly
            let heroDots = heroDotsContainer ? Array.from(heroDotsContainer.querySelectorAll('.emox-dot')) : [];
            if (heroDotsContainer && heroDots.length !== N) {
                heroDotsContainer.innerHTML = '';
                for (let i = 0; i < N; i++) {
                    const dot = document.createElement('span');
                    dot.className = 'emox-dot' + (i === 0 ? ' active' : '');
                    dot.setAttribute('data-index', i);
                    dot.setAttribute('aria-label', 'Slide ' + (i + 1));
                    heroDotsContainer.appendChild(dot);
                }
                heroDots = Array.from(heroDotsContainer.querySelectorAll('.emox-dot'));
            }

            // Tag original slides with their real 0-indexed slide index
            originalSlides.forEach((slide, idx) => {
                slide.setAttribute('data-real-index', idx);
                slide.setAttribute('data-is-clone', 'false');
            });

            if (N === 1) {
                originalSlides[0].classList.add('is-active');
                if (heroPrev) heroPrev.style.display = 'none';
                if (heroNext) heroNext.style.display = 'none';
                if (heroDotsContainer) heroDotsContainer.style.display = 'none';
                return;
            }

            const CLONES_COUNT = N >= 2 ? 2 : 0;
            let vIndex = CLONES_COUNT; // Virtual index starts at first real slide
            let isTransitioning = false;
            let slideInterval = null;
            let justDragged = false;
            let currentTranslateX = 0;

            // Clone slides for seamless infinite loop if N >= 2
            if (CLONES_COUNT > 0) {
                // Clones before: slide N-2, slide N-1
                const clonePre1 = originalSlides[N - 2].cloneNode(true);
                const clonePre2 = originalSlides[N - 1].cloneNode(true);
                clonePre1.setAttribute('data-is-clone', 'true');
                clonePre2.setAttribute('data-is-clone', 'true');
                clonePre1.classList.remove('is-active');
                clonePre2.classList.remove('is-active');

                // Clones after: slide 0, slide 1
                const clonePost1 = originalSlides[0].cloneNode(true);
                const clonePost2 = originalSlides[1].cloneNode(true);
                clonePost1.setAttribute('data-is-clone', 'true');
                clonePost2.setAttribute('data-is-clone', 'true');
                clonePost1.classList.remove('is-active');
                clonePost2.classList.remove('is-active');

                // Insert into track
                heroTrack.insertBefore(clonePre2, originalSlides[0]);
                heroTrack.insertBefore(clonePre1, clonePre2);
                heroTrack.appendChild(clonePost1);
                heroTrack.appendChild(clonePost2);
            }

            // All slides in track (original + clones)
            function getAllSlides() {
                return Array.from(heroTrack.querySelectorAll('.emox-slide'));
            }

            // Prevent drag ghosting on images inside slides
            heroTrack.querySelectorAll('img').forEach(img => {
                img.setAttribute('draggable', 'false');
            });

            // Calculate target translateX to perfectly center the slide at virtual index `idx`
            function getTargetTranslateX(idx) {
                const allSlides = getAllSlides();
                if (!allSlides[idx]) return 0;
                const containerWidth = heroCarousel.offsetWidth;
                const targetSlide = allSlides[idx];
                const slideWidth = targetSlide.offsetWidth;
                const slideLeft = targetSlide.offsetLeft;
                return (containerWidth - slideWidth) / 2 - slideLeft;
            }

            // Update active states on slides and dots
            function updateActiveUI(realIdx) {
                // Update dots
                heroDots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === realIdx);
                });
                // Update slide classes
                const allSlides = getAllSlides();
                allSlides.forEach(slide => {
                    const rIdx = parseInt(slide.getAttribute('data-real-index'), 10);
                    slide.classList.toggle('is-active', rIdx === realIdx);
                });
            }

            // Move to virtual index
            function moveToVirtualIndex(idx, animate = true) {
                const allSlides = getAllSlides();
                if (!allSlides[idx]) return;

                vIndex = idx;
                const targetX = getTargetTranslateX(vIndex);
                currentTranslateX = targetX;

                if (animate) {
                    isTransitioning = true;
                    heroTrack.style.transition = 'transform 0.42s cubic-bezier(0.25, 1, 0.5, 1)';
                } else {
                    heroTrack.style.transition = 'none';
                }

                heroTrack.style.transform = `translate3d(${targetX}px, 0, 0)`;

                // Compute corresponding real slide index (0 to N-1)
                const realIdx = ((vIndex - CLONES_COUNT) % N + N) % N;
                updateActiveUI(realIdx);
            }

            // Handle seamless wrap when reaching clones
            heroTrack.addEventListener('transitionend', function (e) {
                if (e.target !== heroTrack || e.propertyName !== 'transform') return;
                isTransitioning = false;

                if (CLONES_COUNT > 0) {
                    // If moved past the last real slide into the post-clones:
                    // (e.g. from slide N-1 forward into clone 0, clone 1)
                    if (vIndex >= N + CLONES_COUNT) {
                        heroTrack.style.transition = 'none';
                        vIndex = vIndex - N; // Snap back to corresponding real slide
                        const targetX = getTargetTranslateX(vIndex);
                        currentTranslateX = targetX;
                        heroTrack.style.transform = `translate3d(${targetX}px, 0, 0)`;
                        heroTrack.offsetHeight; // force reflow
                    }
                    // If moved before the first real slide into pre-clones:
                    else if (vIndex < CLONES_COUNT) {
                        heroTrack.style.transition = 'none';
                        vIndex = vIndex + N; // Snap forward to corresponding real slide
                        const targetX = getTargetTranslateX(vIndex);
                        currentTranslateX = targetX;
                        heroTrack.style.transform = `translate3d(${targetX}px, 0, 0)`;
                        heroTrack.offsetHeight; // force reflow
                    }
                }
            });

            // Autoplay controls
            function startSlideAutoplay() {
                stopSlideAutoplay();
                if (N <= 1) return;
                slideInterval = setInterval(() => {
                    moveToVirtualIndex(vIndex + 1, true);
                }, 4000);
            }

            function stopSlideAutoplay() {
                if (slideInterval) {
                    clearInterval(slideInterval);
                    slideInterval = null;
                }
            }

            // Arrow buttons
            if (heroPrev) {
                heroPrev.addEventListener('click', (e) => {
                    e.preventDefault();
                    moveToVirtualIndex(vIndex - 1, true);
                    startSlideAutoplay();
                });
            }

            if (heroNext) {
                heroNext.addEventListener('click', (e) => {
                    e.preventDefault();
                    moveToVirtualIndex(vIndex + 1, true);
                    startSlideAutoplay();
                });
            }

            // Dot buttons click
            heroDots.forEach((dot, dotIdx) => {
                dot.addEventListener('click', () => {
                    moveToVirtualIndex(dotIdx + CLONES_COUNT, true);
                    startSlideAutoplay();
                });
            });

            // Pause on hover (desktop)
            heroCarousel.addEventListener('mouseenter', stopSlideAutoplay);
            heroCarousel.addEventListener('mouseleave', () => {
                if (!isDragging) startSlideAutoplay();
            });

            // Page Visibility pause
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) stopSlideAutoplay();
                else startSlideAutoplay();
            });

            // ——— FLUID MANUAL DRAG & TOUCH SWIPE ———
            let isDragging = false;
            let isSwiping = false;
            let startX = 0;
            let startY = 0;
            let startTranslateX = 0;
            let dragStartTime = 0;

            function onDragStart(e) {
                if (N <= 1) return;
                stopSlideAutoplay();

                const pointer = e.touches ? e.touches[0] : e;
                startX = pointer.clientX;
                startY = pointer.clientY;
                startTranslateX = currentTranslateX;
                dragStartTime = Date.now();

                isDragging = true;
                isSwiping = false;

                // Seamless wrap check before drag begins:
                // If currently sitting on a clone, immediately snap to real slide
                if (CLONES_COUNT > 0) {
                    if (vIndex >= N + CLONES_COUNT) {
                        heroTrack.style.transition = 'none';
                        vIndex = vIndex - N;
                        startTranslateX = getTargetTranslateX(vIndex);
                        currentTranslateX = startTranslateX;
                        heroTrack.style.transform = `translate3d(${startTranslateX}px, 0, 0)`;
                        heroTrack.offsetHeight;
                    } else if (vIndex < CLONES_COUNT) {
                        heroTrack.style.transition = 'none';
                        vIndex = vIndex + N;
                        startTranslateX = getTargetTranslateX(vIndex);
                        currentTranslateX = startTranslateX;
                        heroTrack.style.transform = `translate3d(${startTranslateX}px, 0, 0)`;
                        heroTrack.offsetHeight;
                    }
                }

                heroTrack.style.transition = 'none';
            }

            function onDragMove(e) {
                if (!isDragging) return;

                const pointer = e.touches ? e.touches[0] : e;
                const deltaX = pointer.clientX - startX;
                const deltaY = pointer.clientY - startY;

                // Determine intent if not already swiping
                if (!isSwiping) {
                    if (Math.abs(deltaY) > Math.abs(deltaX) && Math.abs(deltaY) > 7) {
                        // Vertical scroll — abort carousel drag
                        isDragging = false;
                        heroTrack.classList.remove('is-dragging');
                        startSlideAutoplay();
                        return;
                    }
                    if (Math.abs(deltaX) > 7) {
                        isSwiping = true;
                        heroTrack.classList.add('is-dragging');
                    }
                }

                if (isSwiping) {
                    if (e.cancelable) e.preventDefault();
                    currentTranslateX = startTranslateX + deltaX;
                    heroTrack.style.transform = `translate3d(${currentTranslateX}px, 0, 0)`;
                }
            }

            function onDragEnd(e) {
                if (!isDragging) return;
                isDragging = false;
                heroTrack.classList.remove('is-dragging');

                if (!isSwiping) {
                    startSlideAutoplay();
                    return;
                }

                isSwiping = false;

                // Suppress link click after dragging
                justDragged = true;
                setTimeout(() => { justDragged = false; }, 260);

                const deltaX = currentTranslateX - startTranslateX;
                const timeDiff = Math.max(1, Date.now() - dragStartTime);
                const velocity = Math.abs(deltaX) / timeDiff; // px/ms

                const allSlides = getAllSlides();
                const activeSlide = allSlides[vIndex];
                const slideWidth = activeSlide ? activeSlide.offsetWidth : 300;
                const threshold = Math.min(slideWidth * 0.16, 50);

                if (deltaX < -threshold || (deltaX < -20 && velocity > 0.35)) {
                    // Swiped next
                    moveToVirtualIndex(vIndex + 1, true);
                } else if (deltaX > threshold || (deltaX > 20 && velocity > 0.35)) {
                    // Swiped prev
                    moveToVirtualIndex(vIndex - 1, true);
                } else {
                    // Snap back to current slide
                    moveToVirtualIndex(vIndex, true);
                }

                startSlideAutoplay();
            }

            // Attach touch events (mobile)
            heroCarousel.addEventListener('touchstart', onDragStart, { passive: true });
            heroCarousel.addEventListener('touchmove', onDragMove, { passive: false });
            heroCarousel.addEventListener('touchend', onDragEnd, { passive: true });
            heroCarousel.addEventListener('touchcancel', onDragEnd, { passive: true });

            // Attach pointer events for mouse dragging (desktop)
            heroCarousel.addEventListener('mousedown', (e) => {
                if (e.button !== 0) return; // only left click
                onDragStart(e);
            });
            window.addEventListener('mousemove', onDragMove);
            window.addEventListener('mouseup', onDragEnd);

            // Block click on slide links if just finished dragging
            heroTrack.addEventListener('click', (e) => {
                if (justDragged) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);

            // Window resize recalculate
            let resizeTimer = null;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    moveToVirtualIndex(vIndex, false);
                }, 80);
            }, { passive: true });

            // Initialize carousel at starting slide
            setTimeout(() => {
                moveToVirtualIndex(CLONES_COUNT, false);
                startSlideAutoplay();
            }, 50);

            // Horizontal category row scrolling
            const catRow = document.getElementById('emoxCatRow');
            const nextBtn = document.getElementById('emoxCatNextBtn');
            if (catRow && nextBtn) {
                nextBtn.addEventListener('click', () => {
                    const maxScroll = catRow.scrollWidth - catRow.clientWidth;
                    if (catRow.scrollLeft >= maxScroll - 20) {
                        catRow.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                        catRow.scrollBy({ left: 260, behavior: 'smooth' });
                    }
                });
            }

            // ——— Vendor Billboard / Vendor Pilihan Carousel ———
            const vbTrack = document.getElementById('vbTrack');
            const vbViewport = document.getElementById('vbViewport');
            const vbPrev = document.getElementById('vbPrev');
            const vbNext = document.getElementById('vbNext');
            const vbWrapper = document.getElementById('vbCarouselWrapper');
            const vbDotsContainer = document.getElementById('vbDots');

            if (vbTrack && vbViewport) {
                const vbSlides = Array.from(vbTrack.querySelectorAll('.vb-slide'));
                const vbCount = vbSlides.length;
                let vbIndex = 0;
                let vbAutoInterval = null;

                function updateVbSlide(idx) {
                    if (vbCount === 0) return;
                    vbIndex = (idx + vbCount) % vbCount;

                    vbSlides.forEach((slide, i) => {
                        slide.classList.toggle('is-active', i === vbIndex);
                    });

                    const slideWidth = vbViewport.offsetWidth;
                    vbTrack.style.transform = `translateX(-${vbIndex * slideWidth}px)`;

                    if (vbDotsContainer) {
                        const dots = vbDotsContainer.querySelectorAll('.vb-dot');
                        dots.forEach((dot, i) => {
                            dot.classList.toggle('active', i === vbIndex);
                        });
                    }
                }

                function startVbAutoplay() {
                    stopVbAutoplay();
                    if (vbCount > 1) {
                        vbAutoInterval = setInterval(() => {
                            updateVbSlide(vbIndex + 1);
                        }, 5000);
                    }
                }

                function stopVbAutoplay() {
                    if (vbAutoInterval) {
                        clearInterval(vbAutoInterval);
                        vbAutoInterval = null;
                    }
                }

                if (vbPrev) {
                    vbPrev.addEventListener('click', (e) => {
                        e.preventDefault();
                        updateVbSlide(vbIndex - 1);
                        startVbAutoplay();
                    });
                }

                if (vbNext) {
                    vbNext.addEventListener('click', (e) => {
                        e.preventDefault();
                        updateVbSlide(vbIndex + 1);
                        startVbAutoplay();
                    });
                }

                if (vbDotsContainer) {
                    vbDotsContainer.addEventListener('click', (e) => {
                        const dot = e.target.closest('.vb-dot');
                        if (dot) {
                            const targetIdx = parseInt(dot.getAttribute('data-index'), 10);
                            if (!isNaN(targetIdx)) {
                                updateVbSlide(targetIdx);
                                startVbAutoplay();
                            }
                        }
                    });
                }

                if (vbWrapper) {
                    vbWrapper.addEventListener('mouseenter', stopVbAutoplay);
                    vbWrapper.addEventListener('mouseleave', startVbAutoplay);

                    let vbTouchStart = 0;
                    vbWrapper.addEventListener('touchstart', (e) => {
                        vbTouchStart = e.changedTouches[0].screenX;
                        stopVbAutoplay();
                    }, { passive: true });

                    vbWrapper.addEventListener('touchend', (e) => {
                        const diff = vbTouchStart - e.changedTouches[0].screenX;
                        if (Math.abs(diff) > 40) {
                            updateVbSlide(diff > 0 ? vbIndex + 1 : vbIndex - 1);
                        }
                        startVbAutoplay();
                    }, { passive: true });
                }

                window.addEventListener('resize', () => {
                    updateVbSlide(vbIndex);
                }, { passive: true });

                updateVbSlide(0);
                startVbAutoplay();
            }

            // Recalculate distances on load and listen to location changes
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