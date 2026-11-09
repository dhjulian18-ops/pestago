@extends('layouts.app')
@section('title', 'Pengaturan Platform & Integrasi — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'settings'])

    <div class="dashboard-content">
        <div class="dashboard-header" style="margin-bottom:24px;">
            <h1 class="dashboard-title">Pengaturan Platform &amp; Integrasi</h1>
            <p class="dashboard-subtitle">Kelola konfigurasi WhatsApp Gateway Foonte, API Key Midtrans, serta komisi dan pajak platform</p>
        </div>

        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:14px 18px;border-radius:2px;margin-bottom:24px;font-size:13.5px;font-weight:600;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill" style="font-size:18px;"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:14px 18px;border-radius:2px;margin-bottom:24px;font-size:13.5px;font-weight:600;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:18px;"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:14px 18px;border-radius:2px;margin-bottom:24px;font-size:13px;">
            <div style="font-weight:800;margin-bottom:6px;">Terdapat beberapa kesalahan:</div>
            <ul style="margin:0;padding-left:20px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div style="display:flex;flex-direction:column;gap:28px;max-width:880px;">

            {{-- 1. WHATSAPP GATEWAY (FOONTE) --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;">
                <div style="padding:18px 24px;background:#fafafa;border-bottom:1px solid #e5e5e5;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="width:34px;height:34px;background:#25D366;color:#ffffff;display:inline-flex;align-items:center;justify-content:center;border-radius:2px;font-size:18px;">
                            <i class="bi bi-whatsapp"></i>
                        </span>
                        <div>
                            <h2 style="font-size:15px;font-weight:800;color:#111111;margin:0;">WhatsApp Gateway (Foonte)</h2>
                            <p style="font-size:12px;color:#666666;margin:2px 0 0;">Untuk pengiriman kode OTP verifikasi pendaftaran akun &amp; reset lupa password</p>
                        </div>
                    </div>
                    @if(!empty($foonteToken) && $foonteStatus)
                        <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:800;padding:4px 10px;border-radius:2px;display:inline-flex;align-items:center;gap:5px;">
                            <span style="width:7px;height:7px;background:#15803d;border-radius:50%;display:inline-block;"></span> AKTIF
                        </span>
                    @else
                        <span style="background:#f1f5f9;color:#64748b;font-size:11px;font-weight:800;padding:4px 10px;border-radius:2px;">
                            BELUM TERHUBUNG
                        </span>
                    @endif
                </div>

                <div style="padding:24px;">
                    <form action="{{ route('admin.settings.platform.update') }}" method="POST">
                        @csrf

                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:12.5px;font-weight:800;color:#111111;margin-bottom:6px;">
                                Status WhatsApp Gateway
                            </label>
                            <div style="display:flex;align-items:center;gap:20px;">
                                <label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#111111;cursor:pointer;">
                                    <input type="radio" name="foonte_status" value="1" {{ $foonteStatus ? 'checked' : '' }} style="accent-color:#111111;width:17px;height:17px;">
                                    Aktif (Kirim OTP via WhatsApp)
                                </label>
                                <label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#666666;cursor:pointer;">
                                    <input type="radio" name="foonte_status" value="0" {{ !$foonteStatus ? 'checked' : '' }} style="accent-color:#111111;width:17px;height:17px;">
                                    Nonaktif
                                </label>
                            </div>
                        </div>

                        <div style="margin-bottom:16px;">
                            <label for="foonte_token" style="display:block;font-size:12.5px;font-weight:800;color:#111111;margin-bottom:6px;">
                                Token Foonte API <span style="color:#D81A24;">*</span>
                            </label>
                            <div style="position:relative;max-width:540px;">
                                <input type="password" id="foonte_token" name="foonte_token" value="{{ old('foonte_token', $foonteToken) }}" placeholder="cth: 1a2b3c4d5e6f..." style="width:100%;height:42px;border:1px solid #d4d4d4;border-radius:2px;padding:0 40px 0 12px;font-size:14px;font-family:monospace;box-sizing:border-box;">
                                <button type="button" onclick="togglePasswordVisibility('foonte_token', this)" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#666;cursor:pointer;padding:6px;font-size:15px;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div style="font-size:11.5px;color:#767676;margin-top:6px;line-height:1.4;">
                                Ambil token perangkat WhatsApp Anda dari dashboard resmi Fonnte di <a href="https://app.fonnte.com" target="_blank" style="color:#111111;font-weight:700;text-decoration:underline;">app.fonnte.com</a> (Menu <em>Device</em> &rarr; <em>API Token</em>).
                            </div>
                        </div>

                        {{-- Hidden inputs to preserve other fields if submitting this card --}}
                        <input type="hidden" name="tax_percentage" value="{{ $taxPercentage }}">
                        <input type="hidden" name="platform_fee_percentage" value="{{ $platformFeePercentage }}">
                        <input type="hidden" name="escrow_auto_release_days" value="{{ $escrowAutoReleaseDays }}">
                        <input type="hidden" name="midtrans_server_key" value="{{ $midtransServerKey }}">
                        <input type="hidden" name="midtrans_client_key" value="{{ $midtransClientKey }}">
                        <input type="hidden" name="midtrans_merchant_id" value="{{ $midtransMerchantId }}">
                        <input type="hidden" name="midtrans_is_production" value="{{ $midtransIsProduction ? '1' : '0' }}">

                        <div style="padding-top:14px;display:flex;justify-content:flex-end;">
                            <button type="submit" style="height:38px;padding:0 20px;background:#111111;color:#ffffff;border:none;border-radius:2px;font-size:12.5px;font-weight:700;letter-spacing:0.3px;cursor:pointer;">
                                Simpan Token Foonte
                            </button>
                        </div>
                    </form>

                    {{-- Form Test Kirim WhatsApp --}}
                    <div style="margin-top:20px;padding-top:18px;border-top:1px dashed #e5e5e5;background:#fcfcfc;padding:16px;border-radius:2px;">
                        <div style="font-size:12px;font-weight:800;color:#111111;margin-bottom:4px;display:flex;align-items:center;gap:6px;">
                            <i class="bi bi-send-check" style="color:#25D366;font-size:15px;"></i>
                            Uji Coba Pengiriman Pesan WhatsApp
                        </div>
                        <div style="font-size:11.5px;color:#666666;margin-bottom:12px;">
                            Kirim pesan percobaan ke nomor Anda untuk memastikan Token Foonte dan perangkat WhatsApp sudah terhubung dengan baik.
                        </div>
                        <form action="{{ route('admin.settings.foonte.test') }}" method="POST" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                            @csrf
                            <input type="text" name="phone" placeholder="cth: 081234567890" required style="width:220px;height:38px;border:1px solid #d4d4d4;border-radius:2px;padding:0 12px;font-size:13px;">
                            <button type="submit" style="height:38px;padding:0 16px;background:#25D366;color:#ffffff;border:none;border-radius:2px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                                <i class="bi bi-whatsapp"></i> Kirim Pesan Uji Coba
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 2. PAYMENT GATEWAY (MIDTRANS) --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;">
                <div style="padding:18px 24px;background:#fafafa;border-bottom:1px solid #e5e5e5;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="width:34px;height:34px;background:#002B49;color:#00B4D8;display:inline-flex;align-items:center;justify-content:center;border-radius:2px;font-size:18px;font-weight:900;">
                            M
                        </span>
                        <div>
                            <h2 style="font-size:15px;font-weight:800;color:#111111;margin:0;">Payment Gateway (Midtrans)</h2>
                            <p style="font-size:12px;color:#666666;margin:2px 0 0;">Kredensial API untuk transaksi pembayaran non-tunai (QRIS &amp; Snap)</p>
                        </div>
                    </div>
                    @if($midtransIsProduction)
                        <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:800;padding:4px 10px;border-radius:2px;display:inline-flex;align-items:center;gap:5px;">
                            <span style="width:7px;height:7px;background:#15803d;border-radius:50%;display:inline-block;"></span> MODE PRODUCTION (LIVE)
                        </span>
                    @else
                        <span style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:800;padding:4px 10px;border-radius:2px;display:inline-flex;align-items:center;gap:5px;">
                            <span style="width:7px;height:7px;background:#f59e0b;border-radius:50%;display:inline-block;"></span> MODE SANDBOX (TESTING)
                        </span>
                    @endif
                </div>

                <div style="padding:24px;">
                    <form action="{{ route('admin.settings.platform.update') }}" method="POST">
                        @csrf

                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:12.5px;font-weight:800;color:#111111;margin-bottom:6px;">
                                Lingkungan Transaksi (Environment)
                            </label>
                            <div style="display:flex;align-items:center;gap:20px;">
                                <label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#111111;cursor:pointer;">
                                    <input type="radio" name="midtrans_is_production" value="0" {{ !$midtransIsProduction ? 'checked' : '' }} style="accent-color:#111111;width:17px;height:17px;">
                                    Sandbox (Testing / Uji Coba)
                                </label>
                                <label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#111111;cursor:pointer;">
                                    <input type="radio" name="midtrans_is_production" value="1" {{ $midtransIsProduction ? 'checked' : '' }} style="accent-color:#111111;width:17px;height:17px;">
                                    Production (Live Transaksi Nyata)
                                </label>
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;margin-bottom:16px;">
                            <div>
                                <label for="midtrans_server_key" style="display:block;font-size:12.5px;font-weight:800;color:#111111;margin-bottom:6px;">
                                    Server Key Midtrans <span style="color:#D81A24;">*</span>
                                </label>
                                <div style="position:relative;">
                                    <input type="password" id="midtrans_server_key" name="midtrans_server_key" value="{{ old('midtrans_server_key', $midtransServerKey) }}" placeholder="Mid-server-..." style="width:100%;height:42px;border:1px solid #d4d4d4;border-radius:2px;padding:0 40px 0 12px;font-size:13.5px;font-family:monospace;box-sizing:border-box;">
                                    <button type="button" onclick="togglePasswordVisibility('midtrans_server_key', this)" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#666;cursor:pointer;padding:6px;font-size:15px;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="midtrans_client_key" style="display:block;font-size:12.5px;font-weight:800;color:#111111;margin-bottom:6px;">
                                    Client Key Midtrans <span style="color:#D81A24;">*</span>
                                </label>
                                <div style="position:relative;">
                                    <input type="text" id="midtrans_client_key" name="midtrans_client_key" value="{{ old('midtrans_client_key', $midtransClientKey) }}" placeholder="Mid-client-..." style="width:100%;height:42px;border:1px solid #d4d4d4;border-radius:2px;padding:0 12px;font-size:13.5px;font-family:monospace;box-sizing:border-box;">
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom:16px;max-width:320px;">
                            <label for="midtrans_merchant_id" style="display:block;font-size:12.5px;font-weight:800;color:#111111;margin-bottom:6px;">
                                Merchant ID (Opsional)
                            </label>
                            <input type="text" id="midtrans_merchant_id" name="midtrans_merchant_id" value="{{ old('midtrans_merchant_id', $midtransMerchantId) }}" placeholder="cth: G971208429" style="width:100%;height:42px;border:1px solid #d4d4d4;border-radius:2px;padding:0 12px;font-size:13.5px;font-family:monospace;box-sizing:border-box;">
                        </div>

                        <div style="font-size:11.5px;color:#767676;line-height:1.5;margin-bottom:18px;">
                            Kredensial API dapat diperoleh dari <a href="https://dashboard.midtrans.com" target="_blank" style="color:#111111;font-weight:700;text-decoration:underline;">dashboard.midtrans.com</a> pada menu <em>Settings</em> &rarr; <em>Access Keys</em>.
                        </div>

                        {{-- Hidden inputs to preserve other fields --}}
                        <input type="hidden" name="tax_percentage" value="{{ $taxPercentage }}">
                        <input type="hidden" name="platform_fee_percentage" value="{{ $platformFeePercentage }}">
                        <input type="hidden" name="escrow_auto_release_days" value="{{ $escrowAutoReleaseDays }}">
                        <input type="hidden" name="foonte_token" value="{{ $foonteToken }}">
                        <input type="hidden" name="foonte_status" value="{{ $foonteStatus ? '1' : '0' }}">

                        <div style="padding-top:14px;border-top:1px solid #f0f0f0;display:flex;justify-content:flex-end;">
                            <button type="submit" style="height:38px;padding:0 20px;background:#111111;color:#ffffff;border:none;border-radius:2px;font-size:12.5px;font-weight:700;letter-spacing:0.3px;cursor:pointer;">
                                Simpan Pengaturan Midtrans
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. KOMISI, PAJAK & ESCROW --}}
            <div style="background:#ffffff;border:1px solid #e5e5e5;border-radius:2px;overflow:hidden;">
                <div style="padding:18px 24px;background:#fafafa;border-bottom:1px solid #e5e5e5;">
                    <h2 style="font-size:15px;font-weight:800;color:#111111;margin:0;">Komisi Platform &amp; Pemotongan Pajak</h2>
                    <p style="font-size:12px;color:#666666;margin:2px 0 0;">Atur persentase potongan pajak vendor, biaya layanan platform customer, dan waktu auto-release escrow</p>
                </div>

                <div style="padding:24px;">
                    <form action="{{ route('admin.settings.platform.update') }}" method="POST">
                        @csrf

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;margin-bottom:20px;">
                            <div>
                                <label style="display:block;font-size:12.5px;font-weight:800;color:#111111;margin-bottom:4px;">
                                    Pemotongan Pajak Vendor (%)
                                </label>
                                <div style="font-size:11.5px;color:#666666;margin-bottom:8px;line-height:1.4;">
                                    Dipotong dari hasil transaksi vendor saat pesanan selesai sebelum masuk saldo aktif.
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <input type="number" step="0.1" min="0" max="100" name="tax_percentage" value="{{ old('tax_percentage', $taxPercentage) }}" required style="width:110px;height:40px;border:1px solid #d4d4d4;border-radius:2px;padding:0 10px;font-size:14px;font-weight:700;">
                                    <span style="font-size:14px;font-weight:700;color:#555;">%</span>
                                </div>
                            </div>

                            <div>
                                <label style="display:block;font-size:12.5px;font-weight:800;color:#111111;margin-bottom:4px;">
                                    Biaya Layanan Customer (%)
                                </label>
                                <div style="font-size:11.5px;color:#666666;margin-bottom:8px;line-height:1.4;">
                                    Ditambahkan ke invoice customer saat checkout untuk biaya perlindungan platform.
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <input type="number" step="0.1" min="0" max="100" name="platform_fee_percentage" value="{{ old('platform_fee_percentage', $platformFeePercentage) }}" required style="width:110px;height:40px;border:1px solid #d4d4d4;border-radius:2px;padding:0 10px;font-size:14px;font-weight:700;">
                                    <span style="font-size:14px;font-weight:700;color:#555;">%</span>
                                </div>
                            </div>

                            <div>
                                <label style="display:block;font-size:12.5px;font-weight:800;color:#111111;margin-bottom:4px;">
                                    Auto-Release Escrow (Hari)
                                </label>
                                <div style="font-size:11.5px;color:#666666;margin-bottom:8px;line-height:1.4;">
                                    Jeda hari otomatis merilis dana pesanan ke saldo vendor jika customer tidak konfirmasi.
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <input type="number" min="1" max="30" name="escrow_auto_release_days" value="{{ old('escrow_auto_release_days', $escrowAutoReleaseDays) }}" required style="width:110px;height:40px;border:1px solid #d4d4d4;border-radius:2px;padding:0 10px;font-size:14px;font-weight:700;">
                                    <span style="font-size:13px;font-weight:600;color:#555;">Hari</span>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden inputs to preserve other fields --}}
                        <input type="hidden" name="foonte_token" value="{{ $foonteToken }}">
                        <input type="hidden" name="foonte_status" value="{{ $foonteStatus ? '1' : '0' }}">
                        <input type="hidden" name="midtrans_server_key" value="{{ $midtransServerKey }}">
                        <input type="hidden" name="midtrans_client_key" value="{{ $midtransClientKey }}">
                        <input type="hidden" name="midtrans_merchant_id" value="{{ $midtransMerchantId }}">
                        <input type="hidden" name="midtrans_is_production" value="{{ $midtransIsProduction ? '1' : '0' }}">

                        <div style="padding-top:14px;border-top:1px solid #f0f0f0;display:flex;justify-content:flex-end;">
                            <button type="submit" style="height:38px;padding:0 20px;background:#111111;color:#ffffff;border:none;border-radius:2px;font-size:12.5px;font-weight:700;letter-spacing:0.3px;cursor:pointer;">
                                Simpan Pajak &amp; Komisi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
@endpush
