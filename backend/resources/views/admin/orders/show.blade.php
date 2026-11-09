@extends('layouts.app')
@section('title', 'Detail Order — Admin PestaGo')
@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'orders'])
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Order #{{ $order->order_number }}</h1>
            <p class="dashboard-subtitle">{{ $order->created_at->translatedFormat('d M Y H:i') }}</p>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--space-6);">
            <div>
                <div class="card mb-6"><div class="card-body">
                    <h3 style="font-weight:700;margin-bottom:var(--space-4);">Item Pesanan</h3>
                    @foreach($order->items as $item)
                    <div style="padding:var(--space-4) 0;border-bottom:1px solid var(--gray-100);">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <strong>{{ $item->service_name_snapshot }}</strong>
                                <div class="text-muted" style="font-size:var(--font-size-xs);">{{ $item->vendorProfile->business_name ?? '-' }}</div>
                                @if($item->booking_date)<div style="font-size:var(--font-size-xs);color:var(--gray-500);"><i class="bi bi-calendar"></i> {{ \Carbon\Carbon::parse($item->booking_date)->translatedFormat('d M Y') }}</div>@endif
                            </div>
                            <div style="text-align:right;">
                                <div><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></div>
                                @php $is=['pending_confirmation'=>['Menunggu','warning'],'confirmed'=>['Dikonfirmasi','success'],'rejected'=>['Ditolak','danger'],'completed'=>['Selesai','success'],'cancelled'=>['Batal','danger']]; $s=$is[$item->status]??[$item->status,'gray']; @endphp
                                <span class="badge badge-{{ $s[1] }}">{{ $s[0] }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <div style="padding-top:var(--space-4);display:grid;gap:var(--space-2);font-size:var(--font-size-sm);">
                        <div style="display:flex;justify-content:space-between;"><span>Subtotal</span><span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                        <div style="display:flex;justify-content:space-between;"><span>Platform Fee (5%)</span><span>Rp {{ number_format($order->platform_fee, 0, ',', '.') }}</span></div>
                        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:var(--font-size-base);"><span>Total</span><span class="text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span></div>
                    </div>
                </div></div>
            </div>

            <div>
                <div class="card mb-4"><div class="card-body">
                    <h3 style="font-weight:700;margin-bottom:var(--space-3);">Customer</h3>
                    <div style="font-size:var(--font-size-sm);">
                        <div><strong>{{ $order->user->name }}</strong></div>
                        <div>{{ $order->user->email }}</div>
                        <div>{{ $order->user->phone }}</div>
                    </div>
                </div></div>

                <div class="card mb-4"><div class="card-body">
                    <h3 style="font-weight:700;margin-bottom:var(--space-3);">Status</h3>
                    @php $os=['pending_payment'=>['Menunggu Bayar','warning'],'payment_processing'=>['Verifikasi Bayar','info'],'paid'=>['Dibayar','info'],'confirmed'=>['Dikonfirmasi','primary'],'completed'=>['Selesai','success'],'cancelled'=>['Batal','danger']]; $s=$os[$order->status]??[$order->status,'gray']; @endphp
                    <span class="badge badge-{{ $s[1] }}">{{ $s[0] }}</span>
                </div></div>

                @if($order->payment)
                <div class="card"><div class="card-body">
                    <h3 style="font-weight:700;margin-bottom:var(--space-3);">Pembayaran</h3>
                    <div style="font-size:var(--font-size-sm);display:grid;gap:var(--space-2);">
                        <div>Metode: <strong>{{ strtoupper($order->payment->payment_method) }}</strong></div>
                        <div>Status: @php $ps=['pending'=>['Pending','warning'],'processing'=>['Processing','info'],'paid'=>['Paid','success'],'failed'=>['Failed','danger']]; $p=$ps[$order->payment->status]??[$order->payment->status,'gray']; @endphp <span class="badge badge-{{ $p[1] }}">{{ $p[0] }}</span></div>
                        @if($order->payment->status === 'processing')
                        <div style="margin-top:var(--space-3);">
                            <form action="{{ route('admin.payments.verify', $order->payment) }}" method="POST">
                                @csrf
                                <div style="display:flex;gap:var(--space-2);">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" onclick="return confirm('Konfirmasi pembayaran?')"><i class="bi bi-check-lg"></i> Konfirmasi</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pembayaran?')"><i class="bi bi-x-lg"></i> Tolak</button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </div>
                </div></div>
                @endif
            </div>
        </div>

        <a href="{{ route('admin.orders') }}" class="btn btn-ghost mt-6"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
</div>
@endsection
