@extends('layouts.app')
@section('title', 'Lupa Password — PestaGo')

@section('content')
<div class="uq-auth-page">
    <div class="uq-auth-container">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="uq-auth-logo">
            <img src="{{ asset('images/pgtam.png') }}" alt="PestaGo" class="uq-auth-logo-img">
        </a>

        {{-- Page Title --}}
        <h1 class="uq-auth-title">Lupa Password</h1>
        <p class="uq-auth-subtitle">
            Masukkan nomor WhatsApp atau alamat email yang terdaftar di akun PestaGo Anda. Kami akan mengirimkan kode OTP ke WhatsApp Anda.
        </p>

        {{-- Alerts --}}
        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:2px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
        @endif

        @if(session('info'))
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:2px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="bi bi-info-circle-fill"></i> {{ session('info') }}
        </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('password.send-otp') }}" method="POST" class="uq-auth-form">
            @csrf

            <div class="uq-form-group">
                <label for="identifier" class="uq-form-label">Nomor WhatsApp atau Email</label>
                <input type="text" id="identifier" name="identifier" class="uq-form-input"
                    value="{{ old('identifier') }}" placeholder="cth: 081234567890 atau email@domain.com" required autofocus>
                @error('identifier')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="uq-btn-primary" style="display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="bi bi-whatsapp"></i> KIRIM KODE OTP WHATSAPP
            </button>
        </form>

        {{-- Divider --}}
        <div class="uq-divider">
            <span>ATAU</span>
        </div>

        {{-- Back to login --}}
        <div class="uq-auth-actions">
            <a href="{{ route('login') }}" class="uq-btn-outline">KEMBALI KE HALAMAN MASUK</a>
        </div>
    </div>
</div>
@endsection

@section('hide_footer', 'true')

