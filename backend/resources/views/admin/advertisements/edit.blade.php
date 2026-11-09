@extends('layouts.app')
@section('title', 'Edit Iklan — Admin PestaGo')

@section('content')
    <div class="dashboard">
        @include('admin._sidebar', ['active' => 'advertisements'])

        <div class="dashboard-content">
            <div class="mb-4">
                <a href="{{ route('admin.advertisements.index') }}" class="btn btn-sm btn-ghost mb-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Iklan
                </a>
                <h1 class="dashboard-title">
                    <i class="bi bi-pencil-square" style="color:var(--primary);"></i> 
                    {{ $advertisement->is_admin ? 'Edit Banner Papan Utama (Admin)' : 'Moderasi & Edit Iklan Vendor' }} #{{ $advertisement->id }}
                </h1>
                <p class="dashboard-subtitle">
                    {{ $advertisement->is_admin ? 'Perbarui banner promosi platform PestaGo pada Papan Utama.' : 'Tinjau data, materi poster, status persetujuan, atau jadwal tayang iklan vendor.' }}
                </p>
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

            <div class="card" style="max-width:860px;">
                <div class="card-body" style="padding:var(--space-6);">
                    <form action="{{ route('admin.advertisements.update', $advertisement) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @if($advertisement->is_admin)
                            {{-- ==================== FORM KHUSUS IKLAN ADMIN (PAPAN UTAMA) ==================== --}}
                            <input type="hidden" name="placement" value="home_carousel">

                            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:20px;font-size:13px;color:#166534;">
                                <i class="bi bi-shield-check" style="color:#16a34a;margin-right:4px;"></i>
                                <strong>Iklan Internal Platform PestaGo:</strong> Ditempatkan di Papan Utama (Hero Carousel Atas Beranda).
                            </div>

                            {{-- 1. Judul Iklan --}}
                            <div class="form-group mb-4">
                                <label class="form-label font-weight-bold">Judul / Kampanye Banner Iklan <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control"
                                    value="{{ old('title', $advertisement->title) }}" required>
                            </div>

                            {{-- 2. Poster Image --}}
                            <div class="form-group mb-4">
                                <label class="form-label font-weight-bold">Gambar Banner Papan Utama</label>
                                <div style="margin-bottom:10px;">
                                    <div style="font-size:12px;color:var(--gray-500);margin-bottom:4px;">Banner Saat Ini:</div>
                                    <img id="currentPosterImg" src="{{ asset('storage/' . $advertisement->poster_image) }}"
                                        alt="{{ $advertisement->title }}"
                                        style="max-width:100%;height:200px;object-fit:cover;background:#f8fafc;border:1px solid var(--gray-300);border-radius:var(--radius-md);padding:4px;display:block;">
                                </div>
                                <input type="file" name="poster_image" class="form-control"
                                    accept="image/jpeg,image/png,image/webp" onchange="previewPoster(event)">
                                <small class="form-hint">Biarkan kosong jika tidak ingin mengganti gambar poster. Format JPG, PNG, WebP (Maks 4MB). Disarankan rasio 16:9.</small>
                            </div>

                            {{-- 3. Target URL --}}
                            <div class="form-group mb-4">
                                <label class="form-label font-weight-bold">Target URL / Link Saat Diklik (Opsional)</label>
                                <input type="text" name="target_url" class="form-control"
                                    value="{{ old('target_url', $advertisement->target_url) }}"
                                    placeholder="https://... atau /layanan?filter=promo">
                                <small class="form-hint">Tautan saat pengunjung mengklik banner di beranda.</small>
                            </div>

                            {{-- 4. Jadwal Tayang --}}
                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:var(--space-4);" class="mb-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Tanggal Mulai Tayang <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="starts_at" class="form-control"
                                        value="{{ old('starts_at', $advertisement->starts_at?->format('Y-m-d\TH:i')) }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Tanggal Berakhir Tayang <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="ends_at" class="form-control"
                                        value="{{ old('ends_at', $advertisement->ends_at?->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>

                            {{-- 5. Status Tayang --}}
                            <div class="form-group mb-4">
                                <label class="form-label font-weight-bold">Status Penayangan <span class="text-danger">*</span></label>
                                <select name="review_status" class="form-control" required style="max-width:320px;">
                                    <option value="approved" {{ old('review_status', $advertisement->review_status) === 'approved' ? 'selected' : '' }}>Aktif / Tayang (Disetujui)</option>
                                    <option value="completed" {{ old('review_status', $advertisement->review_status) === 'completed' ? 'selected' : '' }}>Selesai / Nonaktif</option>
                                    <option value="rejected" {{ old('review_status', $advertisement->review_status) === 'rejected' ? 'selected' : '' }}>Ditarik / Dibatalkan</option>
                                </select>
                            </div>

                        @else
                            {{-- ==================== FORM MODERASI IKLAN VENDOR ==================== --}}
                            <div style="background:#fef3c7;border:1px solid #fde68a;border-left:4px solid #f59e0b;border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:20px;font-size:13px;color:#92400e;">
                                <i class="bi bi-shop" style="color:#f59e0b;margin-right:4px;"></i>
                                <strong>Iklan Vendor Mitra:</strong> Diajukan oleh vendor untuk papan <strong>Vendor Billboard</strong>.
                            </div>

                            {{-- 1. Vendor Mitra --}}
                            <div class="form-group mb-4">
                                <label class="form-label font-weight-bold">Vendor Mitra Terkait</label>
                                <select name="vendor_profile_id" class="form-control">
                                    <option value="">-- Tanpa Vendor --</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendor_profile_id', $advertisement->vendor_profile_id) == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->business_name }} ({{ $vendor->user->name ?? 'No User' }}) — {{ $vendor->city ?? 'Indonesia' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 2. Judul Iklan --}}
                            <div class="form-group mb-4">
                                <label class="form-label font-weight-bold">Judul / Kampanye Iklan <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control"
                                    value="{{ old('title', $advertisement->title) }}" required>
                            </div>

                            {{-- 3. Poster Image --}}
                            <div class="form-group mb-4">
                                <label class="form-label font-weight-bold">Poster Iklan</label>
                                <div style="margin-bottom:10px;">
                                    <img id="currentPosterImg" src="{{ asset('storage/' . $advertisement->poster_image) }}"
                                        alt="{{ $advertisement->title }}"
                                        style="max-width:100%;height:180px;object-fit:contain;background:#f8fafc;border:1px solid var(--gray-300);border-radius:var(--radius-md);padding:6px;display:block;">
                                </div>
                                <input type="file" name="poster_image" class="form-control"
                                    accept="image/jpeg,image/png,image/webp" onchange="previewPoster(event)">
                                <small class="form-hint">Biarkan kosong jika tidak ingin mengganti file poster saat ini. Maks 4MB.</small>
                            </div>

                            {{-- 4. Status Review & Pembayaran --}}
                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:var(--space-4);" class="mb-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Status Tayang / Review <span class="text-danger">*</span></label>
                                    <select name="review_status" class="form-control" required>
                                        <option value="approved" {{ old('review_status', $advertisement->review_status) === 'approved' ? 'selected' : '' }}>Disetujui (Tayang sesuai jadwal)</option>
                                        <option value="pending_review" {{ old('review_status', $advertisement->review_status) === 'pending_review' ? 'selected' : '' }}>Menunggu Review</option>
                                        <option value="needs_revision" {{ old('review_status', $advertisement->review_status) === 'needs_revision' ? 'selected' : '' }}>Perlu Revisi</option>
                                        <option value="pending_payment" {{ old('review_status', $advertisement->review_status) === 'pending_payment' ? 'selected' : '' }}>Menunggu Bayar</option>
                                        <option value="rejected" {{ old('review_status', $advertisement->review_status) === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                        <option value="completed" {{ old('review_status', $advertisement->review_status) === 'completed' ? 'selected' : '' }}>Selesai (Kedaluwarsa)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Status Pembayaran <span class="text-danger">*</span></label>
                                    <select name="payment_status" class="form-control" required>
                                        <option value="paid" {{ old('payment_status', $advertisement->payment_status) === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                                        <option value="pending" {{ old('payment_status', $advertisement->payment_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="failed" {{ old('payment_status', $advertisement->payment_status) === 'failed' ? 'selected' : '' }}>Gagal (Failed)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- 5. Penempatan & Paket --}}
                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:var(--space-4);" class="mb-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Penempatan Papan Iklan <span class="text-danger">*</span></label>
                                    <select name="placement" class="form-control" required>
                                        <option value="vendor_billboard" {{ old('placement', $advertisement->placement) === 'vendor_billboard' ? 'selected' : '' }}>Papan Iklan Khusus Vendor (Vendor Pilihan)</option>
                                        <option value="home_carousel" {{ old('placement', $advertisement->placement) === 'home_carousel' ? 'selected' : '' }}>Papan Utama (Hero Carousel Atas — Khusus Admin)</option>
                                        <option value="category_banner" {{ old('placement', $advertisement->placement) === 'category_banner' ? 'selected' : '' }}>Banner Halaman Kategori</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Tier / Paket Iklan</label>
                                    <select name="package" class="form-control">
                                        <option value="basic" {{ old('package', $advertisement->package) === 'basic' ? 'selected' : '' }}>Basic</option>
                                        <option value="pro" {{ old('package', $advertisement->package) === 'pro' ? 'selected' : '' }}>Pro</option>
                                        <option value="business" {{ old('package', $advertisement->package) === 'business' ? 'selected' : '' }}>Business</option>
                                    </select>
                                </div>
                            </div>

                            {{-- 6. Periode Jadwal Tayang --}}
                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:var(--space-4);" class="mb-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Tanggal Mulai Tayang <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="starts_at" class="form-control"
                                        value="{{ old('starts_at', $advertisement->starts_at?->format('Y-m-d\TH:i')) }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Tanggal Berakhir Tayang <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="ends_at" class="form-control"
                                        value="{{ old('ends_at', $advertisement->ends_at?->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>

                            {{-- 7. Label Badge & Target URL --}}
                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:var(--space-4);" class="mb-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Teks Label / Badge</label>
                                    <input type="text" name="badge_text" class="form-control"
                                        value="{{ old('badge_text', $advertisement->badge_text) }}"
                                        placeholder="Contoh: Sponsor, Diskon Spesial, dll">
                                    <div style="margin-top:6px;">
                                        <label style="display:flex;align-items:center;gap:6px;font-size:var(--font-size-xs);cursor:pointer;">
                                            <input type="checkbox" name="show_badge" value="1" {{ old('show_badge', $advertisement->show_badge) ? 'checked' : '' }}>
                                            Tampilkan badge label di pojok poster
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Target URL / Link Saat Diklik</label>
                                    <input type="text" name="target_url" class="form-control"
                                        value="{{ old('target_url', $advertisement->target_url) }}"
                                        placeholder="https:// atau biarkan kosong untuk profil vendor">
                                </div>
                            </div>

                            {{-- 8. Tombol CTA & Overlay --}}
                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:var(--space-4);" class="mb-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Teks Tombol CTA (Opsional)</label>
                                    <input type="text" name="button_text" class="form-control"
                                        value="{{ old('button_text', $advertisement->button_text) }}"
                                        placeholder="Contoh: Lihat Penawaran">
                                </div>
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">Tampilan Teks Tambahan</label>
                                    <div style="margin-top:10px;">
                                        <label style="display:flex;align-items:center;gap:6px;font-size:var(--font-size-xs);cursor:pointer;">
                                            <input type="checkbox" name="show_overlay_text" value="1" {{ old('show_overlay_text', $advertisement->show_overlay_text) ? 'checked' : '' }}>
                                            Tampilkan teks judul &amp; deskripsi vendor di atas poster
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- 9. Catatan Penolakan / Revisi --}}
                            <div class="form-group mb-4">
                                <label class="form-label font-weight-bold">Catatan Penolakan / Permintaan Revisi (Opsional)</label>
                                <textarea name="rejection_reason" class="form-control" rows="2"
                                    placeholder="Catatan untuk vendor...">{{ old('rejection_reason', $advertisement->rejection_reason) }}</textarea>
                            </div>
                        @endif

                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:var(--space-6);border-top:1px solid var(--gray-100);padding-top:var(--space-4);flex-wrap:wrap;gap:12px;">
                            <button type="button" class="btn btn-danger"
                                onclick="if(confirm('Apakah Anda yakin ingin menghapus iklan ini secara permanen?')) document.getElementById('deleteAdForm').submit();">
                                <i class="bi bi-trash"></i> Hapus Iklan Ini
                            </button>

                            <div style="display:flex;gap:var(--space-3);">
                                <a href="{{ route('admin.advertisements.index') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Hidden Delete Form --}}
                    <form id="deleteAdForm" action="{{ route('admin.advertisements.destroy', $advertisement) }}"
                        method="POST" style="display:none;">
                        @csrf
                        @method('DELETE')
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
                        document.getElementById('currentPosterImg').src = e.target.result;
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>
    @endpush
@endsection
