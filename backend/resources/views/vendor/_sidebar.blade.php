@php
    $uVendor = auth()->user();
    $vp = $uVendor?->vendorProfile;
    $isVActive = $vp && $vp->status === 'active';
    $unreadVendorChats = $uVendor ? $uVendor->unreadMessagesCount() : 0;
    $vendorAvatar = $vp?->logo_url ?: $uVendor?->avatar_url;
    $vendorName = $vp?->business_name ?? $uVendor?->name ?? 'Vendor';
@endphp
<aside class="sidebar">
    <div class="sidebar-menu">
        {{-- Vendor Profile Mini Card --}}
        <div class="sidebar-profile-card">
            @if($vendorAvatar)
                <img src="{{ $vendorAvatar }}" alt="{{ $vendorName }}" style="width:36px;height:36px;border-radius:2px;object-fit:cover;border:1px solid #e5e5e5;flex-shrink:0;">
            @else
                <div style="width:36px;height:36px;background:#111111;color:#ffffff;border-radius:2px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0;">
                    {{ strtoupper(substr($vendorName, 0, 1)) }}
                </div>
            @endif
            <div style="overflow:hidden;line-height:1.25;flex:1;">
                <div style="font-weight:700;font-size:13px;color:#111111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $vendorName }}
                </div>
                <div style="font-size:10px;font-weight:700;color:#888888;text-transform:uppercase;letter-spacing:0.06em;margin-top:2px;">
                    MITRA VENDOR
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" title="Pengaturan Profil" style="color:#888888;font-size:14px;text-decoration:none;padding:4px;">
                <i class="bi bi-gear"></i>
            </a>
        </div>

        <div class="sidebar-label">Vendor Panel</div>
        <a href="{{ route('vendor.dashboard') }}" class="sidebar-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="{{ route('vendor.verification') }}" class="sidebar-link {{ ($active ?? '') === 'verification' ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i> Verifikasi Usaha
            @if(!$isVActive)
                @if($vp && $vp->status === 'incomplete')
                    <span class="badge badge-warning" style="margin-left:auto;font-size:10px;">Lengkapi</span>
                @elseif($vp && $vp->status === 'needs_revision')
                    <span class="badge badge-warning" style="margin-left:auto;font-size:10px;">Revisi</span>
                @elseif($vp && $vp->status === 'pending')
                    <span class="badge badge-info" style="margin-left:auto;font-size:10px;">Ditinjau</span>
                @elseif($vp && $vp->status === 'rejected')
                    <span class="badge badge-danger" style="margin-left:auto;font-size:10px;">Ditolak</span>
                @endif
            @else
                <span class="badge badge-success" style="margin-left:auto;font-size:10px;"><i class="bi bi-check"></i> Aktif</span>
            @endif
        </a>

        <a href="{{ $isVActive ? route('vendor.services.index') : route('vendor.verification') }}" class="sidebar-link {{ ($active ?? '') === 'services' ? 'active' : '' }} {{ !$isVActive ? 'text-muted' : '' }}">
            <i class="bi bi-box-seam"></i> Layanan Saya
            @if(!$isVActive)
                <i class="bi bi-lock-fill" style="margin-left:auto;font-size:12px;opacity:0.6;"></i>
            @endif
        </a>

        <a href="{{ $isVActive ? route('vendor.orders') : route('vendor.verification') }}" class="sidebar-link {{ ($active ?? '') === 'orders' ? 'active' : '' }} {{ !$isVActive ? 'text-muted' : '' }}">
            <i class="bi bi-bag-check"></i> Pesanan
            @if(!$isVActive)
                <i class="bi bi-lock-fill" style="margin-left:auto;font-size:12px;opacity:0.6;"></i>
            @else
                @php
                    $pendingCount = \App\Models\OrderItem::where('vendor_profile_id', $vp->id ?? 0)
                        ->where('status', 'pending_confirmation')
                        ->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="badge badge-warning" style="margin-left:auto;">{{ $pendingCount }}</span>
                @endif
            @endif
        </a>

        <a href="{{ $isVActive ? route('vendor.calendar') : route('vendor.verification') }}" class="sidebar-link {{ ($active ?? '') === 'calendar' ? 'active' : '' }} {{ !$isVActive ? 'text-muted' : '' }}">
            <i class="bi bi-calendar3"></i> Kalender Booking
            @if(!$isVActive)
                <i class="bi bi-lock-fill" style="margin-left:auto;font-size:12px;opacity:0.6;"></i>
            @endif
        </a>

        <a href="{{ $isVActive ? route('vendor.reviews') : route('vendor.verification') }}" class="sidebar-link {{ ($active ?? '') === 'reviews' ? 'active' : '' }} {{ !$isVActive ? 'text-muted' : '' }}">
            <i class="bi bi-star"></i> Ulasan
            @if(!$isVActive)
                <i class="bi bi-lock-fill" style="margin-left:auto;font-size:12px;opacity:0.6;"></i>
            @endif
        </a>

        <a href="{{ $isVActive ? route('vendor.revenue') : route('vendor.verification') }}" class="sidebar-link {{ ($active ?? '') === 'revenue' ? 'active' : '' }} {{ !$isVActive ? 'text-muted' : '' }}">
            <i class="bi bi-wallet2"></i> Pendapatan
            @if(!$isVActive)
                <i class="bi bi-lock-fill" style="margin-left:auto;font-size:12px;opacity:0.6;"></i>
            @endif
        </a>

        <a href="{{ $isVActive ? route('vendor.wallet') : route('vendor.verification') }}" class="sidebar-link {{ ($active ?? '') === 'wallet' ? 'active' : '' }} {{ !$isVActive ? 'text-muted' : '' }}">
            <i class="bi bi-cash-stack"></i> Saldo &amp; Escrow
            @if(!$isVActive)
                <i class="bi bi-lock-fill" style="margin-left:auto;font-size:12px;opacity:0.6;"></i>
            @endif
        </a>

        <a href="{{ $isVActive ? route('vendor.advertisements.index') : route('vendor.verification') }}" class="sidebar-link {{ ($active ?? '') === 'advertisements' ? 'active' : '' }} {{ !$isVActive ? 'text-muted' : '' }}">
            <i class="bi bi-megaphone"></i> Iklan &amp; Promosi
            @if(!$isVActive)
                <i class="bi bi-lock-fill" style="margin-left:auto;font-size:12px;opacity:0.6;"></i>
            @endif
        </a>

        <a href="{{ route('chat.index') }}" class="sidebar-link {{ ($active ?? '') === 'chat' ? 'active' : '' }}">
            <i class="bi bi-chat-dots"></i> Pesan &amp; Chat
            @if($unreadVendorChats > 0)
                <span class="badge badge-danger" style="margin-left:auto;font-size:10px;font-weight:700;padding:2px 7px;background:#D81A24;color:#ffffff;border-radius:10px;">{{ $unreadVendorChats }}</span>
            @endif
        </a>

        <div class="sidebar-divider">
            <div class="sidebar-label">Dukungan</div>
            <form action="{{ route('chat.support') }}" method="POST">
                @csrf
                <button type="submit" class="sidebar-link sidebar-btn-support">
                    <i class="bi bi-headset"></i> <span>Chat Tim PestaGo</span>
                    <span class="badge badge-success sidebar-support-badge">Online</span>
                </button>
            </form>
        </div>
    </div>
</aside>
