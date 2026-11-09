@extends('layouts.app')
@section('title', 'Buat Password Baru — PestaGo')

@section('content')
<div class="uq-auth-page">
    <div class="uq-auth-container">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="uq-auth-logo">
            <img src="{{ asset('images/pgtam.png') }}" alt="PestaGo" class="uq-auth-logo-img">
        </a>

        {{-- Page Title --}}
        <h1 class="uq-auth-title">Buat Password Baru</h1>
        <p class="uq-auth-subtitle">
            Nomor WhatsApp Anda berhasil diverifikasi. Masukkan password baru untuk akun Anda.
        </p>

        {{-- Alerts --}}
        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:2px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
        @endif

        {{-- Reset Password Form --}}
        <form action="{{ route('password.update') }}" method="POST" class="uq-auth-form">
            @csrf

            <div class="uq-form-group">
                <label for="password" class="uq-form-label">Password Baru</label>
                <div class="uq-input-password-wrap">
                    <input type="password" id="password" name="password" class="uq-form-input"
                        placeholder="Minimal 8 karakter" required autofocus>
                    <button type="button" class="uq-password-toggle" onclick="togglePasswordVisibility(this)" aria-label="Tampilkan password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                @error('password')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="uq-form-group">
                <label for="password_confirmation" class="uq-form-label">Konfirmasi Password Baru</label>
                <div class="uq-input-password-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="uq-form-input"
                        placeholder="Ulangi password baru" required>
                    <button type="button" class="uq-password-toggle" onclick="togglePasswordVisibility(this)" aria-label="Tampilkan password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="uq-btn-primary" style="height:44px;">
                SIMPAN PASSWORD &amp; MASUK
            </button>
        </form>
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

