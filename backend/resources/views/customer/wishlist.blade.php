@extends('layouts.app')
@section('title', 'Layanan Favorit Saya — PestaGo')

@section('content')
<div class="dashboard">
    @if(auth()->check() && auth()->user()->role === 'customer')
        @include('customer._sidebar', ['active' => 'wishlist'])
    @endif

    <div class="dashboard-content" style="background:#ffffff;min-height:80vh;">
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
            <div>
                <h1 class="dashboard-title" style="display:flex;align-items:center;gap:10px;">
                    <i class="bi bi-heart-fill" style="color:#D81A24;font-size:1.4rem;"></i> Layanan Favorit Saya
                </h1>
                <p class="dashboard-subtitle">Kumpulan layanan vendor yang telah Anda simpan dan sukai untuk kebutuhan acara Anda</p>
            </div>
            @if($totalWishlists > 0)
                <span style="font-size:12px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#555555;background:#f5f5f5;padding:6px 12px;border:1px solid #e5e5e5;border-radius:2px;">
                    {{ $totalWishlists }} Layanan Disukai
                </span>
            @endif
        </div>

        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:3px;margin-bottom:20px;font-size:13.5px;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($wishlists->count() > 0)
            <div class="uq-wishlist-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:20px;margin-bottom:32px;">
                @foreach($wishlists as $wishlist)
                    @php
                        $service = $wishlist->service;
                        if (!$service) continue;
                    @endphp
                    <div class="uq-wishlist-card" id="wishlist-item-{{ $service->id }}" style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;display:flex;flex-direction:column;position:relative;transition:all 0.2s ease;">
                        {{-- Image Wrap --}}
                        <div style="position:relative;width:100%;aspect-ratio:1/1;background:#f5f5f5;overflow:hidden;">
                            @if($service->category)
                                <span style="position:absolute;top:10px;left:10px;z-index:2;background:rgba(17,17,17,0.85);color:#ffffff;font-size:10px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;padding:3px 8px;border-radius:2px;">
                                    {{ $service->category->name }}
                                </span>
                            @endif

                            {{-- Delete Wishlist Button --}}
                            <form action="{{ route('customer.wishlist.destroy', $service->slug ?? $service->id) }}" method="POST" style="margin:0;position:absolute;top:10px;right:10px;z-index:3;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus layanan ini dari favorit?')" title="Hapus dari Favorit" style="width:34px;height:34px;background:#ffffff;border:1px solid #eeeeee;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#D81A24;box-shadow:0 1px 4px rgba(0,0,0,0.1);transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                    <i class="bi bi-trash3" style="font-size:14px;"></i>
                                </button>
                            </form>

                            <a href="{{ route('services.show', $service->slug) }}" style="display:block;width:100%;height:100%;">
                                <img src="{{ $service->thumbnail_url }}" alt="{{ $service->name }}" style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s ease;" loading="lazy" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'">
                            </a>
                        </div>

                        {{-- Card Body --}}
                        <div style="padding:14px 16px;display:flex;flex-direction:column;flex:1;justify-content:space-between;">
                            <div>
                                <h3 style="font-size:14px;font-weight:700;line-height:1.35;margin:0 0 6px 0;color:#111111;">
                                    <a href="{{ route('services.show', $service->slug) }}" style="color:#111111;text-decoration:none;" title="{{ $service->name }}">
                                        {{ Str::limit($service->name, 50) }}
                                    </a>
                                </h3>

                                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#666666;margin-bottom:6px;">
                                    <i class="bi bi-geo-alt" style="font-size:11px;color:#888888;"></i>
                                    <span>{{ $service->location->city ?? 'Indonesia' }}</span>
                                    @if($service->vendorProfile)
                                        <span style="color:#cccccc;">•</span>
                                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $service->vendorProfile->business_name }}</span>
                                    @endif
                                </div>

                                <div style="display:flex;align-items:center;gap:4px;font-size:12px;color:#f59e0b;margin-bottom:12px;">
                                    <i class="bi bi-star-fill" style="font-size:11px;"></i>
                                    <strong style="color:#111111;font-weight:700;">{{ number_format($service->rating_avg ?: 4.9, 1) }}</strong>
                                    <span style="color:#888888;font-size:11px;">({{ $service->rating_count ?: 30 }})</span>
                                </div>
                            </div>

                            <div>
                                <div style="margin-bottom:12px;padding-top:10px;border-top:1px solid #f0f0f0;">
                                    <span style="font-size:10px;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#888888;display:block;">Mulai Dari</span>
                                    <div style="display:flex;align-items:baseline;gap:4px;">
                                        <strong style="font-size:15px;font-weight:800;color:#D81A24;">Rp {{ number_format($service->price, 0, ',', '.') }}</strong>
                                        <span style="font-size:11px;color:#888888;">/{{ str_replace('per_', '', $service->price_unit) }}</span>
                                    </div>
                                </div>

                                <a href="{{ route('services.show', $service->slug) }}" style="display:block;width:100%;text-align:center;background:#111111;color:#ffffff;font-size:12px;font-weight:700;padding:9px 12px;text-decoration:none;border-radius:2px;letter-spacing:0.04em;text-transform:uppercase;transition:background 0.15s ease;" onmouseover="this.style.background='#D81A24'" onmouseout="this.style.background='#111111'">
                                    Lihat &amp; Pesan
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:24px;">
                {{ $wishlists->links() }}
            </div>
        @else
            {{-- Empty State (Uniqlo Style Minimalist) --}}
            <div style="text-align:center;padding:70px 20px;border:1px dashed #dddddd;border-radius:2px;background:#fafafa;max-width:540px;margin:30px auto;">
                <div style="width:68px;height:68px;background:#ffffff;border:1px solid #e5e5e5;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:18px;">
                    <i class="bi bi-heart" style="font-size:28px;color:#D81A24;"></i>
                </div>
                <h2 style="font-size:18px;font-weight:800;color:#111111;margin:0 0 8px;">Belum Ada Layanan Favorit</h2>
                <p style="font-size:13.5px;color:#666666;line-height:1.6;margin:0 auto 24px;max-width:420px;">
                    Anda belum menyimpan layanan vendor apa pun. Tekan ikon hati pada layanan yang Anda sukai di beranda atau katalog agar mudah dipesan nanti.
                </p>
                <a href="{{ route('services.index') }}" style="display:inline-flex;align-items:center;gap:8px;background:#D81A24;color:#ffffff;font-size:13px;font-weight:700;padding:12px 24px;text-decoration:none;border-radius:2px;letter-spacing:0.04em;text-transform:uppercase;">
                    <i class="bi bi-search"></i> Jelajahi Layanan Sekarang
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

