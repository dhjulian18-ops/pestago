@extends('layouts.app')
@section('title', 'Kelola Tarif & Opsi Iklan — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'ad_plans'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
            <div>
                <a href="{{ route('admin.advertisements.index') }}" style="font-size:var(--font-size-sm);color:var(--gray-500);display:inline-flex;align-items:center;gap:6px;margin-bottom:8px;">
                    <i class="bi bi-arrow-left"></i> Kembali ke Moderasi Iklan
                </a>
                <h1 class="dashboard-title"><i class="bi bi-sliders" style="color:var(--primary);"></i> Tarif &amp; Opsi Iklan Platform</h1>
                <p class="dashboard-subtitle">Atur tarif, durasi paket, dan status aktif papan iklan yang tersedia di PestaGo.</p>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success mb-4" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:4px;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('info'))
        <div class="alert alert-info mb-4" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:4px;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-info-circle-fill" style="color:#2563eb;"></i> {{ session('info') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #fee2e2;color:#991b1b;padding:12px 16px;border-radius:4px;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Info Box Kebijakan Papan Iklan --}}
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:var(--radius-sm);padding:14px 18px;margin-bottom:20px;display:flex;align-items:flex-start;gap:12px;">
            <i class="bi bi-info-circle-fill" style="color:#16a34a;font-size:18px;margin-top:2px;"></i>
            <div style="font-size:13px;color:#166534;line-height:1.5;">
                <strong style="color:#14532d;">Ketentuan Status Penayangan Papan Iklan:</strong><br>
                • <strong>Papan Utama (Besar):</strong> Papan banner resmi platform PestaGo — <strong>Selalu Aktif</strong> secara permanen dan tidak dapat dinonaktifkan.<br>
                • <strong>Papan Iklan Khusus Vendor:</strong> Papan promosi billboard untuk vendor mitra — <strong>Dapat diatur Aktif atau Nonaktif</strong> sewaktu-waktu oleh Admin.
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding:0;">
                <div class="table-wrapper">
                    <table class="table" style="margin-bottom:0;">
                        <thead>
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Nama Papan &amp; Kunci</th>
                                <th>Kategori / Badge</th>
                                <th>Paket Basic</th>
                                <th>Paket Pro</th>
                                <th>Paket Business</th>
                                <th style="text-align:center;">Status</th>
                                <th style="text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $index => $plan)
                            @php
                                $isAdminOnly = ($plan->placement_key === 'home_carousel');
                            @endphp
                            <tr style="{{ !$plan->is_active ? 'opacity:0.65;background:#f9fafb;' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong style="color:var(--gray-900);font-size:14px;display:block;">{{ $plan->name }}</strong>
                                    <code style="font-size:11.5px;color:var(--primary);background:#fef2f2;padding:2px 6px;border-radius:4px;">{{ $plan->placement_key }}</code>
                                    <div style="font-size:12px;color:var(--gray-500);margin-top:4px;max-width:280px;">
                                        @if($isAdminOnly)
                                            Slide Hero Carousel Beranda atas. Khusus promosi resmi platform PestaGo.
                                        @else
                                            {{ Str::limit($plan->description, 85) }}
                                            <div style="margin-top:5px;font-size:11.5px;color:#d81a24;font-weight:700;">
                                                <i class="bi bi-calendar-range"></i> Iklan Kustom: <strong>Rp {{ number_format($plan->price_per_day ?: 15000, 0, ',', '.') }}/hari</strong>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($isAdminOnly)
                                        <span class="badge" style="background:#111111;color:#ffffff;font-weight:800;font-size:10.5px;letter-spacing:0.5px;">
                                            <i class="bi bi-shield-lock-fill" style="color:#fbbf24;"></i> KHUSUS ADMIN
                                        </span>
                                    @elseif($plan->badge)
                                        <span class="badge" style="background:{{ $plan->badge_bg_color ?: '#fee2e2' }};color:{{ $plan->badge_text_color ?: '#d81a24' }};font-weight:800;font-size:10.5px;">
                                            {{ $plan->badge }}
                                        </span>
                                    @else
                                        <span class="badge" style="background:#fee2e2;color:#d81a24;font-weight:800;font-size:10.5px;">
                                            KHUSUS VENDOR
                                        </span>
                                    @endif
                                </td>

                                @if($isAdminOnly)
                                    <td colspan="3" style="background:#f8fafc;vertical-align:middle;text-align:center;">
                                        <div style="display:inline-flex;align-items:center;gap:6px;color:#166534;font-weight:700;font-size:12.5px;">
                                            <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i>
                                            Gratis Internal Platform (Hero Carousel Atas Beranda)
                                        </div>
                                    </td>
                                @else
                                    <td>
                                        <strong style="color:var(--gray-900);font-size:13px;">Rp {{ number_format($plan->price_basic, 0, ',', '.') }}</strong>
                                        <div style="font-size:11px;color:var(--gray-500);">{{ $plan->days_basic }} Hari</div>
                                    </td>
                                    <td>
                                        <strong style="color:var(--gray-900);font-size:13px;">Rp {{ number_format($plan->price_pro, 0, ',', '.') }}</strong>
                                        <div style="font-size:11px;color:var(--gray-500);">{{ $plan->days_pro }} Hari</div>
                                    </td>
                                    <td>
                                        <strong style="color:var(--gray-900);font-size:13px;">Rp {{ number_format($plan->price_business, 0, ',', '.') }}</strong>
                                        <div style="font-size:11px;color:var(--gray-500);">{{ $plan->days_business }} Hari</div>
                                    </td>
                                @endif

                                <td style="text-align:center;vertical-align:middle;">
                                    @if($isAdminOnly)
                                        {{-- Papan Utama Admin Selalu Aktif --}}
                                        <span class="badge badge-success" style="padding:6px 12px;font-size:11px;font-weight:700;letter-spacing:0.04em;">
                                            <i class="bi bi-shield-lock-fill"></i> SELALU AKTIF
                                        </span>
                                    @else
                                        {{-- Papan Vendor Bisa Diatur Aktif / Nonaktif --}}
                                        <form action="{{ route('admin.ad-plans.toggle', $plan) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @if($plan->is_active)
                                                <button type="submit" class="badge badge-success" style="border:none;cursor:pointer;padding:6px 12px;font-size:11px;font-weight:700;transition:opacity 0.2s;" title="Klik untuk menonaktifkan slot vendor" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                                    <i class="bi bi-check-circle-fill"></i> AKTIF
                                                </button>
                                            @else
                                                <button type="submit" class="badge badge-secondary" style="border:none;cursor:pointer;padding:6px 12px;font-size:11px;font-weight:700;background:#64748b;color:#ffffff;transition:opacity 0.2s;" title="Klik untuk mengaktifkan slot vendor" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                                    <i class="bi bi-x-circle-fill"></i> NONAKTIF
                                                </button>
                                            @endif
                                        </form>
                                    @endif
                                </td>

                                <td style="text-align:right;vertical-align:middle;">
                                    <div style="display:inline-flex;gap:6px;">
                                        <a href="{{ route('admin.ad-plans.edit', $plan) }}" class="btn btn-sm btn-ghost" title="Edit Opsi">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align:center;padding:32px;color:var(--gray-500);">
                                    Belum ada opsi iklan yang dikonfigurasi.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
