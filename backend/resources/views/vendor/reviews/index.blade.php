@extends('layouts.app')
@section('title', 'Ulasan Pelanggan — Vendor PestaGo')

@section('content')
<div class="dashboard">
    @include('vendor._sidebar', ['active' => 'reviews'])

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Ulasan Pelanggan</h1>
            <p class="dashboard-subtitle">Pantau rating kepuasan dan tanggapi ulasan dari pelanggan layanan Anda</p>
        </div>

        @if($reviews->isEmpty())
            <div class="uq-empty-state" style="background:#ffffff;border:1px solid #e5e5e5;padding:48px 24px;text-align:center;border-radius:4px;">
                <div style="font-size:2.5rem;color:#888888;margin-bottom:12px;"><i class="bi bi-star"></i></div>
                <h3 style="font-size:1.15rem;font-weight:800;color:#111111;margin:0 0 6px;">Belum Ada Ulasan</h3>
                <p style="font-size:0.86rem;color:#666666;margin:0;">Ulasan dari pelanggan yang telah menyelesaikan pemesanan akan ditampilkan di sini.</p>
            </div>
        @else
            <div style="display:flex;flex-direction:column;gap:16px;">
                @foreach($reviews as $review)
                <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;padding:22px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;">
                        <div style="display:flex;gap:12px;align-items:flex-start;">
                            <div style="width:38px;height:38px;border-radius:2px;background:#111111;color:#ffffff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;flex-shrink:0;">
                                {{ strtoupper(substr($review->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <strong style="font-size:0.92rem;color:#111111;">{{ $review->user->name ?? 'Customer' }}</strong>
                                <div style="color:#111111;font-size:0.82rem;margin:2px 0;">
                                    @for($i=1;$i<=5;$i++)
                                        <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                    <span style="font-weight:700;margin-left:4px;color:#111111;">{{ $review->rating }}.0</span>
                                </div>
                                <div style="font-size:0.75rem;color:#767676;">
                                    {{ $review->service->name ?? $review->orderItem->service_name_snapshot ?? '-' }} · {{ $review->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <p style="margin:14px 0 10px;font-size:0.88rem;color:#333333;line-height:1.5;">
                        {{ $review->comment }}
                    </p>

                    @if($review->images && count($review->images) > 0)
                    <div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
                        @foreach($review->images as $img)
                        <img src="{{ asset('storage/' . $img) }}" style="width:72px;height:72px;object-fit:cover;border-radius:2px;border:1px solid #eeeeee;">
                        @endforeach
                    </div>
                    @endif

                    {{-- Vendor Reply --}}
                    @if($review->vendor_reply)
                    <div style="background:#fafafa;border:1px solid #eeeeee;border-left:3px solid #111111;border-radius:2px;padding:12px 16px;margin-top:12px;">
                        <div style="font-size:0.75rem;font-weight:700;color:#111111;margin-bottom:4px;">
                            <i class="bi bi-reply-fill"></i> Balasan Anda · <span style="font-weight:normal;color:#767676;">{{ $review->vendor_replied_at?->diffForHumans() }}</span>
                        </div>
                        <p style="font-size:0.84rem;color:#444444;margin:0;line-height:1.45;">{{ $review->vendor_reply }}</p>
                    </div>
                    @else
                    <details style="margin-top:12px;">
                        <summary style="font-size:0.82rem;font-weight:700;color:#111111;cursor:pointer;">
                            <i class="bi bi-reply"></i> Balas Ulasan Ini
                        </summary>
                        <form action="{{ route('vendor.reviews.reply', $review) }}" method="POST" style="margin-top:10px;">
                            @csrf
                            <textarea name="vendor_reply" rows="2" placeholder="Tulis tanggapan atau terima kasih untuk pelanggan..." required 
                                      style="width:100%;border:1px solid #e5e5e5;border-radius:2px;padding:8px 12px;font-size:0.84rem;color:#111111;outline:none;background:#fafafa;"></textarea>
                            <button type="submit" class="btn btn-sm btn-primary" style="margin-top:6px;border-radius:2px;padding:6px 14px;font-weight:700;">
                                Kirim Balasan
                            </button>
                        </form>
                    </details>
                    @endif
                </div>
                @endforeach
            </div>
            <div style="margin-top:24px;">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
