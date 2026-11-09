@extends('layouts.app')
@section('title', 'Iklan & Promosi — Vendor PestaGo')

@section('content')
    <div class="dashboard">
        @include('vendor._sidebar', ['active' => 'advertisements'])

        <div class="dashboard-content">
            <div class="dashboard-header"
                style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1 class="dashboard-title">Iklan & Promosi Vendor</h1>
                    <p class="dashboard-subtitle">Promosikan layanan dan toko Anda untuk menjangkau lebih banyak pelanggan
                        di PestaGo.</p>
                </div>
                <a href="{{ route('vendor.advertisements.create') }}" class="btn btn-primary"
                    style="padding:10px 18px;font-weight:700;border-radius:4px;">
                    <i class="bi bi-plus-lg"></i> Ajukan Iklan Baru
                </a>
            </div>

            {{-- Paket Info Cards (Uniqlo Minimal Clean) --}}
            <div class="stats-grid mb-8">
                <div class="stat-card">
                    <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                        <span class="stat-label">Paket Basic</span>
                        <span class="stat-icon"><i class="bi bi-star"></i></span>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size:1.35rem;">Rp 99.000 <span
                                style="font-size:0.8rem;font-weight:600;color:#767676;">/ 7 Hari</span></div>
                        <div style="font-size:0.78rem;color:#666666;margin-top:4px;">Penempatan hasil pencarian bersponsor
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                        <span class="stat-label">Paket Pro</span>
                        <span class="stat-icon"><i class="bi bi-award"></i></span>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size:1.35rem;">Rp 199.000 <span
                                style="font-size:0.8rem;font-weight:600;color:#767676;">/ 14 Hari</span></div>
                        <div style="font-size:0.78rem;color:#666666;margin-top:4px;">Banner promosi di halaman kategori
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                        <span class="stat-label">Paket Business</span>
                        <span class="stat-icon"><i class="bi bi-gem"></i></span>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size:1.35rem;">Rp 349.000 <span
                                style="font-size:0.8rem;font-weight:600;color:#767676;">/ 30 Hari</span></div>
                        <div style="font-size:0.78rem;color:#666666;margin-top:4px;">Tampil di vendor unggulan beranda utama
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success mb-6">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info mb-6">
                    <i class="bi bi-info-circle"></i> {{ session('info') }}
                </div>
            @endif

            @php $needsRev = $advertisements->where('review_status', 'needs_revision')->count(); @endphp
            @if($needsRev > 0)
                <div class="alert alert-warning mb-6" style="border-radius:12px;background:#fffbeb;border:1.5px solid #fde68a;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:22px;"></i>
                        <div>
                            <strong style="color:#92400e;display:block;">Perhatian: Terdapat {{ $needsRev }} Iklan Memerlukan
                                Revisi</strong>
                            <span style="font-size:12.5px;color:#b45309;">
                                Tim Kurasi telah memberikan catatan perbaikan pada iklan Anda. Silakan klik tombol
                                <strong>"Perbaiki Iklan"</strong> pada tabel di bawah untuk memperbarui materi iklan tanpa biaya
                                tambahan.
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Table Advertisements --}}
            <div class="card">
                <div class="card-body">
                    <h2 style="font-size:var(--font-size-base);font-weight:700;margin-bottom:var(--space-4);">
                        Riwayat Pengajuan Iklan
                    </h2>

                    @if($advertisements->count() > 0)
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Poster</th>
                                        <th>Judul Iklan</th>
                                        <th>Penempatan & Paket</th>
                                        <th>Biaya</th>
                                        <th>Periode</th>
                                        <th>Status Review</th>
                                        <th style="text-align:right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($advertisements as $ad)
                                        <tr>
                                            <td>
                                                <img src="{{ asset('storage/' . $ad->poster_image) }}" alt="{{ $ad->title }}"
                                                    style="width:60px;height:45px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid #e2e8f0;">
                                            </td>
                                            <td>
                                                <strong>{{ $ad->title }}</strong>
                                                @if($ad->rejection_reason)
                                                    <div
                                                        style="font-size:var(--font-size-xs);margin-top:4px;background:#fef2f2;border:1px solid #fee2e2;border-radius:6px;padding:4px 8px;color:#991b1b;line-height:1.4;">
                                                        <i class="bi bi-info-circle"></i> <strong>Catatan Kurasi:</strong>
                                                        {{ $ad->rejection_reason }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $placements = [
                                                        'vendor_billboard' => 'Papan Iklan Vendor',
                                                        'home_carousel' => 'Papan Utama (Besar)',
                                                        'home_side' => 'Papan Samping (Kecil)',
                                                        'home_featured' => 'Papan Samping (Kecil)',
                                                        'category_banner' => 'Banner Kategori',
                                                        'search_sponsored' => 'Hasil Pencarian',
                                                    ];
                                                    $durationDays = ($ad->starts_at && $ad->ends_at) ? $ad->starts_at->diffInDays($ad->ends_at) : null;
                                                @endphp
                                                <div>
                                                    @if($ad->package === 'custom')
                                                        <span class="badge" style="background:#0284c7;color:#ffffff;font-size:10px;font-weight:800;letter-spacing:0.5px;">KUSTOM ({{ $durationDays ?? '?' }} HARI)</span>
                                                    @else
                                                        <span class="badge badge-primary">{{ strtoupper($ad->package) }}</span>
                                                    @endif
                                                </div>
                                                <div style="font-size:var(--font-size-xs);color:var(--gray-500);margin-top:2px;">
                                                    {{ $placements[$ad->placement] ?? $ad->placement }}</div>
                                            </td>
                                            <td>
                                                <strong>Rp {{ number_format($ad->payment_amount, 0, ',', '.') }}</strong>
                                                @if($ad->payment_status === 'paid')
                                                    <div style="font-size:10.5px;color:#16a34a;font-weight:600;"><i
                                                            class="bi bi-check2"></i> Lunas</div>
                                                @else
                                                    <div style="font-size:10.5px;color:#d97706;font-weight:600;"><i
                                                            class="bi bi-clock"></i> Belum Bayar</div>
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
                                            <td style="text-align:right;">
                                                <div
                                                    style="display:inline-flex;flex-direction:column;gap:4px;align-items:flex-end;">
                                                    @if(in_array($ad->review_status, ['needs_revision', 'rejected']))
                                                        <a href="{{ route('vendor.advertisements.edit', $ad) }}"
                                                            class="btn btn-sm btn-primary"
                                                            style="font-size:11.5px;padding:4px 10px;font-weight:700;display:inline-flex;align-items:center;gap:4px;">
                                                            <i class="bi bi-pencil-square"></i> Perbaiki Iklan
                                                        </a>
                                                    @elseif($ad->payment_status === 'pending')
                                                        <a href="{{ route('vendor.advertisements.payment', $ad) }}" class="btn btn-sm"
                                                            style="background:#fee2e2;color:#d81a24;border:1px solid #fecaca;font-size:11px;padding:4px 10px;font-weight:700;display:inline-flex;align-items:center;gap:4px;">
                                                            <i class="bi bi-credit-card"></i> Bayar
                                                        </a>
                                                    @elseif($ad->review_status === 'pending_review')
                                                        <span style="font-size:11.5px;color:#64748b;"><i
                                                                class="bi bi-hourglass-split"></i> Sedang Ditinjau</span>
                                                    @elseif($ad->review_status === 'approved' || $ad->review_status === 'active')
                                                        <span style="font-size:11.5px;color:#16a34a;font-weight:600;"><i
                                                                class="bi bi-check-circle"></i> Siap Tayang</span>
                                                    @endif
                                                </div>
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
                            <h3>Belum Ada Iklan</h3>
                            <p>Tingkatkan eksposur bisnis Anda dengan memasang poster iklan di PestaGo.</p>
                            <a href="{{ route('vendor.advertisements.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Ajukan Iklan Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection