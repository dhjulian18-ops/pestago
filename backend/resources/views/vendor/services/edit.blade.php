@extends('layouts.app')
@section('title', 'Edit Layanan — Vendor PestaGo')

@section('content')
<div class="dashboard">
    @include('vendor._sidebar', ['active' => 'services'])

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Edit Layanan</h1>
            <p class="dashboard-subtitle">{{ $service->name }}</p>
        </div>

        <div class="card">
            <div class="card-body" style="padding:var(--space-8);">
                <form action="{{ route('vendor.services.update', $service) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Nama Layanan *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $service->name) }}" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label">Kategori *</label>
                            <select name="category_id" class="form-control" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $service->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Satuan Harga *</label>
                            <select name="price_unit" class="form-control" required>
                                @foreach(['per_acara'=>'Per Acara','per_orang'=>'Per Orang','per_hari'=>'Per Hari','per_paket'=>'Per Paket','penawaran'=>'Penawaran'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('price_unit', $service->price_unit) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Harga (Rp) *</label>
                        <input type="number" name="price" class="form-control" value="{{ old('price', $service->price) }}" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi *</label>
                        <textarea name="description" class="form-control" rows="5" required>{{ old('description', $service->description) }}</textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label">Kapasitas Min</label>
                            <input type="number" name="capacity_min" class="form-control" value="{{ old('capacity_min', $service->capacity_min) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kapasitas Max</label>
                            <input type="number" name="capacity_max" class="form-control" value="{{ old('capacity_max', $service->capacity_max) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fasilitas</label>
                        <input type="text" name="facilities" class="form-control" value="{{ old('facilities', is_array($service->facilities) ? implode(', ', $service->facilities) : '') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kebijakan Pembatalan</label>
                        <textarea name="cancellation_policy" class="form-control" rows="2">{{ old('cancellation_policy', $service->cancellation_policy) }}</textarea>
                    </div>

                    {{-- Existing Images --}}
                    @if($service->images->count() > 0)
                    <div class="form-group">
                        <label class="form-label">Foto Saat Ini</label>
                        <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;">
                            @foreach($service->images as $img)
                            <div style="position:relative;">
                                <img src="{{ asset('storage/' . $img->image_path) }}" style="width:100px;height:80px;object-fit:cover;border-radius:var(--radius-md);">
                                <form action="{{ route('vendor.services.deleteImage', $img) }}" method="POST" style="position:absolute;top:-8px;right:-8px;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" style="width:24px;height:24px;padding:0;border-radius:50%;font-size:0.7rem;" onclick="return confirm('Hapus foto ini?')">×</button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label">Tambah Foto Baru</label>
                        <input type="file" name="new_images[]" class="form-control" multiple accept="image/*">
                    </div>

                    {{-- Location --}}
                    <div class="form-group">
                        <label class="form-label">Tipe Lokasi *</label>
                        <select name="location_type" class="form-control" required>
                            @foreach(['fixed'=>'Lokasi Tetap','mobile'=>'Mobile','area'=>'Area Tertentu'] as $val => $label)
                                <option value="{{ $val }}" {{ old('location_type', $service->location->location_type ?? 'mobile') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $service->location->address ?? '') }}">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label">Kota</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $service->location->city ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" name="district" class="form-control" value="{{ old('district', $service->location->district ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Provinsi</label>
                            <input type="text" name="province" class="form-control" value="{{ old('province', $service->location->province ?? '') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lokasi di Peta</label>
                        <div class="map-container" style="margin-bottom:var(--space-3);">
                            <div id="mapPicker" style="height:300px;"></div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                            <input type="text" name="latitude" id="latitude" class="form-control" value="{{ old('latitude', $service->location->latitude ?? '') }}" readonly>
                            <input type="text" name="longitude" id="longitude" class="form-control" value="{{ old('longitude', $service->location->longitude ?? '') }}" readonly>
                        </div>
                    </div>

                    <div style="display:flex;gap:var(--space-4);margin-top:var(--space-6);">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle"></i> Simpan Perubahan</button>
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
    initMapPicker('mapPicker', 'latitude', 'longitude',
        {{ old('latitude', $service->location->latitude ?? -6.2088) }},
        {{ old('longitude', $service->location->longitude ?? 106.8456) }}
    );
});
</script>
@endpush
