@extends('layouts.app')
@section('title', 'Beri Ulasan Layanan — PestaGo')

@section('content')
<div class="uq-review-wrapper">
    <div class="container uq-review-container">
        {{-- Navigation Back Link --}}
        <div class="uq-review-header">
            <a href="{{ route('customer.orders.show', $item->order) }}" class="uq-review-back-link">
                <i class="bi bi-arrow-left"></i> Kembali ke Detail Pesanan
            </a>
            <h1 class="uq-review-title">Beri Ulasan Layanan</h1>
            <p class="uq-review-desc">Bagikan pengalaman nyata Anda untuk membantu vendor dan calon pelanggan lainnya.</p>
        </div>

        {{-- Service Information Card --}}
        <div class="uq-review-card uq-service-summary-card">
            <div class="uq-service-thumb-wrap">
                @if($item->service && $item->service->thumbnail_url)
                    <img src="{{ $item->service->thumbnail_url }}" alt="{{ $item->service_name_snapshot }}" class="uq-service-thumb">
                @else
                    <div class="uq-service-thumb-placeholder">
                        <i class="bi bi-shop"></i>
                    </div>
                @endif
            </div>
            <div class="uq-service-meta">
                <span class="uq-review-badge-done"><i class="bi bi-check2-circle"></i> Pesanan Selesai</span>
                <h2 class="uq-service-name">{{ $item->service_name_snapshot }}</h2>
                <div class="uq-service-vendor">
                    <i class="bi bi-person-badge"></i> {{ $item->vendorProfile->business_name ?? 'Vendor PestaGo' }}
                    @if($item->booking_date)
                        · <i class="bi bi-calendar-event"></i> {{ \Carbon\Carbon::parse($item->booking_date)->translatedFormat('d M Y') }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Review Form Card --}}
        <div class="uq-review-card">
            <form action="{{ route('customer.reviews.store', $item) }}" method="POST" enctype="multipart/form-data" id="reviewForm">
                @csrf

                {{-- Rating Stars Section --}}
                <div class="uq-form-group">
                    <label class="uq-label-clean">
                        Penilaian Keseluruhan <span class="uq-req">*</span>
                    </label>
                    <div class="uq-star-rating-box">
                        <div class="uq-stars-row" id="starRatingRow">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button" class="uq-star-btn" data-rating="{{ $i }}" title="{{ $i }} Bintang">
                                <i class="bi bi-star"></i>
                            </button>
                            @endfor
                        </div>
                        <span class="uq-rating-feedback" id="ratingFeedbackText">Pilih 1 - 5 bintang penilaian</span>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="{{ old('rating', '') }}" required>
                    @error('rating')
                        <div class="uq-error-msg"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Review Comment Section --}}
                <div class="uq-form-group">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <label class="uq-label-clean" for="comment" style="margin:0;">
                            Ulasan Pengalaman <span class="uq-req">*</span>
                        </label>
                        <span class="uq-char-count" id="charCounter">0 / 1000</span>
                    </div>
                    <textarea name="comment" id="comment" class="uq-input-clean uq-textarea-clean" rows="5"
                              placeholder="Ceritakan kepuasan Anda terhadap ketepatan waktu, kualitas layanan, dan keramahan vendor... (minimal 10 karakter)"
                              required minlength="10" maxlength="1000" oninput="updateCharCount(this)">{{ old('comment') }}</textarea>
                    <span class="uq-input-hint">Minimal 10 karakter untuk memberikan ulasan yang bermanfaat.</span>
                    @error('comment')
                        <div class="uq-error-msg"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Photo Upload Section --}}
                <div class="uq-form-group">
                    <label class="uq-label-clean">
                        Foto Dokumentasi Acara / Hasil Layanan <span style="font-weight:400;color:#888888;">(Opsional)</span>
                    </label>
                    <div class="uq-file-upload-box">
                        <input type="file" name="images[]" id="reviewImagesInput" class="uq-file-input" multiple accept="image/*" onchange="previewReviewImages(this)">
                        <div class="uq-file-upload-content">
                            <i class="bi bi-camera" style="font-size:1.6rem;color:#666666;"></i>
                            <div style="font-weight:700;font-size:0.86rem;color:#111111;margin-top:6px;">Pilih Foto atau Seret ke Sini</div>
                            <span style="font-size:0.75rem;color:#888888;margin-top:2px;">Maksimal 3 foto (Format JPG, PNG · Maks. 2MB per foto)</span>
                        </div>
                    </div>
                    <div id="imagePreviewContainer" class="uq-image-previews"></div>
                    @error('images.*')
                        <div class="uq-error-msg"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div style="margin-top:24px;">
                    <button type="submit" class="uq-review-submit-btn" id="submitReviewBtn">
                        <i class="bi bi-check-lg"></i> Kirim Ulasan Layanan
                    </button>
                    <div class="uq-guarantee-note">
                        <i class="bi bi-shield-check" style="color:#16a34a;"></i> Ulasan Anda akan langsung tampil di halaman toko vendor setelah dikirim.
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.uq-review-wrapper {
    padding-top: calc(var(--navbar-height, 128px) + 32px);
    padding-bottom: 80px;
    background: #fafafa;
    min-height: 100vh;
}

