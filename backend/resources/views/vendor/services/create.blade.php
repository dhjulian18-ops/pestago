@extends('layouts.app')
@section('title', 'Tambah Layanan — Vendor PestaGo')

@section('content')
<div class="dashboard">
    @include('vendor._sidebar', ['active' => 'services'])

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Tambah Layanan Baru</h1>
            <p class="dashboard-subtitle">Isi detail layanan yang ingin Anda tawarkan</p>
        </div>

        <div class="card">
            <div class="card-body" style="padding:var(--space-8);">
                <form action="{{ route('vendor.services.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h3 style="font-size:var(--font-size-base);font-weight:700;color:var(--gray-700);padding-bottom:var(--space-3);border-bottom:1px solid var(--gray-100);margin-bottom:var(--space-5);">
                        <i class="bi bi-info-circle"></i> Informasi Layanan
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Nama Layanan *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="cth: Paket Wedding Premium 500 Tamu" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label">Kategori *</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">— Pilih —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Satuan Harga *</label>
                            <select name="price_unit" class="form-control" required>
                                <option value="per_acara" {{ old('price_unit') == 'per_acara' ? 'selected' : '' }}>Per Acara</option>
                                <option value="per_orang" {{ old('price_unit') == 'per_orang' ? 'selected' : '' }}>Per Orang</option>
                                <option value="per_hari" {{ old('price_unit') == 'per_hari' ? 'selected' : '' }}>Per Hari</option>
                                <option value="per_paket" {{ old('price_unit') == 'per_paket' ? 'selected' : '' }}>Per Paket</option>
                                <option value="penawaran" {{ old('price_unit') == 'penawaran' ? 'selected' : '' }}>Penawaran</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Harga (Rp) *</label>
                        <input type="number" name="price" class="form-control" value="{{ old('price') }}" min="0" step="1000" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Layanan *</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Jelaskan detail layanan, apa saja yang termasuk..." required>{{ old('description') }}</textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label">Kapasitas Min</label>
                            <input type="number" name="capacity_min" class="form-control" value="{{ old('capacity_min') }}" min="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kapasitas Max</label>
                            <input type="number" name="capacity_max" class="form-control" value="{{ old('capacity_max') }}" min="1">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fasilitas</label>
                        <input type="text" name="facilities" class="form-control" value="{{ old('facilities') }}" placeholder="AC, Sound System, Parkir (pisahkan dengan koma)">
                        <span class="form-hint">Pisahkan setiap fasilitas dengan tanda koma</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kebijakan Pembatalan</label>
                        <textarea name="cancellation_policy" class="form-control" rows="2">{{ old('cancellation_policy') }}</textarea>
                    </div>

                    <h3 style="font-size:var(--font-size-base);font-weight:700;color:var(--gray-700);padding-bottom:var(--space-3);border-bottom:1px solid var(--gray-100);margin:var(--space-6) 0 var(--space-5);">
                        <i class="bi bi-images"></i> Foto Layanan
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Upload Foto (maks 8 foto, masing-masing maks 3MB)</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        <span class="form-hint">Foto pertama akan menjadi foto utama</span>
                    </div>

                    <h3 style="font-size:var(--font-size-base);font-weight:700;color:var(--gray-700);padding-bottom:var(--space-3);border-bottom:1px solid var(--gray-100);margin:var(--space-6) 0 var(--space-5);">
                        <i class="bi bi-geo-alt"></i> Lokasi Layanan
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Tipe Lokasi *</label>
                        <select name="location_type" class="form-control" required>
                            <option value="fixed" {{ old('location_type') == 'fixed' ? 'selected' : '' }}>Lokasi Tetap (Gedung/Venue)</option>
                            <option value="mobile" {{ old('location_type', 'mobile') == 'mobile' ? 'selected' : '' }}>Mobile (Datang ke lokasi customer)</option>
                            <option value="area" {{ old('location_type') == 'area' ? 'selected' : '' }}>Area Tertentu</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label">Kota</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" name="district" class="form-control" value="{{ old('district') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Provinsi</label>
                            <input type="text" name="province" class="form-control" value="{{ old('province') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pin Lokasi di Peta <span class="form-hint">(klik atau geser marker)</span></label>
                        <div class="map-container" style="margin-bottom:var(--space-3);">
                            <div id="mapPicker" style="height:300px;"></div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                            <div class="form-group">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="latitude" id="latitude" class="form-control" value="{{ old('latitude') }}" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="longitude" id="longitude" class="form-control" value="{{ old('longitude') }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;gap:var(--space-4);margin-top:var(--space-6);">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle"></i> Simpan Layanan</button>
                        <a href="{{ route('vendor.services.index') }}" class="btn btn-ghost btn-lg">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initMapPicker('mapPicker', 'latitude', 'longitude', {{ old('latitude', -6.2088) }}, {{ old('longitude', 106.8456) }});
});
</script>
@endpush
