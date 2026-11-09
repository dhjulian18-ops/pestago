@extends('layouts.app')
@section('title', 'Perbaiki / Revisi Iklan — PestaGo Vendor')

@section('content')
    <div class="dashboard">
        @include('vendor._sidebar', ['active' => 'advertisements'])

        <div class="dashboard-content">
            <a href="{{ route('vendor.advertisements.index') }}"
                style="font-size:var(--font-size-sm);color:var(--gray-500);display:inline-flex;align-items:center;gap:6px;margin-bottom:8px;">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Iklan
            </a>

            <div class="dashboard-header" style="margin-top:var(--space-2);">
                <h1 class="dashboard-title"><i class="bi bi-pencil-square" style="color:var(--primary);"></i> Perbaiki &amp;
                    Unggah Ulang Iklan</h1>
                <p class="dashboard-subtitle">Perbaiki poster atau informasi materi promosi Anda sesuai catatan kurasi agar
                    dapat segera ditayangkan.</p>
            </div>

            @if($advertisement->rejection_reason)
                {{-- Kotak Catatan Kurasi --}}
                <div class="card mb-5"
                    style="border:1.5px solid #fecaca;background:#fff5f5;border-radius:14px;padding:20px;max-width:820px;">
                    <div style="display:flex;align-items:flex-start;gap:14px;">
                        <div
                            style="width:42px;height:42px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                            <i class="bi bi-chat-left-dots-fill"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="badge badge-danger" style="font-size:11px;">PERLU PERBAIKAN</span>
                                <strong style="color:#991b1b;font-size:14.5px;">Catatan dari Tim Kurasi PestaGo:</strong>
                            </div>
                            <div
                                style="color:#7f1d1d;font-size:13.5px;margin-top:8px;background:white;padding:12px 16px;border-radius:8px;border:1px solid #fecaca;font-weight:600;line-height:1.6;">
                                "{{ $advertisement->rejection_reason }}"
                            </div>
                            <p style="font-size:12.5px;color:#991b1b;margin-top:10px;margin-bottom:0;line-height:1.5;">
                                <i class="bi bi-shield-check"></i> <strong>Slot Iklan Sudah Lunas (Rp
                                    {{ number_format($advertisement->payment_amount, 0, ',', '.') }}):</strong> Anda <u>tidak
                                    perlu membayar biaya lagi</u>. Cukup perbaiki materi promosi Anda sesuai catatan di atas,
                                lalu klik tombol <strong>"Kirim Ulang Revisi Iklan"</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mb-6" style="max-width:820px;">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div style="max-width:820px;">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('vendor.advertisements.update', $advertisement) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            {{-- Ringkasan Slot Yang Telah Dibayar (Read-Only) --}}
                            <div
                                style="background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:24px;">
                                <h4
                                    style="font-size:13px;font-weight:700;color:#475569;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px;">
                                    <i class="bi bi-info-circle"></i> Informasi Slot Promosi Terdaftar
                                </h4>
                                <div
                                    style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;font-size:13px;">
                                    <div>
                                        <span style="color:#64748b;display:block;font-size:11.5px;">Posisi Papan:</span>
                                        <strong
                                            style="color:#0f172a;">{{ $plan->name ?? ucfirst(str_replace('_', ' ', $advertisement->placement)) }}</strong>
                                    </div>
                                    <div>
                                        <span style="color:#64748b;display:block;font-size:11.5px;">Paket Durasi:</span>
                                        <strong style="color:#0f172a;">Paket {{ ucfirst($advertisement->package) }}
                                            ({{ $advertisement->starts_at?->diffInDays($advertisement->ends_at) }}
                                            Hari)</strong>
                                    </div>
                                    <div>
                                        <span style="color:#64748b;display:block;font-size:11.5px;">Status Biaya:</span>
                                        <span class="badge badge-success" style="font-size:11px;">
                                            <i class="bi bi-check2"></i> Lunas (Rp
                                            {{ number_format($advertisement->payment_amount, 0, ',', '.') }})
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color:#64748b;display:block;font-size:11.5px;">Periode Tayang:</span>
                                        <strong
                                            style="color:#0f172a;">{{ $advertisement->starts_at?->translatedFormat('d M Y') }}
                                            - {{ $advertisement->ends_at?->translatedFormat('d M Y') }}</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- 1. Judul Promosi --}}
                            <div class="form-group mb-4">
                                <label class="form-label" style="font-weight:700;">Judul / Kampanye Promosi <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control"
                                    value="{{ old('title', $advertisement->title) }}" required
                                    placeholder="Contoh: Promo Spesial Paket Pernikahan Akhir Tahun">
                            </div>

                            {{-- 2. Poster Saat Ini & Ganti Poster --}}
                            <div class="form-group mb-4">
                                <label class="form-label" style="font-weight:700;">Materi Poster Promosi</label>
                                <div
                                    style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:12px;">
                                    <div>
                                        <span
                                            style="font-size:12px;color:#64748b;display:block;margin-bottom:6px;font-weight:600;">Poster
                                            Sebelumnya:</span>
                                        <img src="{{ asset('storage/' . $advertisement->poster_image) }}"
                                            alt="{{ $advertisement->title }}"
                                            style="width:180px;height:110px;object-fit:cover;border-radius:8px;border:1px solid #cbd5e1;box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                                    </div>
                                    <div style="flex:1;min-width:240px;">
                                        <span
                                            style="font-size:12px;color:#0f172a;display:block;margin-bottom:6px;font-weight:700;">Unggah
                                            Poster Baru (Revisi):</span>
                                        <input type="file" name="poster_image" class="form-control"
                                            accept="image/jpeg,image/png,image/webp" style="background:white;">
                                        <span class="form-hint"
                                            style="font-size:11.5px;color:#64748b;margin-top:6px;display:block;">
                                            Kosongkan jika tidak ingin mengganti file gambar poster. Format didukung: JPG,
                                            PNG, WebP (Maks. 3MB).
                                        </span>
                                    </div>
                                </div>

                                @if($plan && $plan->canvas_guideline)
                                    <div
                                        style="padding:10px 14px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;font-size:12px;color:#0369a1;">
                                        <strong>Panduan Ukuran ({{ $plan->name }}):</strong> {!! $plan->canvas_guideline !!}
                                    </div>
                                @endif
                            </div>

                            {{-- 3. Pengaturan Tombol & Tautan --}}
                            <div class="form-group mb-4">
                                <label class="form-label" style="font-weight:700;">Aksi Pengunjung Saat Mengklik
                                    Poster</label>
                                <div class="form-group mb-3">
                                    <label class="form-label" style="font-size:13px;color:#334155;">Tautan Tujuan
                                        (URL)</label>
                                    <input type="url" name="target_url" class="form-control" placeholder="https://..."
                                        value="{{ old('target_url', $advertisement->target_url) }}" style="font-size:13px;">
                                    <span class="form-hint" style="font-size:11px;">Kosongkan jika ingin mengarahkan
                                        otomatis ke profil toko vendor Anda di PestaGo.</span>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label" style="font-size:13px;color:#334155;">Pilihan Tombol di Atas
                                        Poster</label>
                                    @php
                                        $btn = $advertisement->button_text;
                                        $standardBtns = ['Lihat Vendor', 'Lihat Paket', 'Pesan Sekarang', 'Hubungi Vendor'];
                                        $isCustom = !empty($btn) && !in_array($btn, $standardBtns);
                                    @endphp
                                    <select name="button_option" id="buttonOptionSelect" class="form-control"
                                        style="font-size:13px;" onchange="toggleCustomButtonInput()">
                                        <option value="none" {{ empty($btn) ? 'selected' : '' }}>Tanpa Tombol (Poster tampil
                                            bersih &amp; penuh)</option>
                                        @foreach($standardBtns as $sb)
                                            <option value="{{ $sb }}" {{ $btn === $sb ? 'selected' : '' }}>Tombol: "{{ $sb }}
                                                &rarr;"</option>
                                        @endforeach
                                        <option value="custom" {{ $isCustom ? 'selected' : '' }}>Kustom (Ketik teks tombol
                                            sendiri...)</option>
                                    </select>
                                </div>

                                <div class="form-group mb-2" id="customButtonWrap"
                                    style="display:{{ $isCustom ? 'block' : 'none' }};">
                                    <label class="form-label" style="font-size:12.5px;color:#475569;">Teks Tombol
                                        Kustom</label>
                                    <input type="text" name="custom_button_text" class="form-control"
                                        placeholder="Contoh: Klaim Promo Sekarang"
                                        value="{{ old('custom_button_text', $isCustom ? $btn : '') }}"
                                        style="font-size:13px;" maxlength="40">
                                </div>

                                <hr style="border:none;border-top:1px dashed #e2e8f0;margin:18px 0;">

                                {{-- 4. Pilihan Label Badge Sudut Poster --}}
                                @php
                                    $hasBadge = old('show_badge', $advertisement->show_badge);
                                    $badgeVal = old('badge_text', $advertisement->badge_text);
                                    $badgePresets = ['Sponsor', 'Partner Resmi', 'Promo Khusus', 'Mitra Terverifikasi', 'Eksklusif'];
                                    $isCustomBadge = !empty($badgeVal) && !in_array($badgeVal, $badgePresets);
                                @endphp
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="show_badge" value="1"
                                        id="showBadgeCheck" {{ $hasBadge ? 'checked' : '' }}
                                        style="accent-color:var(--primary);" onchange="toggleBadgeSettings()">
                                    <label class="form-check-label" for="showBadgeCheck"
                                        style="font-size:13px;font-weight:600;color:#334155;">
                                        Tampilkan Label / Badge di Sudut Atas Poster
                                    </label>
                                </div>
                                <span class="form-hint"
                                    style="font-size:11.5px;color:#64748b;margin-bottom:12px;display:block;">
                                    Beri tanda pengenal resmi pada sudut poster (misal: "Sponsor", "Partner Resmi", dll)
                                    atau matikan jika ingin poster tampil bersih tanpa label.
                                </span>

                                <div id="badgeSettingsWrap"
                                    style="display:{{ $hasBadge ? 'block' : 'none' }};background:#ffffff;padding:12px 14px;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:10px;">
                                    <div class="form-group mb-2">
                                        <label class="form-label"
                                            style="font-size:12.5px;color:#475569;font-weight:600;">Pilihan Teks
                                            Label</label>
                                        <select name="badge_option" id="badgeOptionSelect" class="form-control"
                                            style="font-size:13px;" onchange="toggleCustomBadgeInput()">
                                            @foreach($badgePresets as $bp)
                                                <option value="{{ $bp }}" {{ ($badgeVal === $bp || (!$badgeVal && $bp === 'Sponsor')) ? 'selected' : '' }}>{{ $bp }}</option>
                                            @endforeach
                                            <option value="custom" {{ $isCustomBadge ? 'selected' : '' }}>Kustom (Ketik teks
                                                sendiri...)</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-0" id="customBadgeWrap"
                                        style="display:{{ $isCustomBadge ? 'block' : 'none' }};">
                                        <label class="form-label" style="font-size:12px;color:#64748b;">Teks Label Kustom
                                            (Maks. 25 Karakter)</label>
                                        <input type="text" name="custom_badge_text" id="customBadgeInput"
                                            class="form-control" placeholder="Contoh: Official Partner"
                                            value="{{ old('custom_badge_text', $isCustomBadge ? $badgeVal : '') }}"
                                            style="font-size:13px;" maxlength="25">
                                    </div>
                                </div>
                            </div>

                            <div
                                style="display:flex;justify-content:flex-end;gap:12px;margin-top:28px;padding-top:16px;border-top:1px solid #e2e8f0;">
                                <a href="{{ route('vendor.advertisements.index') }}" class="btn btn-ghost"
                                    style="border-radius:4px;box-shadow:none;">Batal</a>
                                <button type="submit" class="btn btn-primary btn-lg"
                                    style="box-shadow:none;border-radius:4px;">
                                    <i class="bi bi-send-check"></i> Kirim Ulang Revisi Iklan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleCustomButtonInput() {
            const val = document.getElementById('buttonOptionSelect').value;
            const customWrap = document.getElementById('customButtonWrap');
            if (val === 'custom') {
                customWrap.style.display = 'block';
            } else {
                customWrap.style.display = 'none';
            }
        }

        function toggleBadgeSettings() {
            const isChecked = document.getElementById('showBadgeCheck').checked;
            const wrap = document.getElementById('badgeSettingsWrap');
            wrap.style.display = isChecked ? 'block' : 'none';
        }

        function toggleCustomBadgeInput() {
            const val = document.getElementById('badgeOptionSelect').value;
            const customWrap = document.getElementById('customBadgeWrap');
            if (val === 'custom') {
                customWrap.style.display = 'block';
                document.getElementById('customBadgeInput')?.focus();
            } else {
                customWrap.style.display = 'none';
            }
        }
    </script>
@endsection