.uq-review-container {
    max-width: 680px;
    margin: 0 auto;
    padding: 0 18px;
}

.uq-review-header {
    margin-bottom: 22px;
}

.uq-review-back-link {
    font-size: 0.82rem;
    font-weight: 700;
    color: #666666;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    transition: color 0.15s ease;
}

.uq-review-back-link:hover {
    color: #111111;
}

.uq-review-title {
    font-size: 1.55rem;
    font-weight: 800;
    color: #111111;
    margin: 0;
    letter-spacing: -0.02em;
    line-height: 1.25;
}

.uq-review-desc {
    font-size: 0.85rem;
    color: #666666;
    margin: 6px 0 0;
}

/* Card Styling - Uniqlo Style Clean */
.uq-review-card {
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 0 !important;
    padding: 24px;
    margin-bottom: 20px;
    box-sizing: border-box;
}

.uq-service-summary-card {
    display: flex;
    align-items: center;
    gap: 16px;
}

.uq-service-thumb-wrap {
    flex-shrink: 0;
}

.uq-service-thumb {
    width: 68px;
    height: 68px;
    object-fit: cover;
    border: 1px solid #e5e5e5;
    border-radius: 0 !important;
    display: block;
}

.uq-service-thumb-placeholder {
    width: 68px;
    height: 68px;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #888888;
    font-size: 1.5rem;
    border: 1px solid #e5e5e5;
    border-radius: 0 !important;
}

.uq-service-meta {
    flex: 1;
    min-width: 0;
}

.uq-review-badge-done {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.72rem;
    font-weight: 800;
    color: #15803d;
    background: #dcfce7;
    padding: 2px 8px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-radius: 0 !important;
    margin-bottom: 6px;
}

.uq-service-name {
    font-size: 1.02rem;
    font-weight: 800;
    color: #111111;
    margin: 0 0 4px;
    line-height: 1.35;
    word-break: break-word;
}

