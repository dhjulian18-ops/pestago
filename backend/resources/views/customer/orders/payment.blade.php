@extends('layouts.app')
@section('title', 'Pembayaran Order #' . $order->order_number . ' — PestaGo')

@section('content')
<div class="uq-pay-page">
    <div class="uq-pay-container">
        {{-- Flow Stepper --}}
        <div class="uq-flow-stepper">
            <div class="uq-flow-step completed">
                <span class="uq-flow-step-num"><i class="bi bi-check"></i></span>
                <span>Pesanan</span>
            </div>
            <div class="uq-flow-line completed"></div>
            <div class="uq-flow-step active">
                <span class="uq-flow-step-num">2</span>
                <span>Pembayaran</span>
            </div>
            <div class="uq-flow-line"></div>
            <div class="uq-flow-step">
                <span class="uq-flow-step-num">3</span>
                <span>Selesai</span>
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
            {{-- Left: Payment Methods & Instructions --}}
            <div class="uq-pay-main">
                {{-- Payment Method Selection - Exclusively QRIS --}}
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
                                    Bisa dibayar menggunakan GoPay, OVO, DANA, ShopeePay, LinkAja, BCA, Mandiri, BRI & Semua Mobile Banking
                                </span>
                            </div>
                        </label>
                    </div>

                    <button type="button" id="payNowBtn" class="uq-btn-submit" style="margin-top:18px;width:100%;height:48px;font-size:14px;letter-spacing:0.3px;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <i class="bi bi-qr-code"></i> Tampilkan QRIS Pembayaran
                    </button>
                </div>

                {{-- Payment Instructions (shown after charge created) --}}
                <div id="paymentInstructionSection" style="display:none;">
                    <div class="uq-section-label">Instruksi Pembayaran</div>

                    <div class="uq-pay-instruction-card">
                        <div id="instructionContent"></div>
                    </div>

                    <div id="pollingStatus" style="text-align:center;padding:10px 0;font-size:12px;color:#999999;">
                        <span class="uq-spinner" style="border-color:#cccccc;border-top-color:transparent;"></span>
                        Menunggu pembayaran...
                    </div>

                    <div class="uq-pay-actions" style="margin-top:16px;">
                        <a href="{{ route('customer.orders.syncPayment', $order) }}" class="uq-btn-submit" style="width:100%;height:46px;display:flex;align-items:center;justify-content:center;gap:8px;">
                            <i class="bi bi-arrow-clockwise"></i> Cek Status Pembayaran
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right: Order Summary --}}
            <div class="uq-pay-sidebar">
                <div class="uq-pay-summary">
                    <div class="uq-section-label">Ringkasan Pesanan</div>

                    <div class="uq-pay-summary-items">
                        @foreach($order->items as $item)
                        <div class="uq-pay-summary-item">
                            <div>
                                <div class="uq-pay-item-name">{{ $item->service_name_snapshot }}</div>
                                <div class="uq-pay-item-vendor">{{ $item->vendorProfile->business_name ?? '-' }}</div>
                            </div>
                            <div class="uq-pay-item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                        </div>
                        @endforeach
                    </div>

                    <div class="uq-pay-summary-totals">
                        <div class="uq-pay-summary-row">
                            <span>Subtotal ({{ $order->items->count() }} item)</span>
                            <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="uq-pay-summary-row">
                            <span>Biaya Platform (5%)</span>
                            <span>Rp {{ number_format($order->platform_fee, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="uq-pay-summary-total">
                        <span>Total</span>
                        <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>

                    @if($payment && $payment->expired_at)
                    <div class="uq-pay-expiry">
                        <i class="bi bi-clock"></i>
                        Batas: {{ $payment->expired_at->translatedFormat('d M Y, H:i') }} WIB
                    </div>
                    @endif
                </div>

                {{-- Order Number --}}
                <div style="padding:14px 16px;border:1px solid #e5e5e5;border-top:none;background:#ffffff;font-size:12px;color:#888888;">
                    Order <strong style="color:#111111;">#{{ $order->order_number }}</strong>
                </div>
            </div>
        </div>

        {{-- Help --}}
        <div class="uq-pay-help">
            Mengalami kendala?
            <form action="{{ route('chat.support') }}" method="POST" style="display:inline;margin:0;">
                @csrf
                <button type="submit" class="uq-pay-help-link">Hubungi Customer Care</button>
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
    const changeMethodBtn = document.getElementById('changeMethodBtn');
    const instructionContent = document.getElementById('instructionContent');
    const pollingStatus = document.getElementById('pollingStatus');
    const radios = document.querySelectorAll('input[name="payment_method"]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const orderId = {{ $order->id }};
    const totalFormatted = 'Rp {{ number_format($order->total_amount, 0, ",", ".") }}';
    let pollInterval = null;

    // Enable pay button on method select
    radios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            payNowBtn.disabled = false;
            document.querySelectorAll('.uq-pay-method').forEach(function(m) { m.classList.remove('selected'); });
            radio.closest('.uq-pay-method').classList.add('selected');
        });
    });

    // Check for existing charge data passed from server
    @if($existingCharge)
    (function() {
        var data = @json($existingCharge);
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

    // Pay Now / Show QRIS
    payNowBtn.addEventListener('click', async function() {
        var selected = document.querySelector('input[name="payment_method"]:checked') || { value: 'qris' };

        payNowBtn.disabled = true;
        payNowBtn.innerHTML = '<span class="uq-spinner"></span> Menyiapkan QRIS...';

        try {
            var response = await fetch('/customer/orders/' + orderId + '/charge', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ payment_method: selected.value || 'qris' }),
            });

            var data = await response.json();

            if (data.success) {
                showInstructions(data);
                startPolling();
            } else {
                alert(data.message || 'Gagal membuat transaksi QRIS. Silakan coba lagi.');
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

    if (changeMethodBtn) {
        changeMethodBtn.addEventListener('click', function() {
            methodSection.style.display = '';
            instructionSection.style.display = 'none';
            payNowBtn.disabled = false;
            payNowBtn.innerHTML = '<i class="bi bi-qr-code"></i> Tampilkan QRIS Pembayaran';
            stopPolling();
        });
    }

    function showInstructions(data) {
        methodSection.style.display = 'none';
        instructionSection.style.display = '';

        var html = '<div class="uq-instruction-qris">' +
            '<div class="uq-instruction-label" style="font-size:14px;letter-spacing:0.5px;margin-bottom:12px;font-weight:800;color:#0f172a;">QRIS — SCAN UNTUK BAYAR</div>' +
            (data.qr_url ?
                '<div class="uq-qr-wrap" style="position:relative;display:inline-block;padding:16px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,0.06);margin:8px auto 16px;">' +
                    '<img src="' + data.qr_url + '" alt="QRIS Code" class="uq-qr-img" style="display:block;width:240px;height:240px;border-radius:8px;">' +
                    '<div class="uq-qr-center-logo" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:52px;height:52px;background:#ffffff;border-radius:12px;padding:4px;box-shadow:0 3px 12px rgba(0,0,0,0.25);display:flex;align-items:center;justify-content:center;z-index:3;border:2px solid #ffffff;">' +
                        '<img src="{{ asset('images/logo-pg.png') }}" alt="PestaGo Logo" style="width:100%;height:100%;object-fit:contain;border-radius:8px;">' +
                    '</div>' +
                '</div>'
                : '<p style="color:#999;">QR Code tidak tersedia.</p>'
            ) +
            '<div class="uq-instruction-amount" style="font-size:18px;margin-bottom:6px;">Total: <strong>' + totalFormatted + '</strong></div>' +
            '<p class="uq-instruction-note" style="font-size:12.5px;color:#64748b;margin-bottom:16px;line-height:1.5;">Scan QR code di atas menggunakan aplikasi e-wallet atau mobile banking<br><strong>(GoPay, OVO, DANA, ShopeePay, BCA, Mandiri, BRI & Lainnya)</strong></p>' +
            (data.expiry_time ? '<div class="uq-instruction-expiry" style="justify-content:center;font-size:12px;color:#64748b;"><i class="bi bi-clock"></i> Bayar sebelum: ' + data.expiry_time + '</div>' : '') +
        '</div>';

        instructionContent.innerHTML = html;
    }

    function getBankInstructions(bank) {
        var instructions = {
            bca: '<ol><li>Buka aplikasi <strong>BCA Mobile</strong> atau <strong>KlikBCA</strong></li><li>Pilih <strong>Transfer</strong> → <strong>BCA Virtual Account</strong></li><li>Masukkan nomor Virtual Account di atas</li><li>Periksa detail, lalu konfirmasi pembayaran</li></ol>',
            bni: '<ol><li>Buka aplikasi <strong>BNI Mobile Banking</strong></li><li>Pilih <strong>Transfer</strong> → <strong>Virtual Account Billing</strong></li><li>Masukkan nomor Virtual Account di atas</li><li>Periksa detail, lalu konfirmasi pembayaran</li></ol>',
            bri: '<ol><li>Buka aplikasi <strong>BRImo</strong></li><li>Pilih menu <strong>BRIVA</strong></li><li>Masukkan nomor Virtual Account di atas</li><li>Periksa detail, lalu konfirmasi pembayaran</li></ol>'
        };
        return instructions[bank] || '<ol><li>Transfer ke nomor Virtual Account di atas melalui ATM atau Mobile Banking</li></ol>';
    }

    // Copy to clipboard
    window._copyText = function(text, btn) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                var orig = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check"></i> Tersalin';
                setTimeout(function() { btn.innerHTML = orig; }, 2000);
            });
        } else {
            // Fallback
            var ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Tersalin';
            setTimeout(function() { btn.innerHTML = orig; }, 2000);
        }
    };

    // Polling for payment status
    function startPolling() {
        pollingStatus.style.display = '';
        pollInterval = setInterval(async function() {
            try {
                var res = await fetch('/customer/orders/' + orderId + '/check-status', {
                    headers: { 'Accept': 'application/json' }
                });
                var data = await res.json();
                if (data.is_paid) {
                    stopPolling();
                    pollingStatus.innerHTML = '<i class="bi bi-check-circle" style="color:#111111;font-size:16px;"></i> Pembayaran dikonfirmasi!';
                    setTimeout(function() {
                        window.location.href = '/customer/orders/' + orderId + '/success';
                    }, 1500);
                }
            } catch (e) {
                console.error('Poll error:', e);
            }
        }, 10000);
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }
});
</script>
@endpush
