@extends('layouts.app')
@section('title', 'Pasang Iklan — Vendor PestaGo')

@section('content')
    <div class="dashboard" style="background:#fafafa;min-height:100vh;">
        @include('vendor._sidebar', ['active' => 'advertisements'])

        <div class="dashboard-content" style="padding:24px 32px;max-width:980px;">
            <a href="{{ route('vendor.advertisements.index') }}"
                style="font-size:13px;font-weight:600;color:#666666;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:16px;">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Iklan
            </a>

            {{-- Uniqlo Header --}}
            <div style="margin-bottom:28px;border-bottom:2px solid #111111;padding-bottom:14px;">
                <div
                    style="font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#d81a24;margin-bottom:4px;">
                    PROMOSI &amp; IKLAN
                </div>
                <h1 style="font-size:24px;font-weight:900;letter-spacing:-0.5px;color:#111111;margin:0 0 6px;">
                    Pengajuan Pemasangan Iklan
                </h1>
                <p style="font-size:13px;color:#666666;margin:0;">
                    Promosikan layanan terbaik Anda langsung di slide banner utama beranda PestaGo.
                </p>
            </div>

            @if($errors->any())
                <div
                    style="background:#fef2f2;border:1px solid #fee2e2;border-left:4px solid #dc2626;padding:14px 18px;margin-bottom:24px;font-size:13px;color:#991b1b;">
                    <strong style="display:block;margin-bottom:4px;"><i class="bi bi-exclamation-circle-fill"></i> Periksa
                        kembali isian formulir:</strong>
                    <ul style="margin:0;padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div style="background:#ffffff;border:1px solid #e5e5e5;padding:32px 36px;">
                <form action="{{ route('vendor.advertisements.store') }}" method="POST" enctype="multipart/form-data"
                    id="adForm">
                    @csrf

                    {{-- Hidden fixed placement: Papan Iklan Khusus Vendor (Bagian Vendor Pilihan di Beranda) --}}
                    <input type="hidden" name="placement" value="vendor_billboard">

@php
    $planModel = $plan ?? ($plans->firstWhere('placement_key', 'vendor_billboard') ?? $plans->first());
    $priceBasic = $planModel?->price_basic ?? 99000;
    $daysBasic = $planModel?->days_basic ?? 7;
    $pricePro = $planModel?->price_pro ?? 189000;
    $daysPro = $planModel?->days_pro ?? 14;
    $priceBusiness = $planModel?->price_business ?? 349000;
    $daysBusiness = $planModel?->days_business ?? 30;
    $pricePerDay = $planModel?->price_per_day ?? 15000;
    $minDays = $planModel?->min_days_custom ?? 1;
    $maxDays = $planModel?->max_days_custom ?? 90;
    $defaultCustomDays = old('custom_days', 5);
