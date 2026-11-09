@extends('layouts.app')
@section('title', 'Manajemen Vendor — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'vendors'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <div>
                <h1 class="dashboard-title">Manajemen Vendor</h1>
                <p class="dashboard-subtitle">Kelola, verifikasi, edit, dan pantau seluruh penyedia jasa (vendor) di PestaGo</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <span style="font-size:12px;font-weight:700;background:#ffffff;border:1px solid #e5e5e5;padding:6px 12px;border-radius:2px;color:#111111;">
                    Total: <strong>{{ $vendors->total() }}</strong> Vendor
                </span>
            </div>
        </div>

        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:2px;margin-bottom:18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:2px;margin-bottom:18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        {{-- Status Filter Tabs --}}
        <div style="display:flex;gap:6px;margin-bottom:18px;flex-wrap:wrap;">
            <a href="{{ route('admin.vendors', array_filter(['search' => $search, 'category_id' => $category_id])) }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-ghost' }}" style="border-radius:2px;font-size:12px;">Semua</a>
            <a href="{{ route('admin.vendors', array_filter(['status' => 'pending', 'search' => $search, 'category_id' => $category_id])) }}" class="btn btn-sm {{ $status == 'pending' ? 'btn-primary' : 'btn-ghost' }}" style="border-radius:2px;font-size:12px;">
                Menunggu Review <span class="badge badge-warning" style="margin-left:4px;">{{ \App\Models\VendorProfile::where('status','pending')->count() }}</span>
            </a>
            <a href="{{ route('admin.vendors', array_filter(['status' => 'needs_revision', 'search' => $search, 'category_id' => $category_id])) }}" class="btn btn-sm {{ $status == 'needs_revision' ? 'btn-primary' : 'btn-ghost' }}" style="border-radius:2px;font-size:12px;">Perlu Revisi</a>
            <a href="{{ route('admin.vendors', array_filter(['status' => 'incomplete', 'search' => $search, 'category_id' => $category_id])) }}" class="btn btn-sm {{ $status == 'incomplete' ? 'btn-primary' : 'btn-ghost' }}" style="border-radius:2px;font-size:12px;">Belum Lengkap</a>
            <a href="{{ route('admin.vendors', array_filter(['status' => 'active', 'search' => $search, 'category_id' => $category_id])) }}" class="btn btn-sm {{ $status == 'active' ? 'btn-primary' : 'btn-ghost' }}" style="border-radius:2px;font-size:12px;">Aktif</a>
            <a href="{{ route('admin.vendors', array_filter(['status' => 'rejected', 'search' => $search, 'category_id' => $category_id])) }}" class="btn btn-sm {{ $status == 'rejected' ? 'btn-primary' : 'btn-ghost' }}" style="border-radius:2px;font-size:12px;">Ditolak</a>
            <a href="{{ route('admin.vendors', array_filter(['status' => 'suspended', 'search' => $search, 'category_id' => $category_id])) }}" class="btn btn-sm {{ $status == 'suspended' ? 'btn-primary' : 'btn-ghost' }}" style="border-radius:2px;font-size:12px;">Suspended</a>
        </div>

        {{-- Search & Filters --}}
        <div style="background:#ffffff;border:1px solid #e5e5e5;padding:16px;border-radius:2px;margin-bottom:20px;">
            <form action="{{ route('admin.vendors') }}" method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                <input type="hidden" name="status" value="{{ $status }}">
                <div style="flex:1;min-width:240px;">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama usaha, pemilik, no telepon, kota..." class="form-control" style="height:38px;font-size:13px;border-radius:2px;border-color:#d4d4d4;">
                </div>
                <div style="width:180px;">
                    <select name="category_id" class="form-control" style="height:38px;font-size:13px;border-radius:2px;border-color:#d4d4d4;">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="height:38px;padding:0 18px;border-radius:2px;font-size:12.5px;font-weight:700;">
                    <i class="bi bi-search"></i> Cari
                </button>
                @if($search || $category_id)
                <a href="{{ route('admin.vendors', ['status' => $status]) }}" class="btn btn-ghost" style="height:38px;padding:0 14px;border:1px solid #d4d4d4;border-radius:2px;font-size:12.5px;">
                    Reset
                </a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="table-wrapper" style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;overflow-x:auto;">
            <table class="table" style="width:100%;border-collapse:collapse;margin:0;">
                <thead>
                    <tr style="background:#fafafa;border-bottom:1px solid #e5e5e5;text-align:left;">
                        <th style="padding:12px 14px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Nama Usaha</th>
                        <th style="padding:12px 14px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Pemilik</th>
                        <th style="padding:12px 14px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">No. WhatsApp / Telp</th>
                        <th style="padding:12px 14px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Kategori &amp; Kota</th>
                        <th style="padding:12px 14px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Verifikasi</th>
                        <th style="padding:12px 14px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Akun</th>
                        <th style="padding:12px 14px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $v)
                    @php
                        $vs = [
                            'incomplete' => ['Belum Lengkap', 'warning'],
                            'pending' => ['Menunggu Review', 'warning'],
                            'active' => ['Aktif', 'success'],
                            'rejected' => ['Ditolak', 'danger'],
                            'needs_revision' => ['Perlu Revisi', 'warning'],
                            'suspended' => ['Suspended', 'danger']
                        ];
                        $s = $vs[$v->status] ?? [$v->status, 'gray'];

                        $vendorPhone = $v->whatsapp ?: ($v->phone ?: ($v->user->phone ?? ''));
                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$vendorPhone);
                        if (str_starts_with($cleanPhone, '0')) $waNumber = '62' . substr($cleanPhone, 1);
                        elseif (str_starts_with($cleanPhone, '8')) $waNumber = '62' . $cleanPhone;
                        else $waNumber = $cleanPhone;
                    @endphp
                    <tr style="border-bottom:1px solid #eeeeee;">
                        <td style="padding:14px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                @if($v->logo_url)
                                    <img src="{{ $v->logo_url }}" alt="{{ $v->business_name }}" style="width:36px;height:36px;border-radius:2px;object-fit:cover;border:1px solid #e5e5e5;flex-shrink:0;">
                                @else
                                    <div style="width:36px;height:36px;background:#111111;color:#ffffff;border-radius:2px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0;">
                                        {{ strtoupper(substr($v->business_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:700;color:#111111;font-size:13.5px;">{{ $v->business_name }}</div>
                                    <div style="font-size:11px;color:#767676;font-family:monospace;">/{{ $v->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:14px;">
                            <div style="font-weight:600;color:#111111;font-size:13px;">{{ $v->user->name ?? '-' }}</div>
                            <div style="font-size:11.5px;color:#767676;">{{ $v->user->email ?? '-' }}</div>
                        </td>
                        <td style="padding:14px;">
                            @if(!empty($vendorPhone))
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="font-weight:700;font-size:12.5px;font-family:monospace;color:#111111;">{{ $vendorPhone }}</span>
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" title="Chat WhatsApp Vendor" style="width:22px;height:22px;background:#25D366;color:#ffffff;border-radius:2px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;text-decoration:none;">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </div>
                            @else
                                <span style="font-size:12px;color:#999;font-style:italic;">Belum diisi</span>
                            @endif
                        </td>
                        <td style="padding:14px;font-size:12.5px;">
                            <div style="font-weight:600;color:#111111;">{{ $v->category->name ?? '-' }}</div>
                            <div style="font-size:11.5px;color:#666666;"><i class="bi bi-geo-alt"></i> {{ $v->city ?: 'Kota belum diisi' }}</div>
                        </td>
                        <td style="padding:14px;">
                            <span class="badge badge-{{ $s[1] }}" style="font-size:10.5px;border-radius:2px;padding:3px 8px;">{{ $s[0] }}</span>
                        </td>
                        <td style="padding:14px;">
                            @if($v->user && $v->user->is_active)
                                <span class="badge badge-success" style="font-size:10.5px;border-radius:2px;padding:3px 6px;">Aktif</span>
                            @else
                                <span class="badge badge-danger" style="font-size:10.5px;border-radius:2px;padding:3px 6px;">Nonaktif</span>
                            @endif
                        </td>
                        <td style="padding:14px;text-align:right;">
                            <div style="display:inline-flex;align-items:center;gap:5px;justify-content:flex-end;">
                                {{-- 1. Mantau (Detail & Verifikasi) --}}
                                <a href="{{ route('admin.vendors.show', $v) }}" class="btn btn-sm btn-ghost" title="Mantau berkas, layanan & verifikasi vendor" style="border:1px solid #d4d4d4;border-radius:2px;padding:4px 8px;font-size:11.5px;font-weight:600;background:#ffffff;color:#111111;">
                                    <i class="bi bi-eye"></i> Detail
                                </a>

                                {{-- 2. Edit Modal --}}
                                <button type="button" class="btn btn-sm btn-ghost" title="Edit data vendor" onclick="openEditVendorModal({{ json_encode($v) }})" style="border:1px solid #d4d4d4;border-radius:2px;padding:4px 8px;font-size:11.5px;font-weight:600;background:#ffffff;color:#111111;">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>

                                {{-- 3. Toggle Akun User --}}
                                @if($v->user)
                                <form action="{{ route('admin.vendors.toggle', $v) }}" method="POST" style="margin:0;display:inline;" onsubmit="return confirm('{{ $v->user->is_active ? 'Nonaktifkan akun pemilik vendor ini?' : 'Aktifkan akun pemilik vendor ini?' }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm" title="{{ $v->user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" style="border:1px solid {{ $v->user->is_active ? '#fca5a5' : '#86efac' }};background:{{ $v->user->is_active ? '#fff1f2' : '#f0fdf4' }};color:{{ $v->user->is_active ? '#be123c' : '#15803d' }};border-radius:2px;padding:4px 7px;font-size:11px;font-weight:700;">
                                        {{ $v->user->is_active ? 'Off' : 'On' }}
                                    </button>
                                </form>
                                @endif

                                {{-- 4. Hapus Vendor --}}
                                <form action="{{ route('admin.vendors.destroy', $v) }}" method="POST" style="margin:0;display:inline;" onsubmit="return confirm('Yakin ingin MENGHAPUS vendor {{ $v->business_name }} secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm" title="Hapus Vendor" style="border:1px solid #fecaca;background:#ffffff;color:#b91c1c;border-radius:2px;padding:4px 7px;font-size:11.5px;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding:48px 16px;text-align:center;color:#888888;">
                            <div style="font-size:32px;margin-bottom:8px;"><i class="bi bi-shop"></i></div>
                            <div style="font-size:14px;font-weight:700;color:#111111;">Tidak Ada Vendor Ditemukan</div>
                            <div style="font-size:12.5px;color:#666666;margin-top:4px;">Coba gunakan filter atau kata kunci pencarian lain.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;">
            {{ $vendors->links() }}
        </div>
    </div>
</div>

{{-- MODAL EDIT VENDOR --}}
<div id="editVendorModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#ffffff;border-radius:2px;width:100%;max-width:580px;box-shadow:0 10px 30px rgba(0,0,0,0.15);overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
        <div style="padding:16px 20px;background:#fafafa;border-bottom:1px solid #e5e5e5;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <h3 style="font-size:15px;font-weight:800;color:#111111;margin:0;">
                <i class="bi bi-pencil-square"></i> Edit Data Vendor
            </h3>
            <button type="button" onclick="closeEditVendorModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#888;">&times;</button>
        </div>
        
        <form id="editVendorForm" action="" method="POST" style="padding:20px;overflow-y:auto;flex:1;">
            @csrf
            @method('PUT')

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#111111;margin-bottom:4px;">Nama Usaha / Brand <span style="color:#D81A24;">*</span></label>
                    <input type="text" id="v_business_name" name="business_name" required class="form-control" style="border-radius:2px;height:38px;font-size:13px;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#111111;margin-bottom:4px;">Kategori Usaha <span style="color:#D81A24;">*</span></label>
                    <select id="v_category_id" name="category_id" required class="form-control" style="border-radius:2px;height:38px;font-size:13px;">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#111111;margin-bottom:4px;">Nama Pemilik <span style="color:#D81A24;">*</span></label>
                    <input type="text" id="v_owner_name" name="owner_name" required class="form-control" style="border-radius:2px;height:38px;font-size:13px;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#111111;margin-bottom:4px;">Email Pemilik <span style="color:#D81A24;">*</span></label>
                    <input type="email" id="v_email" name="email" required class="form-control" style="border-radius:2px;height:38px;font-size:13px;">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#111111;margin-bottom:4px;">No. Telepon <span style="color:#D81A24;">*</span></label>
                    <input type="text" id="v_phone" name="phone" required class="form-control" style="border-radius:2px;height:38px;font-size:13px;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#111111;margin-bottom:4px;">No. WhatsApp</label>
                    <input type="text" id="v_whatsapp" name="whatsapp" class="form-control" style="border-radius:2px;height:38px;font-size:13px;">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#111111;margin-bottom:4px;">Kota</label>
                    <input type="text" id="v_city" name="city" class="form-control" style="border-radius:2px;height:38px;font-size:13px;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#111111;margin-bottom:4px;">Status Verifikasi</label>
                    <select id="v_status" name="status" required class="form-control" style="border-radius:2px;height:38px;font-size:13px;">
                        <option value="incomplete">Belum Lengkap</option>
                        <option value="pending">Menunggu Review</option>
                        <option value="needs_revision">Perlu Revisi</option>
                        <option value="active">Aktif &amp; Terverifikasi</option>
                        <option value="rejected">Ditolak</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:12px;font-weight:700;color:#111111;margin-bottom:4px;">Alamat Lengkap</label>
                <input type="text" id="v_address" name="address" class="form-control" style="border-radius:2px;height:38px;font-size:13px;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:12px;font-weight:700;color:#111111;margin-bottom:4px;">Status Akun Pemilik</label>
                <select id="v_is_active" name="is_active" class="form-control" style="border-radius:2px;height:38px;font-size:13px;">
                    <option value="1">Aktif (Dapat Login &amp; Kelola Katalog)</option>
                    <option value="0">Nonaktif (Akun Dibekukan)</option>
                </select>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:14px;border-top:1px solid #eeeeee;">
                <button type="button" onclick="closeEditVendorModal()" class="btn btn-ghost" style="border:1px solid #d4d4d4;border-radius:2px;padding:0 18px;height:38px;font-size:12.5px;font-weight:700;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="border-radius:2px;padding:0 20px;height:38px;font-size:12.5px;font-weight:700;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditVendorModal(vendor) {
    const modal = document.getElementById('editVendorModal');
    const form = document.getElementById('editVendorForm');

    form.action = '/admin/vendors/' + (vendor.slug || vendor.id);
    document.getElementById('v_business_name').value = vendor.business_name || '';
    document.getElementById('v_category_id').value = vendor.category_id || '';
    document.getElementById('v_owner_name').value = vendor.user ? (vendor.user.name || '') : '';
    document.getElementById('v_email').value = vendor.user ? (vendor.user.email || '') : '';
    document.getElementById('v_phone').value = vendor.phone || '';
    document.getElementById('v_whatsapp').value = vendor.whatsapp || '';
    document.getElementById('v_city').value = vendor.city || '';
    document.getElementById('v_address').value = vendor.address || '';
    document.getElementById('v_status').value = vendor.status || 'pending';
    document.getElementById('v_is_active').value = (vendor.user && vendor.user.is_active) ? '1' : '0';

    modal.style.display = 'flex';
}

function closeEditVendorModal() {
    document.getElementById('editVendorModal').style.display = 'none';
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('editVendorModal');
    if (e.target === modal) {
        closeEditVendorModal();
    }
});
</script>
@endpush
@endsection
