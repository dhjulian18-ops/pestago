@extends('layouts.app')
@section('title', 'Buat Acara — PestaGo')

@section('content')
<div class="uq-event-page">
    <div class="uq-event-container">
        {{-- Back Link --}}
        <a href="{{ route('customer.events.index') }}" class="uq-back-link">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>

        {{-- Page Header --}}
        <div class="uq-event-header">
            <h1 class="uq-event-title">Buat Acara Baru</h1>
            <p class="uq-event-subtitle">Lengkapi detail acara untuk menemukan vendor yang tepat</p>
        </div>

        {{-- Validation Errors --}}
        @if($errors->any())
        <div class="uq-alert uq-alert-danger">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('customer.events.store') }}" method="POST" class="uq-event-form">
            @csrf

            {{-- Section 1: Detail Acara --}}
            <div class="uq-form-section">
                <div class="uq-section-label">Detail Acara</div>

                <div class="uq-field">
                    <label class="uq-field-label">Nama Acara <span class="uq-required">*</span></label>
                    <input type="text" name="name" class="uq-field-input" value="{{ old('name') }}" placeholder="cth: Pernikahan Sari & Budi" required>
                </div>

                <div class="uq-field-row">
                    <div class="uq-field">
                        <label class="uq-field-label">Jenis Acara <span class="uq-required">*</span></label>
                        <select name="event_type" class="uq-field-select" required>
                            <option value="">— Pilih Jenis —</option>
                            @foreach($eventTypes as $val => $label)
                                <option value="{{ $val }}" {{ old('event_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="uq-field">
                        <label class="uq-field-label">Tanggal Acara <span class="uq-required">*</span></label>
                        <input type="date" name="event_date" class="uq-field-input" value="{{ old('event_date') }}" min="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="uq-field-row">
                    <div class="uq-field">
                        <label class="uq-field-label">Jumlah Tamu</label>
                        <input type="number" name="guest_count" class="uq-field-input" value="{{ old('guest_count') }}" min="1" placeholder="cth: 500">
                    </div>
                    <div class="uq-field">
                        <label class="uq-field-label">Budget (Rp)</label>
                        <input type="number" name="budget" class="uq-field-input" value="{{ old('budget') }}" min="0" step="100000" placeholder="cth: 50000000">
                    </div>
                </div>
            </div>

            {{-- Section 2: Lokasi --}}
            <div class="uq-form-section">
                <div class="uq-section-label">Lokasi Acara</div>

                <div class="uq-field">
                    <label class="uq-field-label">Alamat</label>
                    <input type="text" name="location_address" class="uq-field-input" value="{{ old('location_address') }}" placeholder="Jl. ...">
                </div>

                <div class="uq-field">
                    <label class="uq-field-label">Kota</label>
                    <input type="text" name="location_city" class="uq-field-input" value="{{ old('location_city') }}" placeholder="cth: Jakarta Selatan">
                </div>

                <div class="uq-field">
                    <label class="uq-field-label">Lokasi di Peta <span class="uq-label-opt">(opsional)</span></label>
                    <div class="uq-map-wrap">
                        <div id="mapPicker" style="height:220px;width:100%;"></div>
                    </div>
                    <input type="hidden" name="location_latitude" id="latitude" value="{{ old('location_latitude') }}">
                    <input type="hidden" name="location_longitude" id="longitude" value="{{ old('location_longitude') }}">
                </div>
            </div>

            {{-- Section 3: Kebutuhan Vendor --}}
            <div class="uq-form-section">
                <div class="uq-section-label">Kebutuhan Vendor</div>

                <div class="uq-check-grid">
                    @foreach($categories as $cat)
                    <label class="uq-check-item">
                        <input type="checkbox" name="requirements[]" value="{{ $cat->id }}" {{ in_array($cat->id, old('requirements', [])) ? 'checked' : '' }}>
                        <span>{{ $cat->name }}</span>
                    </label>
                    @endforeach
                </div>
                <p class="uq-field-hint">Pilih kategori vendor yang Anda butuhkan</p>
            </div>

            {{-- Section 4: Catatan --}}
            <div class="uq-form-section">
                <div class="uq-section-label">Catatan Tambahan</div>

                <div class="uq-field">
                    <textarea name="notes" class="uq-field-textarea" rows="3" placeholder="Detail tambahan atau preferensi khusus...">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="uq-btn-submit">
                Buat Acara
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initMapPicker === 'function') {
        initMapPicker('mapPicker', 'latitude', 'longitude', -6.2088, 106.8456);
    }
});
</script>
@endpush
