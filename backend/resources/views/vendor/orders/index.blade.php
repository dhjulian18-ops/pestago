@extends('layouts.app')
@section('title', 'Pesanan Masuk — Vendor PestaGo')

@section('content')
<div class="dashboard">
    @include('vendor._sidebar', ['active' => 'orders'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="margin-bottom:24px;">
            <h1 class="dashboard-title">Pesanan & Alur Pekerjaan</h1>
            <p class="dashboard-subtitle">Kelola pesanan pelanggan dari konfirmasi, pelaksanaan, unggah foto bukti, hingga pelepasan saldo</p>
        </div>

        {{-- Filter Tabs (Uniqlo Minimal) --}}
        <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;">
            <a href="{{ route('vendor.orders') }}" 
               class="btn btn-sm" 
               style="border-radius:2px;padding:6px 16px;font-size:0.82rem;font-weight:800;letter-spacing:0.3px;{{ !$status ? 'background:#111111;color:#ffffff;border:1px solid #111111;' : 'background:#ffffff;color:#555555;border:1px solid #e5e5e5;' }}">
               Semua
            </a>
            @php
                $tabLabels = [
                    'pending_confirmation' => 'Menunggu Konfirmasi',
                    'confirmed' => 'Dikonfirmasi',
                    'in_progress' => 'Sedang Dikerjakan',
                    'event_proof' => 'Bukti Diunggah',
                    'completed' => 'Selesai',
                    'rejected' => 'Ditolak',
                ];
            @endphp
            @foreach($tabLabels as $k => $v)
            <a href="{{ route('vendor.orders', ['status' => $k]) }}" 
               class="btn btn-sm" 
               style="border-radius:2px;padding:6px 16px;font-size:0.82rem;font-weight:800;letter-spacing:0.3px;{{ $status == $k ? 'background:#111111;color:#ffffff;border:1px solid #111111;' : 'background:#ffffff;color:#555555;border:1px solid #e5e5e5;' }}">
               {{ $v }}
            </a>
            @endforeach
        </div>

        @if($orders->count() > 0)
        <div style="display:grid;gap:16px;">
            @foreach($orders as $item)
            @php
                $order = $item->order;
                $isCash = $order && $order->isCashPayment();
            @endphp
            <div style="background:#ffffff;border:1px solid #e5e5e5;padding:20px 24px;">
                {{-- Header Item --}}
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:14px;border-bottom:1px solid #f0f0f0;padding-bottom:12px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                            <span style="font-weight:900;font-size:15px;color:#111111;">
                                Order #{{ $order->order_number ?? '-' }}
                            </span>
                            @if($isCash)
                                <span style="background:#e0f2fe;color:#0369a1;font-size:11px;font-weight:800;padding:2px 8px;border-radius:2px;text-transform:uppercase;">
                                    💵 Pembayaran Tunai
                                </span>
                            @else
                                <span style="background:#f0fdf4;color:#16a34a;font-size:11px;font-weight:800;padding:2px 8px;border-radius:2px;text-transform:uppercase;">
                                    💳 Escrow Non-Tunai
                                </span>
                            @endif
                        </div>
                        <div style="font-size:12px;color:#767676;">
                            Customer: <strong style="color:#111111;">{{ $order->user->name ?? '-' }}</strong>
                            @if(!empty($order->user->phone))
                                · <i class="bi bi-telephone"></i> {{ $order->user->phone }}
                            @endif
                            · Dipesan: {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                        </div>
                    </div>

                    <div style="text-align:right;">
                        <div style="font-size:18px;font-weight:900;color:#111111;">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                        @php 
                            $isMap = [
                                'pending_confirmation' => ['Menunggu Konfirmasi', '#f59e0b', '#fef3c7'],
                                'confirmed' => ['Dikonfirmasi', '#10b981', '#d1fae5'],
                                'rejected' => ['Ditolak', '#ef4444', '#fee2e2'],
                                'in_progress' => ['Sedang Dikerjakan', '#3b82f6', '#dbeafe'],
                                'event_proof' => ['Bukti Diunggah', '#8b5cf6', '#ede9fe'],
                                'completed' => ['Selesai', '#10b981', '#d1fae5'],
                                'cancelled' => ['Dibatalkan', '#6b7280', '#f3f4f6'],
                            ]; 
                            $st = $isMap[$item->status] ?? [$item->status, '#6b7280', '#f3f4f6']; 
                        @endphp
                        <span style="display:inline-block;padding:2px 8px;font-size:11px;font-weight:800;color:{{ $st[1] }};background:{{ $st[2] }};text-transform:uppercase;margin-top:2px;">
                            {{ $st[0] }}
                        </span>
                    </div>
                </div>

                {{-- Detail Layanan & Jadwal --}}
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;margin-bottom:14px;background:#fafafa;padding:12px 16px;border:1px solid #eeeeee;">
                    <div>
                        <div style="font-size:11px;color:#767676;font-weight:700;text-transform:uppercase;">Layanan</div>
                        <div style="font-size:14px;font-weight:800;color:#111111;margin-top:2px;">{{ $item->service_name_snapshot }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#767676;font-weight:700;text-transform:uppercase;">Tanggal Acara</div>
                        <div style="font-size:14px;font-weight:800;color:#111111;margin-top:2px;">
                            <i class="bi bi-calendar3"></i> {{ $item->booking_date ? \Carbon\Carbon::parse($item->booking_date)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </div>
                    @if($item->customer_notes)
                    <div style="grid-column:1 / -1;">
                        <div style="font-size:11px;color:#767676;font-weight:700;text-transform:uppercase;">Catatan Pelanggan</div>
                        <div style="font-size:12.5px;color:#333333;margin-top:2px;background:#ffffff;padding:6px 10px;border:1px solid #e5e5e5;">
                            {{ $item->customer_notes }}
                        </div>
                    </div>
                    @endif
                    @if($isCash && $order->cash_meeting_notes)
                    <div style="grid-column:1 / -1;">
                        <div style="font-size:11px;color:#0369a1;font-weight:700;text-transform:uppercase;">Catatan Janjian/Pembayaran Tunai</div>
                        <div style="font-size:12.5px;color:#0c4a6e;margin-top:2px;background:#f0f9ff;padding:6px 10px;border:1px solid #bae6fd;">
                            {{ $order->cash_meeting_notes }}
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Bukti Progres jika ada --}}
                @if($item->progressProofs && $item->progressProofs->count() > 0)
                <div style="margin-bottom:14px;">
                    <div style="font-size:11px;font-weight:800;color:#767676;text-transform:uppercase;margin-bottom:6px;">
                        Foto Bukti Terunggah ({{ $item->progressProofs->count() }})
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        @foreach($item->progressProofs as $proof)
                        <div style="width:70px;height:70px;border:1px solid #e5e5e5;overflow:hidden;position:relative;">
                            <a href="{{ asset('storage/' . $proof->image_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $proof->image_path) }}" alt="Bukti" style="width:100%;height:100%;object-fit:cover;">
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Action Toolbar --}}
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;padding-top:10px;border-top:1px solid #f0f0f0;">
                    <div>
                        {{-- Chat customer --}}
                        <a href="{{ route('chat.index') }}" style="display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 12px;background:#ffffff;border:1px solid #d4d4d4;color:#111111;font-size:12px;font-weight:700;text-decoration:none;">
                            <i class="bi bi-chat-dots"></i> Chat Customer
                        </a>
                    </div>

                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        {{-- Step 1: Terima / Tolak --}}
                        @if($item->status === 'pending_confirmation')
                            <form action="{{ route('vendor.orders.accept', $item) }}" method="POST" style="margin:0;" onsubmit="return confirm('Terima pesanan ini?')">
                                @csrf
                                <button type="submit" style="height:36px;padding:0 16px;background:#111111;color:#ffffff;border:none;font-size:12px;font-weight:800;cursor:pointer;">
                                    <i class="bi bi-check-lg"></i> Terima Pesanan
                                </button>
                            </form>

                            <button type="button" onclick="document.getElementById('rejectModal-{{ $item->id }}').style.display='flex'" style="height:36px;padding:0 14px;background:#ffffff;color:#dc2626;border:1px solid #fca5a5;font-size:12px;font-weight:800;cursor:pointer;">
                                <i class="bi bi-x-lg"></i> Tolak
                            </button>
                        @endif

                        {{-- Step 2: Mulai Kerjakan --}}
                        @if($item->status === 'confirmed')
                            <form action="{{ route('vendor.orders.startProgress', $item) }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" style="height:36px;padding:0 16px;background:#2563eb;color:#ffffff;border:none;font-size:12px;font-weight:800;cursor:pointer;">
                                    <i class="bi bi-play-circle"></i> Mulai Kerjakan Layanan
                                </button>
                            </form>
                        @endif

                        {{-- Step 3/4: Upload Foto Bukti & Selesaikan --}}
                        @if(in_array($item->status, ['in_progress', 'event_proof']))
                            <button type="button" onclick="document.getElementById('vendorProofModal-{{ $item->id }}').style.display='flex'" style="height:36px;padding:0 14px;background:#ffffff;border:1px solid #111111;color:#111111;font-size:12px;font-weight:800;cursor:pointer;">
                                <i class="bi bi-camera"></i> Unggah Bukti Acara
                            </button>

                            <form action="{{ route('vendor.orders.complete', $item) }}" method="POST" style="margin:0;" onsubmit="return confirm('Tandai layanan telah selesai dikerjakan?')">
                                @csrf
                                <button type="submit" style="height:36px;padding:0 16px;background:#16a34a;color:#ffffff;border:none;font-size:12px;font-weight:800;cursor:pointer;">
                                    <i class="bi bi-check2-all"></i> Selesaikan Layanan
                                </button>
                            </form>
                        @endif

                        @if($item->status === 'completed')
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <span style="font-size:12px;font-weight:800;color:#16a34a;display:inline-flex;align-items:center;gap:4px;">
                                    <i class="bi bi-check-circle-fill"></i> Layanan Telah Selesai
                                </span>
                                @if(!empty($item->customer_confirmed_at))
                                    <span style="font-size:11px;font-weight:700;color:#15803d;background:#dcfce7;border:1px solid #86efac;padding:3px 8px;">
                                        <i class="bi bi-cash-coin"></i> Saldo Sudah Dirilis
                                    </span>
                                @else
                                    <span style="font-size:11px;font-weight:700;color:#854d0e;background:#fef9c3;border:1px solid #fde047;padding:3px 8px;">
                                        <i class="bi bi-hourglass-split"></i> Menunggu Konfirmasi Customer
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Modal Tolak Pesanan --}}
                <div id="rejectModal-{{ $item->id }}" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;">
                    <div style="background:#ffffff;max-width:420px;width:100%;padding:24px;border:1px solid #111111;">
                        <h3 style="font-size:16px;font-weight:900;margin:0 0 12px;color:#111111;">Alasan Penolakan Pesanan</h3>
                        <p style="font-size:12.5px;color:#666666;margin:0 0 14px;line-height:1.5;">
                            Mohon berikan alasan penolakan agar sistem dapat merekomendasikan vendor pengganti kepada pelanggan.
                        </p>
                        <form action="{{ route('vendor.orders.reject', $item) }}" method="POST">
                            @csrf
                            <textarea name="reason" required rows="3" placeholder="Contoh: Jadwal acara sudah terisi oleh pemesan lain..." style="width:100%;padding:10px;border:1px solid #d4d4d4;font-size:13px;margin-bottom:14px;resize:vertical;"></textarea>
                            <div style="display:flex;justify-content:flex-end;gap:8px;">
                                <button type="button" onclick="document.getElementById('rejectModal-{{ $item->id }}').style.display='none'" style="height:36px;padding:0 14px;background:#f3f4f6;border:none;font-size:12px;font-weight:700;cursor:pointer;">Batal</button>
                                <button type="submit" style="height:36px;padding:0 18px;background:#dc2626;color:#ffffff;border:none;font-size:12px;font-weight:800;cursor:pointer;">Konfirmasi Tolak</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Modal Upload Proof (Vendor) --}}
                <div id="vendorProofModal-{{ $item->id }}" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;">
                    <div style="background:#ffffff;max-width:440px;width:100%;padding:24px;border:1px solid #111111;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                            <h3 style="font-size:16px;font-weight:900;margin:0;color:#111111;">Unggah Bukti Pengerjaan</h3>
                            <button type="button" onclick="document.getElementById('vendorProofModal-{{ $item->id }}').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
                        </div>
                        <form action="{{ route('vendor.orders.uploadProof', $item) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div style="margin-bottom:12px;">
                                <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Tahap Progres</label>
                                <select name="type" required style="width:100%;height:38px;border:1px solid #d4d4d4;padding:0 10px;font-size:13px;">
                                    <option value="setup">Setup & Persiapan Lokasi</option>
                                    <option value="in_progress">Sedang Berlangsung</option>
                                    <option value="completion" selected>Penyelesaian Layanan</option>
                                </select>
                            </div>
                            <div style="margin-bottom:12px;">
                                <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Foto Bukti (JPG/PNG maks 5MB)</label>
                                <input type="file" name="image" accept="image/*" required style="width:100%;font-size:13px;">
                            </div>
                            <div style="margin-bottom:16px;">
                                <label style="display:block;font-size:12px;font-weight:700;margin-bottom:4px;">Catatan (Opsional)</label>
                                <input type="text" name="caption" placeholder="Keterangan singkat..." style="width:100%;height:38px;border:1px solid #d4d4d4;padding:0 10px;font-size:13px;">
                            </div>
                            <div style="display:flex;justify-content:flex-end;gap:8px;">
                                <button type="button" onclick="document.getElementById('vendorProofModal-{{ $item->id }}').style.display='none'" style="height:36px;padding:0 14px;background:#f3f4f6;border:none;font-size:12px;font-weight:700;cursor:pointer;">Batal</button>
                                <button type="submit" style="height:36px;padding:0 18px;background:#111111;color:#ffffff;border:none;font-size:12px;font-weight:800;cursor:pointer;">Unggah Foto</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        <div style="margin-top:24px;">
            {{ $orders->links() }}
        </div>
        @else
        <div style="background:#ffffff;border:1px solid #e5e5e5;padding:56px 24px;text-align:center;">
            <i class="bi bi-inbox" style="font-size:36px;color:#9ca3af;margin-bottom:12px;display:block;"></i>
            <h3 style="font-size:16px;font-weight:900;color:#111111;margin:0 0 6px;">Tidak Ada Pesanan</h3>
            <p style="font-size:13px;color:#666666;margin:0;">
                Belum ada pesanan pada filter ini.
            </p>
        </div>
        @endif
    </div>
</div>
@endsection