.uq-service-vendor {
    font-size: 0.8rem;
    color: #666666;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

/* Form Groups & Inputs */
.uq-form-group {
    margin-bottom: 22px;
}

.uq-label-clean {
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: #333333;
    text-transform: uppercase;
    margin-bottom: 8px;
    display: block;
}

.uq-req {
    color: #D81A24;
}

.uq-char-count {
    font-size: 0.74rem;
    color: #888888;
    font-weight: 600;
}

.uq-input-clean {
    width: 100%;
    border: 1px solid #d4d4d4;
    border-radius: 0 !important;
    padding: 12px 14px;
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

.uq-textarea-clean {
    resize: vertical;
    line-height: 1.6;
}

.uq-input-hint {
    font-size: 0.74rem;
    color: #888888;
    margin-top: 5px;
    display: block;
}

.uq-error-msg {
    font-size: 0.78rem;
    font-weight: 700;
    color: #dc2626;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Star Rating Section */
.uq-star-rating-box {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #fafafa;
    border: 1px solid #e5e5e5;
    padding: 14px 18px;
    flex-wrap: wrap;
}

.uq-stars-row {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.uq-star-btn {
    background: none;
    border: none;
    padding: 4px 6px;
    font-size: 2rem;
    line-height: 1;
    color: #d1d5db;
    cursor: pointer;
    transition: transform 0.1s ease, color 0.15s ease;
}

.uq-star-btn:hover {
    transform: scale(1.15);
}

.uq-star-btn.active,
.uq-star-btn.hover-active {
    color: #f59e0b;
}

.uq-rating-feedback {
    font-size: 0.88rem;
    font-weight: 700;
    color: #111111;
}

/* File Upload Box */
.uq-file-upload-box {
    position: relative;
    border: 1.5px dashed #d4d4d4;
    background: #fafafa;
    padding: 24px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease;
}

.uq-file-upload-box:hover {
    border-color: #111111;
    background: #f5f5f5;
}

.uq-file-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.uq-image-previews {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.uq-preview-thumb {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border: 1px solid #111111;
    border-radius: 0 !important;
}

/* Submit Button */
.uq-review-submit-btn {
    width: 100%;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.92rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    background: #111111;
    color: #ffffff;
    border: none;
    border-radius: 0 !important;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.2s ease;
}

.uq-review-submit-btn:hover {
    background: #D81A24;
}

.uq-guarantee-note {
    font-size: 0.75rem;
    color: #888888;
    text-align: center;
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

@media (max-width: 768px) {
    .uq-review-wrapper {
        padding-top: calc(var(--navbar-height, 140px) + 36px) !important;
        padding-bottom: 90px;
    }

    .uq-review-card {
        padding: 18px 14px;
    }

    .uq-star-btn {
        font-size: 1.75rem;
        padding: 2px 4px;
    }

    .uq-rating-feedback {
        font-size: 0.8rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function updateCharCount(el) {
    const counter = document.getElementById('charCounter');
    if (counter) {
        counter.textContent = el.value.length + ' / 1000';
        if (el.value.length < 10) {
            counter.style.color = '#dc2626';
        } else {
            counter.style.color = '#15803d';
        }
    }
}

function previewReviewImages(input) {
    const container = document.getElementById('imagePreviewContainer');
    if (!container) return;
    container.innerHTML = '';

    if (input.files) {
        const filesAmount = Math.min(input.files.length, 3);
        for (let i = 0; i < filesAmount; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'uq-preview-thumb';
                img.alt = 'Pratinjau Foto ' + (i + 1);
                container.appendChild(img);
            };
            reader.readAsDataURL(input.files[i]);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.uq-star-btn');
    const ratingInput = document.getElementById('ratingInput');
    const feedbackText = document.getElementById('ratingFeedbackText');
    const commentInput = document.getElementById('comment');

    const labels = [
        'Pilih 1 - 5 bintang penilaian',
        '1 Bintang · Sangat Mengecewakan',
        '2 Bintang · Kurang Memuaskan',
        '3 Bintang · Cukup Baik',
        '4 Bintang · Sangat Baik & Puas',
        '5 Bintang · Luar Biasa Sempurna!'
    ];

    function renderStarState(activeCount, isHover = false) {
        stars.forEach((s, idx) => {
            const starIcon = s.querySelector('i');
            const isActive = idx < activeCount;
            if (isActive) {
                starIcon.className = 'bi bi-star-fill';
                s.classList.add(isHover ? 'hover-active' : 'active');
            } else {
                starIcon.className = 'bi bi-star';
                s.classList.remove('active', 'hover-active');
            }
        });
    }

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating, 10);
            ratingInput.value = rating;
            feedbackText.textContent = labels[rating];
            feedbackText.style.color = rating >= 4 ? '#15803d' : (rating >= 3 ? '#b45309' : '#dc2626');
            renderStarState(rating, false);
        });

        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating, 10);
            feedbackText.textContent = labels[rating];
            renderStarState(rating, true);
        });
    });

    const starRow = document.getElementById('starRatingRow');
    if (starRow) {
        starRow.addEventListener('mouseleave', function() {
            const currentRating = parseInt(ratingInput.value, 10) || 0;
            feedbackText.textContent = labels[currentRating];
            feedbackText.style.color = currentRating >= 4 ? '#15803d' : (currentRating >= 3 ? '#b45309' : '#111111');
            renderStarState(currentRating, false);
        });
    }

    // Initialize if old value exists
    if (ratingInput && ratingInput.value) {
        const initialVal = parseInt(ratingInput.value, 10);
        if (initialVal >= 1 && initialVal <= 5) {
            feedbackText.textContent = labels[initialVal];
            renderStarState(initialVal, false);
        }
    }

    if (commentInput) {
        updateCharCount(commentInput);
    }
});
</script>
@endpush
