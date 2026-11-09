@extends('layouts.app')
@section('title', 'Kalender Booking — Vendor PestaGo')

@section('content')
<div class="dashboard">
    @include('vendor._sidebar', ['active' => 'calendar'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h1 class="dashboard-title">Kalender Booking & Ketersediaan</h1>
                <p class="dashboard-subtitle">Kelola jadwal ketersediaan layanan dan pantau tanggal booking untuk mencegah double booking.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('blockDateModal').style.display='flex'" style="padding:10px 18px;font-weight:700;border-radius:4px;">
                <i class="bi bi-slash-circle"></i> Blokir Tanggal Libur
            </button>
        </div>

        {{-- Filters (Service, Month, Year) --}}
        <div class="card mb-6">
            <div class="card-body" style="padding:var(--space-4);">
                <form action="{{ route('vendor.calendar') }}" method="GET" style="display:flex;gap:var(--space-4);align-items:center;flex-wrap:wrap;">
                    <div style="flex:1;min-width:200px;">
                        <label class="form-label" style="font-size:var(--font-size-xs);margin-bottom:4px;">Filter Layanan</label>
                        <select name="service_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Semua Layanan</option>
                            @foreach($services as $srv)
                            <option value="{{ $srv->id }}" {{ $selectedServiceId == $srv->id ? 'selected' : '' }}>{{ $srv->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:flex;gap:var(--space-2);align-items:flex-end;">
                        @php
                            $prevDate = $startDate->copy()->subMonth();
                            $nextDate = $startDate->copy()->addMonth();
                        @endphp
                        <a href="{{ route('vendor.calendar', ['month' => $prevDate->month, 'year' => $prevDate->year, 'service_id' => $selectedServiceId]) }}" class="btn btn-ghost btn-sm" title="Bulan Sebelumnya">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        <div style="font-weight:700;font-size:var(--font-size-base);padding:0 var(--space-2);white-space:nowrap;line-height:36px;">
                            {{ $startDate->translatedFormat('F Y') }}
                        </div>
                        <a href="{{ route('vendor.calendar', ['month' => $nextDate->month, 'year' => $nextDate->year, 'service_id' => $selectedServiceId]) }}" class="btn btn-ghost btn-sm" title="Bulan Berikutnya">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Legend --}}
        <div style="display:flex;gap:var(--space-6);margin-bottom:var(--space-4);font-size:var(--font-size-xs);flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="width:12px;height:12px;border-radius:3px;background:var(--success);display:inline-block;"></span>
                <span>Terpesan (Booked)</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="width:12px;height:12px;border-radius:3px;background:var(--danger);display:inline-block;"></span>
                <span>Diblokir / Libur</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="width:12px;height:12px;border-radius:3px;background:var(--warning);display:inline-block;"></span>
                <span>Menunggu Konfirmasi</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="width:12px;height:12px;border-radius:3px;background:var(--gray-200);display:inline-block;"></span>
                <span>Tersedia</span>
            </div>
        </div>

        {{-- Calendar Grid --}}
        <div class="card" style="overflow:hidden;">
            @php
                $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                $firstDayOfWeek = $startDate->dayOfWeek; // 0 = Sunday
                $daysInMonth = $startDate->daysInMonth;
            @endphp
            {{-- Week Header --}}
            <div style="display:grid;grid-template-columns:repeat(7, 1fr);background:var(--gray-50);border-bottom:1px solid var(--gray-200);text-align:center;font-weight:700;font-size:var(--font-size-xs);color:var(--gray-600);padding:var(--space-2) 0;">
                @foreach($dayNames as $dn)
                <div>{{ $dn }}</div>
                @endforeach
            </div>

            {{-- Day Cells --}}
            <div style="display:grid;grid-template-columns:repeat(7, 1fr);grid-auto-rows:minmax(110px, auto);gap:1px;background:var(--gray-200);">
                {{-- Leading empty days --}}
                @for($i = 0; $i < $firstDayOfWeek; $i++)
                <div style="background:var(--gray-50);opacity:0.5;"></div>
                @endfor

                {{-- Actual Days --}}
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $currentDateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $isToday = $currentDateStr === now()->format('Y-m-d');
                        $dayAvail = $availabilities->get($currentDateStr);
                        $dayOrders = $bookedOrders->get($currentDateStr, collect());
                        $isBlocked = $dayAvail && $dayAvail->status === 'blocked';
                    @endphp
                    <div style="background:white;padding:var(--space-2);display:flex;flex-direction:column;justify-content:space-between;border:{{ $isToday ? '2px solid var(--primary)' : 'none' }};position:relative;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:{{ $isToday ? '800' : '600' }};font-size:var(--font-size-xs);color:{{ $isToday ? 'var(--primary)' : 'var(--gray-800)' }};">
                                {{ $day }}
                            </span>
                            @if($isBlocked)
                            <form action="{{ route('vendor.calendar.toggle') }}" method="POST" style="margin:0;">
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $selectedServiceId ?: ($services->first()->id ?? 0) }}">
                                <input type="hidden" name="date" value="{{ $currentDateStr }}">
                                <input type="hidden" name="status" value="available">
                                <button type="submit" class="btn btn-xs btn-ghost text-danger" title="Buka kembali tanggal ini" style="padding:1px 4px;font-size:10px;">
                                    <i class="bi bi-unlock-fill"></i>
                                </button>
                            </form>
                            @endif
                        </div>

                        {{-- Day Events / Bookings --}}
                        <div style="display:flex;flex-direction:column;gap:3px;margin:4px 0;overflow:hidden;">
                            @if($isBlocked)
                                <div style="background:rgba(239, 68, 68, 0.1);color:var(--danger);padding:2px 4px;border-radius:4px;font-size:10px;font-weight:600;display:flex;align-items:center;gap:3px;">
                                    <i class="bi bi-slash-circle-fill" style="font-size:9px;"></i> Libur / Blokir
                                </div>
                            @endif

                            @foreach($dayOrders as $orderItem)
                                @php
                                    $isPending = $orderItem->status === 'pending_confirmation';
                                    $bg = $isPending ? 'rgba(245, 158, 11, 0.15)' : 'rgba(16, 185, 129, 0.15)';
                                    $color = $isPending ? '#d97706' : '#059669';
                                @endphp
                                <div style="background:{{ $bg }};color:{{ $color }};padding:2px 4px;border-radius:4px;font-size:10px;line-height:1.2;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $orderItem->service_name_snapshot }} ({{ $orderItem->order->user->name ?? 'Customer' }})">
                                    <i class="bi {{ $isPending ? 'bi-hourglass-split' : 'bi-check-circle-fill' }}" style="font-size:9px;"></i>
                                    {{ $orderItem->service_name_snapshot }}
                                </div>
                            @endforeach
                        </div>

                        {{-- Quick block trigger if not blocked and no order --}}
                        @if(!$isBlocked && $dayOrders->isEmpty())
                        <div style="text-align:right;">
                            <button type="button" class="btn btn-xs btn-ghost text-muted" style="padding:0 2px;font-size:10px;opacity:0.5;" onclick="openBlockModalFor('{{ $currentDateStr }}')" title="Blokir tanggal ini">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                        @else
                        <div></div>
                        @endif
                    </div>
                @endfor

                {{-- Trailing days to fill the row --}}
                @php
                    $totalCells = $firstDayOfWeek + $daysInMonth;
                    $trailingDays = (7 - ($totalCells % 7)) % 7;
                @endphp
                @for($i = 0; $i < $trailingDays; $i++)
                <div style="background:var(--gray-50);opacity:0.5;"></div>
                @endfor
            </div>
        </div>
    </div>