@endphp

                    {{-- 1. Info Papan Iklan Khusus Vendor --}}
                    <div style="margin-bottom:28px;">
                        <label
                            style="display:block;font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#111111;margin-bottom:8px;">
                            Papan Iklan Yang Tersedia
                        </label>

                        <div
                            style="border:2px solid #111111;background:#fafafa;padding:18px 22px;display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap;">
                            <div style="flex:1;min-width:240px;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                    <span
                                        style="background:#d81a24;color:#ffffff;font-size:10px;font-weight:800;padding:2px 8px;letter-spacing:0.5px;text-transform:uppercase;">
                                        Papan Iklan Vendor
                                    </span>
                                    <strong style="font-size:15px;color:#111111;font-weight:800;">
                                        Papan Iklan Vendor Pilihan (Beranda PestaGo)
                                    </strong>
                                </div>
                                <p style="font-size:12.5px;color:#555555;margin:0 0 10px;line-height:1.5;">
                                    Iklan tayang di papan carousel billboard khusus vendor pada bagian <strong>Vendor Pilihan</strong> di halaman beranda PestaGo. Menjangkau calon pelanggan yang mencari rekomendasi vendor acara terpercaya.
                                </p>
                                <div style="font-size:12px;color:#111111;">
                                    <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i> <strong>Status Slot:</strong> Aktif &amp; Siap Tayang
                                </div>
                            </div>

                            <div style="text-align:right;flex-shrink:0;">
                                <div style="font-size:11px;color:#888888;text-transform:uppercase;letter-spacing:0.5px;">
                                    Tarif Mulai</div>
                                <div style="font-size:20px;font-weight:900;color:#111111;letter-spacing:-0.5px;">Rp {{ number_format($priceBasic, 0, ',', '.') }}</div>
                                <div style="font-size:11px;color:#666666;">Durasi {{ $daysBasic }} Hari</div>
                            </div>
                        </div>
                    </div>

                    {{-- Panduan Ukuran Kanvas Poster (Minimalis Uniqlo Style) --}}
                    <div style="border:1px solid #e5e5e5;background:#ffffff;padding:18px 22px;margin-bottom:32px;">
                        <div
                            style="display:flex;align-items:center;gap:8px;margin-bottom:10px;font-size:13px;font-weight:800;color:#111111;">
                            <i class="bi bi-aspect-ratio" style="color:#d81a24;font-size:16px;"></i>
                            Panduan Ukuran Materi Poster Iklan
                        </div>
                        <div
                            style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;font-size:12px;color:#444444;line-height:1.6;">
                            <div style="padding:10px 14px;background:#fafafa;border:1px solid #f0f0f0;">
                                <span style="font-weight:700;color:#111111;display:block;margin-bottom:2px;">Ukuran Rekomendasi</span>
                                <strong>1200 x 675 px</strong> (Rasio 16:9 Lanskap)
                            </div>
                            <div style="padding:10px 14px;background:#fafafa;border:1px solid #f0f0f0;">
                                <span style="font-weight:700;color:#111111;display:block;margin-bottom:2px;">Alternatif Resolusi HD</span>
                                <strong>1920 x 1080 px</strong> (Full HD Lanskap)
                            </div>
                            <div style="padding:10px 14px;background:#fafafa;border:1px solid #f0f0f0;">
                                <span style="font-weight:700;color:#111111;display:block;margin-bottom:2px;">Format &amp; Ukuran Maksimal</span>
                                <strong>PNG, JPG, WebP</strong> (Maksimal 3MB)
                            </div>
                        </div>
                    </div>

                    {{-- 2. Pilih Paket Durasi Tayang (Uniqlo Clean Selector) --}}
                    <div style="margin-bottom:32px;">
                        <label
                            style="display:block;font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#111111;margin-bottom:6px;">
                            Pilih Paket Durasi Tayang <span style="color:#d81a24;">*</span>
                        </label>
                        <p style="font-size:12px;color:#777777;margin:0 0 12px;">
                            Pilih paket tetap atau tentukan durasi hari kustom sesuai kebutuhan promosi Anda.
                        </p>

                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;">
                            {{-- Paket Basic --}}
                            <label class="uq-plan-card"
                                style="border:{{ old('package', 'basic') === 'basic' ? '2px solid #111111' : '1px solid #e5e5e5' }};background:#ffffff;padding:18px;cursor:pointer;position:relative;display:block;transition:all 0.15s ease;">
                                <div
                                    style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                    <strong style="font-size:14px;font-weight:800;color:#111111;">Paket Basic</strong>
                                    <input type="radio" name="package" value="basic" {{ old('package', 'basic') === 'basic' ? 'checked' : '' }}
                                        style="accent-color:#111111;width:16px;height:16px;"
                                        onchange="updatePlanSelection()">
                                </div>
                                <div
                                    style="font-size:22px;font-weight:900;color:#111111;letter-spacing:-0.5px;margin-bottom:4px;">
                                    Rp {{ number_format($priceBasic, 0, ',', '.') }}
                                </div>
                                <div style="font-size:12px;color:#666666;">
                                    <i class="bi bi-calendar-check"></i> Durasi <strong>{{ $daysBasic }} Hari</strong>
                                </div>
                            </label>

                            {{-- Paket Pro --}}
                            <label class="uq-plan-card"
                                style="border:{{ old('package') === 'pro' ? '2px solid #111111' : '1px solid #e5e5e5' }};background:#ffffff;padding:18px;cursor:pointer;position:relative;display:block;transition:all 0.15s ease;">
                                <div
                                    style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <strong style="font-size:14px;font-weight:800;color:#111111;">Paket Pro</strong>
                                        <span
                                            style="font-size:9.5px;font-weight:800;background:#111111;color:#ffffff;padding:1px 6px;letter-spacing:0.5px;">HEMAT</span>
                                    </div>
                                    <input type="radio" name="package" value="pro" {{ old('package') === 'pro' ? 'checked' : '' }} style="accent-color:#111111;width:16px;height:16px;"
                                        onchange="updatePlanSelection()">
                                </div>
                                <div
                                    style="font-size:22px;font-weight:900;color:#111111;letter-spacing:-0.5px;margin-bottom:4px;">
                                    Rp {{ number_format($pricePro, 0, ',', '.') }}
                                </div>
                                <div style="font-size:12px;color:#666666;">
                                    <i class="bi bi-calendar-check"></i> Durasi <strong>{{ $daysPro }} Hari</strong>
                                </div>
                            </label>

                            {{-- Paket Business --}}
                            <label class="uq-plan-card"
                                style="border:{{ old('package') === 'business' ? '2px solid #111111' : '1px solid #e5e5e5' }};background:#ffffff;padding:18px;cursor:pointer;position:relative;display:block;transition:all 0.15s ease;">
                                <div
                                    style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <strong style="font-size:14px;font-weight:800;color:#111111;">Paket Business</strong>
                                        <span
                                            style="font-size:9.5px;font-weight:800;background:#d81a24;color:#ffffff;padding:1px 6px;letter-spacing:0.5px;">TERBAIK</span>
                                    </div>
                                    <input type="radio" name="package" value="business" {{ old('package') === 'business' ? 'checked' : '' }} style="accent-color:#111111;width:16px;height:16px;"
                                        onchange="updatePlanSelection()">
                                </div>
                                <div
                                    style="font-size:22px;font-weight:900;color:#111111;letter-spacing:-0.5px;margin-bottom:4px;">
                                    Rp {{ number_format($priceBusiness, 0, ',', '.') }}
                                </div>
                                <div style="font-size:12px;color:#666666;">
                                    <i class="bi bi-calendar-check"></i> Durasi <strong>{{ $daysBusiness }} Hari</strong>
                                </div>
                            </label>

                            {{-- Paket Kustom (Fleksibel Berdasarkan Hari) --}}
                            <label class="uq-plan-card"
                                style="border:{{ old('package') === 'custom' ? '2px solid #111111' : '1px solid #e5e5e5' }};background:#ffffff;padding:18px;cursor:pointer;position:relative;display:block;transition:all 0.15s ease;">
                                <div
                                    style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <strong style="font-size:14px;font-weight:800;color:#111111;">Paket Kustom</strong>
                                        <span
                                            style="font-size:9.5px;font-weight:800;background:#0284c7;color:#ffffff;padding:1px 6px;letter-spacing:0.5px;">FLEKSIBEL</span>
                                    </div>
                                    <input type="radio" name="package" value="custom" {{ old('package') === 'custom' ? 'checked' : '' }} style="accent-color:#111111;width:16px;height:16px;"
                                        onchange="updatePlanSelection()">
                                </div>
                                <div
                                    style="font-size:22px;font-weight:900;color:#111111;letter-spacing:-0.5px;margin-bottom:4px;">
                                    Rp {{ number_format($pricePerDay, 0, ',', '.') }}<span style="font-size:12px;font-weight:600;color:#666666;">/hari</span>
                                </div>
                                <div style="font-size:12px;color:#0284c7;font-weight:700;">
                                    <i class="bi bi-sliders"></i> Bebas Tentukan Hari
                                </div>
                            </label>
                        </div>

                        {{-- Section Input Durasi Iklan Kustom (Tampil saat memilih Paket Kustom) --}}
                        <div id="customDurationSection" style="display:{{ old('package') === 'custom' ? 'block' : 'none' }};margin-top:16px;border:2px solid #111111;background:#fafafa;padding:20px 24px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
                                <div>
                                    <div style="font-size:13px;font-weight:800;color:#111111;letter-spacing:0.3px;display:flex;align-items:center;gap:6px;">
                                        <i class="bi bi-sliders" style="color:#d81a24;"></i> Tentukan Jumlah Hari Tayang Iklan Kustom
                                    </div>
                                    <div style="font-size:12px;color:#666666;margin-top:2px;">
                                        Pilih durasi hari bebas (Minimal {{ $minDays }} hari, Maksimal {{ $maxDays }} hari).
                                    </div>
                                </div>
                                <div style="font-size:11px;font-weight:700;color:#111111;background:#ffffff;border:1px solid #e5e5e5;padding:4px 10px;">
                                    Tarif: Rp {{ number_format($pricePerDay, 0, ',', '.') }} / hari
                                </div>
                            </div>

                            {{-- Stepper & Direct Input --}}
                            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
                                <div style="display:inline-flex;align-items:center;border:1px solid #111111;background:#ffffff;">
                                    <button type="button" onclick="adjustCustomDays(-1)" style="width:42px;height:42px;background:#ffffff;border:none;border-right:1px solid #e5e5e5;font-size:18px;font-weight:700;cursor:pointer;color:#111111;transition:background 0.15s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='#ffffff'">-</button>
                                    <input type="number" name="custom_days" id="customDaysInput" value="{{ $defaultCustomDays }}" min="{{ $minDays }}" max="{{ $maxDays }}" style="width:70px;height:42px;border:none;text-align:center;font-size:16px;font-weight:900;color:#111111;outline:none;" oninput="recalculateCustomPrice()">
                                    <button type="button" onclick="adjustCustomDays(1)" style="width:42px;height:42px;background:#ffffff;border:none;border-left:1px solid #e5e5e5;font-size:18px;font-weight:700;cursor:pointer;color:#111111;transition:background 0.15s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='#ffffff'">+</button>
                                </div>
                                <span style="font-size:14px;font-weight:800;color:#111111;">HARI</span>

                                {{-- Pilihan Cepat (Preset) --}}
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <span style="font-size:11px;color:#888888;margin-right:2px;">Pilihan Cepat:</span>
                                    @foreach([3, 5, 10, 15, 20, 25, 30, 45, 60] as $presetDay)
                                        @if($presetDay >= $minDays && $presetDay <= $maxDays)
                                            <button type="button" onclick="setCustomDays({{ $presetDay }})" style="border:1px solid #cccccc;background:#ffffff;padding:5px 10px;font-size:11.5px;font-weight:700;color:#333333;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.borderColor='#111111';this.style.background='#f9f9f9'" onmouseout="this.style.borderColor='#cccccc';this.style.background='#ffffff'">
                                                {{ $presetDay }} Hari
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            {{-- Live Price Summary Box --}}
                            <div style="border:1px solid #e5e5e5;background:#ffffff;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                                <div style="font-size:12.5px;color:#444444;">
                                    <span style="color:#888888;">Kalkulasi Biaya:</span> 
                                    <strong id="customFormulaText">{{ $defaultCustomDays }} Hari × Rp {{ number_format($pricePerDay, 0, ',', '.') }}</strong>
                                </div>
                                <div style="text-align:right;">
                                    <span style="font-size:11px;color:#666666;text-transform:uppercase;letter-spacing:0.5px;display:block;">Total Biaya Iklan Kustom</span>
                                    <div id="customTotalPriceDisplay" style="font-size:22px;font-weight:900;color:#d81a24;letter-spacing:-0.5px;">
                                        Rp {{ number_format($defaultCustomDays * $pricePerDay, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Judul / Kampanye Promosi --}}
                    <div style="margin-bottom:24px;">
                        <label
                            style="display:block;font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#111111;margin-bottom:6px;">
                            Judul / Kampanye Promosi <span style="color:#d81a24;">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}"
                            placeholder="Contoh: Promo Spesial Paket Pernikahan Akhir Tahun" required
                            style="width:100%;height:46px;padding:10px 14px;font-size:14px;border:1px solid #cccccc;outline:none;background:#ffffff;font-family:inherit;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#111111'" onblur="this.style.borderColor='#cccccc'">
                    </div>

                    {{-- 4. Unggah File Poster Iklan --}}
                    <div style="margin-bottom:24px;">
                        <label
                            style="display:block;font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#111111;margin-bottom:6px;">
                            Unggah File Poster Iklan <span style="color:#d81a24;">*</span>
                        </label>

                        <div
                            style="border:1px dashed #aaaaaa;background:#fafafa;padding:24px;text-align:center;position:relative;">
                            <input type="file" name="poster_image" id="posterInput" accept="image/*" required
                                style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;"
                                onchange="previewPoster(this)">

                            <div id="posterPlaceholder">
                                <i class="bi bi-cloud-arrow-up"
                                    style="font-size:32px;color:#666666;display:block;margin-bottom:8px;"></i>
                                <div style="font-size:13px;font-weight:700;color:#111111;margin-bottom:4px;">
                                    Klik atau seret file gambar poster ke sini
                                </div>
                                <div style="font-size:11.5px;color:#888888;">
                                    Format JPG, PNG, atau WebP (Rasio 16:9, Maksimal 3MB)
                                </div>
                            </div>

                            <div id="posterPreviewWrap" style="display:none;margin-top:8px;">
                                <img id="posterPreviewImg" src="" alt="Preview Poster"
                                    style="max-height:180px;max-width:100%;border:1px solid #e5e5e5;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                                <div style="font-size:11.5px;color:#16a34a;margin-top:6px;font-weight:600;">
                                    <i class="bi bi-check-circle-fill"></i> File gambar siap diunggah. Klik untuk mengganti.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Tanggal Mulai Tayang --}}
                    <div style="margin-bottom:32px;">
                        <label
                            style="display:block;font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#111111;margin-bottom:6px;">
                            Tanggal Mulai Tayang <span style="color:#d81a24;">*</span>
                        </label>
                        <input type="date" name="starts_at" min="{{ date('Y-m-d') }}"
                            value="{{ old('starts_at', date('Y-m-d')) }}" required
                            style="width:100%;height:46px;padding:10px 14px;font-size:14px;border:1px solid #cccccc;outline:none;background:#ffffff;font-family:inherit;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#111111'" onblur="this.style.borderColor='#cccccc'">
                        <span style="display:block;font-size:11.5px;color:#888888;margin-top:4px;">
                            Iklan akan ditinjau dan tayang otomatis sesuai tanggal mulai setelah disetujui kurasi.
                        </span>
                    </div>

                    {{-- 6. Opsi Kustomisasi Tambahan di Atas Poster (Collapsible Opsional) --}}
                    <div style="border:1px solid #e5e5e5;background:#ffffff;padding:20px;margin-bottom:32px;">
                        <button type="button" onclick="toggleExtraOptions()"
                            style="background:none;border:none;padding:0;cursor:pointer;display:flex;align-items:center;justify-content:space-between;width:100%;text-align:left;">
                            <span
                                style="font-size:12px;font-weight:800;letter-spacing:0.5px;text-transform:uppercase;color:#111111;">
                                <i class="bi bi-sliders2"></i> Pengaturan Tambahan di Atas Poster (Opsional)
                            </span>
                            <i class="bi bi-chevron-down" id="extraOptionsIcon"
                                style="font-size:13px;color:#666666;transition:transform 0.2s;"></i>
                        </button>

                        <div id="extraOptionsContent"
                            style="display:none;margin-top:16px;padding-top:16px;border-top:1px solid #f0f0f0;">
                            <div style="font-size:12px;color:#666666;margin-bottom:14px;line-height:1.5;">
                                Jika poster Anda sudah berdesain lengkap, Anda <strong>tidak perlu</strong> mengisi opsi
                                ini. Poster akan ditampilkan penuh secara murni tanpa teks yang menutupi.
                            </div>

                            {{-- Checkbox Teks Overlay --}}
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                                <input type="checkbox" name="show_overlay_text" value="1" id="showTextCheck" {{ old('show_overlay_text') ? 'checked' : '' }}
                                    style="accent-color:#111111;width:16px;height:16px;">
                                <label for="showTextCheck"
                                    style="font-size:13px;color:#333333;font-weight:600;cursor:pointer;">
                                    Tampilkan teks nama vendor &amp; judul di atas gambar
                                </label>
                            </div>

                            {{-- Tombol Aksi di Atas Poster --}}
                            <div style="margin-bottom:14px;">
                                <label
                                    style="display:block;font-size:12px;font-weight:700;color:#333333;margin-bottom:4px;">Tombol
                                    Aksi di Atas Poster</label>
                                <select name="button_option" id="buttonOptionSelect"
                                    style="width:100%;height:40px;padding:8px 12px;font-size:13px;border:1px solid #cccccc;outline:none;"
                                    onchange="toggleCustomButtonInput()">
                                    <option value="none" selected>Tanpa Tombol (Poster tampil murni &amp; bersih)</option>
                                    <option value="Lihat Vendor">Tombol: "Lihat Vendor →"</option>
                                    <option value="Lihat Paket">Tombol: "Lihat Paket →"</option>
                                    <option value="Pesan Sekarang">Tombol: "Pesan Sekarang →"</option>
                                    <option value="Hubungi Vendor">Tombol: "Hubungi Vendor →"</option>
                                    <option value="custom">Kustom (Ketik teks tombol sendiri...)</option>
                                </select>
                            </div>

                            <div id="customButtonWrap" style="display:none;margin-bottom:14px;">
                                <label style="display:block;font-size:12px;color:#555555;margin-bottom:4px;">Teks Tombol
                                    Kustom</label>
                                <input type="text" name="custom_button_text" placeholder="Contoh: Klaim Promo Sekarang"
                                    maxlength="40"
                                    style="width:100%;height:38px;padding:6px 12px;font-size:13px;border:1px solid #cccccc;">
                            </div>

                            {{-- Label Badge Sudut Poster --}}
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                                <input type="checkbox" name="show_badge" value="1" id="showBadgeCheck" {{ old('show_badge') ? 'checked' : '' }} style="accent-color:#111111;width:16px;height:16px;"
                                    onchange="toggleBadgeSettings()">
                                <label for="showBadgeCheck"
                                    style="font-size:13px;color:#333333;font-weight:600;cursor:pointer;">
                                    Tampilkan Label / Badge di Sudut Atas Poster
                                </label>
                            </div>

                            <div id="badgeSettingsWrap"
                                style="display:{{ old('show_badge') ? 'block' : 'none' }};margin-top:8px;">
                                <select name="badge_option" id="badgeOptionSelect"
                                    style="width:100%;height:40px;padding:8px 12px;font-size:13px;border:1px solid #cccccc;outline:none;">
                                    <option value="Sponsor" selected>Sponsor</option>
                                    <option value="Partner Resmi">Partner Resmi</option>
                                    <option value="Promo Khusus">Promo Khusus</option>
                                    <option value="Mitra Terverifikasi">Mitra Terverifikasi</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div
                        style="display:flex;align-items:center;justify-content:flex-end;gap:14px;border-top:1px solid #f0f0f0;padding-top:24px;">
                        <a href="{{ route('vendor.advertisements.index') }}"
                            style="font-size:13px;font-weight:700;color:#666666;text-decoration:none;padding:12px 20px;border:1px solid #dddddd;background:#ffffff;">
                            Batal
                        </a>
                        <button type="submit"
                            style="font-size:14px;font-weight:800;color:#ffffff;background:#111111;border:1px solid #111111;padding:12px 28px;cursor:pointer;letter-spacing:0.5px;display:inline-flex;align-items:center;gap:8px;transition:background 0.15s ease;"
                            onmouseover="this.style.background='#d81a24';this.style.borderColor='#d81a24';"
                            onmouseout="this.style.background='#111111';this.style.borderColor='#111111';">
                            <i class="bi bi-qr-code-scan"></i> Lanjut ke Pembayaran QRIS &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const customPricePerDay = {{ (float) $pricePerDay }};
        const customMinDays = {{ (int) $minDays }};
        const customMaxDays = {{ (int) $maxDays }};

        function updatePlanSelection() {
            const cards = document.querySelectorAll('.uq-plan-card');
            let selectedPackage = 'basic';
            cards.forEach(card => {
                const radio = card.querySelector('input[type="radio"]');
                if (radio && radio.checked) {
                    selectedPackage = radio.value;
                    card.style.borderColor = '#111111';
                    card.style.borderWidth = '2px';
                    card.style.background = '#ffffff';
                } else {
                    card.style.borderColor = '#e5e5e5';
                    card.style.borderWidth = '1px';
                    card.style.background = '#ffffff';
                }
            });

            const customSection = document.getElementById('customDurationSection');
            if (customSection) {
                if (selectedPackage === 'custom') {
                    customSection.style.display = 'block';
                    recalculateCustomPrice();
                } else {
                    customSection.style.display = 'none';
                }
            }
        }

        function adjustCustomDays(delta) {
            const input = document.getElementById('customDaysInput');
            if (!input) return;
            let val = parseInt(input.value) || customMinDays;
            val += delta;
            if (val < customMinDays) val = customMinDays;
            if (val > customMaxDays) val = customMaxDays;
            input.value = val;
            recalculateCustomPrice();
        }

        function setCustomDays(days) {
            const input = document.getElementById('customDaysInput');
            if (!input) return;
            let val = parseInt(days) || customMinDays;
            if (val < customMinDays) val = customMinDays;
            if (val > customMaxDays) val = customMaxDays;
            input.value = val;
            recalculateCustomPrice();
        }

        function recalculateCustomPrice() {
            const input = document.getElementById('customDaysInput');
            if (!input) return;
            let days = parseInt(input.value) || customMinDays;
            if (days < customMinDays) days = customMinDays;
            if (days > customMaxDays) days = customMaxDays;

            const total = days * customPricePerDay;
            const formattedPrice = new Intl.NumberFormat('id-ID').format(customPricePerDay);
            const formattedTotal = new Intl.NumberFormat('id-ID').format(total);

            const formulaEl = document.getElementById('customFormulaText');
            if (formulaEl) {
                formulaEl.innerText = `${days} Hari × Rp ${formattedPrice}`;
            }

            const totalEl = document.getElementById('customTotalPriceDisplay');
            if (totalEl) {
                totalEl.innerText = `Rp ${formattedTotal}`;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            updatePlanSelection();
            recalculateCustomPrice();
        });

        function previewPoster(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('posterPlaceholder').style.display = 'none';
                    document.getElementById('posterPreviewWrap').style.display = 'block';
                    document.getElementById('posterPreviewImg').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function toggleExtraOptions() {
            const content = document.getElementById('extraOptionsContent');
            const icon = document.getElementById('extraOptionsIcon');
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }

        function toggleCustomButtonInput() {
            const sel = document.getElementById('buttonOptionSelect');
            const wrap = document.getElementById('customButtonWrap');
            wrap.style.display = sel.value === 'custom' ? 'block' : 'none';
        }

        function toggleBadgeSettings() {
            const check = document.getElementById('showBadgeCheck');
            const wrap = document.getElementById('badgeSettingsWrap');
            wrap.style.display = check.checked ? 'block' : 'none';
        }
    </script>
@endsection