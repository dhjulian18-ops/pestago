@extends('layouts.app')
@section('title', 'Daftar Vendor — PestaGo')

@section('content')
<div class="uq-auth-page">
    <div class="uq-auth-container uq-auth-container--wide">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="uq-auth-logo">
            <img src="{{ asset('images/pgtam.png') }}" alt="PestaGo" class="uq-auth-logo-img">
        </a>

        {{-- Page Title --}}
        <h1 class="uq-auth-title">Daftar Sebagai Vendor</h1>
        <p class="uq-auth-subtitle">Mulai terima pesanan dan kembangkan bisnis Anda bersama PestaGo.</p>

        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:2px;margin-bottom:16px;font-size:13px;font-weight:600;">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Register Form --}}
        <form action="{{ route('register.vendor') }}" method="POST" class="uq-auth-form">
            @csrf

            {{-- Section: Data Akun --}}
            <div class="uq-form-section-label">Data Akun & Kontak</div>

            <div class="uq-form-row">
                <div class="uq-form-group">
                    <label for="name" class="uq-form-label">Nama lengkap pemilik</label>
                    <input type="text" id="name" name="name" class="uq-form-input"
                        value="{{ old('name') }}" placeholder="cth: Ahmad Fauzi" required autofocus>
                    @error('name')
                        <span class="uq-form-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="uq-form-group">
                    <label for="phone" class="uq-form-label">No. WhatsApp / Telepon <span style="color:#D81A24;font-size:11px;font-weight:700;">* (untuk verifikasi OTP)</span></label>
                    <input type="text" id="phone" name="phone" class="uq-form-input"
                        value="{{ old('phone') }}" placeholder="cth: 081234567890" required>
                    <span style="font-size:11px;color:#767676;margin-top:4px;display:block;">Kode OTP akan dikirim ke WhatsApp ini.</span>
                    @error('phone')
                        <span class="uq-form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="uq-form-group">
                <label for="email" class="uq-form-label">Alamat email</label>
                <input type="email" id="email" name="email" class="uq-form-input"
                    value="{{ old('email') }}" placeholder="nama@email.com" required>
                @error('email')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="uq-form-row">
                <div class="uq-form-group">
                    <label for="password" class="uq-form-label">Password</label>
                    <div class="uq-input-password-wrap">
                        <input type="password" id="password" name="password" class="uq-form-input"
                            placeholder="Min. 8 karakter" required>
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
            </div>

            {{-- Section: Informasi Usaha --}}
            <div class="uq-form-section-label">Informasi Usaha</div>

            <div class="uq-form-group">
                <label for="business_name" class="uq-form-label">Nama usaha / brand</label>
                <input type="text" id="business_name" name="business_name" class="uq-form-input"
                    value="{{ old('business_name') }}" placeholder="cth: Cantik Catering & Event" required>
                @error('business_name')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="uq-form-group">
                <label for="category_id" class="uq-form-label">Kategori usaha utama</label>
                <select id="category_id" name="category_id" class="uq-form-input uq-form-select" required>
                    <option value="">— Pilih Kategori —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <span class="uq-form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="uq-auth-notice">
                <p>Setelah mendaftar, Anda dapat langsung masuk ke dashboard vendor untuk melengkapi dokumen persyaratan verifikasi usaha sebelum mempublikasikan layanan.</p>
            </div>

            <button type="submit" class="uq-btn-primary">DAFTAR & MASUK KE DASHBOARD</button>
        </form>

        {{-- Divider --}}
        <div class="uq-divider">
            <span>ATAU</span>
        </div>

        {{-- Secondary Actions --}}
        <div class="uq-auth-actions">
            <a href="{{ route('login') }}" class="uq-btn-outline">SUDAH PUNYA AKUN? MASUK</a>
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
