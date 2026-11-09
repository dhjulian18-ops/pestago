@extends('layouts.app')
@section('title', 'Pengaturan Profil — PestaGo')

@section('content')
<div style="background:#fafafa;min-height:100vh;padding-top:calc(var(--navbar-height, 56px) + 24px);padding-bottom:60px;">
    <div class="container" style="max-width:820px;padding:0 16px;">

        {{-- Page Header --}}
        <div style="margin-bottom:24px;">
            <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#767676;margin-bottom:6px;">
                @if($user->role === 'vendor')
                    <a href="{{ route('vendor.dashboard') }}" style="color:#666666;text-decoration:none;">Dashboard Vendor</a>
                @elseif(in_array($user->role, ['admin', 'super_admin']))
                    <a href="{{ route('admin.dashboard') }}" style="color:#666666;text-decoration:none;">Dashboard Admin</a>
                @else
                    <a href="{{ route('customer.dashboard') }}" style="color:#666666;text-decoration:none;">Dashboard</a>
                @endif
                <i class="bi bi-chevron-right" style="font-size:10px;"></i>
                <span style="color:#111111;font-weight:600;">Pengaturan Profil</span>
            </div>
            <h1 style="font-size:24px;font-weight:800;color:#111111;margin:0 0 4px;letter-spacing:-0.02em;">
                Pengaturan Profil
            </h1>
            <p style="font-size:13px;color:#666666;margin:0;">
                Kelola foto profil, data kontak, dan keamanan akun PestaGo Anda.
            </p>
        </div>

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:2px;margin-bottom:20px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:10px;">
                <i class="bi bi-check-circle-fill" style="font-size:16px;"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:2px;margin-bottom:20px;font-size:13px;">
                <div style="font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-exclamation-triangle-fill"></i> Terjadi kesalahan validasi:
                </div>
                <ul style="margin:0;padding-left:18px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="display:flex;flex-direction:column;gap:20px;">

            {{-- 1. INFORMASI AKUN & FOTO PROFIL --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;">
                <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between;">
                    <div style="font-size:14px;font-weight:800;color:#111111;text-transform:uppercase;letter-spacing:0.04em;display:flex;align-items:center;gap:8px;">
                        <i class="bi bi-person-circle" style="color:#D81A24;font-size:16px;"></i>
                        <span>Informasi Akun &amp; Foto Profil</span>
                    </div>
                    <span style="font-size:11px;font-weight:700;color:#888888;text-transform:uppercase;letter-spacing:0.05em;background:#f5f5f5;padding:3px 8px;border-radius:2px;">
                        {{ strtoupper($user->role) }}
                    </span>
                </div>

                <div style="padding:22px 20px;">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Avatar Picker & Preview --}}
                        <div style="display:flex;align-items:center;gap:18px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid #f5f5f5;flex-wrap:wrap;">
                            <div style="position:relative;flex-shrink:0;">
                                <div id="avatarContainer" style="width:84px;height:84px;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;background:#fafafa;display:flex;align-items:center;justify-content:center;">
                                    @if($user->avatar_url)
                                        <img id="avatarPreview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <img id="avatarPreview" src="" alt="Preview" style="display:none;width:100%;height:100%;object-fit:cover;">
                                        <span id="avatarPlaceholder" style="font-size:28px;font-weight:900;color:#111111;letter-spacing:0.02em;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div style="flex:1;min-width:200px;">
                                <div style="font-size:13.5px;font-weight:700;color:#111111;margin-bottom:3px;">
                                    Foto Profil
                                </div>
                                <p style="font-size:12px;color:#767676;margin:0 0 10px;line-height:1.4;">
                                    Foto ini akan tampil di menu samping, navigasi, dan identitas akun Anda. Format: JPG, PNG, atau WebP (Maks. 2MB).
                                </p>
                                <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none;">
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <button type="button" onclick="document.getElementById('avatarInput').click()" class="btn btn-sm" style="border:1px solid #111111;background:#ffffff;color:#111111;border-radius:2px;font-size:12px;font-weight:700;padding:6px 14px;cursor:pointer;">
                                        <i class="bi bi-upload"></i> Pilih Foto
                                    </button>
                                    <span id="avatarFileName" style="font-size:11.5px;color:#767676;font-family:monospace;"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Form Inputs --}}
                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:16px;margin-bottom:20px;">
                            <div>
                                <label for="name" style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Nama Lengkap <span style="color:#D81A24;">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;" required>
                            </div>

                            <div>
                                <label for="email" style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Alamat Email <span style="color:#D81A24;">*</span>
                                </label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;" required>
                            </div>

                            <div style="grid-column:1 / -1;">
                                <label for="phone" style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Nomor Telepon / WhatsApp
                                </label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;">
                                <small style="display:block;font-size:11px;color:#767676;margin-top:4px;">
                                    Digunakan untuk verifikasi OTP, konfirmasi pesanan, dan komunikasi customer support.
                                </small>
                            </div>
                        </div>

                        <div style="display:flex;justify-content:flex-end;padding-top:10px;">
                            <button type="submit" class="btn" style="background:#D81A24;color:#ffffff;border:none;border-radius:2px;font-weight:700;font-size:13px;padding:10px 22px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                                <i class="bi bi-check-lg"></i> Simpan Perubahan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 2. UBAH PASSWORD --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;">
                <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;">
                    <div style="font-size:14px;font-weight:800;color:#111111;text-transform:uppercase;letter-spacing:0.04em;display:flex;align-items:center;gap:8px;">
                        <i class="bi bi-shield-lock" style="color:#111111;font-size:16px;"></i>
                        <span>Keamanan &amp; Kata Sandi</span>
                    </div>
                </div>

                <div style="padding:22px 20px;">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(230px, 1fr));gap:16px;margin-bottom:20px;">
                            <div>
                                <label for="current_password" style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Password Saat Ini <span style="color:#D81A24;">*</span>
                                </label>
                                <input type="password" name="current_password" id="current_password" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;" required>
                            </div>

                            <div>
                                <label for="password" style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Password Baru <span style="color:#D81A24;">*</span>
                                </label>
                                <input type="password" name="password" id="password" minlength="8" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;" required>
                            </div>

                            <div>
                                <label for="password_confirmation" style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Konfirmasi Password Baru <span style="color:#D81A24;">*</span>
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;" required>
                            </div>
                        </div>

                        <div style="display:flex;justify-content:flex-end;padding-top:6px;">
                            <button type="submit" class="btn" style="background:#111111;color:#ffffff;border:none;border-radius:2px;font-weight:700;font-size:13px;padding:10px 22px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                                <i class="bi bi-key"></i> Perbarui Kata Sandi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. PROFIL VENDOR (Hanya Tampil Jika Akun Vendor) --}}
            @if($user->role === 'vendor' && $vendorProfile)
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;">
                <div style="padding:16px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between;">
                    <div style="font-size:14px;font-weight:800;color:#111111;text-transform:uppercase;letter-spacing:0.04em;display:flex;align-items:center;gap:8px;">
                        <i class="bi bi-shop" style="color:#D81A24;font-size:16px;"></i>
                        <span>Profil Bisnis Mitra Vendor</span>
                    </div>
                    <span style="font-size:11px;font-weight:700;color:#15803d;background:#dcfce7;padding:3px 8px;border-radius:2px;">
                        {{ strtoupper($vendorProfile->status) }}
                    </span>
                </div>

                <div style="padding:22px 20px;">
                    <form action="{{ route('profile.vendor') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Vendor Logo & Banner previews --}}
                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;margin-bottom:20px;padding-bottom:18px;border-bottom:1px solid #f5f5f5;">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Logo Bisnis Vendor
                                </label>
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div style="width:56px;height:56px;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;background:#fafafa;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        @if($vendorProfile->logo_url)
                                            <img id="logoPreview" src="{{ $vendorProfile->logo_url }}" alt="Logo" style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            <img id="logoPreview" src="" alt="Logo" style="display:none;width:100%;height:100%;object-fit:cover;">
                                            <i class="bi bi-shop" style="font-size:22px;color:#888888;"></i>
                                        @endif
                                    </div>
                                    <div style="flex:1;">
                                        <input type="file" name="logo" id="logoInput" accept="image/*" class="form-control" style="font-size:11.5px;padding:4px 8px;height:auto;border-radius:2px;border:1px solid #d4d4d4;">
                                        <small style="display:block;font-size:11px;color:#767676;margin-top:3px;">JPG, PNG, atau WebP (Maks. 2MB)</small>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Banner Toko / Header Usaha
                                </label>
                                <input type="file" name="banner" id="bannerInput" accept="image/*" class="form-control" style="font-size:11.5px;padding:4px 8px;height:auto;border-radius:2px;border:1px solid #d4d4d4;">
                                <small style="display:block;font-size:11px;color:#767676;margin-top:3px;">Rekomendasi rasio 16:9 atau 3:1 (Maks. 4MB)</small>
                            </div>
                        </div>

                        {{-- Vendor Fields --}}
                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:16px;margin-bottom:16px;">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Nama Usaha / Brand <span style="color:#D81A24;">*</span>
                                </label>
                                <input type="text" name="business_name" value="{{ old('business_name', $vendorProfile->business_name) }}" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;" required>
                            </div>

                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Telepon Usaha
                                </label>
                                <input type="text" name="phone" value="{{ old('phone', $vendorProfile->phone) }}" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;">
                            </div>

                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    WhatsApp Pelanggan
                                </label>
                                <input type="text" name="whatsapp" value="{{ old('whatsapp', $vendorProfile->whatsapp) }}" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;">
                            </div>

                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Instagram (@username)
                                </label>
                                <input type="text" name="instagram" value="{{ old('instagram', $vendorProfile->instagram) }}" placeholder="cth: pestago.official" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;">
                            </div>

                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Kota Domisili Usaha
                                </label>
                                <input type="text" name="city" value="{{ old('city', $vendorProfile->city) }}" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;">
                            </div>

                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Kecamatan
                                </label>
                                <input type="text" name="district" value="{{ old('district', $vendorProfile->district) }}" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;">
                            </div>

                            <div style="grid-column:1 / -1;">
                                <label style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Alamat Lengkap Workshop / Kantor
                                </label>
                                <input type="text" name="address" value="{{ old('address', $vendorProfile->address) }}" class="form-control" style="height:40px;border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:0 12px;width:100%;box-sizing:border-box;">
                            </div>

                            <div style="grid-column:1 / -1;">
                                <label style="display:block;font-size:11px;font-weight:700;color:#555555;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                                    Deskripsi Profil Usaha
                                </label>
                                <textarea name="description" rows="3" class="form-control" style="border-radius:2px;border:1px solid #d4d4d4;font-size:13.5px;padding:10px 12px;width:100%;box-sizing:border-box;">{{ old('description', $vendorProfile->description) }}</textarea>
                            </div>
                        </div>

                        <div style="display:flex;justify-content:flex-end;padding-top:6px;">
                            <button type="submit" class="btn" style="background:#D81A24;color:#ffffff;border:none;border-radius:2px;font-weight:700;font-size:13px;padding:10px 22px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                                <i class="bi bi-check-lg"></i> Simpan Profil Usaha Vendor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- Instant Avatar Preview Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarPlaceholder = document.getElementById('avatarPlaceholder');
    const avatarFileName = document.getElementById('avatarFileName');

    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (avatarFileName) {
                    avatarFileName.textContent = file.name;
                }
                const reader = new FileReader();
                reader.onload = function(evt) {
                    if (avatarPreview) {
                        avatarPreview.src = evt.target.result;
                        avatarPreview.style.display = 'block';
                    }
                    if (avatarPlaceholder) {
                        avatarPlaceholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    const logoInput = document.getElementById('logoInput');
    const logoPreview = document.getElementById('logoPreview');
    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    logoPreview.src = evt.target.result;
                    logoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endsection
