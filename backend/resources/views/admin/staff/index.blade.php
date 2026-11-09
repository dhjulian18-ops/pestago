@extends('layouts.app')
@section('title', 'Kelola Staf & Sub-Admin — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'staff'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
                <h1 class="dashboard-title"><i class="bi bi-person-badge"></i> Kelola Staf &amp; Sub-Admin</h1>
                <p class="dashboard-subtitle">Kelola akun staf internal dengan hak akses fitur (permissions) yang dapat dikustomisasi.</p>
            </div>
            <div>
                <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill"></i> Tambah Staf Baru
                </a>
            </div>
        </div>

        {{-- Info Box --}}
        <div class="alert alert-info mb-6" style="background:#EFF6FF;border-left:4px solid #3B82F6;">
            <i class="bi bi-shield-check" style="font-size:1.5rem;color:#3B82F6;"></i>
            <div>
                <strong>Role-Based Access Control (RBAC)</strong>
                <p style="margin-top:2px;font-size:var(--font-size-sm);color:var(--gray-700);">
                    Anda dapat membuat akun untuk tim <strong>Customer Service (CS)</strong>, <strong>Verifikator Vendor</strong>, <strong>Finance</strong>, maupun <strong>Marketing</strong> dengan memilih modul fitur yang boleh dibuka oleh akun tersebut.
                </p>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama &amp; Kontak</th>
                        <th>Role / Jabatan</th>
                        <th>Hak Akses Fitur</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffs as $staff)
                    <tr>
                        <td>
                            <strong>{{ $staff->name }}</strong>
                            @if($staff->isSuperAdmin())
                                <span class="badge badge-primary" style="margin-left:4px;font-size:10px;"><i class="bi bi-star-fill"></i> Super Admin</span>
                            @endif
                            <div style="font-size:var(--font-size-xs);color:var(--gray-500);margin-top:2px;">
                                <i class="bi bi-envelope"></i> {{ $staff->email }}
                                @if($staff->phone) • <i class="bi bi-telephone"></i> {{ $staff->phone }} @endif
                            </div>
                        </td>
                        <td>
                            @if($staff->isSuperAdmin())
                                <strong style="color:var(--primary);">Super Administrator</strong>
                            @else
                                <span class="badge badge-info" style="font-size:12px;font-weight:600;padding:4px 8px;">
                                    {{ $staff->admin_role_title ?: 'Staf Admin' }}
                                </span>
                            @endif
                        </td>
                        <td style="max-width:320px;">
                            @if($staff->isSuperAdmin())
                                <span class="badge badge-success"><i class="bi bi-check-all"></i> Akses Seluruh Fitur Platform</span>
                            @else
                                @php $perms = $staff->permissions ?? []; @endphp
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                    @foreach($perms as $p)
                                        @if(isset($availablePermissions[$p]))
                                            <span class="badge badge-gray" style="font-size:10.5px;padding:2px 6px;">
                                                <i class="bi {{ $availablePermissions[$p]['icon'] }}" style="color:{{ $availablePermissions[$p]['color'] }};margin-right:2px;"></i>
                                                {{ $availablePermissions[$p]['name'] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($staff->is_active)
                                <span class="badge badge-success"><i class="bi bi-check-circle"></i> Aktif</span>
                            @else
                                <span class="badge badge-danger"><i class="bi bi-x-circle"></i> Nonaktif</span>
                            @endif
                        </td>
                        <td style="font-size:var(--font-size-xs);color:var(--gray-500);">
                            {{ $staff->created_at->translatedFormat('d M Y') }}
                        </td>
                        <td style="text-align:right;">
                            @if(!$staff->isSuperAdmin())
                                <div style="display:flex;justify-content:flex-end;gap:6px;">
                                    <a href="{{ route('admin.staff.edit', $staff) }}" class="btn btn-sm btn-ghost" title="Edit Hak Akses">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('admin.staff.toggle', $staff) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $staff->is_active ? 'btn-ghost text-warning' : 'btn-ghost text-success' }}" title="{{ $staff->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" onclick="return confirm('Ubah status aktif akun ini?')">
                                            <i class="bi {{ $staff->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.staff.destroy', $staff) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost text-danger" title="Hapus Akun" onclick="return confirm('Hapus akun staf ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-muted" style="font-size:11px;">Utama</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted" style="padding:var(--space-8);">Tidak ada staf terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:var(--space-4);">
            {{ $staffs->links() }}
        </div>
    </div>
</div>
@endsection
