@extends('layouts.app')
@section('title', 'Saldo & Keuangan — PestaGo Vendor')

@section('content')
<div class="dashboard">
    @include('vendor._sidebar', ['active' => 'wallet'])

    <div class="dashboard-content uq-wallet-page">
        {{-- Page Header --}}
        <div class="uq-wallet-header">
            <div>
                <h1 class="uq-wallet-title">Saldo &amp; Keuangan</h1>
                <p class="uq-wallet-subtitle">Pantau pendapatan, saldo tertahan di penampung (escrow), dan pencairan saldo Anda</p>
            </div>
            <div class="uq-wallet-header-action">
                <button type="button" class="uq-btn-withdraw-header" onclick="openWithdrawModal()">
                    <i class="bi bi-cash-stack"></i> Tarik Saldo
                </button>
            </div>
        </div>

        {{-- Pending Withdrawal Alert if any --}}
        @php
            $activePendingWithdrawal = $transactions->firstWhere(fn($t) => $t->type === 'withdrawn' && $t->status === 'pending');
        @endphp
        @if($activePendingWithdrawal)
        <div class="uq-pending-alert">
            <div class="uq-pending-alert-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="uq-pending-alert-body">
                <div class="uq-pending-alert-title">
                    Penarikan Saldo Sedang Diproses Admin
                </div>
                <div class="uq-pending-alert-desc">
                    Pengajuan penarikan sebesar <strong>Rp {{ number_format($activePendingWithdrawal->amount, 0, ',', '.') }}</strong> (Kode: {{ $activePendingWithdrawal->transaction_number }}) sedang diverifikasi oleh Admin untuk ditransfer ke rekening bank Anda.
                </div>
            </div>
        </div>
        @endif

        {{-- Hero Wallet Section: Optimized Mobile-First --}}
        <div class="uq-wallet-grid">
            {{-- 1. Hero Card: Saldo Tersedia (Paling Utama) --}}
            <div class="uq-card-hero">
                <div class="uq-card-hero-header">
                    <div class="uq-card-hero-tag">
                        <i class="bi bi-wallet2"></i> Saldo Tersedia (Siap Ditarik)
                    </div>
                    @if($wallet->balance >= 50000)
                        <span class="uq-status-pill-green">
                            <i class="bi bi-check-circle-fill"></i> Siap Dicairkan
                        </span>
                    @else
                        <span class="uq-status-pill-gray">
                            Min. Rp 50.000
                        </span>
                    @endif
                </div>

                <div class="uq-card-hero-amount">
                    Rp {{ number_format($wallet->balance, 0, ',', '.') }}
                </div>

                <div class="uq-card-hero-footer">
                    <button type="button" class="uq-btn-hero-withdraw" onclick="openWithdrawModal()">
                        <i class="bi bi-arrow-up-right-circle"></i> Tarik Saldo Sekarang
                    </button>
                </div>
            </div>

            {{-- 2 Sub Cards: Escrow & Total Pendapatan (2-Column Grid on Mobile) --}}
            <div class="uq-subcards-grid">
                {{-- 2. Saldo Tertahan (Escrow) --}}
                <div class="uq-subcard uq-subcard-pending">
                    <div class="uq-subcard-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div class="uq-subcard-content">
                        <div class="uq-subcard-label">Saldo Tertahan (Escrow)</div>
                        <div class="uq-subcard-amount {{ $wallet->pending_balance > 0 ? 'text-amber' : '' }}">
                            Rp {{ number_format($wallet->pending_balance, 0, ',', '.') }}
                        </div>
                        <div class="uq-subcard-hint">
                            @if($wallet->pending_balance > 0)
                                Menunggu customer konfirmasi selesai
                            @else
                                Tidak ada dana yang tertahan
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 3. Total Pendapatan --}}
                <div class="uq-subcard uq-subcard-total">
                    <div class="uq-subcard-icon">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="uq-subcard-content">
                        <div class="uq-subcard-label">Total Akumulasi Pendapatan</div>
                        <div class="uq-subcard-amount">
                            Rp {{ number_format($wallet->total_earned, 0, ',', '.') }}
                        </div>
                        <div class="uq-subcard-hint">
                            Total seluruh dana yang berhasil dicairkan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bank Account Info Bar (Clean Minimalist Uniqlo) --}}
        <div class="uq-bank-card">
            <div class="uq-bank-info">
                <div class="uq-bank-icon">
                    <i class="bi bi-bank2"></i>
                </div>
                <div class="uq-bank-text">
                    <div class="uq-bank-label">Rekening Bank Tujuan Pencairan Dana</div>
                    @if($vendor->bank_name && $vendor->bank_account_number)
                        <div class="uq-bank-val">
                            <span class="uq-bank-badge">{{ strtoupper($vendor->bank_name) }}</span>
                            <span class="uq-bank-num">{{ $vendor->bank_account_number }}</span>
                        </div>
                        <div class="uq-bank-holder">
                            Atas Nama: <strong>{{ $vendor->bank_account_holder }}</strong>
                        </div>
                    @else
                        <div class="uq-bank-missing">
                            <i class="bi bi-exclamation-triangle-fill"></i> Data rekening bank belum diatur.
                        </div>
                    @endif
                </div>
            </div>
            <div class="uq-bank-action">
                <a href="{{ route('vendor.verification') }}" class="uq-btn-bank-edit">
                    <i class="bi bi-pencil-square"></i> {{ $vendor->bank_name ? 'Ubah Rekening' : 'Atur Rekening' }}
                </a>
            </div>
        </div>

        {{-- Collapsible / Clean Escrow Guide (Compact for Mobile) --}}
        <div class="uq-guide-card">
            <div class="uq-guide-header" onclick="toggleEscrowGuide()">
                <div class="uq-guide-header-title">
                    <i class="bi bi-info-circle-fill" style="color:#D81A24;"></i>
                    <span>Alur Kerja Sistem Escrow &amp; Pencairan</span>
                </div>
                <button type="button" class="uq-guide-toggle-btn" id="guideToggleBtn">
                    <span id="guideToggleText">Lihat Alur</span>
                    <i class="bi bi-chevron-down" id="guideToggleIcon"></i>
                </button>
            </div>
            <div class="uq-guide-body" id="guideBody" style="display:none;">
                <div class="uq-guide-steps">
                    <div class="uq-guide-step">
                        <div class="uq-guide-num">1</div>
                        <div>
                            <strong>Customer Bayar Online</strong>
                            <p>Dana aman di rekening penampung resmi PestaGo (tercatat di <em>Saldo Tertahan</em>).</p>
                        </div>
                    </div>
                    <div class="uq-guide-step">
                        <div class="uq-guide-num">2</div>
                        <div>
                            <strong>Vendor Selesaikan Acara</strong>
                            <p>Vendor melayani acara dan menekan tombol <em>Tandai Selesai</em> di detail pesanan.</p>
                        </div>
                    </div>
                    <div class="uq-guide-step">
                        <div class="uq-guide-num">3</div>
                        <div>
                            <strong>Customer Konfirmasi</strong>
                            <p>Customer klik <em>Konfirmasi Selesai</em> / beri rating. Dana otomatis pindah ke <em>Saldo Tersedia</em>.</p>
                        </div>
                    </div>
                    <div class="uq-guide-step">
                        <div class="uq-guide-num">4</div>
                        <div>
                            <strong>Tarik Saldo ke Rekening</strong>
                            <p>Vendor klik <em>Tarik Saldo</em>. Admin mentransfer dana manual ke rekening bank vendor.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transaction History Section --}}
        <div class="uq-section">
            <div class="uq-section-header">
                <div>
                    <h2 class="uq-section-title">Riwayat Mutasi Saldo &amp; Transaksi</h2>
                    <p class="uq-section-subtitle">Daftar lengkap pergerakan dana masuk, pelepasan escrow, dan penarikan saldo</p>
                </div>
                <span class="uq-badge-count">{{ $transactions->total() ?? $transactions->count() }} Transaksi</span>
            </div>

            @if($transactions->count() > 0)
            <div class="uq-txn-list">
                @foreach($transactions as $txn)
                @php
                    $isPositive = in_array($txn->type, ['escrow_in', 'released', 'refunded']);
                    $isFailed = $txn->status === 'failed';
                @endphp
                <div class="uq-txn-card {{ $isFailed ? 'txn-is-failed' : '' }}">
                    {{-- Row 1: Type + Status Badge + Amount --}}
                    <div class="uq-txn-row-top">
                        <div class="uq-txn-meta-left">
                            <div class="uq-txn-icon-box {{ $isPositive ? 'txn-in' : 'txn-out' }}">
                                @if($txn->type === 'escrow_in')
                                    <i class="bi bi-arrow-down-circle"></i>
                                @elseif($txn->type === 'released')
                                    <i class="bi bi-check-circle"></i>
                                @elseif($txn->type === 'withdrawn')
                                    <i class="bi bi-arrow-up-right-circle"></i>
                                @elseif($txn->type === 'refunded')
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                @elseif($txn->type === 'tax_deducted')
                                    <i class="bi bi-receipt-cutoff"></i>
                                @else
                                    <i class="bi bi-cash-stack"></i>
                                @endif
                            </div>
                            <div>
                                <div class="uq-txn-type-name">{{ $txn->type_label }}</div>
                                <div class="uq-txn-time">{{ $txn->created_at->translatedFormat('d M Y, H:i') }}</div>
                            </div>
                        </div>

                        <div class="uq-txn-amount {{ $isPositive ? 'amount-in' : ($isFailed ? 'amount-failed' : 'amount-out') }}">
                            {{ $isPositive ? '+' : '-' }}Rp {{ number_format($txn->amount, 0, ',', '.') }}
                        </div>
                    </div>

                    {{-- Row 2: Status Pill if Withdrawn --}}
                    @if($txn->type === 'withdrawn')
                    <div class="uq-txn-status-bar">
                        @if($txn->status === 'pending')
                            <span class="uq-pill uq-pill-pending">
                                <i class="bi bi-hourglass-split"></i> Menunggu Transfer Admin
                            </span>
                        @elseif($txn->status === 'completed')
                            <span class="uq-pill uq-pill-completed">
                                <i class="bi bi-check-circle-fill"></i> Sudah Ditransfer ke Rekening
                            </span>
                        @elseif($txn->status === 'failed')
                            <span class="uq-pill uq-pill-failed">
                                <i class="bi bi-x-circle-fill"></i> Ditolak Admin (Saldo Sudah Dikembalikan)
                            </span>
                        @endif
                    </div>
                    @endif

                    {{-- Row 3: Description --}}
                    @if($txn->description)
                    <div class="uq-txn-desc">
                        {{ $txn->description }}
                    </div>
                    @endif

                    {{-- Row 4: Admin Notes if rejected/approved with note --}}
                    @if($txn->admin_notes)
                    <div class="uq-txn-admin-note {{ $isFailed ? 'note-danger' : 'note-info' }}">
                        <strong>Catatan Admin:</strong> {{ $txn->admin_notes }}
                    </div>
                    @endif

                    {{-- Row 5: Bukti Transfer Admin if available --}}
                    @if($txn->proof_image)
                    <div class="uq-txn-proof-row">
                        <a href="{{ asset('storage/' . $txn->proof_image) }}" target="_blank" class="uq-btn-proof-link">
                            <i class="bi bi-image"></i> Lihat Bukti Transfer Bank Admin
                        </a>
                    </div>
                    @endif

                    {{-- Row 6: Transaction Code --}}
                    <div class="uq-txn-footer">
                        <span class="uq-txn-code">Kode TRX: <code>{{ $txn->transaction_number }}</code></span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="uq-pagination-wrap">
                {{ $transactions->links() }}
            </div>
            @else
            <div class="uq-empty-box">
                <i class="bi bi-receipt uq-empty-icon"></i>
                <h4 style="font-size:14px;font-weight:800;color:#111111;margin:10px 0 4px;">Belum Ada Riwayat Mutasi</h4>
                <p style="font-size:12px;color:#888888;margin:0;">Seluruh mutasi pembayaran pesanan dan penarikan saldo Anda akan tercatat di sini.</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Tarik Saldo (Uniqlo Minimalist Bottom Sheet / Dialog) --}}
