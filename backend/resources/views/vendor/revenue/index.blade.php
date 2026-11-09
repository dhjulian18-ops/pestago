@extends('layouts.app')
@section('title', 'Pendapatan — Vendor PestaGo')

@section('content')
<div class="dashboard">
    @include('vendor._sidebar', ['active' => 'revenue'])

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Pendapatan Vendor</h1>
            <p class="dashboard-subtitle">Ringkasan transaksi dan akumulasi pendapatan dari pesanan yang telah selesai</p>
        </div>

        {{-- Summary Card (Uniqlo Minimal Clean) --}}
        <div style="background:#ffffff;border:1px solid #e5e5e5;border-left:4px solid #111111;border-radius:4px;padding:24px 28px;margin-bottom:28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
                <span style="font-size:0.75rem;font-weight:700;color:#767676;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">
                    Total Akumulasi Pendapatan
                </span>
                <div style="font-size:2.2rem;font-weight:800;color:#111111;line-height:1.1;letter-spacing:-0.02em;">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
            </div>
            <div style="font-size:0.82rem;color:#767676;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-shield-check" style="color:#111111;font-size:1.1rem;"></i> Dana ditransfer berkala ke rekening terdaftar
            </div>
        </div>

        {{-- Period Filter --}}
        <div style="display:flex;gap:6px;margin-bottom:24px;flex-wrap:wrap;">
            @foreach(['all'=>'Semua','today'=>'Hari Ini','week'=>'7 Hari','month'=>'30 Hari','year'=>'Tahun Ini'] as $key => $label)
            <a href="{{ route('vendor.revenue', ['period'=>$key]) }}" 
               class="btn btn-sm" 
               style="border-radius:2px;padding:6px 14px;font-size:0.82rem;font-weight:700;{{ $period === $key ? 'background:#111111;color:#ffffff;border:1px solid #111111;' : 'background:#ffffff;color:#333333;border:1px solid #e5e5e5;' }}">
               {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Monthly Chart --}}
        @if(count($monthlyRevenue) > 0)
        <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;padding:24px;margin-bottom:28px;">
            <h3 style="font-size:1.05rem;font-weight:800;color:#111111;margin:0 0 20px;letter-spacing:-0.01em;">
                Tren Pendapatan Bulanan ({{ now()->year }})
            </h3>
            <div style="display:flex;gap:8px;align-items:flex-end;height:220px;padding-top:20px;border-bottom:1px solid #eeeeee;">
                @php
                    $months = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                    $maxVal = max($monthlyRevenue) ?: 1;
                @endphp
                @for($m = 1; $m <= 12; $m++)
                @php $val = $monthlyRevenue[$m] ?? 0; $pct = ($val / $maxVal) * 100; @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;gap:8px;">
                    <div style="font-size:0.7rem;font-weight:700;color:#555555;white-space:nowrap;">
                        @if($val > 0)
                            {{ 'Rp ' . number_format($val/1000000, 1) . 'jt' }}
                        @endif
                    </div>
                    <div style="width:100%;max-width:32px;background:{{ $val > 0 ? '#111111' : '#f0f0f0' }};border-radius:2px 2px 0 0;min-height:4px;height:{{ max($pct, 2) }}%;transition:all 0.2s ease;"></div>
                    <div style="font-size:0.75rem;font-weight:600;color:#767676;">{{ $months[$m] }}</div>
                </div>
                @endfor
            </div>
        </div>
        @endif

        {{-- Transaction List --}}
        <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;padding:24px;">
            <h3 style="font-size:1.05rem;font-weight:800;color:#111111;margin:0 0 16px;letter-spacing:-0.01em;">
                Riwayat Transaksi Masuk
            </h3>
            @if($transactions->isEmpty())
                <div class="uq-empty-state" style="background:#ffffff;border:none;padding:36px 20px;text-align:center;">
                    <div style="font-size:2rem;color:#888888;margin-bottom:8px;"><i class="bi bi-receipt"></i></div>
                    <p style="font-size:0.86rem;color:#666666;margin:0;">Belum ada riwayat transaksi yang selesai pada periode ini.</p>
                </div>
            @else
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal Selesai</th>
                                <th>Layanan</th>
                                <th>Pelanggan</th>
                                <th style="text-align:right;">Nominal Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $trx)
                            <tr>
                                <td style="color:#666666;">{{ $trx->updated_at->format('d M Y') }}</td>
                                <td style="font-weight:600;color:#111111;">{{ $trx->service_name_snapshot }}</td>
                                <td style="color:#555555;">{{ $trx->order->user->name ?? '-' }}</td>
                                <td style="text-align:right;font-weight:800;color:#111111;">
                                    Rp {{ number_format($trx->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:20px;">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
