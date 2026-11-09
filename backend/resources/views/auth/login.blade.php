@extends('layouts.app')
@section('title', 'Masuk — PestaGo')

@section('content')
<div class="uq-auth-page">
    <div class="uq-auth-container">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="uq-auth-logo">
            <img src="{{ asset('images/pgtam.png') }}" alt="PestaGo" class="uq-auth-logo-img">
        </a>

        {{-- Alerts --}}
        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:2px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('info'))
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:12px 16px;border-radius:2px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="bi bi-info-circle-fill"></i> {{ session('info') }}
        </div>
        @endif
        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:2px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Info Banner --}}
        <div class="uq-auth-notice">
            <p>Jangan gunakan password yang sama dengan layanan lain atau password yang mudah ditebak.</p>
        </div>

        {{-- Login Form --}}
        <form action="{{ route('login') }}" method="POST" class="uq-auth-form">
            @csrf

            <div class="uq-form-group">
                <label for="email" class="uq-form-label">Alamat email</label>
                <input type="email" id="email" name="email" class="uq-form-input"
                    value="{{ old('email') }}" placeholder="Masukkan email yang valid" required autofocus>
                @error('email')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="uq-form-group">
                <label for="password" class="uq-form-label">Password</label>
                <div class="uq-input-password-wrap">
                    <input type="password" id="password" name="password" class="uq-form-input"
                        placeholder="Masukkan password" required>
                    <button type="button" class="uq-password-toggle" onclick="togglePasswordVisibility(this)" aria-label="Tampilkan password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                @error('password')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <a href="{{ route('password.request') }}" class="uq-forgot-link">Lupa password?</a>

            <button type="submit" class="uq-btn-primary">MASUK</button>
        </form>

        {{-- Divider --}}
        <div class="uq-divider">
            <span>ATAU</span>
        </div>

        {{-- Secondary Actions --}}
        <div class="uq-auth-actions">
            <a href="{{ route('register') }}" class="uq-btn-outline">BUAT AKUN CUSTOMER</a>
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
