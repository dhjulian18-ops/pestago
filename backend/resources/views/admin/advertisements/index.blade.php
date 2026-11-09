@extends('layouts.app')
@section('title', 'Moderasi Iklan — Admin PestaGo')

@section('content')
    <div class="dashboard">
        @include('admin._sidebar', ['active' => 'advertisements'])

        <div class="dashboard-content">
            <div class="dashboard-header"
                style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1 class="dashboard-title"><i class="bi bi-megaphone" style="color:var(--primary);"></i> Kelola &amp;
                        Moderasi Iklan</h1>
                    <p class="dashboard-subtitle">Kelola seluruh poster promosi vendor, setujui, ubah, atau hapus iklan
                        kapan saja.</p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ route('admin.advertisements.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Pasang Iklan Baru (Admin)
                    </a>
                    <a href="{{ route('admin.ad-plans.index') }}" class="btn btn-secondary">
                        <i class="bi bi-sliders"></i> Kelola Tarif &amp; Opsi Iklan
                    </a>
                </div>
            </div>

            {{-- Filter Sumber Iklan (Admin vs Vendor) --}}
            <div style="display:flex;gap:var(--space-2);margin-bottom:12px;flex-wrap:wrap;">
                <a href="{{ route('admin.advertisements.index', array_filter(['status' => $status])) }}"
                    class="btn btn-sm {{ !$type ? 'btn-primary' : 'btn-ghost' }}">Semua Sumber</a>
                <a href="{{ route('admin.advertisements.index', array_filter(['type' => 'admin', 'status' => $status])) }}"
                    class="btn btn-sm {{ $type == 'admin' ? 'btn-primary' : 'btn-ghost' }}">
                    <i class="bi bi-shield-check"></i> Iklan Admin (Papan Utama)
                </a>
                <a href="{{ route('admin.advertisements.index', array_filter(['type' => 'vendor', 'status' => $status])) }}"
                    class="btn btn-sm {{ $type == 'vendor' ? 'btn-primary' : 'btn-ghost' }}">
                    <i class="bi bi-shop"></i> Iklan Vendor (Billboard)
                </a>
            </div>

            {{-- Filter Status --}}
            <div style="display:flex;gap:var(--space-2);margin-bottom:var(--space-6);flex-wrap:wrap;">
                <a href="{{ route('admin.advertisements.index', array_filter(['type' => $type])) }}"
                    class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-ghost' }}">Semua Status</a>
                <a href="{{ route('admin.advertisements.index', array_filter(['type' => $type, 'status' => 'pending_review'])) }}"
                    class="btn btn-sm {{ $status == 'pending_review' ? 'btn-primary' : 'btn-ghost' }}">Menunggu Review</a>
                <a href="{{ route('admin.advertisements.index', array_filter(['type' => $type, 'status' => 'approved'])) }}"
                    class="btn btn-sm {{ $status == 'approved' ? 'btn-primary' : 'btn-ghost' }}">Disetujui</a>
                <a href="{{ route('admin.advertisements.index', array_filter(['type' => $type, 'status' => 'needs_revision'])) }}"
                    class="btn btn-sm {{ $status == 'needs_revision' ? 'btn-primary' : 'btn-ghost' }}">Perlu Revisi</a>
                <a href="{{ route('admin.advertisements.index', array_filter(['type' => $type, 'status' => 'rejected'])) }}"
                    class="btn btn-sm {{ $status == 'rejected' ? 'btn-primary' : 'btn-ghost' }}">Ditolak</a>
            </div>

            {{-- Advertisements Table --}}
            <div class="card">
                <div class="card-body">
                    @if($advertisements->count() > 0)
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Poster</th>
                                        <th>Vendor & Judul</th>
                                        <th>Penempatan</th>
                                        <th>Paket & Biaya</th>
                                        <th>Periode</th>
                                        <th>Status</th>
                                        <th style="min-width:140px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($advertisements as $ad)
                                        <tr>
                                            <td>
                                                <a href="{{ asset('storage/' . $ad->poster_image) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $ad->poster_image) }}" alt="{{ $ad->title }}"
                                                        style="width:70px;height:50px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--gray-200);">
                                                </a>
                                            </td>
                                            <td>
                                                <div style="font-weight:700;">{{ $ad->title }}</div>
                                                @if($ad->is_admin)
                                                    <div style="margin-top:3px;">
                                                        <span class="badge" style="background:#111111;color:#ffffff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:2px;">
                                                            <i class="bi bi-shield-check" style="color:#10b981;"></i> IKLAN ADMIN
                                                        </span>
                                                    </div>
                                                    @if($ad->target_url)
                                                        <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                                            <i class="bi bi-link-45deg"></i> <a href="{{ $ad->target_url }}" target="_blank" style="color:#64748b;text-decoration:none;">{{ Str::limit($ad->target_url, 30) }}</a>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div style="font-size:var(--font-size-xs);color:var(--gray-600);margin-top:2px;">
                                                        <strong>{{ $ad->vendorProfile->business_name ?? 'Vendor Mitra' }}</strong>
                                                        @if($ad->vendorProfile?->user)
                                                            <span style="color:#888;">({{ $ad->vendorProfile->user->name }})</span>
                                                        @endif
                                                    </div>
                                                    @if($ad->show_badge && !empty($ad->badge_text))
                                                        <div style="font-size:11px;color:#0284c7;margin-top:3px;">
                                                            <i class="bi bi-tag-fill"></i> Label: <strong>{{ $ad->badge_text }}</strong>
                                                        </div>
                                                    @endif
                                                @endif
                                                @if($ad->rejection_reason)
                                                    <div class="text-danger" style="font-size:var(--font-size-xs);margin-top:2px;">
                                                        Catatan: {{ $ad->rejection_reason }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ad->placement === 'home_carousel' || $ad->is_admin)
                                                    <span class="badge" style="background:#d81a24;color:#ffffff;font-weight:700;font-size:11px;padding:3px 8px;">
                                                        <i class="bi bi-tv"></i> Papan Utama (Besar)
                                                    </span>
                                                @elseif($ad->placement === 'vendor_billboard')
                                                    <span class="badge" style="background:#f1f5f9;color:#1e293b;border:1px solid #cbd5e1;font-weight:700;font-size:11px;padding:3px 8px;">
                                                        <i class="bi bi-shop"></i> Vendor Billboard
                                                    </span>
                                                @else
                                                    @php
                                                        $placements = [
                                                            'home_carousel' => 'Papan Utama (Besar)',
                                                            'vendor_billboard' => 'Vendor Billboard',
                                                            'home_side' => 'Papan Samping (Kecil)',
                                                            'home_featured' => 'Papan Samping (Kecil)',
                                                            'category_banner' => 'Banner Kategori',
                                                            'search_sponsored' => 'Hasil Pencarian',
                                                            'brand_pilihan' => 'Brand Pilihan Slider',
                                                        ];
                                                    @endphp
                                                    <span class="badge badge-gray">{{ $placements[$ad->placement] ?? $ad->placement }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ad->is_admin)
                                                    <div><span class="badge" style="background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;font-weight:700;font-size:10px;">INTERNAL PLATFORM</span></div>
                                                    <div style="font-weight:700;font-size:11px;color:#16a34a;margin-top:3px;">
                                                        <i class="bi bi-check-circle-fill"></i> Gratis (Admin)
                                                    </div>
                                                @else
                                                    @if($ad->package === 'custom')
                                                        @php
                                                            $durDays = ($ad->starts_at && $ad->ends_at) ? $ad->starts_at->diffInDays($ad->ends_at) : null;
                                                        @endphp
                                                        <div><span class="badge" style="background:#0284c7;color:#ffffff;font-weight:700;font-size:10px;">KUSTOM ({{ $durDays ?? '?' }} HARI)</span></div>
                                                    @else
                                                        <div><span class="badge badge-primary">{{ strtoupper($ad->package) }}</span></div>
                                                    @endif
                                                    <div style="font-weight:600;font-size:var(--font-size-xs);margin-top:2px;">Rp
                                                        {{ number_format($ad->payment_amount, 0, ',', '.') }}
                                                    </div>
                                                    @if($ad->payment_status === 'paid')
                                                        <span class="badge badge-success" style="font-size:10px;margin-top:2px;"><i
                                                                class="bi bi-check2"></i> Lunas
                                                            ({{ $ad->payment_type ?: 'Midtrans' }})</span>
                                                    @else
                                                        <span class="badge badge-warning" style="font-size:10px;margin-top:2px;"><i
                                                                class="bi bi-clock"></i> Belum Bayar</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td style="font-size:var(--font-size-xs);white-space:nowrap;">
                                                {{ $ad->starts_at?->translatedFormat('d M Y') }} s/d<br>
                                                {{ $ad->ends_at?->translatedFormat('d M Y') }}
                                            </td>
                                            <td>
                                                @php
                                                    $stMap = [
                                                        'pending_payment' => ['Menunggu Bayar', 'warning'],
                                                        'pending_review' => ['Menunggu Review', 'warning'],
                                                        'needs_revision' => ['Perlu Revisi', 'info'],
                                                        'approved' => ['Disetujui', 'success'],
                                                        'scheduled' => ['Dijadwalkan', 'info'],
                                                        'active' => ['Aktif', 'success'],
                                                        'rejected' => ['Ditolak', 'danger'],
                                                        'completed' => ['Selesai', 'gray'],
                                                    ];
                                                    $st = $stMap[$ad->review_status] ?? [$ad->review_status, 'gray'];
                                                @endphp
                                                <span class="badge badge-{{ $st[1] }}">{{ $st[0] }}</span>
                                            </td>
                                            <td>
                                                <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                                    @if(in_array($ad->review_status, ['pending_review', 'needs_revision']))
                                                        {{-- Approve --}}
                                                        <form action="{{ route('admin.advertisements.process', $ad) }}" method="POST"
                                                            style="display:inline;">
                                                            @csrf
                                                            <input type="hidden" name="action" value="approve">
                                                            <button type="submit" class="btn btn-sm btn-success" title="Setujui Tayang"
                                                                onclick="return confirm('Setujui iklan ini untuk ditayangkan?')">
                                                                <i class="bi bi-check-lg"></i>
                                                            </button>
                                                        </form>

                                                        {{-- Reject / Revisi trigger --}}
                                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                            onclick="toggleAdReject({{ $ad->id }})" title="Tolak / Minta Revisi">
                                                            <i class="bi bi-chat-left-dots"></i>
                                                        </button>
                                                    @endif

                                                    {{-- Edit --}}
                                                    <a href="{{ route('admin.advertisements.edit', $ad) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Edit Iklan">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>

                                                    {{-- Hapus Kapan Saja --}}
                                                    <form action="{{ route('admin.advertisements.destroy', $ad) }}" method="POST"
                                                        style="display:inline;"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus iklan \'{{ addslashes($ad->title) }}\' ini secara permanen?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus Iklan">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                @if(in_array($ad->review_status, ['pending_review', 'needs_revision']))
                                                    {{-- Mini reject/revision form --}}
                                                    <div id="adRejectBox{{ $ad->id }}"
                                                        style="display:none;margin-top:var(--space-2);background:var(--gray-50);padding:var(--space-2);border-radius:var(--radius-sm);">
                                                        <form action="{{ route('admin.advertisements.process', $ad) }}" method="POST">
                                                            @csrf
                                                            <textarea name="rejection_reason" class="form-control mb-2" rows="2"
                                                                placeholder="Alasan penolakan / revisi..."
                                                                style="font-size:var(--font-size-xs);" required></textarea>
                                                            <div style="display:flex;gap:var(--space-1);">
                                                                <button type="submit" name="action" value="revision"
                                                                    class="btn btn-xs btn-outline-warning">Minta Revisi</button>
                                                                <button type="submit" name="action" value="reject"
                                                                    class="btn btn-xs btn-danger">Tolak</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $advertisements->links() }}
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="bi bi-megaphone"></i></div>
                            <h3>Tidak Ada Pengajuan Iklan</h3>
                            <p>Belum ada iklan dengan status {{ $status ?: 'tersebut' }}.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleAdReject(id) {
            const el = document.getElementById('adRejectBox' + id);
            if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }
    </script>
@endpush