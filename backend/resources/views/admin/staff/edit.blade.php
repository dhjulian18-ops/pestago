@extends('layouts.app')
@section('title', 'Edit Staf & Hak Akses — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'staff'])

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title"><i class="bi bi-pencil-square"></i> Edit Staf: {{ $user->name }}</h1>
            <p class="dashboard-subtitle">Perbarui data staf dan atur ulang hak akses fitur platform.</p>
        </div>

        <form action="{{ route('admin.staff.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- 1. Data Akun --}}
            <div class="card mb-6">
                <div class="card-body">
                    <h3 style="font-weight:700;margin-bottom:var(--space-4);"><i class="bi bi-person"></i> Informasi Akun Login</h3>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label" for="name">Nama Lengkap Staf <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Email Login <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label" for="phone">No. WhatsApp / Telepon</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="admin_role_title">Jabatan / Role Name <span class="text-danger">*</span></label>
                            <input type="text" id="admin_role_title" name="admin_role_title" class="form-control" value="{{ old('admin_role_title', $user->admin_role_title) }}" required>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label" for="password">Password Baru (Opsional)</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password baru jika diganti">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Preset Cepat --}}
            <div class="card mb-6">
                <div class="card-body">
                    <h3 style="font-weight:700;margin-bottom:var(--space-2);"><i class="bi bi-lightning-charge-fill" style="color:#F59E0B;"></i> Template Cepat Role</h3>
                    <p class="text-muted mb-4" style="font-size:var(--font-size-sm);">Klik untuk menerapkan konfigurasi izin secara instan.</p>

                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:var(--space-3);">
                        @foreach($presets as $key => $preset)
                        <div class="preset-card" onclick="applyPreset('{{ $preset['title'] }}', {{ json_encode($preset['permissions']) }})" 
                             style="cursor:pointer;border:1px solid var(--gray-200);background:var(--gray-50);padding:var(--space-3) var(--space-4);border-radius:var(--radius-md);transition:all 0.2s ease;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                                <strong style="font-size:var(--font-size-sm);color:var(--gray-900);">{{ $preset['title'] }}</strong>
                                <span class="badge badge-primary" style="font-size:10px;">{{ $preset['badge'] }}</span>
                            </div>
                            <p style="font-size:11.5px;color:var(--gray-500);margin:0 0 6px 0;">{{ $preset['desc'] }}</p>
                            <span class="text-primary" style="font-size:11px;font-weight:600;"><i class="bi bi-arrow-repeat"></i> Terapkan Template</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. Checkbox Hak Akses Fitur (Permissions) --}}
            <div class="card mb-6">
                <div class="card-body">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:var(--space-4);">
                        <div>
                            <h3 style="font-weight:700;margin:0;"><i class="bi bi-toggles2"></i> Hak Akses Fitur (Permissions) <span class="text-danger">*</span></h3>
                            <p class="text-muted" style="font-size:var(--font-size-sm);margin:2px 0 0 0;">Centang modul yang diizinkan untuk dikelola oleh akun ini.</p>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="button" class="btn btn-sm btn-ghost" onclick="toggleAllPermissions(true)">Pilih Semua</button>
                            <button type="button" class="btn btn-sm btn-ghost" onclick="toggleAllPermissions(false)">Batalkan Semua</button>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:var(--space-3);">
                        @php $currentPerms = old('permissions', $user->permissions ?? []); @endphp
                        @foreach($availablePermissions as $permKey => $perm)
                        <label class="permission-item" style="display:flex;align-items:flex-start;gap:12px;padding:var(--space-3);background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--radius-md);cursor:pointer;">
                            <input type="checkbox" name="permissions[]" value="{{ $permKey }}" class="perm-checkbox" {{ in_array($permKey, $currentPerms) ? 'checked' : '' }} style="margin-top:3px;transform:scale(1.2);">
                            <div>
                                <div style="font-weight:600;font-size:var(--font-size-sm);color:var(--gray-900);display:flex;align-items:center;gap:6px;">
                                    <i class="bi {{ $perm['icon'] }}" style="color:{{ $perm['color'] }};font-size:14px;"></i>
                                    {{ $perm['name'] }}
                                </div>
                                <div style="font-size:11.5px;color:var(--gray-500);margin-top:2px;">{{ $perm['desc'] }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <a href="{{ route('admin.staff.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Kembali</a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function applyPreset(roleTitle, permsArray) {
    document.getElementById('admin_role_title').value = roleTitle;
    const checkboxes = document.querySelectorAll('.perm-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = permsArray.includes(cb.value);
    });
}

function toggleAllPermissions(check) {
    const checkboxes = document.querySelectorAll('.perm-checkbox');
    checkboxes.forEach(cb => cb.checked = check);
}
</script>
@endsection
