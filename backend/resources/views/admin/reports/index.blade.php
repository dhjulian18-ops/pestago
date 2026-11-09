@extends('layouts.app')
@section('title', 'Laporan — Admin PestaGo')
@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'reports'])
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Laporan Sistem</h1>
        </div>

        {{-- Revenue Summary Cards --}}
        <div class="stats-grid" style="margin-bottom:var(--space-8);">
            @php
                $totalRevenue = $revenueByMonth->sum('total');
                $totalCommission = $revenueByMonth->sum('commission');
                $totalOrders = array_sum($orderStats);
                $completedOrders = $orderStats['completed'] ?? 0;
            @endphp
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg, var(--primary), var(--primary-dark));">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-label">Total Pendapatan ({{ now()->year }})</div>
                    <div class="stat-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg, var(--accent), #e8a000);">
                    <i class="bi bi-percent"></i>
                </div>
                <div>
                    <div class="stat-label">Komisi Platform</div>
                    <div class="stat-value">Rp {{ number_format($totalCommission, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg, var(--success), #16a34a);">
                    <i class="bi bi-bag-check"></i>
                </div>
                <div>
                    <div class="stat-label">Total Pesanan</div>
                    <div class="stat-value">{{ number_format($totalOrders) }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg, var(--info), #0284c7);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="stat-label">Pesanan Selesai</div>
                    <div class="stat-value">{{ number_format($completedOrders) }}</div>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6);margin-bottom:var(--space-8);">

            {{-- Revenue Trend Chart --}}
            <div class="card" style="padding:var(--space-6);">
                <h3 style="margin-bottom:var(--space-4);font-size:var(--font-size-lg);font-weight:700;">
                    <i class="bi bi-graph-up" style="color:var(--primary);"></i> Tren Pendapatan {{ now()->year }}
                </h3>
                <div id="revenueChart" style="min-height:260px;display:flex;align-items:flex-end;gap:var(--space-1);padding-top:var(--space-2);">
                    @php
                        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
                        $maxRevenue = $revenueByMonth->max('total') ?: 1;
                    @endphp
                    @for($i = 1; $i <= 12; $i++)
                        @php
                            $monthData = $revenueByMonth->firstWhere('month', $i);
                            $value = $monthData ? $monthData->total : 0;
                            $height = $maxRevenue > 0 ? max(4, ($value / $maxRevenue) * 200) : 4;
                            $hasData = $value > 0;
                        @endphp
                        <div style="flex:1;text-align:center;">
                            <div title="Rp {{ number_format($value, 0, ',', '.') }}"
                                 style="height:{{ $height }}px;background:{{ $hasData ? 'linear-gradient(to top, var(--primary), var(--primary-light))' : 'var(--gray-100)' }};border-radius:var(--rounded) var(--rounded) 0 0;margin:0 auto;transition:all 0.3s;cursor:pointer;position:relative;"
                                 onmouseenter="this.querySelector('.chart-tooltip').style.display='block'"
                                 onmouseleave="this.querySelector('.chart-tooltip').style.display='none'">
                                <div class="chart-tooltip" style="display:none;position:absolute;top:-40px;left:50%;transform:translateX(-50%);background:var(--gray-800);color:white;padding:4px 8px;border-radius:var(--rounded-sm);font-size:10px;white-space:nowrap;z-index:10;">
                                    Rp {{ number_format($value, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="font-size:10px;color:var(--gray-400);margin-top:4px;">{{ $months[$i-1] }}</div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Order Status Breakdown --}}
            <div class="card" style="padding:var(--space-6);">
                <h3 style="margin-bottom:var(--space-4);font-size:var(--font-size-lg);font-weight:700;">
                    <i class="bi bi-pie-chart" style="color:var(--accent);"></i> Status Pesanan
                </h3>
                @php
                    $statusColors = [
                        'pending_payment' => ['Menunggu Bayar', 'var(--warning)', 'bi-clock'],
                        'payment_processing' => ['Verifikasi Bayar', 'var(--info)', 'bi-hourglass-split'],
                        'paid' => ['Dibayar', '#3b82f6', 'bi-credit-card'],
                        'partially_confirmed' => ['Sebagian Dikonfirmasi', '#8b5cf6', 'bi-check'],
                        'confirmed' => ['Dikonfirmasi', '#06b6d4', 'bi-check-circle'],
                        'in_progress' => ['Berlangsung', '#f59e0b', 'bi-play-circle'],
                        'completed' => ['Selesai', 'var(--success)', 'bi-check-circle-fill'],
                        'cancelled' => ['Dibatalkan', 'var(--danger)', 'bi-x-circle'],
                        'refunded' => ['Refund', '#6b7280', 'bi-arrow-return-left'],
                    ];
                @endphp
                <div style="display:flex;flex-direction:column;gap:var(--space-3);">
                    @forelse($orderStats as $status => $count)
                        @php
                            $info = $statusColors[$status] ?? [$status, 'var(--gray-400)', 'bi-circle'];
                            $percentage = $totalOrders > 0 ? ($count / $totalOrders * 100) : 0;
                        @endphp
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                <span style="font-size:var(--font-size-sm);display:flex;align-items:center;gap:var(--space-1);">
                                    <i class="bi {{ $info[2] }}" style="color:{{ $info[1] }};"></i>
                                    {{ $info[0] }}
                                </span>
                                <span style="font-size:var(--font-size-sm);font-weight:600;">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                            </div>
                            <div style="height:8px;background:var(--gray-100);border-radius:999px;overflow:hidden;">
                                <div style="height:100%;width:{{ $percentage }}%;background:{{ $info[1] }};border-radius:999px;transition:width 0.5s ease;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">Belum ada data pesanan.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6);">

            {{-- Top Vendors --}}
            <div class="card" style="padding:var(--space-6);">
                <h3 style="margin-bottom:var(--space-4);font-size:var(--font-size-lg);font-weight:700;">
                    <i class="bi bi-trophy" style="color:var(--accent);"></i> Top 10 Vendor
                </h3>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vendor</th>
                                <th>Kategori</th>
                                <th>Rating</th>
                                <th>Ulasan</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topVendors as $idx => $vendor)
                            <tr>
                                <td>
                                    @if($idx < 3)
                                        <span class="badge" style="background:{{ ['#fef08a','#e2e8f0','#fed7aa'][$idx] }};color:{{ ['#854d0e','#475569','#9a3412'][$idx] }};font-weight:800;font-size:11px;padding:3px 8px;border-radius:6px;">
                                            <i class="bi bi-trophy-fill"></i> #{{ $idx + 1 }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-weight:600;font-size:12px;">#{{ $idx + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $vendor->business_name }}</div>
                                    <div style="font-size:var(--font-size-xs);color:var(--gray-400);">{{ $vendor->city }}</div>
                                </td>
                                <td><span class="badge badge-gray">{{ $vendor->category->name ?? '-' }}</span></td>
                                <td>
                                    <span style="color:var(--accent);font-weight:600;">
                                        <i class="bi bi-star-fill"></i> {{ number_format($vendor->rating_avg, 1) }}
                                    </span>
                                </td>
                                <td>{{ $vendor->reviews_count }}</td>
                                <td style="font-weight:600;">Rp {{ number_format($vendor->total_revenue ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">Belum ada data vendor.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Category Breakdown --}}
            <div class="card" style="padding:var(--space-6);">
                <h3 style="margin-bottom:var(--space-4);font-size:var(--font-size-lg);font-weight:700;">
                    <i class="bi bi-grid-3x3-gap" style="color:var(--primary);"></i> Layanan per Kategori
                </h3>
                <div style="display:flex;flex-direction:column;gap:var(--space-3);">
                    @php
                        $maxServices = $categoryStats->max('services_count') ?: 1;
                        $catColors = ['#E63946', '#F4A261', '#2A9D8F', '#264653', '#E76F51', '#457B9D', '#6D6875'];
                    @endphp
                    @foreach($categoryStats as $idx => $cat)
                        @php
                            $barWidth = $maxServices > 0 ? ($cat->services_count / $maxServices * 100) : 0;
                            $color = $catColors[$idx % count($catColors)];
                        @endphp
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                <span style="font-size:var(--font-size-sm);display:flex;align-items:center;gap:var(--space-1);">
                                    @if($cat->icon)<i class="bi {{ $cat->icon }}" style="color:{{ $color }};"></i>@endif
                                    {{ $cat->name }}
                                </span>
                                <span class="badge badge-gray">{{ $cat->services_count }} layanan</span>
                            </div>
                            <div style="height:8px;background:var(--gray-100);border-radius:999px;overflow:hidden;">
                                <div style="height:100%;width:{{ $barWidth }}%;background:{{ $color }};border-radius:999px;transition:width 0.5s ease;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
