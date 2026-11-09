@extends('layouts.app')
@section('title', 'Detail Pesanan #' . $order->order_number . ' — PestaGo')

@section('content')
<div class="uq-order-detail-page" style="padding-top:calc(var(--navbar-height, 128px) + 24px);padding-bottom:80px;background:#fafafa;min-height:100vh;">
    <div class="container" style="max-width:1040px;padding:0 20px;">

        {{-- Top Navigation & Action Bar (UNIQLO Clean) --}}
        <div style="margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;border-bottom:1px solid #e5e5e5;padding-bottom:16px;">
            <a href="{{ route('customer.orders.index') }}" style="display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:800;letter-spacing:0.06em;text-transform:uppercase;color:#111111;text-decoration:none;transition:color 0.2s ease;">
                <i class="bi bi-arrow-left" style="font-size:14px;"></i>
                <span>Kembali ke Riwayat Pesanan</span>
            </a>

            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <a href="{{ route('customer.orders.invoice', $order) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;height:38px;padding:0 18px;background:#ffffff;border:1px solid #111111;color:#111111;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;text-decoration:none;transition:all 0.2s ease;">
                    <i class="bi bi-receipt"></i> Lihat Invoice
                </a>
                @if($order->status === 'pending_payment')
                    <a href="{{ route('customer.orders.payment', $order) }}" style="display:inline-flex;align-items:center;gap:6px;height:38px;padding:0 22px;background:#ed1d24;border:1px solid #ed1d24;color:#ffffff;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;text-decoration:none;transition:all 0.2s ease;">
                        <i class="bi bi-credit-card"></i> Bayar Sekarang
                    </a>
                @endif
            </div>
        </div>

        {{-- Order Header Box (UNIQLO Pure Sharp Clean) --}}
        <div style="background:#ffffff;border:1px solid #e5e5e5;padding:24px 28px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
                <div>
                    <div style="font-size:11px;font-weight:800;letter-spacing:0.1em;text-transform:uppercase;color:#888888;margin-bottom:6px;">
                        DETAIL TRANSAKSI PESANAN
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <h1 style="font-size:22px;font-weight:900;color:#111111;margin:0;letter-spacing:-0.02em;">
                            Order #{{ $order->order_number }}
                        </h1>
                        @if($order->event)
                            <span style="font-size:11px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;color:#111111;background:#f5f5f5;border:1px solid #e5e5e5;padding:3px 10px;display:inline-flex;align-items:center;gap:5px;">
                                <i class="bi bi-calendar-event"></i> {{ $order->event->name }}
                            </span>
                        @endif
                    </div>
                    <div style="font-size:12.5px;color:#666666;margin-top:8px;display:flex;align-items:center;gap:6px;">
                        <i class="bi bi-clock"></i> Dibuat pada {{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB
                    </div>
                </div>

                @php
                    $isPaid = $order->status !== 'pending_payment' && ($order->payment?->status === 'paid' || in_array($order->status, ['paid', 'confirmed', 'completed']));
                @endphp
                <div>
                    @if($isPaid)
                        <span style="display:inline-flex;align-items:center;gap:6px;background:#111111;color:#ffffff;padding:6px 14px;font-size:11px;font-weight:800;letter-spacing:0.06em;text-transform:uppercase;">
                            <i class="bi bi-check2"></i> Pembayaran Lunas
                        </span>
                    @else
                        <span style="display:inline-flex;align-items:center;gap:6px;background:#fff8e6;color:#92400e;padding:6px 14px;font-size:11px;font-weight:800;letter-spacing:0.06em;text-transform:uppercase;border:1px solid #fde68a;">
                            <i class="bi bi-hourglass-split"></i> Menunggu Pembayaran
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Payment Protection Notice (UNIQLO Minimalist Callout) --}}
        @if($order->payment_type === 'non_tunai')
        <div style="background:#ffffff;border:1px solid #e5e5e5;border-left:4px solid #111111;padding:18px 22px;margin-bottom:20px;display:flex;align-items:flex-start;gap:14px;">
            <div style="color:#111111;font-size:20px;line-height:1;margin-top:2px;flex-shrink:0;">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <div style="font-weight:800;font-size:13px;letter-spacing:0.04em;text-transform:uppercase;color:#111111;">
                    Pembayaran Non-Tunai Dilindungi PestaGo Escrow
                </div>
                <div style="font-size:12.5px;color:#666666;line-height:1.55;margin-top:4px;">
                    Dana Anda tertahan aman di sistem rekber resmi PestaGo. Pembayaran baru akan diteruskan ke vendor setelah seluruh rangkaian acara selesai dan Anda mengonfirmasi penyelesaian layanan.
                </div>
            </div>
        </div>
        @else
        <div style="background:#ffffff;border:1px solid #e5e5e5;border-left:4px solid #ed1d24;padding:18px 22px;margin-bottom:20px;display:flex;align-items:flex-start;gap:14px;">
            <div style="color:#ed1d24;font-size:20px;line-height:1;margin-top:2px;flex-shrink:0;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div style="font-weight:800;font-size:13px;letter-spacing:0.04em;text-transform:uppercase;color:#111111;">
                    Pembayaran Tunai Langsung (Cash on Meeting / On-Site)
                </div>
                <div style="font-size:12.5px;color:#666666;line-height:1.55;margin-top:4px;">
                    Pembayaran dilakukan langsung ke masing-masing vendor sesuai kesepakatan pertemuan atau saat pelaksanaan acara.
                </div>
                @if($order->cash_meeting_notes)
                <div style="font-size:12px;color:#111111;margin-top:8px;padding:8px 12px;background:#fafafa;border:1px solid #eeeeee;">
                    <strong>Catatan Pertemuan:</strong> {{ $order->cash_meeting_notes }}
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Main Section: Order Items & Progress Trackers --}}
        <div style="background:#ffffff;border:1px solid #e5e5e5;padding:26px 28px;margin-bottom:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;padding-bottom:14px;border-bottom:1px solid #f0f0f0;">
                <h2 style="font-size:14px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;color:#111111;margin:0;display:flex;align-items:center;gap:8px;">
                    <i class="bi bi-box-seam" style="color:#ed1d24;"></i> Layanan Dipesan & Alur Pelaksanaan ({{ $order->items->count() }})
                </h2>
            </div>

            <div style="display:flex;flex-direction:column;gap:20px;">
                @foreach($order->items as $item)
                @php
                    $stepIndex = 1;
                    if (in_array($item->status, ['confirmed'])) $stepIndex = 2;
                    if ($item->status === 'in_progress') $stepIndex = 3;
                    if ($item->status === 'event_proof') $stepIndex = 4;
                    if ($item->status === 'completed') $stepIndex = 5;
                    if ($item->status === 'rejected') $stepIndex = -1;
                    if ($item->status === 'cancelled') $stepIndex = 0;

                    $stMap = [
                        'pending_confirmation' => ['Menunggu Vendor', '#854d0e', '#fef9c3', 'bi-hourglass-split'],
                        'confirmed' => ['Dikonfirmasi Vendor', '#111111', '#f5f5f5', 'bi-check2-circle'],
                        'rejected' => ['Ditolak Vendor', '#b91c1c', '#fee2e2', 'bi-x-circle'],
                        'in_progress' => ['Sedang Dikerjakan', '#111111', '#f5f5f5', 'bi-gear-wide-connected'],
                        'event_proof' => ['Bukti Diunggah', '#111111', '#f5f5f5', 'bi-camera'],
                        'completed' => ['Acara Selesai', '#ffffff', '#111111', 'bi-patch-check-fill'],
                        'cancelled' => ['Dibatalkan', '#666666', '#f5f5f5', 'bi-slash-circle'],
                    ];
                    $st = $stMap[$item->status] ?? [$item->status, '#666666', '#f5f5f5', 'bi-info-circle'];
                @endphp

                <div style="border:1px solid #e5e5e5;padding:22px;background:#ffffff;position:relative;">
                    {{-- Item Header --}}
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;margin-bottom:18px;">
                        <div style="flex:1;min-width:260px;">
                            <div style="font-size:16px;font-weight:900;color:#111111;letter-spacing:-0.01em;">
                                {{ $item->service_name_snapshot }}
                            </div>
                            <div style="font-size:12.5px;color:#666666;margin-top:6px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                <span><i class="bi bi-shop"></i> Vendor: <strong style="color:#111111;">{{ $item->vendorProfile->business_name ?? '-' }}</strong></span>
                                @if($item->booking_date)
                                    <span>·</span>
                                    <span><i class="bi bi-calendar3"></i> Jadwal: <strong style="color:#111111;">{{ \Carbon\Carbon::parse($item->booking_date)->translatedFormat('d M Y') }}</strong></span>
                                @endif
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:18px;font-weight:900;color:#111111;letter-spacing:-0.02em;">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                            <div style="margin-top:6px;">
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border:1px solid #e5e5e5;font-size:10.5px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;color:{{ $st[1] }};background:{{ $st[2] }};">
                                    <i class="bi {{ $st[3] }}"></i> {{ $st[0] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- UNIQLO SEGMENTED STEP TRACKER (Clean, Minimal, Modern) --}}
                    @if($stepIndex > 0)
                    <div style="background:#fafafa;border:1px solid #eeeeee;padding:18px 20px 16px;margin-bottom:18px;">
                        <div style="font-size:10.5px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;color:#888888;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
                            <i class="bi bi-activity"></i> Status Alur Pengerjaan
                        </div>

                        {{-- 5-Segmented Bar Tracker --}}
                        <div style="display:grid;grid-template-columns:repeat(5, 1fr);gap:6px;">
                            @php
                                $steps = [
                                    1 => 'Order Dibuat',
                                    2 => 'Dikonfirmasi',
                                    3 => 'Dikerjakan',
                                    4 => 'Foto Bukti',
                                    5 => 'Selesai'
                                ];
                            @endphp
                            @foreach($steps as $sNum => $sLabel)
                            @php
                                $isPast = $stepIndex > $sNum;
                                $isCurrent = $stepIndex === $sNum;
                                $isCompleted = $sNum === 5 && $stepIndex >= 5;
                                $barColor = ($isCurrent || $isPast || $isCompleted) ? '#111111' : '#e5e5e5';
                                if ($isCurrent) $barColor = '#ed1d24';
                            @endphp
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                {{-- Segment Bar --}}
                                <div style="height:3px;background:{{ $barColor }};width:100%;transition:background 0.3s ease;"></div>
                                {{-- Number & Label --}}
                                <div style="padding-top:2px;">
                                    <span style="font-size:10px;font-weight:900;letter-spacing:0.05em;color:{{ ($isCurrent || $isPast || $isCompleted) ? ($isCurrent ? '#ed1d24' : '#111111') : '#999999' }};display:block;">
                                        0{{ $sNum }}
                                    </span>
                                    <span style="font-size:11px;font-weight:{{ ($isCurrent || $isPast || $isCompleted) ? '800' : '500' }};color:{{ ($isCurrent || $isPast || $isCompleted) ? '#111111' : '#888888' }};line-height:1.2;display:block;margin-top:2px;word-break:break-word;">
                                        {{ $sLabel }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Rejected Alert & Replacement Choice --}}
                    @if($item->status === 'rejected')
                    <div style="background:#fff8f8;border:1px solid #fecaca;border-left:4px solid #ed1d24;padding:16px 18px;margin-bottom:18px;">
                        <div style="display:flex;align-items:center;gap:8px;color:#ed1d24;font-weight:800;font-size:13px;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:4px;">
                            <i class="bi bi-x-circle-fill"></i> Vendor Menolak Pesanan Ini
                        </div>
                        <div style="font-size:12.5px;color:#666666;margin-bottom:14px;">
                            Alasan: <em style="color:#111111;">"{{ $item->rejection_reason ?: 'Jadwal bentrok / kapasitas penuh' }}"</em>
                        </div>

                        {{-- Choose Replacement Form --}}
                        @if(isset($alternatives) && $alternatives->count() > 0)
                        <div style="background:#ffffff;border:1px solid #e5e5e5;padding:14px;margin-bottom:12px;">
                            <div style="font-size:11px;font-weight:800;letter-spacing:0.06em;color:#111111;text-transform:uppercase;margin-bottom:8px;">
                                <i class="bi bi-arrow-repeat"></i> Pilih Vendor Pengganti
                            </div>
                            <form action="{{ route('customer.orders.chooseReplacement', $item) }}" method="POST" style="margin:0;">
                                @csrf
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <select name="replacement_service_id" required style="flex:1;height:38px;border:1px solid #cccccc;padding:0 12px;font-size:12.5px;outline:none;">
                                        <option value="">-- Pilih Rekomendasi Vendor Pengganti --</option>
                                        @foreach($alternatives as $alt)
                                            <option value="{{ $alt->id }}">
                                                {{ $alt->vendorProfile->business_name ?? 'Vendor' }} - {{ $alt->name }} (Rp {{ number_format($alt->price, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" style="height:38px;padding:0 18px;background:#111111;color:#ffffff;border:none;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;cursor:pointer;">
                                        Ganti Vendor
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        <div>
                            <form action="{{ route('customer.orders.requestRefund', $item) }}" method="POST" style="display:inline;" onsubmit="return confirm('Ajukan pengembalian dana untuk layanan ini?')">
                                @csrf
                                <input type="hidden" name="reason" value="Vendor menolak pesanan: {{ $item->rejection_reason }}">
                                <button type="submit" style="background:#ed1d24;color:#ffffff;border:none;padding:9px 18px;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                                    <i class="bi bi-wallet2"></i> Ajukan Refund (Rp {{ number_format($item->subtotal, 0, ',', '.') }})
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    {{-- Progress Proofs Display --}}
                    @if($item->progressProofs && $item->progressProofs->count() > 0)
                    <div style="margin-bottom:18px;">
                        <div style="font-size:11px;font-weight:800;letter-spacing:0.06em;color:#111111;text-transform:uppercase;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                            <i class="bi bi-images" style="color:#ed1d24;"></i> Foto Bukti Pelaksanaan ({{ $item->progressProofs->count() }})
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(110px, 1fr));gap:10px;">
                            @foreach($item->progressProofs as $proof)
                            <div style="border:1px solid #e5e5e5;background:#fafafa;padding:6px;">
                                <a href="{{ asset('storage/' . $proof->image_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $proof->image_path) }}" alt="Bukti" style="width:100%;aspect-ratio:1;object-fit:cover;display:block;">
                                </a>
                                <div style="font-size:10px;font-weight:800;letter-spacing:0.04em;color:#111111;margin-top:6px;text-transform:uppercase;">
                                    {{ $proof->type }}
                                </div>
                                @if($proof->caption)
                                    <div style="font-size:10px;color:#666666;line-height:1.2;margin-top:2px;max-height:26px;overflow:hidden;">
                                        {{ $proof->caption }}
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Customer Actions for this item (UNIQLO Clean Rectangular Buttons) --}}
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding-top:14px;border-top:1px solid #f0f0f0;">
                        {{-- Chat vendor --}}
                        @if($item->vendorProfile)
                        <form action="{{ route('chat.start') }}" method="POST" style="margin:0;">
                            @csrf
                            <input type="hidden" name="vendor_profile_id" value="{{ $item->vendor_profile_id }}">
                            <input type="hidden" name="message" value="Halo {{ $item->vendorProfile->business_name }}, saya ingin koordinasi mengenai pesanan #{{ $order->order_number }} untuk {{ $item->service_name_snapshot }}.">
                            <button type="submit" style="height:36px;padding:0 18px;background:#ffffff;border:1px solid #111111;color:#111111;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s ease;">
                                <i class="bi bi-chat-dots"></i> Chat Vendor
                            </button>
                        </form>
                        @endif

                        {{-- Customer can upload progress photo --}}
                        @if(in_array($item->status, ['in_progress', 'event_proof', 'confirmed']))
                        <button type="button" onclick="document.getElementById('uploadProofModal-{{ $item->id }}').style.display='flex'" style="height:36px;padding:0 18px;background:#ffffff;border:1px solid #111111;color:#111111;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                            <i class="bi bi-camera"></i> Unggah Foto Bukti
                        </button>
                        @endif

                        {{-- Customer Confirm Complete Button --}}
                        @if((in_array($item->status, ['in_progress', 'event_proof']) || $item->status === 'completed') && empty($item->customer_confirmed_at))
                        <form action="{{ route('customer.orders.confirmComplete', $item) }}" method="POST" style="margin:0;" onsubmit="return confirm('Konfirmasi bahwa acara telah selesai dan layanan telah dipenuhi dengan baik? Saldo vendor akan dirilis.')">
                            @csrf
                            <button type="submit" style="height:36px;padding:0 20px;background:#ed1d24;border:1px solid #ed1d24;color:#ffffff;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                                <i class="bi bi-check2-circle"></i> Konfirmasi Acara Selesai
                            </button>
                        </form>
                        @endif

                        @if(!empty($item->customer_confirmed_at))
                        <span style="font-size:11px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;color:#16a34a;display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:#f0fdf4;border:1px solid #bbf7d0;">
                            <i class="bi bi-check-all"></i> Acara Dikonfirmasi Selesai (Dana Dirilis)
                        </span>
                        @endif

                        {{-- Review --}}
                        @if($item->status === 'completed')
                            @if($item->review)
                                <span style="font-size:11px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;color:#111111;display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:#f5f5f5;border:1px solid #e5e5e5;">
                                    <i class="bi bi-star-fill" style="color:#f59e0b;"></i> Sudah Diulas ({{ $item->review->rating }}/5)
                                </span>
                            @else
                                <a href="{{ route('customer.reviews.create', $item) }}" style="display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 18px;background:#111111;border:1px solid #111111;color:#ffffff;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;text-decoration:none;">
                                    <i class="bi bi-star"></i> Beri Rating & Ulasan
                                </a>
                            @endif
                        @endif
                    </div>

                    {{-- Modal Upload Proof for this item --}}
                    <div id="uploadProofModal-{{ $item->id }}" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;padding:20px;">
                        <div style="background:#ffffff;max-width:440px;width:100%;padding:26px;border:1px solid #111111;box-shadow:0 10px 30px rgba(0,0,0,0.15);">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;border-bottom:1px solid #eeeeee;padding-bottom:10px;">
                                <h3 style="font-size:14px;font-weight:900;letter-spacing:0.06em;text-transform:uppercase;margin:0;color:#111111;">Unggah Bukti Progres</h3>
                                <button type="button" onclick="document.getElementById('uploadProofModal-{{ $item->id }}').style.display='none'" style="background:none;border:none;font-size:22px;color:#999999;cursor:pointer;line-height:1;">&times;</button>
                            </div>
                            <form action="{{ route('customer.orders.uploadProof', $item) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div style="margin-bottom:14px;">
                                    <label style="display:block;font-size:11.5px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;color:#111111;">Tahap Pelaksanaan</label>
                                    <select name="type" required style="width:100%;height:40px;border:1px solid #cccccc;padding:0 12px;font-size:13px;outline:none;">
                                        <option value="setup">Persiapan / Setup di Lokasi</option>
                                        <option value="in_progress">Sedang Berlangsung</option>
                                        <option value="completion" selected>Selesai Pelaksanaan</option>
                                    </select>
                                </div>
                                <div style="margin-bottom:14px;">
                                    <label style="display:block;font-size:11.5px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;color:#111111;">Foto Bukti (JPG/PNG, maks 5MB)</label>
                                    <input type="file" name="image" accept="image/*" required style="width:100%;font-size:13px;">
                                </div>
                                <div style="margin-bottom:18px;">
                                    <label style="display:block;font-size:11.5px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:6px;color:#111111;">Catatan Keterangan (Opsional)</label>
                                    <input type="text" name="caption" placeholder="Contoh: Meja catering sudah tertata rapi..." style="width:100%;height:40px;border:1px solid #cccccc;padding:0 12px;font-size:13px;outline:none;">
                                </div>
                                <div style="display:flex;justify-content:flex-end;gap:10px;">
                                    <button type="button" onclick="document.getElementById('uploadProofModal-{{ $item->id }}').style.display='none'" style="height:38px;padding:0 18px;background:#f5f5f5;border:1px solid #e5e5e5;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;color:#666666;cursor:pointer;">Batal</button>
                                    <button type="submit" style="height:38px;padding:0 22px;background:#111111;color:#ffffff;border:1px solid #111111;font-size:11.5px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;cursor:pointer;">Upload Foto</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>

            {{-- UNIQLO Minimalist Receipt Breakdown --}}
            <div style="margin-top:28px;padding-top:22px;border-top:1px solid #e5e5e5;display:flex;flex-direction:column;gap:12px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#666666;">
                    <span>Subtotal Layanan ({{ $order->items->count() }} layanan)</span>
                    <span style="font-weight:700;color:#111111;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#666666;">
                    <span>Biaya Layanan & Perlindungan Platform</span>
                    <span style="font-weight:700;color:#111111;">Rp {{ number_format($order->platform_fee, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:2px solid #111111;margin-top:6px;">
                    <span style="font-size:13px;font-weight:900;letter-spacing:0.08em;color:#111111;text-transform:uppercase;">Total Pembayaran</span>
                    <span style="font-size:22px;font-weight:900;letter-spacing:-0.02em;color:#ed1d24;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
