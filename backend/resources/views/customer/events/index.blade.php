@extends('layouts.app')
@section('title', 'Acara Saya — PestaGo')

@section('content')
<div class="dashboard">
    @include('customer._sidebar', ['active' => 'events'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h1 class="dashboard-title">Acara Saya</h1>
                <p class="dashboard-subtitle">Rencanakan jadwal dan kelola seluruh kebutuhan acara Anda</p>
            </div>
            <a href="{{ route('customer.events.create') }}" class="btn btn-primary" style="padding:10px 18px;font-weight:700;border-radius:4px;">
                <i class="bi bi-plus-lg"></i> Buat Acara Baru
            </a>
        </div>

        @if($events->count() > 0)
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:16px;">
            @foreach($events as $event)
            <a href="{{ route('customer.events.show', $event) }}" 
               style="background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;padding:20px;text-decoration:none;display:flex;flex-direction:column;justify-content:space-between;transition:border-color 0.15s ease;"
               onmouseover="this.style.borderColor='#111111';"
               onmouseout="this.style.borderColor='#e5e5e5';">
                <div>
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:10px;">
                        <div>
                            <span style="font-size:0.72rem;font-weight:700;color:#888888;text-transform:uppercase;letter-spacing:0.04em;">
                                {{ $event->event_type }}
                            </span>
                            <h3 style="font-size:1.1rem;font-weight:800;color:#111111;margin:4px 0 0;line-height:1.3;">
                                {{ $event->name }}
                            </h3>
                        </div>
                        <span class="badge {{ $event->event_date >= today() ? 'badge-primary' : 'badge-gray' }}" style="font-size:10px;">
                            {{ $event->event_date >= today() ? 'Mendatang' : 'Selesai' }}
                        </span>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:6px;font-size:0.83rem;color:#555555;margin-top:14px;padding-top:14px;border-top:1px solid #f0f0f0;">
                        <div><i class="bi bi-calendar3" style="color:#888;margin-right:6px;"></i> {{ $event->event_date?->translatedFormat('d M Y') }}</div>
                        @if($event->location_city)
                        <div><i class="bi bi-geo-alt" style="color:#888;margin-right:6px;"></i> {{ $event->location_city }}</div>
                        @endif
                        @if($event->guest_count)
                        <div><i class="bi bi-people" style="color:#888;margin-right:6px;"></i> {{ $event->guest_count }} Tamu</div>
                        @endif
                        @if($event->budget)
                        <div><i class="bi bi-wallet2" style="color:#888;margin-right:6px;"></i> Estimasi: <strong>Rp {{ number_format($event->budget, 0, ',', '.') }}</strong></div>
                        @endif
                    </div>
                </div>

                <div style="margin-top:18px;padding-top:12px;border-top:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.8rem;font-weight:700;color:#111111;">Kelola Vendor Acara</span>
                    <i class="bi bi-arrow-right" style="color:#111111;font-size:0.9rem;"></i>
                </div>
            </a>
            @endforeach
        </div>
        <div style="margin-top:24px;">
            {{ $events->links() }}
        </div>
        @else
        <div class="uq-empty-state" style="background:#ffffff;border:1px solid #e5e5e5;padding:48px 24px;text-align:center;border-radius:4px;">
            <div style="font-size:2.5rem;color:#888888;margin-bottom:12px;"><i class="bi bi-calendar-event"></i></div>
            <h3 style="font-size:1.15rem;font-weight:800;color:#111111;margin:0 0 6px;">Belum Ada Rencana Acara</h3>
            <p style="font-size:0.86rem;color:#666666;margin:0 auto 18px;max-width:380px;">
                Mulai buat rencana acara Anda (pernikahan, ulang tahun, gathering) dan booking vendor dengan rapi.
            </p>
            <a href="{{ route('customer.events.create') }}" class="btn btn-primary" style="padding:8px 18px;border-radius:4px;font-weight:700;">
                <i class="bi bi-plus-lg"></i> Buat Acara Pertama
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
