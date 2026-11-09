@extends('layouts.app')
@section('title', 'Saldo, Escrow & Pencairan Dana Vendor — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'wallets'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="margin-bottom:24px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:14px;">
                <div>
                    <h1 class="dashboard-title">Saldo, Escrow &amp; Pencairan Dana Vendor</h1>
                    <p class="dashboard-subtitle">Kelola verifikasi transfer manual penarikan saldo vendor, pantau saldo aktif &amp; rekening penampung (escrow)</p>
                </div>
                <div>
                    @if(isset($pendingWithdrawals) && $pendingWithdrawals->count() > 0)
                        <span style="display:inline-flex;align-items:center;gap:6px;background:#fef2f2;border:1px solid #fecaca;padding:8px 14px;color:#991b1b;font-size:12px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;">
                            <i class="bi bi-bell-fill" style="color:#D81A24;"></i> {{ $pendingWithdrawals->count() }} Permintaan Menunggu Transfer
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Metrics Summary (4 Kotak Uniqlo Clean) --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:28px;">
            {{-- 1. Menunggu Transfer --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-top:3px solid #D81A24;padding:20px;">
                <div style="font-size:11px;font-weight:800;color:#767676;letter-spacing:1px;text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;">
                    <span>Menunggu Transfer (Pending)</span>
                    <i class="bi bi-hourglass-split" style="color:#D81A24;font-size:14px;"></i>
                </div>
                <div style="font-size:24px;font-weight:900;color:#D81A24;margin-top:6px;">
                    Rp {{ number_format($totalPendingWithdrawal ?? 0, 0, ',', '.') }}
                </div>
                <div style="font-size:11.5px;color:#888;margin-top:4px;">{{ $pendingWithdrawals->count() }} transaksi menunggu transfer manual</div>
            </div>

            {{-- 2. Saldo Escrow --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-top:3px solid #f59e0b;padding:20px;">
                <div style="font-size:11px;font-weight:800;color:#767676;letter-spacing:1px;text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;">
                    <span>Saldo Escrow (Tertahan)</span>
                    <i class="bi bi-shield-lock" style="color:#f59e0b;font-size:14px;"></i>
                </div>
                <div style="font-size:24px;font-weight:900;color:#f59e0b;margin-top:6px;">
                    Rp {{ number_format($totalEscrow, 0, ',', '.') }}
                </div>
                <div style="font-size:11.5px;color:#888;margin-top:4px;">Aman di penampung menunggu acara selesai</div>
            </div>

            {{-- 3. Saldo Aktif Vendor --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-top:3px solid #16a34a;padding:20px;">
                <div style="font-size:11px;font-weight:800;color:#767676;letter-spacing:1px;text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;">
                    <span>Total Saldo Aktif Vendor</span>
                    <i class="bi bi-wallet2" style="color:#16a34a;font-size:14px;"></i>
                </div>
                <div style="font-size:24px;font-weight:900;color:#16a34a;margin-top:6px;">
                    Rp {{ number_format($totalAvailable, 0, ',', '.') }}
                </div>
                <div style="font-size:11.5px;color:#888;margin-top:4px;">Saldo siap dicairkan oleh vendor</div>
            </div>

            {{-- 4. Total Akumulasi Pencairan --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-top:3px solid #111111;padding:20px;">
                <div style="font-size:11px;font-weight:800;color:#767676;letter-spacing:1px;text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;">
                    <span>Total Akumulasi Pendapatan</span>
                    <i class="bi bi-graph-up-arrow" style="color:#111111;font-size:14px;"></i>
                </div>
                <div style="font-size:24px;font-weight:900;color:#111111;margin-top:6px;">
                    Rp {{ number_format($totalEarned, 0, ',', '.') }}
                </div>
                <div style="font-size:11.5px;color:#888;margin-top:4px;">Sepanjang waktu operasional</div>
            </div>
        </div>

        {{-- SECTION 1: PERMINTAAN PENARIKAN SALDO VENDOR (TRANSFER MANUAL) --}}
        <div style="background:#ffffff;border:1px solid #e5e5e5;padding:24px;margin-bottom:32px;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid #eeeeee;">
                <div>
                    <h2 style="font-size:15px;font-weight:900;letter-spacing:0.04em;text-transform:uppercase;color:#111111;margin:0;display:flex;align-items:center;gap:8px;">
                        <i class="bi bi-cash-stack" style="color:#D81A24;"></i> Permintaan Penarikan Saldo (Pencairan Vendor)
                    </h2>
                    <p style="font-size:12.5px;color:#666666;margin:4px 0 0;">Transfer manual ke rekening vendor di bawah, lalu klik konfirmasi atau tolak jika data tidak sesuai.</p>
                </div>

                {{-- Status Filter Pills --}}
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    @php
                        $curSt = request('status', 'all');
                    @endphp
                    <a href="{{ route('admin.wallets.index', ['status' => 'all']) }}" style="padding:6px 12px;font-size:11.5px;font-weight:800;text-decoration:none;border:1px solid #111111;{{ $curSt === 'all' ? 'background:#111111;color:#ffffff;' : 'background:#ffffff;color:#111111;' }}">
                        Semua Status
                    </a>
                    <a href="{{ route('admin.wallets.index', ['status' => 'pending']) }}" style="padding:6px 12px;font-size:11.5px;font-weight:800;text-decoration:none;border:1px solid #eab308;{{ $curSt === 'pending' ? 'background:#eab308;color:#ffffff;' : 'background:#ffffff;color:#854d0e;' }}">
                        Menunggu Transfer ({{ $pendingWithdrawals->count() }})
                    </a>
                    <a href="{{ route('admin.wallets.index', ['status' => 'completed']) }}" style="padding:6px 12px;font-size:11.5px;font-weight:800;text-decoration:none;border:1px solid #16a34a;{{ $curSt === 'completed' ? 'background:#16a34a;color:#ffffff;' : 'background:#ffffff;color:#16a34a;' }}">
                        Selesai Ditransfer
                    </a>
                    <a href="{{ route('admin.wallets.index', ['status' => 'failed']) }}" style="padding:6px 12px;font-size:11.5px;font-weight:800;text-decoration:none;border:1px solid #dc2626;{{ $curSt === 'failed' ? 'background:#dc2626;color:#ffffff;' : 'background:#ffffff;color:#dc2626;' }}">
                        Ditolak
                    </a>
                </div>
            </div>

            @if(isset($allWithdrawals) && $allWithdrawals->count() > 0)
            <div class="table-wrapper" style="border:1px solid #eeeeee;">
                <table class="table" style="margin:0;">
                    <thead>
                        <tr style="background:#fcfcfc;">
                            <th>Kode TRX &amp; Waktu</th>
                            <th>Vendor &amp; Pemilik</th>
                            <th>Nominal Penarikan</th>
                            <th>Rekening Tujuan Transfer</th>
                            <th>Status &amp; Bukti</th>
                            <th style="text-align:right;">Aksi Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allWithdrawals as $txn)
                        @php
                            $vendor = $txn->wallet->vendorProfile;
                        @endphp
                        <tr>
                            <td>
                                <strong style="font-size:12px;color:#111111;">{{ $txn->transaction_number }}</strong>
                                <div style="font-size:11px;color:#888888;margin-top:2px;">{{ $txn->created_at->translatedFormat('d M Y, H:i') }}</div>
                                @if($txn->description)
                                    <div style="font-size:10.5px;color:#666666;margin-top:4px;max-width:200px;">{{ $txn->description }}</div>
                                @endif
                            </td>
                            <td>
                                <strong style="color:#111111;font-size:13px;">{{ $vendor->business_name ?? '-' }}</strong>
                                <div style="font-size:11.5px;color:#666666;">{{ $vendor->user->name ?? '-' }}</div>
                                <div style="font-size:11px;color:#888888;">{{ $vendor->phone ?: $vendor->user->email }}</div>
                            </td>
                            <td>
                                <strong style="font-size:15px;font-weight:900;color:{{ $txn->status === 'failed' ? '#888888' : '#D81A24' }};">
                                    Rp {{ number_format($txn->amount, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:8px 10px;font-size:12px;min-width:210px;">
                                    <div style="font-weight:800;color:#0f172a;text-transform:uppercase;display:flex;align-items:center;justify-content:space-between;">
                                        <span>{{ $vendor->bank_name ?? 'BANK' }}</span>
                                        <button type="button" onclick="copyAccountNumber('{{ $vendor->bank_account_number }}', this)" style="background:none;border:none;color:#D81A24;font-size:11px;font-weight:700;cursor:pointer;padding:0;">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </div>
                                    <div style="font-family:monospace;font-size:13.5px;font-weight:900;color:#111111;letter-spacing:0.5px;margin:3px 0;">
                                        {{ $vendor->bank_account_number ?? '-' }}
                                    </div>
                                    <div style="font-size:11px;color:#475569;">a/n <strong>{{ $vendor->bank_account_holder ?? '-' }}</strong></div>
                                </div>
                            </td>
                            <td>
                                @if($txn->status === 'pending')
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;font-size:10.5px;font-weight:800;text-transform:uppercase;background:#fef9c3;color:#854d0e;border:1px solid #fde047;">
                                        <i class="bi bi-hourglass-split"></i> Menunggu Transfer
                                    </span>
                                @elseif($txn->status === 'completed')
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;font-size:10.5px;font-weight:800;text-transform:uppercase;background:#dcfce7;color:#15803d;border:1px solid #86efac;">
                                        <i class="bi bi-check-circle-fill"></i> Sudah Ditransfer
                                    </span>
                                    @if($txn->processed_at)
                                        <div style="font-size:10.5px;color:#666666;margin-top:3px;">
                                            {{ $txn->processed_at->translatedFormat('d M, H:i') }}
                                            @if($txn->processedBy) · oleh {{ $txn->processedBy->name }} @endif
                                        </div>
                                    @endif
                                    @if($txn->proof_image)
                                        <div style="margin-top:4px;">
                                            <a href="{{ asset('storage/' . $txn->proof_image) }}" target="_blank" style="font-size:11px;font-weight:700;color:#1d4ed8;text-decoration:underline;">
                                                <i class="bi bi-image"></i> Lihat Bukti Transfer
                                            </a>
                                        </div>
                                    @endif
                                @elseif($txn->status === 'failed')
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;font-size:10.5px;font-weight:800;text-transform:uppercase;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;">
                                        <i class="bi bi-x-circle-fill"></i> Ditolak (Saldo Kembali)
                                    </span>
                                    @if($txn->admin_notes)
                                        <div style="font-size:11px;color:#991b1b;margin-top:3px;font-style:italic;">
                                            {{ $txn->admin_notes }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td style="text-align:right;">
                                @if($txn->status === 'pending')
                                    <div style="display:inline-flex;gap:6px;">
                                        <button type="button" class="btn btn-sm" style="background:#16a34a;color:#ffffff;border:none;font-weight:800;font-size:11px;padding:6px 12px;border-radius:0;" onclick="openApproveModal({{ $txn->id }}, '{{ $txn->transaction_number }}', '{{ number_format($txn->amount, 0, ',', '.') }}', '{{ strtoupper($vendor->bank_name ?? '-') }} - {{ $vendor->bank_account_number ?? '-' }} a/n {{ $vendor->bank_account_holder ?? '-' }}')">
                                            <i class="bi bi-check-lg"></i> Konfirmasi Transfer
                                        </button>
                                        <button type="button" class="btn btn-sm" style="background:#ffffff;border:1px solid #dc2626;color:#dc2626;font-weight:800;font-size:11px;padding:6px 12px;border-radius:0;" onclick="openRejectModal({{ $txn->id }}, '{{ $txn->transaction_number }}', '{{ number_format($txn->amount, 0, ',', '.') }}')">
                                            <i class="bi bi-x-lg"></i> Tolak
                                        </button>
                                    </div>
                                @else
                                    <span style="font-size:11.5px;color:#888888;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;">
                {{ $allWithdrawals->appends(['status' => request('status')])->links() }}
            </div>
            @else
            <div style="padding:32px 16px;text-align:center;background:#fafafa;border:1px dashed #e5e5e5;">
                <i class="bi bi-inbox" style="font-size:2rem;color:#ccc;"></i>
                <p style="color:#666666;font-size:13px;margin:8px 0 0;">Tidak ada permintaan penarikan saldo dengan status ini.</p>
            </div>
            @endif
        </div>

        {{-- SECTION 2: MONITORING SALDO & ESCROW SELURUH VENDOR --}}
        <div style="background:#ffffff;border:1px solid #e5e5e5;padding:24px;">
            <div style="margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid #eeeeee;">
                <h2 style="font-size:15px;font-weight:900;letter-spacing:0.04em;text-transform:uppercase;color:#111111;margin:0;display:flex;align-items:center;gap:8px;">
                    <i class="bi bi-shop" style="color:#111111;"></i> Monitoring Saldo &amp; Escrow Seluruh Vendor
                </h2>
                <p style="font-size:12.5px;color:#666666;margin:4px 0 0;">Daftar seluruh dompet vendor, saldo aktif yang siap ditarik, dan saldo yang masih tertahan di escrow.</p>
            </div>

            @if($wallets->count() > 0)
            <div class="table-wrapper" style="border:1px solid #eeeeee;">
                <table class="table" style="margin:0;">
                    <thead>
                        <tr style="background:#fcfcfc;">
                            <th>Vendor / Bisnis</th>
                            <th>Pemilik &amp; Kontak</th>
                            <th>Rekening Bank Terdaftar</th>
                            <th>Saldo Tersedia</th>
                            <th>Saldo Escrow</th>
                            <th>Total Pendapatan</th>
                            <th style="text-align:right;">Terakhir Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wallets as $wallet)
                        @php
                            $v = $wallet->vendorProfile;
                        @endphp
                        <tr>
                            <td>
                                <strong style="color:#111111;font-size:13px;">{{ $v->business_name ?? '-' }}</strong>
                                <div style="font-size:11.5px;color:#767676;">{{ $v->category->name ?? '-' }}</div>
                            </td>
                            <td>
                                <span style="color:#111111;font-weight:600;">{{ $v->user->name ?? '-' }}</span>
                                <div style="font-size:11.5px;color:#767676;">{{ $v->user->email ?? '-' }}</div>
                            </td>
                            <td>
                                @if($v && $v->bank_name && $v->bank_account_number)
                                    <div style="font-size:12px;">
                                        <strong style="color:#111111;">{{ strtoupper($v->bank_name) }}</strong> — {{ $v->bank_account_number }}
                                        <div style="font-size:11px;color:#666666;">a/n {{ $v->bank_account_holder }}</div>
                                    </div>
                                @else
                                    <span style="font-size:11px;color:#999999;font-style:italic;">Belum diatur</span>
                                @endif
                            </td>
                            <td>
                                <strong style="color:#16a34a;font-size:14px;">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <strong style="color:#f59e0b;font-size:14px;">Rp {{ number_format($wallet->pending_balance, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <span style="color:#111111;font-weight:700;">Rp {{ number_format($wallet->total_earned, 0, ',', '.') }}</span>
                            </td>
                            <td style="text-align:right;font-size:12px;color:#767676;">
                                {{ $wallet->updated_at->translatedFormat('d M Y, H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">
                {{ $wallets->links() }}
            </div>
            @else
            <div style="padding:32px 16px;text-align:center;background:#fafafa;">
                <p style="color:#666;margin:0;">Belum ada data saldo vendor.</p>
            </div>
            @endif
        </div>
    </div>
</div>

@include('admin.wallets._withdrawal_modals')
@endsection
