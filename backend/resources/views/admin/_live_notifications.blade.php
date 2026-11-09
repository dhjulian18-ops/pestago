{{-- Live Notification Toasts & Audio Chime for Admin Panel --}}
<div id="adminNotificationToastContainer" class="admin-toast-container" aria-live="polite" aria-atomic="true"></div>

<style>
.admin-toast-container {
    position: fixed;
    top: 74px;
    right: 20px;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 380px;
    width: calc(100vw - 36px);
    pointer-events: none;
}

.admin-toast {
    background: #ffffff;
    border: 1px solid #111111;
    border-left: 5px solid #D81A24;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    pointer-events: auto;
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    position: relative;
    transform: translateX(110%);
    opacity: 0;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;
}

.admin-toast.show {
    transform: translateX(0);
    opacity: 1;
}

.admin-toast.fade-out {
    transform: translateX(110%);
    opacity: 0;
}

.admin-toast-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.admin-toast-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #D81A24;
}

.admin-toast-badge-dot {
    width: 8px;
    height: 8px;
    background: #D81A24;
    border-radius: 50%;
    display: inline-block;
    animation: adminDotPulse 1.2s infinite ease-in-out;
}

@keyframes adminDotPulse {
    0% { transform: scale(0.9); opacity: 0.6; }
    50% { transform: scale(1.3); opacity: 1; }
    100% { transform: scale(0.9); opacity: 0.6; }
}

.admin-toast-close {
    background: none;
    border: none;
    font-size: 20px;
    line-height: 1;
    color: #888888;
    cursor: pointer;
    padding: 0;
    transition: color 0.2s;
}

.admin-toast-close:hover {
    color: #111111;
}

.admin-toast-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #111111;
    margin: 0;
    line-height: 1.35;
}

.admin-toast-message {
    font-size: 12px;
    color: #555555;
    line-height: 1.45;
    margin: 0;
    word-break: break-word;
}

.admin-toast-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 8px;
    border-top: 1px solid #f0f0f0;
    margin-top: 4px;
}

.admin-toast-time {
    font-size: 10.5px;
    color: #888888;
}

.admin-toast-btn {
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #D81A24;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: #fff1f2;
    border: 1px solid #fecdd3;
    transition: all 0.2s;
}

.admin-toast-btn:hover {
    background: #D81A24;
    color: #ffffff;
    border-color: #D81A24;
}

/* Sound Toggle Button in Topbar */
.admin-sound-toggle-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    border-radius: 4px;
    color: #111111;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s;
}
.admin-sound-toggle-btn:hover {
    background: #f9fafb;
    border-color: #111111;
}
</style>

