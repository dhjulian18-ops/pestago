@extends('layouts.app')
@section('title', 'Pesan & Diskusi — PestaGo')

@section('content')
@php
    $role = auth()->user()->role;
@endphp

@if($role === 'vendor' || $role === 'customer')
<div class="dashboard">
    @if($role === 'vendor')
        @include('vendor._sidebar', ['active' => 'chat'])
    @else
        @include('customer._sidebar', ['active' => 'chat'])
    @endif

    <div class="dashboard-content">
        <div class="uq-chat-header-bar">
            <div>
                <h1 class="uq-chat-header-title">
                    <i class="bi bi-chat-dots" style="color:#111111;"></i> Pesan &amp; Diskusi
                </h1>
                <p class="uq-chat-header-sub">
                    {{ $role === 'vendor' ? 'Daftar pesan dari pelanggan serta pusat bantuan Customer Care PestaGo.' : 'Obrolan langsung dengan vendor acara serta pusat bantuan Customer Care PestaGo.' }}
                </p>
            </div>
            <div class="uq-chat-header-action">
                <form action="{{ route('chat.support') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="uq-chat-cs-btn">
                        <i class="bi bi-headset"></i> <span class="uq-chat-cs-label">Chat Customer Care</span>
                    </button>
                </form>
            </div>
        </div>

        @if($conversations->isEmpty())
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;padding:48px 24px;text-align:center;">
                <div style="width:64px;height:64px;background:#fafafa;border:1px solid #e5e5e5;border-radius:4px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:#888888;font-size:28px;">
                    <i class="bi bi-chat-square-text"></i>
                </div>
                <h3 style="font-size:16px;font-weight:800;color:#111111;margin:0 0 6px;">Belum Ada Percakapan</h3>
                <p style="font-size:13px;color:#666666;margin:0 auto 20px;max-width:420px;line-height:1.5;">
                    {{ $role === 'vendor' ? 'Pertanyaan atau diskusi pesanan dari pelanggan akan tampil di halaman ini.' : 'Mulai percakapan dengan memilih salah satu vendor atau ajukan pertanyaan ke tim Customer Care.' }}
                </p>
                <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
                    @if($role === 'customer')
                    <a href="{{ route('services.index') }}" class="btn" style="height:38px;padding:0 18px;background:#111111;color:#ffffff;border:1px solid #111111;border-radius:2px;font-size:12px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;box-shadow:none;">
                        <i class="bi bi-search"></i> Cari Vendor
                    </a>
                    @endif
                    <form action="{{ route('chat.support') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn" style="height:38px;padding:0 18px;background:#ffffff;color:#111111;border:1px solid #e5e5e5;border-radius:2px;font-size:12px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;display:inline-flex;align-items:center;gap:6px;box-shadow:none;">
                            <i class="bi bi-headset" style="color:#D81A24;"></i> Bantuan CS
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="uq-chat-list">
                @foreach($conversations as $conv)
                @php
                    $isSupport = $conv->isSupport();
                    $unread = $conv->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count();
                @endphp
                <a href="{{ route('chat.show', $conv) }}" class="uq-chat-item {{ $unread > 0 ? 'unread' : '' }}">
                    {{-- Avatar --}}
                    @if($isSupport)
                        <div class="uq-chat-avatar support">
                            <i class="bi bi-headset"></i>
                        </div>
                    @elseif($role === 'vendor')
                        <div class="uq-chat-avatar customer">
                            {{ strtoupper(substr($conv->customer->name ?? '?', 0, 1)) }}
                        </div>
                    @else
                        <div class="uq-chat-avatar vendor">
                            {{ strtoupper(substr($conv->vendorProfile->business_name ?? '?', 0, 1)) }}
                        </div>
                    @endif

                    {{-- Main info --}}
                    <div class="uq-chat-main">
                        <div class="uq-chat-top-row">
                            <div class="uq-chat-name">
                                @if($isSupport)
                                    <span>Customer Care PestaGo</span>
                                    <span class="uq-chat-tag official"><i class="bi bi-patch-check-fill"></i> Official</span>
                                @elseif($role === 'vendor')
                                    <span>{{ $conv->customer->name ?? 'Customer' }}</span>
                                    <span class="uq-chat-tag customer">Pelanggan</span>
                                @else
                                    <span>{{ $conv->vendorProfile->business_name ?? 'Vendor' }}</span>
                                    <span class="uq-chat-tag vendor">Vendor</span>
                                @endif

                                @if($conv->orderItem && $conv->orderItem->service)
                                    <span style="font-size:11px;font-weight:500;color:#767676;display:inline-flex;align-items:center;gap:4px;">
                                        <i class="bi bi-box-seam"></i> {{ \Illuminate\Support\Str::limit($conv->orderItem->service->name, 28) }}
                                    </span>
                                @endif
                            </div>
                            @if($conv->last_message_at)
                            <div class="uq-chat-time">{{ $conv->last_message_at->diffForHumans() }}</div>
                            @endif
                        </div>

                        @if($conv->latestMessage)
                        <p class="uq-chat-preview">
                            @if($conv->latestMessage->sender_id === auth()->id())
                                <span style="color:#111111;font-weight:600;">Anda:</span>
                            @endif
                            {{ $conv->latestMessage->message }}
                        </p>
                        @elseif($isSupport)
                        <p class="uq-chat-preview" style="color:#888888;font-style:italic;">
                            Topik: {{ $conv->support_subject ?: 'Pusat Bantuan & Layanan CS' }}
                        </p>
                        @else
                        <p class="uq-chat-preview" style="color:#888888;font-style:italic;">
                            Klik untuk mulai percakapan...
                        </p>
                        @endif
                    </div>

                    {{-- Right Badge / Arrow --}}
                    <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                        @if($unread > 0)
                            <span class="uq-chat-unread-badge">{{ $unread }}</span>
                        @endif
                        <i class="bi bi-chevron-right" style="color:#bbbbbb;font-size:13px;"></i>
                    </div>
                </a>
                @endforeach
            </div>

            @if($conversations->hasPages())
            <div style="margin-top:20px;">
                {{ $conversations->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@else
{{-- Fallback container for admin/guest --}}
<div style="padding-top:calc(var(--navbar-height) + 24px);padding-bottom:60px;">
    <div class="uq-chat-container">
        <div class="uq-chat-header-bar">
            <div>
                <h1 class="uq-chat-header-title">
                    <i class="bi bi-chat-dots" style="color:#111111;"></i> Pesan &amp; Diskusi
                </h1>
                <p class="uq-chat-header-sub">
                    Pusat perpesanan dan bantuan resmi PestaGo.
                </p>
            </div>
            <div>
                <form action="{{ route('chat.support') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn" style="height:40px;padding:0 16px;background:#ffffff;border:1px solid #111111;color:#111111;border-radius:2px;font-size:12px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;display:inline-flex;align-items:center;gap:8px;box-shadow:none;">
                        <i class="bi bi-headset" style="color:#D81A24;font-size:15px;"></i> Chat Customer Care
                    </button>
                </form>
            </div>
        </div>

        @if($conversations->isEmpty())
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;padding:48px 24px;text-align:center;">
                <p style="color:#767676;margin:0;">Belum ada percakapan aktif.</p>
            </div>
        @else
            <div class="uq-chat-list">
                @foreach($conversations as $conv)
                <a href="{{ route('chat.show', $conv) }}" class="uq-chat-item">
                    <div class="uq-chat-main">
                        <strong style="color:#111111;">Percakapan #{{ $conv->id }}</strong>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endif
@endsection