</div>

{{-- Block Date Modal --}}
<div id="blockDateModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:var(--space-4);">
    <div class="card" style="width:100%;max-width:460px;background:white;border-radius:var(--radius-xl);overflow:hidden;animation:fadeIn 0.2s ease;">
        <div style="padding:var(--space-4) var(--space-6);border-bottom:1px solid var(--gray-100);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-size:var(--font-size-base);font-weight:700;margin:0;">
                <i class="bi bi-slash-circle text-danger"></i> Blokir Tanggal Libur
            </h3>
            <button type="button" class="btn btn-sm btn-ghost" onclick="document.getElementById('blockDateModal').style.display='none'">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form action="{{ route('vendor.calendar.toggle') }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="blocked">
            <div style="padding:var(--space-6);">
                <div class="form-group mb-4">
                    <label class="form-label">Pilih Layanan</label>
                    <select name="service_id" id="modalServiceId" class="form-control" required>
                        @foreach($services as $srv)
                        <option value="{{ $srv->id }}" {{ $selectedServiceId == $srv->id ? 'selected' : '' }}>{{ $srv->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Tanggal yang Diblokir</label>
                    <input type="date" name="date" id="modalDate" class="form-control" min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Alasan / Catatan (opsional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Contoh: Libur hari raya / pesanan offline">
                </div>
                <div style="display:flex;justify-content:flex-end;gap:var(--space-3);">
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('blockDateModal').style.display='none'">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-lock-fill"></i> Blokir Tanggal</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openBlockModalFor(dateStr) {
    document.getElementById('modalDate').value = dateStr;
    document.getElementById('blockDateModal').style.display = 'flex';
}
</script>
@endpush
