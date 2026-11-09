@extends('layouts.app')
@section('title', 'Edit Opsi Iklan — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'ad_plans'])

    <div class="dashboard-content">
        <a href="{{ route('admin.ad-plans.index') }}" style="font-size:var(--font-size-sm);color:var(--gray-500);display:inline-flex;align-items:center;gap:6px;margin-bottom:8px;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Opsi Iklan
        </a>

        <div class="dashboard-header">
            <h1 class="dashboard-title"><i class="bi bi-pencil-square" style="color:var(--primary);"></i> Edit Opsi: {{ $plan->name }}</h1>
            <p class="dashboard-subtitle">Sesuaikan penamaan, badge promosi, tarif biaya per durasi, dan panduan kanvas poster.</p>
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

        @if($plan->placement_key === 'home_carousel')
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;padding:12px 16px;border-radius:4px;font-size:13px;color:#166534;margin-bottom:18px;max-width:850px;">
            <i class="bi bi-shield-check" style="color:#16a34a;margin-right:6px;"></i>
            <strong>Papan Utama (Hero Carousel Atas):</strong> Papan iklan resmi platform PestaGo khusus admin. Papan ini <strong>selalu aktif secara permanen</strong> dan tidak dapat dinonaktifkan.
        </div>
        @endif

        <div class="card" style="max-width:850px;">
            <div class="card-body">
                <form action="{{ route('admin.ad-plans.update', $plan) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" class="mb-4">
                        <div class="form-group">
                            <label class="form-label">Nama Tampilan Papan Iklan <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required placeholder="Contoh: Papan Utama (Besar)">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kunci Penempatan (System Key) <span class="text-danger">*</span></label>
                            <input type="text" name="placement_key" class="form-control" value="{{ old('placement_key', $plan->placement_key) }}" required readonly style="background:#f1f5f9;cursor:not-allowed;">
                            <span class="form-hint" style="font-size:11px;">Kunci sistem permanen untuk penempatan iklan di frontend.</span>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Deskripsi Singkat untuk Vendor <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="2" required placeholder="Jelaskan di mana letak iklan ini muncul dan kelebihannya...">{{ old('description', $plan->description) }}</textarea>
                    </div>

                    {{-- Badge Label --}}
                    <div style="background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:20px;">
                        <h4 style="font-size:13.5px;font-weight:700;margin-bottom:12px;color:#1e293b;">Badge Label Promosi (Opsional)</h4>
                        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;">
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Teks Badge</label>
                                <input type="text" name="badge" class="form-control" value="{{ old('badge', $plan->badge) }}" placeholder="Contoh: POPULER, TERLARIS, STANDOUT">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Warna Latar (BG)</label>
                                <input type="text" name="badge_bg_color" class="form-control" value="{{ old('badge_bg_color', $plan->badge_bg_color ?: '#fee2e2') }}" placeholder="#fee2e2">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Warna Teks</label>
                                <input type="text" name="badge_text_color" class="form-control" value="{{ old('badge_text_color', $plan->badge_text_color ?: '#d81a24') }}" placeholder="#d81a24">
                            </div>
                        </div>
                    </div>

                    @if($plan->placement_key === 'home_carousel')
                        {{-- Papan Utama Bebas Tarif / Khusus Admin --}}
                        <div style="background:#f8fafc;padding:16px 20px;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:24px;">
                            <div style="font-weight:700;font-size:14px;color:#166534;display:flex;align-items:center;gap:8px;">
                                <i class="bi bi-shield-check" style="font-size:18px;"></i> Papan Khusus Admin — Bebas Tarif (Gratis)
                            </div>
                            <p style="font-size:12.5px;color:#64748b;margin:4px 0 0;">Papan Utama (Hero Carousel Atas) tidak memerlukan pengaturan tarif berbayar karena dikhususkan untuk promosi resmi platform PestaGo.</p>
                            <input type="hidden" name="price_basic" value="0">
                            <input type="hidden" name="days_basic" value="7">
                            <input type="hidden" name="price_pro" value="0">
                            <input type="hidden" name="days_pro" value="14">
                            <input type="hidden" name="price_business" value="0">
                            <input type="hidden" name="days_business" value="30">
                            <input type="hidden" name="price_per_day" value="0">
                            <input type="hidden" name="min_days_custom" value="1">
                            <input type="hidden" name="max_days_custom" value="365">
                        </div>
                    @else
                        {{-- Tarif & Durasi per Paket untuk Vendor --}}
                        <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin-bottom:14px;">
                            <i class="bi bi-cash-stack" style="color:var(--primary);"></i> Tarif &amp; Durasi Paket Vendor
                        </h3>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px;">
                            {{-- Paket Basic --}}
                            <div style="background:#ffffff;padding:16px;border-radius:12px;border:2px solid #e2e8f0;">
                                <div style="font-weight:800;font-size:14px;color:#0f172a;margin-bottom:8px;">Paket Basic</div>
                                <div class="form-group mb-2">
                                    <label class="form-label" style="font-size:12px;">Tarif (Rp)</label>
                                    <input type="number" name="price_basic" class="form-control" value="{{ old('price_basic', (int)$plan->price_basic) }}" required min="0">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label" style="font-size:12px;">Durasi (Hari)</label>
                                    <input type="number" name="days_basic" class="form-control" value="{{ old('days_basic', $plan->days_basic) }}" required min="1">
                                </div>
                            </div>

                            {{-- Paket Pro --}}
                            <div style="background:#ffffff;padding:16px;border-radius:12px;border:2px solid #e2e8f0;">
                                <div style="font-weight:800;font-size:14px;color:#0f172a;margin-bottom:8px;">Paket Pro</div>
                                <div class="form-group mb-2">
                                    <label class="form-label" style="font-size:12px;">Tarif (Rp)</label>
                                    <input type="number" name="price_pro" class="form-control" value="{{ old('price_pro', (int)$plan->price_pro) }}" required min="0">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label" style="font-size:12px;">Durasi (Hari)</label>
                                    <input type="number" name="days_pro" class="form-control" value="{{ old('days_pro', $plan->days_pro) }}" required min="1">
                                </div>
                            </div>

                            {{-- Paket Business --}}
                            <div style="background:#ffffff;padding:16px;border-radius:12px;border:2px solid #e2e8f0;">
                                <div style="font-weight:800;font-size:14px;color:#0f172a;margin-bottom:8px;">Paket Business</div>
                                <div class="form-group mb-2">
                                    <label class="form-label" style="font-size:12px;">Tarif (Rp)</label>
                                    <input type="number" name="price_business" class="form-control" value="{{ old('price_business', (int)$plan->price_business) }}" required min="0">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label" style="font-size:12px;">Durasi (Hari)</label>
                                    <input type="number" name="days_business" class="form-control" value="{{ old('days_business', $plan->days_business) }}" required min="1">
                                </div>
                            </div>
                        </div>

                        {{-- Fitur Iklan Kustom (Pilih Hari Sendiri) --}}
                        <div style="background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #d81a24;border-radius:10px;padding:18px 20px;margin-bottom:24px;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                <span class="badge" style="background:#d81a24;color:#ffffff;font-size:10px;font-weight:800;padding:2px 8px;border-radius:2px;">FITUR KUSTOM</span>
                                <h4 style="font-size:14.5px;font-weight:800;color:#991b1b;margin:0;">
                                    Tarif Iklan Kustom Vendor (Per Hari)
                                </h4>
                            </div>
                            <p style="font-size:12.5px;color:#7f1d1d;margin:0 0 14px;line-height:1.5;">
                                Atur tarif per hari untuk vendor yang memilih durasi kustom (contoh: 5 hari, 10 hari). Total biaya akan dihitung otomatis: <em>Jumlah Hari &times; Tarif Per Hari</em>.
                            </p>

                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:16px;">
                                <div class="form-group mb-0">
                                    <label class="form-label" style="font-size:12px;font-weight:700;color:#991b1b;">Tarif Per Hari (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" name="price_per_day" class="form-control" value="{{ old('price_per_day', (int)($plan->price_per_day ?: 15000)) }}" required min="0" placeholder="15000">
                                    <span class="form-hint" style="font-size:11px;">Biaya yang dikenakan per 1 hari tayang.</span>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label" style="font-size:12px;font-weight:700;color:#991b1b;">Minimal Durasi (Hari)</label>
                                    <input type="number" name="min_days_custom" class="form-control" value="{{ old('min_days_custom', $plan->min_days_custom ?: 1) }}" required min="1">
                                    <span class="form-hint" style="font-size:11px;">Minimal hari yang bisa dipesan vendor.</span>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label" style="font-size:12px;font-weight:700;color:#991b1b;">Maksimal Durasi (Hari)</label>
                                    <input type="number" name="max_days_custom" class="form-control" value="{{ old('max_days_custom', $plan->max_days_custom ?: 90) }}" required min="1">
                                    <span class="form-hint" style="font-size:11px;">Batas maksimal durasi kustom.</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Panduan Kanvas --}}
                    <div style="background:#f0f9ff;padding:16px;border-radius:12px;border:1px solid #bae6fd;margin-bottom:20px;">
                        <h4 style="font-size:13.5px;font-weight:700;margin-bottom:12px;color:#0369a1;">Panduan Kanvas Desain Poster</h4>
                        <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;margin-bottom:12px;">
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Judul Panduan</label>
                                <input type="text" name="canvas_title" class="form-control" value="{{ old('canvas_title', $plan->canvas_title) }}">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Rasio Kanvas</label>
                                <input type="text" name="canvas_ratio" class="form-control" value="{{ old('canvas_ratio', $plan->canvas_ratio) }}" placeholder="16:9">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label" style="font-size:12px;">Orientasi</label>
                                <input type="text" name="canvas_orientation" class="form-control" value="{{ old('canvas_orientation', $plan->canvas_orientation) }}" placeholder="Lanskap / Potret">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label" style="font-size:12px;">Rincian HTML Panduan (Piksel, Format, Tips)</label>
                            <textarea name="canvas_guideline" class="form-control" rows="3">{{ old('canvas_guideline', $plan->canvas_guideline) }}</textarea>
                        </div>
                    </div>

                    {{-- Pengaturan Status & Urutan --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:16px;background:#f9fafb;border-radius:10px;margin-bottom:24px;">
                        @if($plan->placement_key === 'home_carousel')
                            <input type="hidden" name="is_active" value="1">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span class="badge badge-success" style="padding:6px 12px;font-size:11px;font-weight:700;">
                                    <i class="bi bi-shield-lock-fill"></i> SELALU AKTIF (KHUSUS ADMIN)
                                </span>
                                <span style="font-size:12px;color:#166534;">Papan Utama platform selalu aktif.</span>
                            </div>
                        @else
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--primary);">
                                <span style="font-weight:700;font-size:14px;color:#1e293b;">Aktifkan Opsi Papan Iklan Ini</span>
                            </label>
                        @endif
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="font-size:12.5px;color:#64748b;">Urutan Tampilan:</span>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $plan->sort_order) }}" style="width:80px;">
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:10px;">
                        <a href="{{ route('admin.ad-plans.index') }}" class="btn btn-ghost">Batal</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
