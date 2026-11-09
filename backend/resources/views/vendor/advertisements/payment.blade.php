@extends('layouts.app')
@section('title', 'Pembayaran Slot Iklan — PestaGo Vendor')

@section('content')
<div class="uq-pay-page">
    <div class="uq-pay-container">
        {{-- Flow Stepper --}}
        <div class="uq-flow-stepper">
            <div class="uq-flow-step completed">
                <span class="uq-flow-step-num"><i class="bi bi-check"></i></span>
                <span>Detail Iklan</span>
            </div>
            <div class="uq-flow-line completed"></div>
            <div class="uq-flow-step active">
                <span class="uq-flow-step-num">2</span>
                <span>Pembayaran QRIS</span>
            </div>
            <div class="uq-flow-line"></div>
            <div class="uq-flow-step">
                <span class="uq-flow-step-num">3</span>
                <span>Kurasi Admin</span>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
        <div class="uq-alert uq-alert-success">{{ session('success') }}</div>
        @endif
        @if(session('info'))
        <div class="uq-alert" style="border-color:#e5e5e5;background:#fafafa;">{{ session('info') }}</div>
        @endif
        @if(session('error'))
        <div class="uq-alert uq-alert-danger">{{ session('error') }}</div>
        @endif

        <div class="uq-pay-layout">
            {{-- Left: Payment Methods & QRIS Instructions --}}
            <div class="uq-pay-main">
                {{-- Payment Method Selection - Exclusively QRIS (Non-Tunai) --}}
                <div id="paymentMethodSection">
                    <div class="uq-section-label">Metode Pembayaran Non-Tunai</div>

                    <div class="uq-pay-group" style="border:none;padding:0;background:transparent;">
                        <label class="uq-pay-method selected" style="border:2px solid #0f172a;background:#ffffff;border-radius:14px;padding:16px 20px;display:flex;align-items:center;gap:16px;box-shadow:0 2px 10px rgba(0,0,0,0.04);cursor:default;">
                            <input type="radio" name="payment_method" value="qris" checked style="accent-color:#111111;width:20px;height:20px;cursor:pointer;">
                            <div style="width:48px;height:48px;background:#fef2f2;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#d81a24;font-size:24px;flex-shrink:0;">
                                <i class="bi bi-qr-code-scan"></i>
                            </div>
                            <div class="uq-pay-method-info" style="flex:1;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="uq-pay-method-name" style="font-weight:800;font-size:15px;color:#0f172a;">QRIS (Quick Response Code Indonesian Standard)</span>
                                    <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:800;padding:2px 8px;border-radius:10px;">Instan</span>
                                </div>
                                <span class="uq-pay-method-desc" style="color:#64748b;font-size:12.5px;margin-top:3px;line-height:1.4;">
                                    Bisa dibayar menggunakan GoPay, OVO, DANA, ShopeePay, LinkAja, BCA, Mandiri, BRI &amp; Semua Mobile Banking
                                </span>
                            </div>
                        </label>
                    </div>

                    <button type="button" id="payNowBtn" class="uq-btn-submit" style="margin-top:18px;width:100%;height:48px;font-size:14px;letter-spacing:0.3px;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <i class="bi bi-qr-code"></i> Tampilkan QRIS Pembayaran
                    </button>
                </div>

                {{-- Payment Instructions (QR Code Display) --}}
                <div id="paymentInstructionSection" style="display:none;">
                    <div class="uq-section-label">Instruksi Pembayaran QRIS</div>

                    <div class="uq-pay-instruction-card">
                        <div id="instructionContent"></div>
                    </div>

                    <div id="pollingStatus" style="text-align:center;padding:12px 0;font-size:12px;color:#999999;">
                        <span class="uq-spinner" style="border-color:#cccccc;border-top-color:transparent;"></span>
                        Menunggu verifikasi pembayaran...
                    </div>

                    <div class="uq-pay-actions" style="margin-top:16px;display:flex;flex-direction:column;gap:10px;">
                        <a href="{{ route('vendor.advertisements.syncPayment', $advertisement) }}" class="uq-btn-submit" style="width:100%;height:46px;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
                            <i class="bi bi-arrow-clockwise"></i> Cek Status Pembayaran
                        </a>

                        {{-- Tombol Simulasi Pembayaran Berhasil untuk Testing / Dev --}}
                        <form action="{{ route('vendor.advertisements.simulatePaid', $advertisement) }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" style="width:100%;height:38px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;font-size:12.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                                <i class="bi bi-check-circle-fill"></i> Simulasikan Pembayaran Berhasil (Dev / Sandbox)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Right: Order / Ad Summary --}}
            <div class="uq-pay-sidebar">
                <div class="uq-pay-summary">
                    <div class="uq-section-label">Ringkasan Pemasangan Iklan</div>

                    <div class="uq-pay-summary-items">
                        <div class="uq-pay-summary-item">
                            <div>
                                <div class="uq-pay-item-name">{{ $advertisement->title }}</div>
                                <div class="uq-pay-item-vendor">{{ $plan->name ?? 'Papan Utama (Hero Carousel Beranda)' }}</div>
                            </div>
                            <div class="uq-pay-item-price">Rp {{ number_format($advertisement->payment_amount, 0, ',', '.') }}</div>
                        </div>
                    </div>

                    <div class="uq-pay-summary-totals">
                        <div class="uq-pay-summary-row">
                            <span>Posisi Papan</span>
                            <span style="font-weight:700;color:#111111;">{{ $plan->name ?? ($advertisement->placement === 'vendor_billboard' ? 'Papan Iklan Vendor (Vendor Pilihan)' : 'Papan Utama') }}</span>
                        </div>
                        <div class="uq-pay-summary-row">
                            <span>Paket Durasi</span>
                            <span style="font-weight:700;color:#111111;">
                                @if($advertisement->package === 'custom')
                                    Paket Kustom ({{ $advertisement->starts_at?->diffInDays($advertisement->ends_at) }} Hari)
                                @else
                                    Paket {{ ucfirst($advertisement->package) }} ({{ $advertisement->starts_at?->diffInDays($advertisement->ends_at) }} Hari)
                                @endif
                            </span>
                        </div>
                        <div class="uq-pay-summary-row">
                            <span>Jadwal Tayang</span>
                            <span style="font-size:11px;color:#555555;">{{ $advertisement->starts_at?->translatedFormat('d M Y') }} s/d {{ $advertisement->ends_at?->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>

                    <div class="uq-pay-summary-total">
                        <span>Total Tagihan</span>
                        <span>Rp {{ number_format($advertisement->payment_amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="uq-pay-expiry">
                        <i class="bi bi-shield-check" style="color:#16a34a;"></i>
                        Transaksi Non-Tunai Aman via Midtrans QRIS
                    </div>
                </div>

                {{-- Order / Ad Number --}}
                <div style="padding:14px 16px;border:1px solid #e5e5e5;border-top:none;background:#ffffff;font-size:12px;color:#888888;display:flex;justify-content:space-between;align-items:center;">
                    <span>Kode Pengajuan:</span>
                    <strong style="color:#111111;">#AD-{{ $advertisement->id }}</strong>
                </div>
            </div>
        </div>

        {{-- Help --}}
        <div class="uq-pay-help">
            Mengalami kendala pembayaran?
            <form action="{{ route('chat.support') }}" method="POST" style="display:inline;margin:0;">
                @csrf
                <button type="submit" class="uq-pay-help-link">Hubungi Customer Care PestaGo</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodSection = document.getElementById('paymentMethodSection');
    const instructionSection = document.getElementById('paymentInstructionSection');
    const payNowBtn = document.getElementById('payNowBtn');
    const instructionContent = document.getElementById('instructionContent');
    const pollingStatus = document.getElementById('pollingStatus');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const adId = {{ $advertisement->id }};
    const totalFormatted = 'Rp {{ number_format($advertisement->payment_amount, 0, ",", ".") }}';
    let pollInterval = null;

    // Check for existing charge data passed from server
    @if(isset($chargeData) && !empty($chargeData['success']))
    (function() {
        var data = @json($chargeData);
        if (data && data.success) {
            showInstructions(data);
            startPolling();
        }
    })();
    @else
    // Auto-request QRIS charge if not created yet
    setTimeout(function() {
        if (payNowBtn) payNowBtn.click();
    }, 200);
    @endif

    // Pay Now / Show QRIS click handler
    if (payNowBtn) {
        payNowBtn.addEventListener('click', async function() {
            payNowBtn.disabled = true;
            payNowBtn.innerHTML = '<span class="uq-spinner"></span> Menyiapkan QRIS...';

            try {
                var response = await fetch('/vendor/advertisements/' + adId + '/charge', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ payment_method: 'qris' }),
                });

                var data = await response.json();

                if (data.success) {
                    showInstructions(data);
                    startPolling();
                } else {
                    alert(data.message || 'Gagal memproses QRIS. Silakan coba lagi.');
                    payNowBtn.disabled = false;
                    payNowBtn.innerHTML = '<i class="bi bi-qr-code"></i> Tampilkan QRIS Pembayaran';
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
                payNowBtn.disabled = false;
                payNowBtn.innerHTML = '<i class="bi bi-qr-code"></i> Tampilkan QRIS Pembayaran';
            }
        });
    }

    function showInstructions(data) {
        if (methodSection) methodSection.style.display = 'none';
        if (instructionSection) instructionSection.style.display = '';

        var qrUrl = data.qr_url || 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' + encodeURIComponent('PESTAGO-AD-' + adId);

        var html = '<div class="uq-instruction-qris" style="text-align:center;">' +
            '<div class="uq-instruction-label" style="font-size:14px;letter-spacing:0.5px;margin-bottom:12px;font-weight:800;color:#0f172a;">QRIS — SCAN UNTUK BAYAR</div>' +
            '<div class="uq-qr-wrap" style="position:relative;display:inline-block;padding:16px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,0.06);margin:8px auto 16px;">' +
                '<img src="' + qrUrl + '" alt="QRIS Code" class="uq-qr-img" style="display:block;width:240px;height:240px;border-radius:8px;">' +
                '<div class="uq-qr-center-logo" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:52px;height:52px;background:#ffffff;border-radius:12px;padding:4px;box-shadow:0 3px 12px rgba(0,0,0,0.25);display:flex;align-items:center;justify-content:center;z-index:3;border:2px solid #ffffff;">' +
                    '<img src="{{ asset('images/logo-pg.png') }}" alt="PestaGo Logo" style="width:100%;height:100%;object-fit:contain;border-radius:8px;">' +
                '</div>' +
            '</div>' +
            '<div class="uq-instruction-amount" style="font-size:18px;margin-bottom:6px;">Total Tagihan: <strong>' + totalFormatted + '</strong></div>' +
            '<p class="uq-instruction-note" style="font-size:12.5px;color:#64748b;margin-bottom:16px;line-height:1.5;">Buka aplikasi e-wallet atau mobile banking Anda,<br>pilih menu <strong>Scan / Bayar QRIS</strong> lalu arahkan kamera ke barcode di atas.</p>' +
            '<div style="font-size:11.5px;color:#15803d;background:#f0fdf4;padding:6px 12px;border-radius:20px;display:inline-flex;align-items:center;gap:6px;font-weight:600;"><i class="bi bi-shield-check"></i> Mendukung GoPay, OVO, DANA, ShopeePay, BCA, Mandiri, BRI &amp; Semua Bank</div>' +
        '</div>';

        instructionContent.innerHTML = html;
    }

    function startPolling() {
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(async function() {
            try {
                var res = await fetch('/vendor/advertisements/' + adId + '/check-status', {
                    headers: { 'Accept': 'application/json' }
                });
                var data = await res.json();
                if (data.is_paid) {
                    clearInterval(pollInterval);
                    if (pollingStatus) {
                        pollingStatus.innerHTML = '<span style="color:#15803d;font-weight:700;"><i class="bi bi-check-circle-fill"></i> Pembayaran Berhasil! Mengalihkan...</span>';
                    }
                    setTimeout(function() {
                        window.location.href = "{{ route('vendor.advertisements.index') }}";
                    }, 1200);
                }
            } catch (e) {
                console.error('Polling check status error:', e);
            }
        }, 3000);
    }
});
</script>
@endpush