<div id="withdrawModal" class="uq-modal-backdrop" style="display:none;">
    <div class="uq-modal-dialog">
        <div class="uq-modal-header">
            <h3 class="uq-modal-title">
                <i class="bi bi-cash-stack" style="color:#D81A24;"></i> Tarik Saldo Vendor
            </h3>
            <button type="button" class="uq-modal-close" onclick="closeWithdrawModal()">&times;</button>
        </div>

        @php
            $hasBank = !empty($vendor->bank_name) && !empty($vendor->bank_account_number) && !empty($vendor->bank_account_holder);
            $canWithdraw = $hasBank && $wallet->balance >= 50000;
        @endphp

        @if(!$hasBank)
            <div class="uq-modal-body">
                <div class="uq-alert-box alert-danger">
                    <strong style="display:block;margin-bottom:4px;"><i class="bi bi-exclamation-triangle-fill"></i> Rekening Bank Belum Lengkap</strong>
                    Silakan lengkapi nama bank, nomor rekening, dan nama pemilik rekening terlebih dahulu sebelum melakukan penarikan saldo.
                </div>
                <a href="{{ route('vendor.verification') }}" class="uq-btn-submit" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;">
                    <i class="bi bi-pencil-square" style="margin-right:6px;"></i> Lengkapi Rekening Bank Sekarang
                </a>
            </div>
        @elseif($wallet->balance < 50000)
            <div class="uq-modal-body">
                <div class="uq-alert-box alert-warning">
                    <strong style="display:block;margin-bottom:4px;"><i class="bi bi-info-circle-fill"></i> Saldo Belum Mencukupi</strong>
                    Saldo tersedia saat ini: <strong>Rp {{ number_format($wallet->balance, 0, ',', '.') }}</strong>.<br>
                    Minimal penarikan dana adalah <strong>Rp 50.000</strong>.
                    @if($wallet->pending_balance > 0)
                        <div style="margin-top:8px;font-size:11.5px;color:#78350f;padding-top:8px;border-top:1px dashed #fde68a;">
                            Anda memiliki <strong>Rp {{ number_format($wallet->pending_balance, 0, ',', '.') }}</strong> di Saldo Tertahan. Dana tersebut akan langsung masuk ke Saldo Tersedia begitu pesanan dikonfirmasi selesai oleh customer.
                        </div>
                    @endif
                </div>
                <button type="button" class="uq-btn-cancel" onclick="closeWithdrawModal()" style="width:100%;">
                    Tutup
                </button>
            </div>
        @else
            <form action="{{ route('vendor.wallet.withdraw') }}" method="POST" id="withdrawForm">
                @csrf
                <div class="uq-modal-body">
                    {{-- Rekening Tujuan Preview --}}
                    <div class="uq-modal-bank-preview">
                        <div class="uq-modal-bank-label">Transfer Ditujukan ke Rekening:</div>
                        <div class="uq-modal-bank-acc">
                            <span class="uq-bank-badge">{{ strtoupper($vendor->bank_name) }}</span>
                            <span style="font-family:monospace;font-weight:900;letter-spacing:0.5px;">{{ $vendor->bank_account_number }}</span>
                        </div>
                        <div class="uq-modal-bank-holder">a/n <strong>{{ $vendor->bank_account_holder }}</strong></div>
                    </div>

                    {{-- Saldo Tersedia Row --}}
                    <div class="uq-modal-balance-row">
                        <span>Saldo Tersedia:</span>
                        <strong class="text-green">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</strong>
                    </div>

                    {{-- Input Nominal --}}
                    <div style="margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                            <label class="uq-form-label">
                                Nominal Penarikan (Rp) <span style="color:#D81A24;">*</span>
                            </label>
                            <button type="button" class="uq-btn-set-all" onclick="setAllBalance({{ $wallet->balance }})">
                                Tarik Semua Saldo
                            </button>
                        </div>
                        <input type="number" name="amount" id="withdrawAmountInput" class="uq-form-input" min="50000" max="{{ $wallet->balance }}" value="{{ $wallet->balance }}" inputmode="numeric" required>
                        <span class="uq-form-hint">Minimal penarikan Rp 50.000 · Bebas biaya transfer platform</span>
                    </div>

                    {{-- Catatan Opsional --}}
                    <div style="margin-bottom:20px;">
                        <label class="uq-form-label">
                            Catatan Penarikan (Opsional)
                        </label>
                        <input type="text" name="notes" class="uq-form-input" placeholder="Contoh: Pencairan termin acara catering">
                    </div>

                    {{-- Action Buttons --}}
                    <div class="uq-modal-actions">
                        <button type="button" class="uq-btn-cancel" onclick="closeWithdrawModal()">
                            Batal
                        </button>
                        <button type="submit" class="uq-btn-submit">
                            <i class="bi bi-check-lg"></i> Konfirmasi Penarikan
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
/* ========================================================
   UNIQLO CLEAN MINIMALIST VENDOR WALLET (MOBILE FIRST)
   ======================================================== */