<script>
(function() {
    var lastNotificationId = 0;
    var pollIntervalMs = 8000; // 8 detik untuk efisiensi koneksi DB

    // Setup Admin Notification Center Dropdown Toggle
    document.addEventListener('DOMContentLoaded', function() {
        var notifBtn = document.getElementById('adminNotifBtn');
        var notifMenu = document.getElementById('adminNotifMenu');
        if (notifBtn && notifMenu) {
            notifBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                var isOpen = notifMenu.style.display === 'block';
                notifMenu.style.display = isOpen ? 'none' : 'block';
            });

            document.addEventListener('click', function(e) {
                if (!notifMenu.contains(e.target) && e.target !== notifBtn && !notifBtn.contains(e.target)) {
                    notifMenu.style.display = 'none';
                }
            });
        }
    });

    // Render Admin Notification Center Dropdown
    function renderAdminNotifDropdown(counts, notifications) {
        var container = document.getElementById('adminNotifList');
        var totalBadge = document.getElementById('adminNotifTotalBadge');
        var topbarBadge = document.getElementById('topbarBadge-notifications');
        if (!container) return;

        var total = (counts.vendors || 0) + (counts.wallets || 0) + (counts.orders || 0) + (counts.ads || 0) + (counts.refunds || 0) + (counts.chats || 0);

        if (totalBadge) {
            totalBadge.textContent = total + ' pending';
        }

        if (topbarBadge) {
            if (total > 0) {
                topbarBadge.textContent = total;
                topbarBadge.style.display = 'inline-flex';
            } else {
                topbarBadge.textContent = '';
                topbarBadge.style.display = 'none';
            }
        }

        var itemsHtml = '';

        if (counts.vendors > 0) {
            itemsHtml += `
                <a href="{{ route('admin.vendors', ['status' => 'pending']) }}" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f0f0;text-decoration:none;color:#111;transition:background 0.15s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                    <div style="width:34px;height:34px;background:#fef2f2;color:#dc2626;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:12.5px;color:#111;">Verifikasi Vendor Baru</div>
                        <div style="font-size:11.5px;color:#666;">Ada ${counts.vendors} vendor menunggu ditinjau</div>
                    </div>
                    <span class="uq-badge-red">${counts.vendors}</span>
                </a>
            `;
        }

        if (counts.wallets > 0) {
            itemsHtml += `
                <a href="{{ route('admin.wallets.index', ['status' => 'pending']) }}" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f0f0;text-decoration:none;color:#111;transition:background 0.15s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                    <div style="width:34px;height:34px;background:#f0fdf4;color:#16a34a;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:12.5px;color:#111;">Pencairan Saldo Vendor</div>
                        <div style="font-size:11.5px;color:#666;">Ada ${counts.wallets} permohonan penarikan dana</div>
                    </div>
                    <span class="uq-badge-red">${counts.wallets}</span>
                </a>
            `;
        }

        if (counts.orders > 0) {
            itemsHtml += `
                <a href="{{ route('admin.orders') }}" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f0f0;text-decoration:none;color:#111;transition:background 0.15s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                    <div style="width:34px;height:34px;background:#eff6ff;color:#2563eb;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="bi bi-bag"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:12.5px;color:#111;">Pesanan Menunggu Aksi</div>
                        <div style="font-size:11.5px;color:#666;">Ada ${counts.orders} pesanan menunggu pembayaran</div>
                    </div>
                    <span class="uq-badge-red">${counts.orders}</span>
                </a>
            `;
        }

        if (counts.ads > 0) {
            itemsHtml += `
                <a href="{{ route('admin.advertisements.index') }}" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f0f0;text-decoration:none;color:#111;transition:background 0.15s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                    <div style="width:34px;height:34px;background:#fef3c7;color:#d97706;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:12.5px;color:#111;">Moderasi Iklan Banner</div>
                        <div style="font-size:11.5px;color:#666;">Ada ${counts.ads} materi iklan menunggu review</div>
                    </div>
                    <span class="uq-badge-red">${counts.ads}</span>
                </a>
            `;
        }

        if (counts.chats > 0) {
            itemsHtml += `
                <a href="{{ route('admin.chats.index') }}" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f0f0;text-decoration:none;color:#111;transition:background 0.15s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                    <div style="width:34px;height:34px;background:#f5f3ff;color:#7c3aed;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:12.5px;color:#111;">Live Chat &amp; CS Masuk</div>
                        <div style="font-size:11.5px;color:#666;">Ada ${counts.chats} pesan baru</div>
                    </div>
                    <span class="uq-badge-red">${counts.chats}</span>
                </a>
            `;
        }

        if (counts.refunds > 0) {
            itemsHtml += `
                <a href="{{ route('admin.refunds') }}" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #f0f0f0;text-decoration:none;color:#111;transition:background 0.15s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                    <div style="width:34px;height:34px;background:#fff1f2;color:#e11d48;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="bi bi-arrow-return-left"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:12.5px;color:#111;">Pengajuan Refund</div>
                        <div style="font-size:11.5px;color:#666;">Ada ${counts.refunds} refund pending</div>
                    </div>
                    <span class="uq-badge-red">${counts.refunds}</span>
                </a>
            `;
        }

        if (itemsHtml === '') {
            itemsHtml = `
                <div style="padding:24px 16px;text-align:center;color:#888888;font-size:12px;">
                    <i class="bi bi-check2-circle" style="font-size:24px;color:#16a34a;display:block;margin-bottom:6px;"></i>
                    Semua antrean beres! Tidak ada tugas yang tertunda.
                </div>
            `;
        }

        container.innerHTML = itemsHtml;
    }
    var soundEnabled = localStorage.getItem('pestago_admin_sound_enabled') !== 'false'; // default active

    // Setup Web Audio API Synthesizer (Pleasant High-Quality Glass Bell Chime)
    function playAdminNotificationChime() {
        if (!soundEnabled) return;
        try {
            var AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            var ctx = new AudioCtx();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            var now = ctx.currentTime;

            // Tone 1: E6 (1318.5 Hz)
            var osc1 = ctx.createOscillator();
            var gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(1318.5, now);
            gain1.gain.setValueAtTime(0.25, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.55);

            // Tone 2: G#6 (1661.2 Hz) chord harmoni
            var osc2 = ctx.createOscillator();
            var gain2 = ctx.createGain();
            osc2.type = 'triangle';
            osc2.frequency.setValueAtTime(1661.2, now + 0.08);
            gain2.gain.setValueAtTime(0.18, now + 0.08);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.7);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.08);
            osc2.stop(now + 0.7);
        } catch (e) {
            console.warn('Web Audio error:', e);
        }
    }

    // Toggle Sound Button
    window.toggleAdminNotificationSound = function() {
        soundEnabled = !soundEnabled;
        localStorage.setItem('pestago_admin_sound_enabled', soundEnabled ? 'true' : 'false');
        updateSoundButtonUI();
        if (soundEnabled) {
            playAdminNotificationChime();
        }
    };

    function updateSoundButtonUI() {
        var btn = document.getElementById('adminSoundToggleBtn');
        if (btn) {
            if (soundEnabled) {
                btn.innerHTML = '<i class="bi bi-bell-fill" style="color:#16a34a;"></i>';
                btn.title = 'Suara Notifikasi: Aktif (Klik untuk matikan)';
            } else {
                btn.innerHTML = '<i class="bi bi-bell-slash" style="color:#999999;"></i>';
                btn.title = 'Suara Notifikasi: Bisu (Klik untuk aktifkan)';
            }
        }
    }

    // Render Pop-up Toast di Samping
    function showNotificationToast(item) {
        var container = document.getElementById('adminNotificationToastContainer');
        if (!container) return;

        var toast = document.createElement('div');
        toast.className = 'admin-toast';
        toast.id = 'toast-notif-' + item.id;

        var categoryLabel = 'Notifikasi Admin';
        if (item.type === 'withdrawal_request') categoryLabel = '💸 Penarikan Saldo';
        if (item.type === 'support_chat') categoryLabel = '💬 Live Chat / CS';
        if (item.type === 'vendor_verification') categoryLabel = '🏪 Verifikasi Vendor';
        if (item.type === 'order_new' || item.type === 'new_paid_order' || item.type === 'payment_submitted') categoryLabel = '📦 Pesanan Baru';
        if (item.type === 'refund_requested') categoryLabel = '↩️ Pengajuan Refund';
        if (item.type === 'new_ad_paid' || item.type === 'ad_revision_submitted') categoryLabel = '📢 Iklan Vendor';

        toast.innerHTML = `
            <div class="admin-toast-header">
                <div class="admin-toast-badge">
                    <span class="admin-toast-badge-dot"></span>
                    <span>${categoryLabel}</span>
                </div>
                <button type="button" class="admin-toast-close" onclick="dismissToast(this.closest('.admin-toast'))">&times;</button>
            </div>
            <h4 class="admin-toast-title">${escapeHtml(item.title)}</h4>
            <p class="admin-toast-message">${escapeHtml(item.message)}</p>
            <div class="admin-toast-footer">
                <span class="admin-toast-time"><i class="bi bi-clock"></i> ${escapeHtml(item.time || 'Baru saja')}</span>
                <a href="${item.url}" class="admin-toast-btn" onclick="handleToastClick(event, ${item.id}, '${item.url}')">
                    Buka Halaman <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        `;

        container.appendChild(toast);

        // Slide in animation
        requestAnimationFrame(function() {
            toast.classList.add('show');
        });

        // Auto dismiss after 8 seconds
        setTimeout(function() {
            dismissToast(toast);
        }, 8000);
    }

    window.dismissToast = function(el) {
        if (!el) return;
        el.classList.remove('show');
        el.classList.add('fade-out');
        setTimeout(function() {
            if (el.parentNode) {
                el.parentNode.removeChild(el);
            }
        }, 400);
    };

    window.handleToastClick = function(e, notifId, url) {
        e.preventDefault();
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch("{{ url('/admin/api/notifications') }}/" + notifId + "/read", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).finally(function() {
            window.location.href = url;
        });
    };

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.innerText = str;
        return div.innerHTML;
    }

    // Update Angka di Bulat Merah Sidebar (Hanya tampil jika ada angka > 0)
    function updateSidebarBadge(id, count) {
        var el = document.getElementById(id);
        if (!el) return;
        var num = parseInt(count, 10) || 0;
        if (num > 0) {
            el.textContent = num;
            el.style.display = 'inline-flex';
        } else {
            el.textContent = '';
            el.style.display = 'none';
        }
    }

    // Polling Function
    function checkLiveNotifications() {
        var url = "{{ route('admin.api.liveNotifications') }}?last_id=" + lastNotificationId;
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data.success) return;

            // Update badge bulat merah di sidebar & dropdown antrean
            if (data.counts) {
                updateSidebarBadge('sidebarBadge-chats', data.counts.chats);
                updateSidebarBadge('sidebarBadge-wallets', data.counts.wallets);
                updateSidebarBadge('sidebarBadge-vendors', data.counts.vendors);
                updateSidebarBadge('sidebarBadge-orders', data.counts.orders);
                updateSidebarBadge('sidebarBadge-refunds', data.counts.refunds);
                updateSidebarBadge('sidebarBadge-ads', data.counts.ads);

                renderAdminNotifDropdown(data.counts, data.notifications);

                // Update topbar chat badge jika ada
                var topbarChatBadge = document.getElementById('topbarBadge-chats');
                if (topbarChatBadge) {
                    var chatCount = parseInt(data.counts.chats, 10) || 0;
                    if (chatCount > 0) {
                        topbarChatBadge.textContent = chatCount;
                        topbarChatBadge.style.display = '';
                    } else {
                        topbarChatBadge.textContent = '';
                        topbarChatBadge.style.display = 'none';
                    }
                }
            }

            // Jika ada notifikasi baru masuk (dan bukan load pertama saat lastNotificationId == 0)
            if (data.notifications && data.notifications.length > 0) {
                playAdminNotificationChime();
                data.notifications.forEach(function(item) {
                    showNotificationToast(item);
                });
            }

            // Simpan latest_id
            if (data.latest_id) {
                lastNotificationId = data.latest_id;
            }
        })
        .catch(function(err) {
            console.debug('Polling notification error:', err);
        })
        .finally(function() {
            setTimeout(checkLiveNotifications, pollIntervalMs);
        });
    }

    // Inisialisasi saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            updateSoundButtonUI();
            checkLiveNotifications();
        });
    } else {
        updateSoundButtonUI();
        checkLiveNotifications();
    }
})();
</script>

