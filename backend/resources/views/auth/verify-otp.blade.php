@extends('layouts.app')
@section('title', 'Verifikasi Kode OTP WhatsApp — PestaGo')

@section('content')
<div class="uq-auth-page">
    <div class="uq-auth-container">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="uq-auth-logo">
            <img src="{{ asset('images/pgtam.png') }}" alt="PestaGo" class="uq-auth-logo-img">
        </a>

        <div style="text-align:center;margin-bottom:20px;">
            <div style="width:54px;height:54px;background:#e6f9ed;color:#25D366;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:12px;">
                <i class="bi bi-whatsapp"></i>
            </div>
            <h1 class="uq-auth-title" style="margin-bottom:6px;">Verifikasi WhatsApp</h1>
            <p class="uq-auth-subtitle" style="margin:0;">
                Masukkan 6 digit kode OTP yang telah dikirimkan ke WhatsApp Anda di nomor <strong>{{ $maskedPhone }}</strong>
            </p>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:2px;margin-bottom:18px;font-size:13px;font-weight:600;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('info'))
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:2px;margin-bottom:18px;font-size:13px;font-weight:600;">
            <i class="bi bi-info-circle-fill"></i> {{ session('info') }}
        </div>
        @endif

        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:2px;margin-bottom:18px;font-size:13px;font-weight:600;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
        @endif

        {{-- OTP Verification Form --}}
        <form action="{{ route('otp.verify') }}" method="POST" class="uq-auth-form">
            @csrf

            <div class="uq-form-group">
                <label for="otp_code" class="uq-form-label" style="text-align:center;display:block;">
                    6 DIGIT KODE OTP
                </label>
                <input type="text" id="otp_code" name="otp_code" class="uq-form-input"
                    maxlength="6" inputmode="numeric" pattern="[0-9]*"
                    placeholder="______" autofocus required
                    style="font-size:26px;font-weight:800;letter-spacing:10px;text-align:center;height:52px;font-family:monospace;">
                @error('otp_code')
                    <span class="uq-form-error" style="text-align:center;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="uq-btn-primary" style="height:46px;font-size:14px;letter-spacing:0.5px;">
                VERIFIKASI &amp; SELESAIKAN PENDAFTARAN
            </button>
        </form>

        {{-- Resend OTP Section --}}
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid #eeeeee;text-align:center;">
            <p style="font-size:12.5px;color:#666666;margin:0 0 10px;">Tidak menerima pesan kode OTP di WhatsApp?</p>
            
            <form action="{{ route('otp.resend') }}" method="POST" id="resendForm" style="margin:0;">
                @csrf
                <button type="submit" id="resendBtn" class="uq-btn-outline" style="height:38px;padding:0 20px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:6px;width:auto;">
                    <i class="bi bi-arrow-repeat"></i> <span id="resendBtnText">Kirim Ulang Kode OTP</span>
                </button>
            </form>

            <div style="margin-top:16px;">
                <a href="{{ $action === 'register_vendor' ? route('register.vendor') : route('register') }}" style="font-size:12px;color:#767676;text-decoration:underline;">
                    &larr; Ubah nomor WhatsApp / Kembali ke pendaftaran
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('hide_footer', 'true')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp_code');
    const resendBtn = document.getElementById('resendBtn');
    const resendText = document.getElementById('resendBtnText');

    if (otpInput) {
        // Automatically submit when 6 digits are typed
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                // optionally auto submit
            }
        });
    }

    // 60-second cooldown timer for Resend button
    let cooldown = 60;
    resendBtn.disabled = true;
    resendBtn.style.opacity = '0.6';
    resendBtn.style.cursor = 'not-allowed';

    const interval = setInterval(function() {
        cooldown--;
        if (cooldown > 0) {
            resendText.textContent = 'Kirim Ulang (' + cooldown + 's)';
        } else {
            clearInterval(interval);
            resendBtn.disabled = false;
            resendBtn.style.opacity = '1';
            resendBtn.style.cursor = 'pointer';
            resendText.textContent = 'Kirim Ulang Kode OTP';
        }
    }, 1000);
});
</script>
@endpush

