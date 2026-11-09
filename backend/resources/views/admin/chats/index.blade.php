@extends('layouts.app')
@section('title', 'Pusat Bantuan & Live Chat CS — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'chats'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:24px;padding-bottom:18px;border-bottom:1px solid #e5e5e5;">
            <div>
                <h1 class="dashboard-title" style="font-size:1.45rem;font-weight:800;color:#111111;margin:0;display:flex;align-items:center;gap:10px;">
                    <i class="bi bi-chat-dots" style="color:#111111;"></i> Live Chat &amp; Helpdesk CS
                </h1>
                <p class="dashboard-subtitle" style="font-size:0.85rem;color:#767676;margin:4px 0 0;">
                    Layanan chat bantuan untuk menjawab pertanyaan dan kendala dari pelanggan maupun mitra vendor.
                </p>
            </div>
            <div>
                <span style="background:#ffffff;border:1px solid #111111;color:#111111;font-size:11px;font-weight:700;padding:6px 14px;border-radius:2px;display:inline-flex;align-items:center;gap:8px;letter-spacing:0.5px;text-transform:uppercase;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#059669;"></span> CS Online
                </span>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div style="display:flex;gap:8px;margin-bottom:20px;">
            <a href="{{ route('admin.chats.index') }}" class="btn" style="height:36px;padding:0 16px;font-size:12px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;border-radius:2px;display:inline-flex;align-items:center;gap:6px;box-shadow:none;{{ ($filter ?? 'all') === 'all' ? 'background:#111111;color:#ffffff;border:1px solid #111111;' : 'background:#ffffff;color:#444444;border:1px solid #e5e5e5;' }}">
                Semua Percakapan
            </a>
            <a href="{{ route('admin.chats.index', ['filter' => 'unread']) }}" class="btn" style="height:36px;padding:0 16px;font-size:12px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;border-radius:2px;display:inline-flex;align-items:center;gap:6px;box-shadow:none;{{ ($filter ?? '') === 'unread' ? 'background:#111111;color:#ffffff;border:1px solid #111111;' : 'background:#ffffff;color:#444444;border:1px solid #e5e5e5;' }}">
                <i class="bi bi-envelope-fill"></i> Belum Dibalas
            </a>
        </div>

        @if($conversations->isEmpty())
        <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:4px;padding:48px 24px;text-align:center;">
            <div style="width:64px;height:64px;background:#fafafa;border:1px solid #e5e5e5;border-radius:4px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:#888888;font-size:28px;">
                <i class="bi bi-chat-square-text"></i>
            </div>
            <h3 style="font-size:16px;font-weight:800;color:#111111;margin:0 0 6px;">Belum Ada Pesan Masuk</h3>
            <p style="font-size:13px;color:#666666;margin:0 auto;max-width:440px;line-height:1.5;">
                Ketika pelanggan atau vendor mengajukan pertanyaan atau pesan bantuan, percakapan mereka akan langsung ditampilkan di antrean ini.
            </p>
        </div>
        @else
        <div class="uq-chat-list">
            @foreach($conversations as $conv)
            @php
                $isVendor = ($conv->customer->role ?? '') === 'vendor';
                $unreadCount = $conv->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count();
            @endphp
            <a href="{{ route('admin.chats.show', $conv) }}" class="uq-chat-item {{ $unreadCount > 0 ? 'unread' : '' }}">
                {{-- Avatar --}}
                <div class="uq-chat-avatar {{ $isVendor ? 'vendor' : 'customer' }}">
                    {{ strtoupper(substr($conv->customer->name ?? '?', 0, 1)) }}
                </div>

                {{-- Content --}}
                <div class="uq-chat-main">
                    <div class="uq-chat-top-row">
                        <div class="uq-chat-name">
                            <span>{{ $conv->customer->name ?? 'Pengguna' }}</span>
                            <span class="uq-chat-tag {{ $isVendor ? 'vendor' : 'customer' }}">
                                {{ $isVendor ? 'Vendor' : 'Pelanggan' }}
                            </span>
                            @if($isVendor && $conv->customer->vendorProfile)
                                <span style="font-size:11px;font-weight:500;color:#767676;">
                                    — {{ $conv->customer->vendorProfile->business_name }}
                                </span>
                            @endif
                        </div>
                        <div class="uq-chat-time">
                            {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : '-' }}
                        </div>
                    </div>

                    <div style="font-size:12px;color:#767676;margin-bottom:4px;display:flex;align-items:center;gap:6px;">
                        <i class="bi bi-tag" style="color:#D81A24;"></i> Topik: <strong style="color:#111111;">{{ $conv->support_subject ?: 'Bantuan Umum' }}</strong>
                    </div>

                    @if($conv->latestMessage)
                    <p class="uq-chat-preview">
                        @if($conv->latestMessage->sender_id === auth()->id())
                            <span style="color:#111111;font-weight:700;">Anda (CS):</span>
                        @endif
                        {{ $conv->latestMessage->message }}
                    </p>
                    @endif
                </div>

                {{-- Unread Badge & Chevron --}}
                <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;">
                    @if($unreadCount > 0)
                        <span class="uq-chat-unread-badge" style="border-radius:2px;font-size:10px;padding:3px 8px;letter-spacing:0.4px;">
                            {{ $unreadCount }} PESAN
                        </span>
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
@endsection