.uq-wallet-page {
    padding: 20px;
    background: #f8fafc;
    min-height: 100vh;
}

/* Header */
.uq-wallet-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 20px;
}

.uq-wallet-title {
    font-size: 22px;
    font-weight: 900;
    color: #111111;
    margin: 0;
    letter-spacing: -0.02em;
}

.uq-wallet-subtitle {
    font-size: 13px;
    color: #64748b;
    margin: 4px 0 0;
    line-height: 1.4;
}

.uq-btn-withdraw-header {
    height: 40px;
    padding: 0 18px;
    background: #D81A24;
    color: #ffffff;
    border: none;
    border-radius: 0 !important;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.15s ease;
}
.uq-btn-withdraw-header:hover { background: #b91c1c; }

/* Pending Alert */
.uq-pending-alert {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-left: 4px solid #f59e0b;
    padding: 14px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.uq-pending-alert-icon {
    font-size: 18px;
    color: #d97706;
    margin-top: 2px;
    flex-shrink: 0;
}
.uq-pending-alert-title {
    font-size: 12.5px;
    font-weight: 800;
    color: #92400e;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.uq-pending-alert-desc {
    font-size: 12px;
    color: #78350f;
    margin-top: 2px;
    line-height: 1.45;
}

/* Wallet Grid Layout */
.uq-wallet-grid {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 20px;
}

/* Hero Card (Saldo Tersedia) */
.uq-card-hero {
    background: #ffffff;
    border: 1px solid #111111;
    border-top: 4px solid #16a34a;
    padding: 22px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.uq-card-hero-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}
.uq-card-hero-tag {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 6px;
}
.uq-status-pill-green {
    font-size: 10.5px;
    font-weight: 800;
    color: #15803d;
    background: #dcfce7;
    border: 1px solid #86efac;
    padding: 2px 8px;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.uq-status-pill-gray {
    font-size: 10.5px;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 8px;
}
.uq-card-hero-amount {
    font-size: 28px;
    font-weight: 900;
    color: #111111;
    letter-spacing: -0.03em;
    line-height: 1.2;
    margin: 6px 0 16px;
}
.uq-card-hero-footer {
    display: flex;
}
.uq-btn-hero-withdraw {
    width: 100%;
    height: 44px;
    background: #D81A24;
    color: #ffffff;
    border: none;
    border-radius: 0 !important;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.15s ease;
}
.uq-btn-hero-withdraw:hover { background: #b91c1c; }

/* Subcards 2-Column Grid */
.uq-subcards-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.uq-subcard {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 16px 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.uq-subcard-pending { border-top: 3px solid #f59e0b; }
.uq-subcard-total { border-top: 3px solid #0f172a; }

.uq-subcard-icon {
    font-size: 16px;
    margin-bottom: 8px;
}
.uq-subcard-pending .uq-subcard-icon { color: #d97706; }
.uq-subcard-total .uq-subcard-icon { color: #0f172a; }

.uq-subcard-label {
    font-size: 10.5px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    line-height: 1.3;
}
.uq-subcard-amount {
    font-size: 17px;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin: 4px 0 2px;
}
.uq-subcard-amount.text-amber { color: #d97706; }
.uq-subcard-hint {
    font-size: 11px;
    color: #94a3b8;
    line-height: 1.3;
}

/* Bank Account Card */
.uq-bank-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
}
.uq-bank-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 240px;
}
.uq-bank-icon {
    width: 38px;
    height: 38px;
    background: #f1f5f9;
    color: #0f172a;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.uq-bank-label {
    font-size: 10.5px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.uq-bank-val {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 3px;
    flex-wrap: wrap;
}
.uq-bank-badge {
    background: #0f172a;
    color: #ffffff;
    font-size: 10.5px;
    font-weight: 800;
    padding: 1px 6px;
    letter-spacing: 0.04em;
}
.uq-bank-num {
    font-family: monospace;
    font-size: 14px;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: 0.5px;
}
.uq-bank-holder {
    font-size: 11.5px;
    color: #475569;
    margin-top: 2px;
}
.uq-bank-missing {
    font-size: 12px;
    color: #b45309;
    font-weight: 700;
    margin-top: 2px;
}
.uq-btn-bank-edit {
    padding: 6px 14px;
    background: #ffffff;
    border: 1px solid #0f172a;
    color: #0f172a;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s;
}
.uq-btn-bank-edit:hover {
    background: #0f172a;
    color: #ffffff;
}

/* Escrow Guide Card */
.uq-guide-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
}
.uq-guide-header {
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    background: #fafafa;
    user-select: none;
}
.uq-guide-header-title {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.uq-guide-toggle-btn {
    background: none;
    border: none;
    font-size: 11.5px;
    font-weight: 700;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    padding: 0;
}
.uq-guide-body {
    padding: 16px;
    border-top: 1px solid #e2e8f0;
}
.uq-guide-steps {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}
.uq-guide-step {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.uq-guide-num {
    width: 24px;
    height: 24px;
    background: #0f172a;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.uq-guide-step strong {
    font-size: 12px;
    font-weight: 800;
    color: #0f172a;
    display: block;
    line-height: 1.3;
}
.uq-guide-step p {
    font-size: 11px;
    color: #64748b;
    margin: 2px 0 0;
    line-height: 1.4;
}

/* Transaction Section */
.uq-section {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 20px;
}
.uq-section-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 16px;
}
.uq-section-title {
    font-size: 15px;
    font-weight: 900;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin: 0;
}
.uq-section-subtitle {
    font-size: 12px;
    color: #64748b;
    margin: 3px 0 0;
}
.uq-badge-count {
    font-size: 11px;
    font-weight: 800;
    background: #f1f5f9;
    color: #475569;
    padding: 3px 8px;
    letter-spacing: 0.04em;
}

/* Transaction List (Card-Based Mobile First) */
.uq-txn-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.uq-txn-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: border-color 0.15s;
}
.uq-txn-card:hover {
    border-color: #cbd5e1;
}
.uq-txn-card.txn-is-failed {
    background: #fffafa;
    border-color: #fecaca;
}

.uq-txn-row-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}
.uq-txn-meta-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.uq-txn-icon-box {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.uq-txn-icon-box.txn-in { background: #dcfce7; color: #15803d; }
.uq-txn-icon-box.txn-out { background: #fee2e2; color: #b91c1c; }

.uq-txn-type-name {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.25;
}
.uq-txn-time {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 2px;
}
.uq-txn-amount {
    font-size: 15px;
    font-weight: 900;
    white-space: nowrap;
    letter-spacing: -0.01em;
}
.uq-txn-amount.amount-in { color: #16a34a; }
.uq-txn-amount.amount-out { color: #D81A24; }
.uq-txn-amount.amount-failed { color: #94a3b8; text-decoration: line-through; }

.uq-txn-status-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.uq-pill {
    font-size: 10.5px;
    font-weight: 800;
    padding: 2px 7px;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.uq-pill-pending { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
.uq-pill-completed { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
.uq-pill-failed { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

.uq-txn-desc {
    font-size: 12px;
    color: #475569;
    line-height: 1.4;
    word-break: break-word;
}
.uq-txn-admin-note {
    font-size: 11.5px;
    padding: 8px 10px;
    border-left: 3px solid;
    line-height: 1.4;
}
.uq-txn-admin-note.note-danger {
    background: #fef2f2;
    border-color: #ef4444;
    color: #991b1b;
}
.uq-txn-admin-note.note-info {
    background: #f8fafc;
    border-color: #3b82f6;
    color: #1e3a8a;
}

.uq-txn-proof-row {
    margin-top: 2px;
}
.uq-btn-proof-link {
    font-size: 11.5px;
    font-weight: 700;
    color: #1d4ed8;
    text-decoration: underline;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.uq-txn-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 10.5px;
    color: #94a3b8;
    padding-top: 6px;
    border-top: 1px dashed #f1f5f9;
}
.uq-txn-code code {
    font-family: monospace;
    color: #475569;
    background: #f1f5f9;
    padding: 1px 4px;
}

/* Empty State */
.uq-empty-box {
    text-align: center;
    padding: 40px 16px;
}
.uq-empty-icon {
    font-size: 2.2rem;
    color: #cbd5e1;
}

.uq-pagination-wrap {
    margin-top: 16px;
}

/* Modal Styling */
.uq-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(2px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.uq-modal-dialog {
    background: #ffffff;
    border: 1px solid #111111;
    width: 100%;
    max-width: 460px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25);
    max-height: 90vh;
    overflow-y: auto;
}
.uq-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: #fafafa;
}
.uq-modal-title {
    font-size: 14px;
    font-weight: 900;
    color: #0f172a;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: flex;
    align-items: center;
    gap: 8px;
}
.uq-modal-close {
    background: none;
    border: none;
    font-size: 22px;
    color: #94a3b8;
    cursor: pointer;
    line-height: 1;
}
.uq-modal-body {
    padding: 18px;
}

.uq-alert-box {
    padding: 12px 14px;
    font-size: 12px;
    line-height: 1.45;
    margin-bottom: 16px;
}
.uq-alert-box.alert-danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.uq-alert-box.alert-warning {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #92400e;
}

.uq-modal-bank-preview {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 12px 14px;
    margin-bottom: 14px;
}
.uq-modal-bank-label {
    font-size: 10.5px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.uq-modal-bank-acc {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    margin: 4px 0 2px;
}
.uq-modal-bank-holder {
    font-size: 11.5px;
    color: #475569;
}

.uq-modal-balance-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 14px;
    font-size: 12.5px;
    color: #64748b;
}
.uq-modal-balance-row strong {
    font-size: 16px;
    font-weight: 900;
    color: #16a34a;
}

.uq-form-label {
    font-size: 11px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin: 0;
}
.uq-btn-set-all {
    background: none;
    border: none;
    color: #D81A24;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: underline;
    padding: 0;
}
.uq-form-input {
    width: 100%;
    height: 44px;
    border: 1px solid #cbd5e1;
    padding: 0 12px;
    font-size: 15px;
    color: #0f172a;
    outline: none;
    box-sizing: border-box;
}
.uq-form-input:focus {
    border-color: #0f172a;
    box-shadow: 0 0 0 1px #0f172a;
}
.uq-form-hint {
    font-size: 10.5px;
    color: #94a3b8;
    margin-top: 4px;
    display: block;
}

.uq-modal-actions {
    display: flex;
    gap: 8px;
}
.uq-btn-cancel {
    flex: 1;
    height: 42px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #475569;
    cursor: pointer;
}
.uq-btn-submit {
    flex: 2;
    height: 42px;
    background: #D81A24;
    border: 1px solid #D81A24;
    color: #ffffff;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background 0.15s;
}
.uq-btn-submit:hover { background: #b91c1c; }

/* Responsive Desktop Overrides */
@media (min-width: 769px) {
    .uq-wallet-page {
        padding: 24px 28px;
    }
    .uq-wallet-grid {
        display: grid;
        grid-template-columns: 1.4fr 1.6fr;
        gap: 16px;
    }
    .uq-subcards-grid {
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .uq-card-hero {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .uq-card-hero-amount {
        font-size: 32px;
    }
    .uq-guide-body {
        display: block !important;
    }
    .uq-guide-toggle-btn {
        display: none;
    }
    .uq-guide-header {
        cursor: default;
        background: transparent;
        padding: 16px 20px 8px;
        border-bottom: none;
    }
    .uq-guide-body {
        padding: 0 20px 20px;
        border-top: none;
    }
}

@media (max-width: 768px) {
    .uq-wallet-page {
        padding: 14px;
    }
    .uq-wallet-header {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    .uq-wallet-header-action {
        display: none; /* Already prominent in hero card */
    }
    .uq-card-hero-amount {
        font-size: 26px;
    }
    .uq-guide-steps {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .uq-bank-card {
        flex-direction: column;
        align-items: stretch;
    }
    .uq-btn-bank-edit {
        justify-content: center;
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
function openWithdrawModal() {
    const modal = document.getElementById('withdrawModal');
    if (modal) modal.style.display = 'flex';
}

function closeWithdrawModal() {
    const modal = document.getElementById('withdrawModal');
    if (modal) modal.style.display = 'none';
}

function setAllBalance(amount) {
    const input = document.getElementById('withdrawAmountInput');
    if (input) input.value = amount;
}

function toggleEscrowGuide() {
    if (window.innerWidth > 768) return;
    const body = document.getElementById('guideBody');
    const text = document.getElementById('guideToggleText');
    const icon = document.getElementById('guideToggleIcon');
    if (body.style.display === 'none') {
        body.style.display = 'block';
        text.innerText = 'Tutup Alur';
        icon.className = 'bi bi-chevron-up';
    } else {
        body.style.display = 'none';
        text.innerText = 'Lihat Alur';
        icon.className = 'bi bi-chevron-down';
    }
}

// Close when clicking outside dialog
document.addEventListener('click', function(e) {
    const modal = document.getElementById('withdrawModal');
    if (e.target === modal) closeWithdrawModal();
});
</script>
@endpush
