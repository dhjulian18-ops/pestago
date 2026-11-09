@extends('layouts.app')
@section('title', 'Pesanan Saya — PestaGo')

@section('content')
<div class="dashboard">
    @include('customer._sidebar', ['active' => 'orders'])

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Pesanan Saya</h1>
            <p class="dashboard-subtitle">Kelola dan pantau seluruh status pesanan layanan acara Anda</p>
        </div>

        {{-- Status Filter Tabs (Uniqlo Minimal) --}}
        <div style="display:flex;gap:6px;margin-bottom:24px;flex-wrap:wrap;">
            <a href="{{ route('customer.orders.index') }}" 
               class="btn btn-sm" 
               style="border-radius:2px;padding:6px 14px;font-size:0.82rem;font-weight:700;{{ !$status ? 'background:#111111;color:#ffffff;border:1px solid #111111;' : 'background:#ffffff;color:#333333;border:1px solid #e5e5e5;' }}">
               Semua
            </a>
            @foreach(['pending_payment'=>'Menunggu Bayar','paid'=>'Dibayar','confirmed'=>'Dikonfirmasi','completed'=>'Selesai','cancelled'=>'Batal'] as $k=>$v)
            <a href="{{ route('customer.orders.index', ['status'=>$k]) }}" 
               class="btn btn-sm" 
               style="border-radius:2px;padding:6px 14px;font-size:0.82rem;font-weight:700;{{ $status==$k ? 'background:#111111;color:#ffffff;border:1px solid #111111;' : 'background:#ffffff;color:#333333;border:1px solid #e5e5e5;' }}">
               {{ $v }}
            </a>
            @endforeach
        </div>

        @if($orders->count() > 0)
        <div style="display:flex;flex-direction:column;gap:16px;">
            @foreach($orders as $order)
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;padding:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #f0f0f0;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span style="font-weight:800;color:#111111;font-size:0.95rem;">{{ $order->order_number }}</span>
                        <span style="font-size:0.8rem;color:#767676;">{{ $order->created_at->translatedFormat('d M Y H:i') }}</span>
                    </div>
                    @php 
                        $sm = [
                            'pending_payment' => ['Menunggu Bayar','warning'],
                            'payment_processing' => ['Verifikasi','info'],
                            'paid' => ['Dibayar','info'],
                            'partially_confirmed' => ['Sebagian Dikonfirmasi','info'],
                            'confirmed' => ['Dikonfirmasi','primary'],
                            'in_progress' => ['Berlangsung','info'],
                            'completed' => ['Selesai','success'],
                            'cancelled' => ['Batal','danger']
                        ]; 
                        $s = $sm[$order->status] ?? [$order->status,'gray']; 
                    @endphp
                    <span class="badge badge-{{ $s[1] }}">{{ $s[0] }}</span>
                </div>

                {{-- Order Items --}}
                <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                    @foreach($order->items->take(3) as $item)
                    <div style="display:flex;align-items:center;gap:10px;font-size:0.86rem;">
                        <span style="width:6px;height:6px;background:#111111;border-radius:50%;display:inline-block;flex-shrink:0;"></span>
                        <strong style="color:#111111;">{{ $item->service_name_snapshot }}</strong>
                        <span style="color:#888888;">—</span>
                        <span style="color:#666666;">{{ $item->vendorProfile->business_name ?? '' }}</span>
                        <span style="margin-left:auto;font-weight:700;color:#111111;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                    @if($order->items->count() > 3)
                        <div style="font-size:0.78rem;color:#767676;margin-left:16px;">+{{ $order->items->count() - 3 }} layanan lainnya</div>
                    @endif
                </div>

                {{-- Order Footer --}}
                <div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid #f0f0f0;flex-wrap:wrap;gap:12px;">
                    <div>
                        <span style="font-size:0.75rem;color:#767676;text-transform:uppercase;letter-spacing:0.04em;display:block;">Total Pesanan</span>
                        <div style="font-size:1.15rem;font-weight:800;color:#111111;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                    </div>
                    <div style="display:flex;gap:8px;">
                        @if($order->status === 'pending_payment')
                        <a href="{{ route('customer.orders.payment', $order) }}" class="btn btn-sm btn-primary" style="background:#D81A24;border-color:#D81A24;border-radius:2px;padding:6px 14px;font-weight:700;">
                            <i class="bi bi-credit-card"></i> Bayar Sekarang
                        </a>
                        @endif
                        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-ghost" style="border:1px solid #e5e5e5;background:#ffffff;color:#111111;border-radius:2px;padding:6px 14px;font-weight:700;">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:24px;">
            {{ $orders->links() }}
        </div>
        @else
        <div class="uq-empty-state" style="background:#ffffff;border:1px solid #e5e5e5;padding:48px 24px;text-align:center;border-radius:4px;">
            <div style="font-size:2.5rem;color:#888888;margin-bottom:12px;"><i class="bi bi-bag"></i></div>
            <h3 style="font-size:1.15rem;font-weight:800;color:#111111;margin:0 0 6px;">Belum Ada Pesanan</h3>
            <p style="font-size:0.86rem;color:#666666;margin:0 auto 18px;max-width:380px;">
                Mulai cari dan pesan vendor terpercaya untuk acara Anda.
            </p>
            <a href="{{ route('services.index') }}" class="btn btn-primary" style="padding:8px 18px;border-radius:4px;font-weight:700;">
                Cari Vendor
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
