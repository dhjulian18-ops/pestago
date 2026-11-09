@extends('layouts.app')
@section('title', 'Daftar Customer — PestaGo')

@section('content')
<div class="uq-auth-page">
    <div class="uq-auth-container">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="uq-auth-logo">
            <img src="{{ asset('images/pgtam.png') }}" alt="PestaGo" class="uq-auth-logo-img">
        </a>

        {{-- Page Title --}}
        <h1 class="uq-auth-title">Buat Akun Customer</h1>
        <p class="uq-auth-subtitle">Mulai rencanakan acara impian Anda bersama PestaGo.</p>

        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:2px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Register Form --}}
        <form action="{{ route('register') }}" method="POST" class="uq-auth-form">
            @csrf

            <div class="uq-form-group">
                <label for="name" class="uq-form-label">Nama lengkap</label>
                <input type="text" id="name" name="name" class="uq-form-input"
                    value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required autofocus>
                @error('name')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="uq-form-group">
                <label for="email" class="uq-form-label">Alamat email</label>
                <input type="email" id="email" name="email" class="uq-form-input"
                    value="{{ old('email') }}" placeholder="Masukkan email yang valid" required>
                @error('email')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="uq-form-group">
                <label for="phone" class="uq-form-label">No. WhatsApp / HP <span style="color:#D81A24;font-size:11px;font-weight:700;">* (Wajib, untuk verifikasi OTP)</span></label>
                <input type="text" id="phone" name="phone" class="uq-form-input"
                    value="{{ old('phone') }}" placeholder="cth: 081234567890" required>
                <span style="font-size:11px;color:#767676;margin-top:4px;display:block;">Kode OTP verifikasi akan dikirimkan langsung ke nomor WhatsApp ini.</span>
                @error('phone')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="uq-form-group">
                <label for="password" class="uq-form-label">Password</label>
                <div class="uq-input-password-wrap">
                    <input type="password" id="password" name="password" class="uq-form-input"
                        placeholder="Minimal 8 karakter" required>
                    <button type="button" class="uq-password-toggle" onclick="togglePasswordVisibility(this)" aria-label="Tampilkan password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                @error('password')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="uq-form-group">
                <label for="password_confirmation" class="uq-form-label">Konfirmasi password</label>
                <div class="uq-input-password-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="uq-form-input"
                        placeholder="Ulangi password" required>
                    <button type="button" class="uq-password-toggle" onclick="togglePasswordVisibility(this)" aria-label="Tampilkan password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="uq-btn-primary">DAFTAR SEKARANG</button>
        </form>

        {{-- Divider --}}
        <div class="uq-divider">
            <span>ATAU</span>
        </div>

        {{-- Secondary Actions --}}
        <div class="uq-auth-actions">
            <a href="{{ route('login') }}" class="uq-btn-outline">SUDAH PUNYA AKUN? MASUK</a>
            <a href="{{ route('register.vendor') }}" class="uq-btn-outline">DAFTAR SEBAGAI VENDOR</a>
        </div>
    </div>
</div>
@endsection

@section('hide_footer', 'true')

@push('scripts')
<script>
function togglePasswordVisibility(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye-slash';
    }
}
</script>
@endpush
