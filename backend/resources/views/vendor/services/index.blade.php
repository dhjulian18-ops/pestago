@extends('layouts.app')
@section('title', 'Layanan Saya — Vendor PestaGo')

@section('content')
<div class="dashboard">
    @include('vendor._sidebar', ['active' => 'services'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h1 class="dashboard-title">Katalog Layanan Saya</h1>
                <p class="dashboard-subtitle">Kelola seluruh paket dan produk layanan yang Anda tawarkan di PestaGo</p>
            </div>
            @if($vendor->status === 'active')
            <a href="{{ route('vendor.services.create') }}" class="btn btn-primary" style="padding:10px 18px;font-weight:700;border-radius:4px;">
                <i class="bi bi-plus-lg"></i> Tambah Layanan
            </a>
            @endif
        </div>

        @if($services->count() > 0)
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Layanan</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $service)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:14px;">
                                <div style="width:52px;height:52px;border-radius:2px;overflow:hidden;background:#f5f5f5;border:1px solid #eeeeee;flex-shrink:0;">
                                    @if($service->primaryImage)
                                        <img src="{{ asset('storage/' . $service->primaryImage->image_path) }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#aaaaaa;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <strong style="color:#111111;font-size:0.92rem;">{{ $service->name }}</strong>
                                    @if($service->location)
                                    <div style="font-size:0.75rem;color:#767676;margin-top:2px;">
                                        <i class="bi bi-geo-alt"></i> {{ $service->location->city }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="color:#555555;">{{ $service->category->name ?? '-' }}</td>
                        <td><strong style="color:#111111;">Rp {{ number_format($service->price, 0, ',', '.') }}</strong></td>
                        <td>
                            <span style="font-weight:700;color:#111111;">
                                <i class="bi bi-star-fill" style="color:#111111;font-size:0.8rem;"></i> {{ number_format($service->rating_avg, 1) }}
                            </span>
                        </td>
                        <td>
                            @if($service->status === 'published' && $service->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @elseif($service->status === 'draft')
                                <span class="badge badge-gray">Draft</span>
                            @else
                                <span class="badge badge-warning">{{ ucfirst($service->status) }}</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="display:inline-flex;gap:6px;justify-content:flex-end;">
                                <a href="{{ route('vendor.services.edit', $service) }}" 
                                   class="btn btn-sm btn-ghost" 
                                   style="border:1px solid #e5e5e5;background:#ffffff;color:#111111;border-radius:2px;padding:4px 8px;" 
                                   title="Edit Layanan">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('services.show', $service->slug) }}" 
                                   class="btn btn-sm btn-ghost" 
                                   target="_blank" 
                                   style="border:1px solid #e5e5e5;background:#ffffff;color:#111111;border-radius:2px;padding:4px 8px;" 
                                   title="Lihat Pratinjau">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="{{ route('vendor.services.destroy', $service) }}" method="POST" onsubmit="return confirm('Nonaktifkan layanan ini?')" style="margin:0;">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-ghost" 
                                            style="border:1px solid #fecaca;background:#ffffff;color:#D81A24;border-radius:2px;padding:4px 8px;" 
                                            title="Hapus / Nonaktifkan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:24px;">
            {{ $services->links() }}
        </div>
        @else
        <div class="uq-empty-state" style="background:#ffffff;border:1px solid #e5e5e5;padding:48px 24px;text-align:center;border-radius:4px;">
            <div style="font-size:2.5rem;color:#888888;margin-bottom:12px;"><i class="bi bi-box-seam"></i></div>
            <h3 style="font-size:1.15rem;font-weight:800;color:#111111;margin:0 0 6px;">Belum Ada Layanan di Katalog</h3>
            <p style="font-size:0.86rem;color:#666666;margin:0 auto 16px;max-width:380px;">
                Mulai tampilkan penawaran paket sewa, dekorasi, atau jasa terbaik Anda kepada ribuan calon pelanggan.
            </p>
            @if($vendor->status === 'active')
            <a href="{{ route('vendor.services.create') }}" class="btn btn-primary" style="padding:8px 18px;border-radius:4px;font-weight:700;">
                <i class="bi bi-plus-lg"></i> Tambah Layanan Pertama
            </a>
            @else
            <div style="font-size:0.8rem;color:#767676;">
                Status toko Anda saat ini belum aktif. Silakan selesaikan <a href="{{ route('vendor.verification') }}" style="color:#111111;font-weight:700;text-decoration:underline;">Verifikasi Usaha</a> terlebih dahulu.
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
