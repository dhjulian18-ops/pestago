@php
    $cCount = $cartCount ?? (auth()->check() ? \App\Models\Cart::where('user_id', auth()->id())->count() : 0);
    $unreadChats = auth()->check() ? auth()->user()->unreadMessagesCount() : 0;
    $uCust = auth()->user();
@endphp
<aside class="sidebar">
    <div class="sidebar-menu">
        {{-- Profile Header Card --}}
        @if($uCust)
        <div class="sidebar-profile-card">
            @if($uCust->avatar_url)
                <img src="{{ $uCust->avatar_url }}" alt="{{ $uCust->name }}" style="width:36px;height:36px;border-radius:2px;object-fit:cover;border:1px solid #e5e5e5;flex-shrink:0;">
            @else
                <div style="width:36px;height:36px;background:#111111;color:#ffffff;border-radius:2px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0;">
                    {{ strtoupper(substr($uCust->name, 0, 1)) }}
                </div>
            @endif
            <div style="overflow:hidden;line-height:1.25;flex:1;">
                <div style="font-weight:700;font-size:13px;color:#111111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $uCust->name }}
                </div>
                <div style="font-size:10px;font-weight:700;color:#888888;text-transform:uppercase;letter-spacing:0.06em;margin-top:2px;">
                    CUSTOMER
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" title="Pengaturan Profil" style="color:#888888;font-size:14px;text-decoration:none;padding:4px;">
                <i class="bi bi-gear"></i>
            </a>
        </div>
        @endif

        <div class="sidebar-label">Menu Pelanggan</div>
        <a href="{{ route('customer.dashboard') }}" class="sidebar-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('customer.wishlist') }}" class="sidebar-link {{ ($active ?? '') === 'wishlist' ? 'active' : '' }}">
            <i class="bi bi-heart"></i> Favorit Saya
            @php $wCount = auth()->check() ? auth()->user()->wishlists()->count() : 0; @endphp
            @if($wCount > 0)
                <span class="badge" style="background:#D81A24;color:#ffffff;margin-left:auto;font-size:11px;padding:2px 6px;border-radius:10px;">{{ $wCount }}</span>
            @endif
        </a>
        <a href="{{ route('customer.events.index') }}" class="sidebar-link {{ ($active ?? '') === 'events' ? 'active' : '' }}">
            <i class="bi bi-calendar-event"></i> Acara Saya
        </a>
        <a href="{{ route('services.index') }}" class="sidebar-link {{ ($active ?? '') === 'services' ? 'active' : '' }}">
            <i class="bi bi-search"></i> Cari Vendor
        </a>
        <a href="{{ route('customer.cart') }}" class="sidebar-link {{ ($active ?? '') === 'cart' ? 'active' : '' }}">
            <i class="bi bi-cart3"></i> Keranjang
            @if($cCount > 0)
                <span class="badge badge-primary" style="margin-left:auto;font-size:11px;padding:2px 6px;">{{ $cCount }}</span>
            @endif
        </a>
        <a href="{{ route('customer.orders.index') }}" class="sidebar-link {{ ($active ?? '') === 'orders' ? 'active' : '' }}">
            <i class="bi bi-bag-check"></i> Pesanan
        </a>
        <a href="{{ route('chat.index') }}" class="sidebar-link {{ ($active ?? '') === 'chat' ? 'active' : '' }}">
            <i class="bi bi-chat-dots"></i> Pesan &amp; Chat
            @if($unreadChats > 0)
                <span class="badge badge-danger" style="margin-left:auto;font-size:10px;font-weight:700;padding:2px 7px;background:#D81A24;color:#ffffff;border-radius:10px;">{{ $unreadChats }}</span>
            @endif
        </a>

        <div class="sidebar-divider">
            <div class="sidebar-label">Bantuan</div>
            <form action="{{ route('chat.support') }}" method="POST">
                @csrf
                <button type="submit" class="sidebar-link sidebar-btn-support">
                    <i class="bi bi-headset"></i> <span>Hubungi CS</span>
                    <span class="badge badge-success sidebar-support-badge">Online</span>
                </button>
            </form>
        </div>
    </div>
</aside>
