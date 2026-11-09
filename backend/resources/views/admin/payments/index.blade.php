@extends('layouts.app')
@section('title', 'Pembayaran — Admin PestaGo')
@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'payments'])
    <div class="dashboard-content">
        <div class="dashboard-header"><h1 class="dashboard-title">Manajemen Pembayaran</h1></div>
        <div style="display:flex;gap:var(--space-2);margin-bottom:var(--space-6);">
            <a href="{{ route('admin.payments') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-ghost' }}">Semua</a>
            <a href="{{ route('admin.payments', ['status'=>'processing']) }}" class="btn btn-sm {{ $status=='processing' ? 'btn-primary' : 'btn-ghost' }}">Perlu Verifikasi</a>
            <a href="{{ route('admin.payments', ['status'=>'paid']) }}" class="btn btn-sm {{ $status=='paid' ? 'btn-primary' : 'btn-ghost' }}">Sudah Dibayar</a>
            <a href="{{ route('admin.payments', ['status'=>'pending']) }}" class="btn btn-sm {{ $status=='pending' ? 'btn-primary' : 'btn-ghost' }}">Pending</a>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead><tr><th>Order #</th><th>Customer</th><th>Jumlah</th><th>Komisi</th><th>Metode</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($payments as $pay)
                    <tr>
                        <td style="font-weight:600;">{{ $pay->order->order_number ?? '-' }}</td>
                        <td>{{ $pay->order->user->name ?? '-' }}</td>
                        <td>Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($pay->platform_commission, 0, ',', '.') }}</td>
                        <td><span class="badge badge-gray">{{ strtoupper($pay->payment_method) }}</span></td>
                        <td>@php $ps=['pending'=>['Pending','warning'],'processing'=>['Verifikasi','info'],'paid'=>['Paid','success'],'failed'=>['Failed','danger']]; $p=$ps[$pay->status]??[$pay->status,'gray']; @endphp<span class="badge badge-{{ $p[1] }}">{{ $p[0] }}</span></td>
                        <td>{{ $pay->created_at->translatedFormat('d M Y') }}</td>
                        <td>
                            @if($pay->status === 'processing')
                            <form action="{{ route('admin.payments.verify', $pay) }}" method="POST" style="display:flex;gap:var(--space-1);">
                                @csrf
                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" title="Konfirmasi" onclick="return confirm('Konfirmasi?')"><i class="bi bi-check-lg"></i></button>
                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" title="Tolak" onclick="return confirm('Tolak?')"><i class="bi bi-x-lg"></i></button>
                            </form>
                            @else—@endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted" style="padding:var(--space-8);">Tidak ada data pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $payments->links() }}
    </div>
</div>
@endsection
