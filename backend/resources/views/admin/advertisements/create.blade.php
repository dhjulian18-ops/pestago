@extends('layouts.app')
@section('title', 'Pasang Banner Iklan Papan Utama — Admin PestaGo')

@section('content')
    <div class="dashboard">
        @include('admin._sidebar', ['active' => 'advertisements'])

        <div class="dashboard-content">
            <div class="mb-4">
                <a href="{{ route('admin.advertisements.index') }}" class="btn btn-sm btn-ghost mb-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Iklan
                </a>
                <h1 class="dashboard-title">
                    <i class="bi bi-plus-circle" style="color:var(--primary);"></i> Pasang Banner Iklan Papan Utama (Admin)
                </h1>
                <p class="dashboard-subtitle">Upload poster/banner promosi platform PestaGo untuk ditayangkan di Papan Utama (Hero Carousel Atas Beranda).</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0" style="padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Info Box Penempatan Papan Utama --}}
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:var(--radius-sm);padding:14px 18px;margin-bottom:24px;display:flex;align-items:flex-start;gap:12px;max-width:860px;">
                <i class="bi bi-info-circle-fill" style="color:#16a34a;font-size:18px;margin-top:2px;"></i>
                <div style="font-size:13px;color:#166534;line-height:1.5;">
                    <strong style="color:#14532d;">Khusus Papan Utama (Hero Carousel Atas):</strong><br>
                    Formulir ini dikhususkan untuk iklan resmi platform PestaGo yang tayang di carousel paling atas halaman beranda. Banner akan otomatis disetujui (aktif) dan tayang sesuai jadwal periode yang Anda tentukan tanpa biaya.
                </div>
            </div>

            <div class="card" style="max-width:860px;">
                <div class="card-body" style="padding:var(--space-6);">
                    <form action="{{ route('admin.advertisements.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Hidden fixed placement for Admin --}}
                        <input type="hidden" name="placement" value="home_carousel">

                        {{-- 1. Judul / Kampanye Iklan --}}
                        <div class="form-group mb-4">
                            <label class="form-label font-weight-bold">Judul / Kampanye Banner Iklan <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}"
                                placeholder="Contoh: Promo Spesial PestaGo Akhir Tahun" required>
                            <small class="form-hint">Nama kampanye atau judul banner untuk identifikasi dan laporan di panel admin.</small>
                        </div>

                        {{-- 2. File Poster / Banner Iklan --}}
                        <div class="form-group mb-4">
                            <label class="form-label font-weight-bold">File Gambar Banner Papan Utama <span class="text-danger">*</span></label>
                            <input type="file" name="poster_image" class="form-control"
                                accept="image/jpeg,image/png,image/webp" required onchange="previewPoster(event)">
                            <small class="form-hint">Format JPG, PNG, atau WebP. Maksimal 4MB. Disarankan rasio 16:9 Lanskap (resolusi 1200 x 675 px atau sejenis) agar tampilan banner utama tajam dan proporsional.</small>
                            <div id="posterPreviewContainer" style="margin-top:14px;display:none;">
                                <div style="font-size:12px;font-weight:700;color:var(--gray-600);margin-bottom:6px;">Pratinjau Banner:</div>
                                <img id="posterPreviewImg" src="" alt="Preview Poster"
                                    style="max-width:100%;height:220px;object-fit:cover;background:#f8fafc;border:1px dashed var(--gray-300);border-radius:var(--radius-md);padding:4px;">
                            </div>
                        </div>

                        {{-- 3. Target URL / Link Saat Diklik --}}
                        <div class="form-group mb-4">
                            <label class="form-label font-weight-bold">Target URL / Link Saat Diklik (Opsional)</label>
                            <input type="text" name="target_url" class="form-control" value="{{ old('target_url') }}"
                                placeholder="https://... atau /layanan?filter=promo">
                            <small class="form-hint">Bisa berupa tautan eksternal (https://...) atau tautan halaman internal platform PestaGo. Pengunjung yang mengklik banner di beranda akan langsung diarahkan ke link ini.</small>
                        </div>

                        {{-- 4. Periode Jadwal Tayang --}}
                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:var(--space-4);" class="mb-4">
                            <div class="form-group">
                                <label class="form-label font-weight-bold">Tanggal Mulai Tayang <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="starts_at" class="form-control"
                                    value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label font-weight-bold">Tanggal Berakhir Tayang <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="ends_at" class="form-control"
                                    value="{{ old('ends_at', now()->addDays(30)->format('Y-m-d\TH:i')) }}" required>
                            </div>
                        </div>

                        <div style="display:flex;justify-content:flex-end;gap:var(--space-3);margin-top:var(--space-6);border-top:1px solid var(--gray-100);padding-top:var(--space-4);">
                            <a href="{{ route('admin.advertisements.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" style="padding:10px 22px;font-weight:700;">
                                <i class="bi bi-check-circle"></i> Tayangkan Banner Iklan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function previewPoster(event) {
                const input = event.target;
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const preview = document.getElementById('posterPreviewImg');
                        const container = document.getElementById('posterPreviewContainer');
                        preview.src = e.target.result;
                        container.style.display = 'block';
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>
    @endpush
@endsection
