@extends('layouts.app')
@section('title', 'Manajemen Customer — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'customers'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <div>
                <h1 class="dashboard-title">Manajemen Customer</h1>
                <p class="dashboard-subtitle">Kelola dan pantau seluruh data pelanggan terdaftar di platform PestaGo</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center;">
                <span style="font-size:12px;font-weight:700;background:#ffffff;border:1px solid #e5e5e5;padding:6px 12px;border-radius:2px;color:#111111;">
                    Total: <strong>{{ $totalCustomers }}</strong> Customer
                </span>
                <span style="font-size:12px;font-weight:700;background:#f0fdf4;border:1px solid #bbf7d0;padding:6px 12px;border-radius:2px;color:#15803d;">
                    Aktif: <strong>{{ $activeCustomers }}</strong>
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

        {{-- Filters & Search --}}
        <div style="background:#ffffff;border:1px solid #e5e5e5;padding:16px;border-radius:2px;margin-bottom:20px;">
            <form action="{{ route('admin.customers.index') }}" method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="flex:1;min-width:240px;">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, atau no. telepon customer..." class="form-control" style="height:38px;font-size:13px;border-radius:2px;border-color:#d4d4d4;">
                </div>
                <div style="width:160px;">
                    <select name="status" class="form-control" style="height:38px;font-size:13px;border-radius:2px;border-color:#d4d4d4;">
                        <option value="">Semua Status</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="height:38px;padding:0 18px;border-radius:2px;font-size:12.5px;font-weight:700;">
                    <i class="bi bi-search"></i> Cari Customer
                </button>
                @if($search || $status !== '')
                <a href="{{ route('admin.customers.index') }}" class="btn btn-ghost" style="height:38px;padding:0 14px;border:1px solid #d4d4d4;border-radius:2px;font-size:12.5px;">
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
                        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Customer</th>
                        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Kontak WhatsApp / Telp</th>
                        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Aktivitas</th>
                        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Status</th>
                        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Terdaftar</th>
                        <th style="padding:12px 16px;font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$c->phone);
                        if (str_starts_with($cleanPhone, '0')) {
                            $waNumber = '62' . substr($cleanPhone, 1);
                        } elseif (str_starts_with($cleanPhone, '8')) {
                            $waNumber = '62' . $cleanPhone;
                        } else {
                            $waNumber = $cleanPhone;
                        }
                    @endphp
                    <tr style="border-bottom:1px solid #eeeeee;">
                        <td style="padding:14px 16px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                @if($c->avatar_url)
                                    <img src="{{ $c->avatar_url }}" alt="{{ $c->name }}" style="width:36px;height:36px;border-radius:2px;object-fit:cover;border:1px solid #e5e5e5;flex-shrink:0;">
                                @else
                                    <div style="width:36px;height:36px;background:#111111;color:#ffffff;border-radius:2px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;flex-shrink:0;">
                                        {{ strtoupper(substr($c->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:700;color:#111111;font-size:13.5px;">{{ $c->name }}</div>
                                    <div style="font-size:12px;color:#767676;">{{ $c->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:14px 16px;">
                            @if(!empty($c->phone))
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="font-weight:700;font-size:13px;color:#111111;font-family:monospace;">{{ $c->phone }}</span>
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" title="Chat WhatsApp" style="width:24px;height:24px;background:#25D366;color:#ffffff;border-radius:2px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;text-decoration:none;">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </div>
                            @else
                                <span style="font-size:12px;color:#999999;font-style:italic;">Belum diisi</span>
                            @endif
                        </td>
                        <td style="padding:14px 16px;">
                            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                <span style="font-size:11px;font-weight:700;background:#f1f5f9;color:#334155;padding:2px 7px;border-radius:2px;">
                                    <i class="bi bi-bag"></i> {{ $c->orders_count }} Pesanan
                                </span>
                                <span style="font-size:11px;font-weight:700;background:#f8fafc;color:#64748b;padding:2px 7px;border-radius:2px;border:1px solid #e2e8f0;">
                                    <i class="bi bi-calendar-event"></i> {{ $c->events_count }} Acara
                                </span>
                            </div>
                        </td>
                        <td style="padding:14px 16px;">
                            @if($c->is_active)
                                <span class="badge badge-success" style="font-size:11px;border-radius:2px;padding:3px 8px;">Aktif</span>
                            @else
                                <span class="badge badge-danger" style="font-size:11px;border-radius:2px;padding:3px 8px;">Nonaktif</span>
                            @endif
                        </td>
                        <td style="padding:14px 16px;font-size:12.5px;color:#666666;">
                            {{ $c->created_at->translatedFormat('d M Y') }}
                        </td>
                        <td style="padding:14px 16px;text-align:right;">
                            <div style="display:inline-flex;align-items:center;gap:6px;justify-content:flex-end;">
                                {{-- 1. Mantau / Detail --}}
                                <a href="{{ route('admin.customers.show', $c) }}" class="btn btn-sm btn-ghost" title="Mantau detail aktivitas customer" style="border:1px solid #d4d4d4;border-radius:2px;padding:4px 8px;font-size:12px;font-weight:600;color:#111111;background:#ffffff;">
                                    <i class="bi bi-eye"></i> Detail
                                </a>

                                {{-- 2. Edit (Buka Modal) --}}
                                <button type="button" class="btn btn-sm btn-ghost" title="Edit data customer" onclick="openEditCustomerModal({{ json_encode($c) }})" style="border:1px solid #d4d4d4;border-radius:2px;padding:4px 8px;font-size:12px;font-weight:600;color:#111111;background:#ffffff;">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>

                                {{-- 3. Toggle Status --}}
                                <form action="{{ route('admin.customers.toggle', $c) }}" method="POST" style="margin:0;display:inline;" onsubmit="return confirm('{{ $c->is_active ? 'Nonaktifkan akun customer ini?' : 'Aktifkan akun customer ini?' }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm" title="{{ $c->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" style="border:1px solid {{ $c->is_active ? '#fca5a5' : '#86efac' }};background:{{ $c->is_active ? '#fff1f2' : '#f0fdf4' }};color:{{ $c->is_active ? '#be123c' : '#15803d' }};border-radius:2px;padding:4px 8px;font-size:12px;font-weight:700;">
                                        {{ $c->is_active ? 'Off' : 'On' }}
                                    </button>
                                </form>

                                {{-- 4. Hapus --}}
                                <form action="{{ route('admin.customers.destroy', $c) }}" method="POST" style="margin:0;display:inline;" onsubmit="return confirm('Yakin ingin MENGHAPUS customer {{ $c->name }} secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm" title="Hapus Customer" style="border:1px solid #fecaca;background:#ffffff;color:#b91c1c;border-radius:2px;padding:4px 8px;font-size:12px;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:48px 16px;text-align:center;color:#888888;">
                            <div style="font-size:32px;margin-bottom:8px;"><i class="bi bi-people"></i></div>
                            <div style="font-size:14px;font-weight:700;color:#111111;">Tidak Ada Customer Ditemukan</div>
                            <div style="font-size:12.5px;color:#666666;margin-top:4px;">Coba gunakan kata kunci pencarian yang berbeda.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;">
            {{ $customers->links() }}
        </div>
    </div>
</div>

{{-- MODAL EDIT CUSTOMER --}}
<div id="editCustomerModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#ffffff;border-radius:2px;width:100%;max-width:520px;box-shadow:0 10px 30px rgba(0,0,0,0.15);overflow:hidden;animation:fadeIn 0.15s ease;">
        <div style="padding:16px 20px;background:#fafafa;border-bottom:1px solid #e5e5e5;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-size:15px;font-weight:800;color:#111111;margin:0;">
                <i class="bi bi-pencil-square"></i> Edit Data Customer
            </h3>
            <button type="button" onclick="closeEditCustomerModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#888;">&times;</button>
        </div>
        
        <form id="editCustomerForm" action="" method="POST" style="padding:20px;">
            @csrf
            @method('PUT')

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12.5px;font-weight:700;color:#111111;margin-bottom:6px;">Nama Lengkap <span style="color:#D81A24;">*</span></label>
                <input type="text" id="edit_name" name="name" required class="form-control" style="border-radius:2px;height:40px;font-size:13.5px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12.5px;font-weight:700;color:#111111;margin-bottom:6px;">Alamat Email <span style="color:#D81A24;">*</span></label>
                <input type="email" id="edit_email" name="email" required class="form-control" style="border-radius:2px;height:40px;font-size:13.5px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12.5px;font-weight:700;color:#111111;margin-bottom:6px;">No. WhatsApp / Telepon <span style="color:#D81A24;">*</span></label>
                <input type="text" id="edit_phone" name="phone" required placeholder="cth: 081234567890" class="form-control" style="border-radius:2px;height:40px;font-size:13.5px;">
                <span style="font-size:11.5px;color:#767676;margin-top:4px;display:block;">Nomor ini digunakan customer untuk menerima kode OTP WhatsApp.</span>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12.5px;font-weight:700;color:#111111;margin-bottom:6px;">Status Akun</label>
                <select id="edit_is_active" name="is_active" class="form-control" style="border-radius:2px;height:40px;font-size:13.5px;">
                    <option value="1">Aktif (Dapat Login &amp; Buat Pesanan)</option>
                    <option value="0">Nonaktif (Akun Dibekukan)</option>
                </select>
            </div>

            <div style="margin-bottom:20px;padding-top:14px;border-top:1px dashed #e5e5e5;">
                <label style="display:block;font-size:12.5px;font-weight:700;color:#111111;margin-bottom:4px;">Reset Password Baru (Opsional)</label>
                <span style="font-size:11.5px;color:#767676;margin-bottom:6px;display:block;">Kosongkan jika tidak ingin mengubah password customer.</span>
                <input type="password" id="edit_new_password" name="new_password" placeholder="Minimal 8 karakter" class="form-control" style="border-radius:2px;height:40px;font-size:13px;">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:14px;border-top:1px solid #eeeeee;">
                <button type="button" onclick="closeEditCustomerModal()" class="btn btn-ghost" style="border:1px solid #d4d4d4;border-radius:2px;padding:0 18px;height:38px;font-size:12.5px;font-weight:700;">
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
function openEditCustomerModal(customer) {
    const modal = document.getElementById('editCustomerModal');
    const form = document.getElementById('editCustomerForm');
    
    form.action = '/admin/customers/' + customer.id;
    document.getElementById('edit_name').value = customer.name || '';
    document.getElementById('edit_email').value = customer.email || '';
    document.getElementById('edit_phone').value = customer.phone || '';
    document.getElementById('edit_is_active').value = customer.is_active ? '1' : '0';
    document.getElementById('edit_new_password').value = '';

    modal.style.display = 'flex';
}

function closeEditCustomerModal() {
    document.getElementById('editCustomerModal').style.display = 'none';
}

// Close on click outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('editCustomerModal');
    if (e.target === modal) {
        closeEditCustomerModal();
    }
});
</script>
@endpush
@endsection

