@extends('layouts.app')
@section('title', 'Tambah Opsi Iklan Baru — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'ad_plans'])

    <div class="dashboard-content">
        <a href="{{ route('admin.ad-plans.index') }}" style="font-size:var(--font-size-sm);color:var(--gray-500);display:inline-flex;align-items:center;gap:6px;margin-bottom:8px;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Opsi Iklan
        </a>

        <div class="dashboard-header">
            <h1 class="dashboard-title"><i class="bi bi-plus-circle" style="color:var(--primary);"></i> Tambah Opsi Iklan Baru</h1>
            <p class="dashboard-subtitle">Tambahkan posisi penempatan poster baru lengkap dengan tarif dan durasi paket.</p>
        </div>

        @if($errors->any())
        <div class="alert alert-danger mb-6">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card" style="max-width:850px;">
            <div class="card-body">
                <form action="{{ route('admin.ad-plans.store') }}" method="POST">
                    @csrf

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="mb-4">
                        <div class="form-group">
                            <label class="form-label">Nama Tampilan Papan Iklan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Contoh: Banner Populer Halaman Checkout">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kunci Penempatan (System Key) <span class="text-danger">*</span></label>
                            <input type="text" name="placement_key" class="form-control" value="{{ old('placement_key') }}" required placeholder="Contoh: checkout_banner">
                            <span class="form-hint" style="font-size:11px;">Hanya huruf kecil, angka, dan garis bawah (_).</span>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Deskripsi Singkat untuk Vendor <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="2" required placeholder="Jelaskan di mana letak iklan ini muncul dan kelebihannya...">{{ old('description') }}</textarea>
                    </div>

                    {{-- Badge Label --}}
                    <div style="background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:20px;">
                        <h4 style="font-size:13.5px;font-weight:700;margin-bottom:12px;color:#1e293b;">Badge Label Promosi (Opsional)</h4>
                        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;">
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Teks Badge</label>
                                <input type="text" name="badge" class="form-control" value="{{ old('badge') }}" placeholder="Contoh: BARU, EKSKLUSIF, POPULER">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Warna Latar (BG)</label>
                                <input type="text" name="badge_bg_color" class="form-control" value="{{ old('badge_bg_color', '#fee2e2') }}" placeholder="#fee2e2">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Warna Teks</label>
                                <input type="text" name="badge_text_color" class="form-control" value="{{ old('badge_text_color', '#d81a24') }}" placeholder="#d81a24">
                            </div>
                        </div>
                    </div>

                    {{-- Tarif & Durasi per Paket --}}
                    <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin-bottom:14px;">
                        <i class="bi bi-cash-stack" style="color:var(--primary);"></i> Tarif &amp; Durasi Paket
                    </h3>

                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px;">
                        {{-- Paket Basic --}}
                        <div style="background:#ffffff;padding:16px;border-radius:12px;border:2px solid #e2e8f0;">
                            <div style="font-weight:800;font-size:14px;color:#0f172a;margin-bottom:8px;">Paket Basic</div>
                            <div class="form-group mb-2">
                                <label class="form-label" style="font-size:12px;">Tarif (Rp)</label>
                                <input type="number" name="price_basic" class="form-control" value="{{ old('price_basic', 99000) }}" required min="0">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Durasi (Hari)</label>
                                <input type="number" name="days_basic" class="form-control" value="{{ old('days_basic', 7) }}" required min="1">
                            </div>
                        </div>

                        {{-- Paket Pro --}}
                        <div style="background:#ffffff;padding:16px;border-radius:12px;border:2px solid #e2e8f0;">
                            <div style="font-weight:800;font-size:14px;color:#0f172a;margin-bottom:8px;">Paket Pro</div>
                            <div class="form-group mb-2">
                                <label class="form-label" style="font-size:12px;">Tarif (Rp)</label>
                                <input type="number" name="price_pro" class="form-control" value="{{ old('price_pro', 179000) }}" required min="0">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Durasi (Hari)</label>
                                <input type="number" name="days_pro" class="form-control" value="{{ old('days_pro', 14) }}" required min="1">
                            </div>
                        </div>

                        {{-- Paket Business --}}
                        <div style="background:#ffffff;padding:16px;border-radius:12px;border:2px solid #e2e8f0;">
                            <div style="font-weight:800;font-size:14px;color:#0f172a;margin-bottom:8px;">Paket Business</div>
                            <div class="form-group mb-2">
                                <label class="form-label" style="font-size:12px;">Tarif (Rp)</label>
                                <input type="number" name="price_business" class="form-control" value="{{ old('price_business', 299000) }}" required min="0">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Durasi (Hari)</label>
                                <input type="number" name="days_business" class="form-control" value="{{ old('days_business', 30) }}" required min="1">
                            </div>
                        </div>
                    </div>

                    {{-- Panduan Kanvas --}}
                    <div style="background:#f0f9ff;padding:16px;border-radius:12px;border:1px solid #bae6fd;margin-bottom:20px;">
                        <h4 style="font-size:13.5px;font-weight:700;margin-bottom:12px;color:#0369a1;">Panduan Kanvas Desain Poster</h4>
                        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;margin-bottom:12px;">
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Judul Panduan</label>
                                <input type="text" name="canvas_title" class="form-control" value="{{ old('canvas_title', 'Panduan Ukuran Kanvas') }}">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Rasio Kanvas</label>
                                <input type="text" name="canvas_ratio" class="form-control" value="{{ old('canvas_ratio', '16:9') }}" placeholder="16:9">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Orientasi</label>
                                <input type="text" name="canvas_orientation" class="form-control" value="{{ old('canvas_orientation', 'Lanskap') }}" placeholder="Lanskap / Potret">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label" style="font-size:12px;">Rincian HTML Panduan (Piksel, Format, Tips)</label>
                            <textarea name="canvas_guideline" class="form-control" rows="3">{{ old('canvas_guideline', '&bull; <strong>Ukuran Kanvas Rekomendasi</strong>: 1200 x 675 piksel<br>&bull; <strong>Format</strong>: JPG, PNG, atau WebP (Maksimal 2MB).') }}</textarea>
                        </div>
                    </div>

                    {{-- Pengaturan Status & Urutan --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px;background:#f9fafb;border-radius:10px;margin-bottom:24px;">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--primary);">
                            <span style="font-weight:700;font-size:14px;color:#1e293b;">Langsung Aktifkan Opsi Ini untuk Vendor</span>
                        </label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="font-size:12.5px;color:#64748b;">Urutan Tampilan:</span>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 5) }}" style="width:80px;">
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:10px;">
                        <a href="{{ route('admin.ad-plans.index') }}" class="btn btn-ghost">Batal</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> Simpan Opsi Iklan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
