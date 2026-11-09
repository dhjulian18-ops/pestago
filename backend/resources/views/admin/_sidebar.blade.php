{{-- Admin Sidebar Partial --}}
@php
    $u = auth()->user();
    $sidebarPendingVendors = \App\Models\VendorProfile::where('status', 'pending')->count();
    $sidebarPendingRefunds = \App\Models\Refund::where('status', 'pending')->count();
    $sidebarPendingAds = \App\Models\Advertisement::where('review_status', 'pending_review')->count();
    $sidebarPendingWithdrawals = \App\Models\WalletTransaction::where('type', 'withdrawn')->where('status', 'pending')->count();
    $sidebarPendingOrders = \App\Models\Order::whereIn('status', ['pending_payment', 'payment_processing', 'paid'])->count();
    $sidebarPendingOrders = \App\Models\Order::whereIn('status', ['pending_payment', 'payment_processing'])->count();
    $sidebarUnreadChats = \App\Models\Conversation::where('type', 'support')
        ->whereHas('messages', function ($q) use ($u) {
            $q->where('sender_id', '!=', $u->id)->where('is_read', false);
        })->count();
@endphp
<style>
    /* Uniqlo Clean Flat Red Badge (No Glow, No Shadow) */
    .uq-badge-red {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #D81A24 !important;
        color: #ffffff !important;
        min-width: 20px !important;
        height: 20px !important;
        border-radius: 10px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        line-height: 1 !important;
        padding: 0 6px !important;
        margin-left: auto !important;
        box-shadow: 0 2px 4px rgba(216, 26, 36, 0.35) !important;
        letter-spacing: -0.02em !important;
        transition: all 0.2s ease !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #D81A24;
        color: #ffffff;
        min-width: 18px;
        height: 18px;
        border-radius: 9px;
        font-size: 10.5px;
        font-weight: 800;
        line-height: 1;
        padding: 0 5px;
        margin-left: auto;
        box-shadow: none !important;
        text-shadow: none !important;
        letter-spacing: -0.02em;
    }

    .uq-badge-red[style*="display: none"],
    .uq-badge-red[style*="display:none"],
    .uq-badge-red:empty {
        display: none !important;
    }
