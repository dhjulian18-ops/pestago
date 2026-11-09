@extends('layouts.app')
@section('title', 'Detail Customer: ' . $customer->name . ' — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'customers'])

    <div class="dashboard-content">
        {{-- Header --}}
        <div class="dashboard-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                    <a href="{{ route('admin.customers.index') }}" style="color:#666666;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Customer
                    </a>
                </div>
                <div style="display:flex;align-items:center;gap:14px;margin-top:6px;">
                    @if($customer->avatar_url)
                        <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" style="width:52px;height:52px;border-radius:2px;object-fit:cover;border:1px solid #e5e5e5;flex-shrink:0;">
                    @else
                        <div style="width:52px;height:52px;background:#111111;color:#ffffff;border-radius:2px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:20px;flex-shrink:0;">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="dashboard-title" style="margin:0;">{{ $customer->name }}</h1>
                        <p class="dashboard-subtitle" style="margin-top:2px;">
                            Status Akun:
                            @if($customer->is_active)
                                <span class="badge badge-success" style="font-size:11px;border-radius:2px;padding:3px 8px;">Aktif</span>
                            @else
                                <span class="badge badge-danger" style="font-size:11px;border-radius:2px;padding:3px 8px;">Nonaktif</span>
                            @endif
                            &bull; Terdaftar sejak {{ $customer->created_at->translatedFormat('d F Y, H:i') }}
                        </p>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <button type="button" class="btn btn-ghost" onclick="openEditCustomerModal({{ json_encode($customer) }})" style="border:1px solid #d4d4d4;border-radius:2px;height:38px;padding:0 16px;font-size:12.5px;font-weight:700;color:#111111;background:#ffffff;">
                    <i class="bi bi-pencil"></i> Edit Profil
                </button>
                <form action="{{ route('admin.customers.toggle', $customer) }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn" style="border:1px solid {{ $customer->is_active ? '#fca5a5' : '#86efac' }};background:{{ $customer->is_active ? '#fff1f2' : '#f0fdf4' }};color:{{ $customer->is_active ? '#be123c' : '#15803d' }};border-radius:2px;height:38px;padding:0 16px;font-size:12.5px;font-weight:700;">
                        {{ $customer->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:2px;margin-bottom:18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        {{-- Top Summary Grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:14px;margin-bottom:24px;">
            {{-- Stat 1: Total Orders --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;padding:18px 20px;">
                <div style="font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">
                    Total Pesanan
                </div>
                <div style="font-size:24px;font-weight:800;color:#111111;line-height:1.1;">
                    {{ $customer->orders_count }}
                </div>
            </div>

            {{-- Stat 2: Total Spent --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;padding:18px 20px;">
                <div style="font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">
                    Total Belanja (Berhasil)
                </div>
                <div style="font-size:22px;font-weight:800;color:#15803d;line-height:1.1;">
                    Rp {{ number_format($totalSpent, 0, ',', '.') }}
                </div>
            </div>

            {{-- Stat 3: Total Events --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;padding:18px 20px;">
                <div style="font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">
                    Total Acara Dibuat
                </div>
                <div style="font-size:24px;font-weight:800;color:#111111;line-height:1.1;">
                    {{ $customer->events_count }}
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;align-items:flex-start;">
            {{-- Left: Informasi Kontak Customer --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;">
                <div style="padding:14px 18px;background:#fafafa;border-bottom:1px solid #e5e5e5;font-size:13px;font-weight:800;color:#111111;">
                    <i class="bi bi-person-lines-fill"></i> Informasi Kontak
                </div>
                <div style="padding:18px;display:flex;flex-direction:column;gap:14px;font-size:13px;">
                    <div>
                        <div style="font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Nama Lengkap</div>
                        <div style="font-weight:700;color:#111111;margin-top:2px;">{{ $customer->name }}</div>
                    </div>

                    <div>
                        <div style="font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Alamat Email</div>
                        <div style="font-weight:600;color:#111111;margin-top:2px;">
                            <a href="mailto:{{ $customer->email }}" style="color:#111111;text-decoration:none;">{{ $customer->email }}</a>
                        </div>
                    </div>

                    <div>
                        <div style="font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">No. WhatsApp / Telepon</div>
                        <div style="margin-top:4px;">
                            @if(!empty($customer->phone))
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', (string)$customer->phone);
                                    if (str_starts_with($cleanPhone, '0')) $waNumber = '62' . substr($cleanPhone, 1);
                                    elseif (str_starts_with($cleanPhone, '8')) $waNumber = '62' . $cleanPhone;
                                    else $waNumber = $cleanPhone;
                                @endphp
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <strong style="font-family:monospace;font-size:14px;">{{ $customer->phone }}</strong>
                                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="btn btn-sm" style="background:#25D366;color:#ffffff;padding:3px 10px;font-size:11px;font-weight:700;border-radius:2px;display:inline-flex;align-items:center;gap:5px;text-decoration:none;">
                                        <i class="bi bi-whatsapp"></i> Chat WhatsApp
                                    </a>
                                </div>
                            @else
                                <span style="color:#999;font-style:italic;">Belum ditambahkan</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div style="font-size:11px;font-weight:700;color:#767676;text-transform:uppercase;">Tanggal Pendaftaran</div>
                        <div style="color:#444444;margin-top:2px;">{{ $customer->created_at->translatedFormat('d F Y, H:i') }} WIB</div>
                    </div>
                </div>
            </div>

            {{-- Right: Riwayat Pesanan & Acara --}}
            <div style="display:flex;flex-direction:column;gap:24px;">
                {{-- Riwayat Pesanan --}}
                <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;">
                    <div style="padding:14px 18px;background:#fafafa;border-bottom:1px solid #e5e5e5;display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:13px;font-weight:800;color:#111111;">
                            <i class="bi bi-bag-check"></i> Riwayat Pesanan ({{ $orders->total() }})
                        </span>
                    </div>

                    <div class="table-wrapper" style="overflow-x:auto;">
                        <table class="table" style="width:100%;border-collapse:collapse;margin:0;">
                            <thead>
                                <tr style="background:#fafafa;border-bottom:1px solid #e5e5e5;text-align:left;">
                                    <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#767676;">No. Order</th>
                                    <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#767676;">Tanggal</th>
                                    <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#767676;">Total</th>
                                    <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#767676;">Status</th>
                                    <th style="padding:10px 14px;font-size:11px;font-weight:700;color:#767676;text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $o)
                                <tr style="border-bottom:1px solid #eeeeee;">
                                    <td style="padding:12px 14px;font-weight:700;color:#111111;">{{ $o->order_number }}</td>
                                    <td style="padding:12px 14px;font-size:12px;color:#666666;">{{ $o->created_at->translatedFormat('d M Y') }}</td>
                                    <td style="padding:12px 14px;font-weight:700;">Rp {{ number_format($o->total_amount, 0, ',', '.') }}</td>
                                    <td style="padding:12px 14px;">
                                        <span class="badge badge-info" style="font-size:10.5px;border-radius:2px;">{{ $o->status }}</span>
                                    </td>
                                    <td style="padding:12px 14px;text-align:right;">
                                        <a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-ghost" style="border:1px solid #d4d4d4;border-radius:2px;padding:3px 8px;font-size:11.5px;">
                                            Detail Order
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" style="padding:24px;text-align:center;color:#888888;font-size:12.5px;">
                                        Customer ini belum pernah membuat pesanan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Acara yang Dibuat --}}
                <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;">
                    <div style="padding:14px 18px;background:#fafafa;border-bottom:1px solid #e5e5e5;">
                        <span style="font-size:13px;font-weight:800;color:#111111;">
                            <i class="bi bi-calendar-event"></i> Acara yang Dibuat ({{ $events->count() }})
                        </span>
                    </div>

                    <div style="padding:16px;">
                        @forelse($events as $e)
                        <div style="padding:12px 14px;border:1px solid #eeeeee;border-radius:2px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;background:#fcfcfc;">
                            <div>
                                <div style="font-weight:700;color:#111111;font-size:13.5px;">{{ $e->name }}</div>
                                <div style="font-size:12px;color:#666666;margin-top:2px;">
                                    Tipe: <strong>{{ $e->event_type }}</strong> &bull; Tanggal: {{ $e->event_date?->translatedFormat('d M Y') ?: '-' }} &bull; Lokasi: {{ $e->location_city ?: '-' }}
                                </div>
                            </div>
                            <span class="badge badge-primary" style="font-size:10px;border-radius:2px;">{{ $e->event_type }}</span>
                        </div>
                        @empty
                        <div style="text-align:center;color:#888888;font-size:12.5px;padding:16px;">
                            Customer ini belum membuat acara.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT CUSTOMER --}}
<div id="editCustomerModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#ffffff;border-radius:2px;width:100%;max-width:520px;box-shadow:0 10px 30px rgba(0,0,0,0.15);overflow:hidden;">
        <div style="padding:16px 20px;background:#fafafa;border-bottom:1px solid #e5e5e5;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-size:15px;font-weight:800;color:#111111;margin:0;">
                <i class="bi bi-pencil-square"></i> Edit Profil Customer
            </h3>
            <button type="button" onclick="closeEditCustomerModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#888;">&times;</button>
        </div>
        
        <form id="editCustomerForm" action="{{ route('admin.customers.update', $customer) }}" method="POST" style="padding:20px;">
            @csrf
            @method('PUT')

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12.5px;font-weight:700;color:#111111;margin-bottom:6px;">Nama Lengkap <span style="color:#D81A24;">*</span></label>
                <input type="text" id="edit_name" name="name" value="{{ $customer->name }}" required class="form-control" style="border-radius:2px;height:40px;font-size:13.5px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12.5px;font-weight:700;color:#111111;margin-bottom:6px;">Alamat Email <span style="color:#D81A24;">*</span></label>
                <input type="email" id="edit_email" name="email" value="{{ $customer->email }}" required class="form-control" style="border-radius:2px;height:40px;font-size:13.5px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12.5px;font-weight:700;color:#111111;margin-bottom:6px;">No. WhatsApp / Telepon <span style="color:#D81A24;">*</span></label>
                <input type="text" id="edit_phone" name="phone" value="{{ $customer->phone }}" required class="form-control" style="border-radius:2px;height:40px;font-size:13.5px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12.5px;font-weight:700;color:#111111;margin-bottom:6px;">Status Akun</label>
                <select id="edit_is_active" name="is_active" class="form-control" style="border-radius:2px;height:40px;font-size:13.5px;">
                    <option value="1" {{ $customer->is_active ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ !$customer->is_active ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div style="margin-bottom:20px;padding-top:14px;border-top:1px dashed #e5e5e5;">
                <label style="display:block;font-size:12.5px;font-weight:700;color:#111111;margin-bottom:4px;">Ganti Password Baru (Opsional)</label>
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
function openEditCustomerModal() {
    document.getElementById('editCustomerModal').style.display = 'flex';
}
function closeEditCustomerModal() {
    document.getElementById('editCustomerModal').style.display = 'none';
}
</script>
@endpush
@endsection

