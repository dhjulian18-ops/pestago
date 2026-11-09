@extends('layouts.app')
@section('title', 'Pesanan — Admin PestaGo')
@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'orders'])
    <div class="dashboard-content">
        <div class="dashboard-header"><h1 class="dashboard-title">Manajemen Pesanan</h1></div>
        <form action="{{ route('admin.orders') }}" method="GET" style="display:flex;gap:var(--space-3);margin-bottom:var(--space-6);flex-wrap:wrap;">
            <select name="status" class="form-control" style="width:auto;"><option value="">Semua Status</option>@foreach(['pending_payment'=>'Menunggu Bayar','payment_processing'=>'Verifikasi','paid'=>'Dibayar','confirmed'=>'Dikonfirmasi','completed'=>'Selesai','cancelled'=>'Batal'] as $k=>$v)<option value="{{ $k }}" {{ $status==$k?'selected':'' }}>{{ $v }}</option>@endforeach</select>
            <input type="text" name="search" class="form-control" style="width:250px;" value="{{ $search }}" placeholder="No. Order / Customer...">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
        </form>
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Pembayaran</th><th>Tanggal</th><th></th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td style="font-weight:600;">{{ $order->order_number }}</td>
                        <td>{{ $order->user->name ?? '-' }}</td>
                        <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td>@php $m=['pending_payment'=>['Menunggu','warning'],'paid'=>['Dibayar','info'],'confirmed'=>['Dikonfirmasi','primary'],'completed'=>['Selesai','success'],'cancelled'=>['Batal','danger']]; $s=$m[$order->status]??[$order->status,'gray']; @endphp<span class="badge badge-{{ $s[1] }}">{{ $s[0] }}</span></td>
                        <td>@php $ps=['pending'=>['Pending','warning'],'processing'=>['Processing','info'],'paid'=>['Paid','success'],'failed'=>['Failed','danger']]; $p=$ps[$order->payment->status??'']??['—','gray']; @endphp<span class="badge badge-{{ $p[1] }}">{{ $p[0] }}</span></td>
                        <td>{{ $order->created_at->translatedFormat('d M Y') }}</td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-ghost">Detail</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted" style="padding:var(--space-8);">Tidak ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>
</div>
@endsection