</style>
<aside class="sidebar">
    <div class="sidebar-menu">
        {{-- Admin Profile Mini Card --}}
        <div
            style="padding:14px 18px 12px;border-bottom:1px solid #f0f0f0;margin-bottom:8px;display:flex;align-items:center;gap:12px;">
            @if($u->avatar_url)
                <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}"
                    style="width:36px;height:36px;border-radius:2px;object-fit:cover;border:1px solid #e5e5e5;flex-shrink:0;">
            @else
                <div
                    style="width:36px;height:36px;background:#D81A24;color:#ffffff;border-radius:2px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0;">
                    {{ strtoupper(substr($u->name, 0, 1)) }}
                </div>
            @endif
            <div style="overflow:hidden;line-height:1.25;flex:1;">
                <div
                    style="font-weight:700;font-size:13px;color:#111111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $u->name }}
                </div>
                <div
                    style="font-size:10px;font-weight:700;color:#D81A24;text-transform:uppercase;letter-spacing:0.06em;margin-top:2px;">
                    {{ $u->admin_role_title ?? ($u->role === 'super_admin' ? 'SUPER ADMIN' : 'STAFF ADMIN') }}
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" title="Pengaturan Profil"
                style="color:#888888;font-size:14px;text-decoration:none;padding:4px;">
                <i class="bi bi-gear"></i>
            </a>
        </div>

        <div class="sidebar-label">Admin Panel</div>

        <a href="{{ route('admin.dashboard') }}"
            class="sidebar-link {{ ($active ?? '') == 'dashboard' ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        @if($u->hasPermission('chats'))
            <a href="{{ route('admin.chats.index') }}"
                class="sidebar-link {{ ($active ?? '') == 'chats' ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i> Live Chat &amp; CS
                <span class="uq-badge-red" id="sidebarBadge-chats"
                    style="{{ $sidebarUnreadChats > 0 ? '' : 'display:none;' }}">{{ $sidebarUnreadChats }}</span>
                <span class="uq-badge-red" id="sidebarBadge-chats"
                    style="{{ $sidebarUnreadChats > 0 ? '' : 'display:none;' }}">{{ $sidebarUnreadChats > 0 ? $sidebarUnreadChats : '' }}</span>
            </a>
        @endif

        @if($u->hasPermission('vendors'))
            <a href="{{ route('admin.vendors') }}" class="sidebar-link {{ ($active ?? '') == 'vendors' ? 'active' : '' }}">
                <i class="bi bi-shop"></i> Vendor
                <span class="uq-badge-red" id="sidebarBadge-vendors"
                    style="{{ $sidebarPendingVendors > 0 ? '' : 'display:none;' }}">{{ $sidebarPendingVendors }}</span>
                <span class="uq-badge-red" id="sidebarBadge-vendors"
                    style="{{ $sidebarPendingVendors > 0 ? '' : 'display:none;' }}">{{ $sidebarPendingVendors > 0 ? $sidebarPendingVendors : '' }}</span>
            </a>
        @endif

        @if($u->hasPermission('users'))
            <a href="{{ route('admin.customers.index') }}"
                class="sidebar-link {{ in_array(($active ?? ''), ['users', 'customers']) ? 'active' : '' }}">
                <i class="bi bi-people"></i> Customer
            </a>
        @endif

        @if($u->hasPermission('categories'))
            <a href="{{ route('admin.categories') }}"
                class="sidebar-link {{ ($active ?? '') == 'categories' ? 'active' : '' }}">
                <i class="bi bi-grid-3x3-gap"></i> Kategori
            </a>
        @endif

        @if($u->hasPermission('orders'))
            <a href="{{ route('admin.orders') }}" class="sidebar-link {{ ($active ?? '') == 'orders' ? 'active' : '' }}">
                <i class="bi bi-bag"></i> Pesanan
                <span class="uq-badge-red" id="sidebarBadge-orders"
                    style="{{ $sidebarPendingOrders > 0 ? '' : 'display:none;' }}">{{ $sidebarPendingOrders }}</span>
                <span class="uq-badge-red" id="sidebarBadge-orders"
                    style="{{ $sidebarPendingOrders > 0 ? '' : 'display:none;' }}">{{ $sidebarPendingOrders > 0 ? $sidebarPendingOrders : '' }}</span>
            </a>
        @endif

        @if($u->hasPermission('payments'))
            <a href="{{ route('admin.payments') }}"
                class="sidebar-link {{ ($active ?? '') == 'payments' ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Pembayaran
            </a>
        @endif

        @if($u->hasPermission('refunds'))
            <a href="{{ route('admin.refunds') }}" class="sidebar-link {{ ($active ?? '') == 'refunds' ? 'active' : '' }}">
                <i class="bi bi-arrow-return-left"></i> Refund
                <span class="uq-badge-red" id="sidebarBadge-refunds"
                    style="{{ $sidebarPendingRefunds > 0 ? '' : 'display:none;' }}">{{ $sidebarPendingRefunds }}</span>
                <span class="uq-badge-red" id="sidebarBadge-refunds"
                    style="{{ $sidebarPendingRefunds > 0 ? '' : 'display:none;' }}">{{ $sidebarPendingRefunds > 0 ? $sidebarPendingRefunds : '' }}</span>
            </a>
        @endif

        @if($u->hasPermission('promos'))
            <a href="{{ route('admin.promos') }}" class="sidebar-link {{ ($active ?? '') == 'promos' ? 'active' : '' }}">
                <i class="bi bi-tag"></i> Promo
            </a>
        @endif

        @if($u->hasPermission('advertisements'))
            <a href="{{ route('admin.advertisements.index') }}"
                class="sidebar-link {{ ($active ?? '') == 'advertisements' ? 'active' : '' }}">
                <i class="bi bi-megaphone"></i> Iklan Vendor
                <span class="uq-badge-red" id="sidebarBadge-ads"
                    style="{{ $sidebarPendingAds > 0 ? '' : 'display:none;' }}">{{ $sidebarPendingAds }}</span>
                <span class="uq-badge-red" id="sidebarBadge-ads"
                    style="{{ $sidebarPendingAds > 0 ? '' : 'display:none;' }}">{{ $sidebarPendingAds > 0 ? $sidebarPendingAds : '' }}</span>
            </a>
            <a href="{{ route('admin.ad-plans.index') }}"
                class="sidebar-link {{ ($active ?? '') == 'ad_plans' ? 'active' : '' }}"
                style="padding-left:36px;font-size:12.5px;">
                <i class="bi bi-sliders"></i> Tarif &amp; Opsi Iklan
            </a>
        @endif

        @if($u->hasPermission('maps'))
            <a href="{{ route('admin.maps.index') }}" class="sidebar-link {{ ($active ?? '') == 'maps' ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> Peta Sebaran
            </a>
        @endif

        @if($u->hasPermission('reports'))
            <a href="{{ route('admin.reports') }}" class="sidebar-link {{ ($active ?? '') == 'reports' ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Laporan
            </a>
        @endif

        @if($u->hasPermission('payments'))
            <a href="{{ route('admin.wallets.index') }}"
                class="sidebar-link {{ ($active ?? '') == 'wallets' ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Saldo & Escrow Vendor
                <span class="uq-badge-red" id="sidebarBadge-wallets"
                    style="{{ $sidebarPendingWithdrawals > 0 ? '' : 'display:none;' }}">{{ $sidebarPendingWithdrawals }}</span>
                <span class="uq-badge-red" id="sidebarBadge-wallets"
                    style="{{ $sidebarPendingWithdrawals > 0 ? '' : 'display:none;' }}">{{ $sidebarPendingWithdrawals > 0 ? $sidebarPendingWithdrawals : '' }}</span>
            </a>
        @endif

        @if($u->isSuperAdmin())
            <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--gray-200);">
                <div class="sidebar-label" style="padding-top:0;">Super Admin</div>
                <a href="{{ route('admin.staff.index') }}"
                    class="sidebar-link {{ ($active ?? '') == 'staff' ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i> Kelola Staf Admin
                </a>
                <a href="{{ route('admin.settings.platform') }}"
                    class="sidebar-link {{ ($active ?? '') == 'settings' ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i> Pengaturan &amp; Integrasi
                </a>
            </div>
        @endif

        <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--gray-200);">
            <div class="sidebar-label" style="padding-top:0;">Website</div>
            <a href="{{ route('home') }}" target="_blank" class="sidebar-link"
                title="Buka website utama PestaGo di tab baru" style="color: var(--primary); font-weight: 600;">
                <i class="bi bi-globe"></i> Kunjungi Web
                <i class="bi bi-box-arrow-up-right" style="margin-left: auto; font-size: 11px;"></i>
            </a>
        </div>
    </div>
</aside>

@if($u->isSuperAdmin())
    <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--gray-200);">
        <div class="sidebar-label" style="padding-top:0;">Super Admin</div>
        <a href="{{ route('admin.staff.index') }}" class="sidebar-link {{ ($active ?? '') == 'staff' ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Kelola Staf Admin
        </a>
        <a href="{{ route('admin.settings.platform') }}"
            class="sidebar-link {{ ($active ?? '') == 'settings' ? 'active' : '' }}">
            <i class="bi bi-sliders"></i> Pengaturan &amp; Integrasi
        </a>
    </div>
@endif

<div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--gray-200);">
    <div class="sidebar-label" style="padding-top:0;">Website</div>
    <a href="{{ route('home') }}" target="_blank" class="sidebar-link" title="Buka website utama PestaGo di tab baru"
        style="color: var(--primary); font-weight: 600;">
        <i class="bi bi-globe"></i> Kunjungi Web
        <i class="bi bi-box-arrow-up-right" style="margin-left: auto; font-size: 11px;"></i>
    </a>
</div>
</div>
</aside>