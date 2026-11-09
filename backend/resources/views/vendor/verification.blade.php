@extends('layouts.app')
@section('title', 'Persyaratan & Verifikasi Usaha — Vendor PestaGo')

@section('content')
<div class="dashboard">
    @include('vendor._sidebar', ['active' => 'verification'])

    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Persyaratan & Verifikasi Usaha 📋</h1>
            <p class="dashboard-subtitle">Lengkapi identitas, rekening, dan dokumen legalitas agar toko Anda disetujui untuk membuat katalog layanan.</p>
        </div>

        {{-- Status Stepper --}}
        <div class="card mb-6" style="background:var(--white);border-radius:var(--radius-lg);padding:var(--space-5);">
            <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:var(--space-3);position:relative;">
                {{-- Step 1 --}}
                <div style="text-align:center;">
                    <div style="width:40px;height:40px;border-radius:50%;background:#10B981;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700;margin-bottom:8px;">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <div style="font-size:var(--font-size-xs);font-weight:700;color:var(--gray-900);">1. Registrasi Akun</div>
                    <div style="font-size:11px;color:var(--gray-500);">Selesai</div>
                </div>

                {{-- Step 2 --}}
                <div style="text-align:center;">
                    @php
                        $step2Done = in_array($vendor->status, ['pending', 'active']);
                        $step2Active = in_array($vendor->status, ['incomplete', 'needs_revision']);
                    @endphp
                    <div style="width:40px;height:40px;border-radius:50%;background:{{ $step2Done ? '#10B981' : ($step2Active ? '#6366F1' : '#E2E8F0') }};color:{{ $step2Done || $step2Active ? '#fff' : '#64748B' }};display:inline-flex;align-items:center;justify-content:center;font-weight:700;margin-bottom:8px;">
                        @if($step2Done) <i class="bi bi-check-lg"></i> @else 2 @endif
                    </div>
                    <div style="font-size:var(--font-size-xs);font-weight:700;color:{{ $step2Active ? 'var(--primary)' : 'var(--gray-900)' }};">2. Lengkapi Dokumen</div>
                    <div style="font-size:11px;color:var(--gray-500);">
                        @if($vendor->status === 'incomplete') Wajib Diisi
                        @elseif($vendor->status === 'needs_revision') Perlu Koreksi
                        @else Sudah Terkirim @endif
                    </div>
                </div>

                {{-- Step 3 --}}
                <div style="text-align:center;">
                    @php
                        $step3Done = $vendor->status === 'active';
                        $step3Active = $vendor->status === 'pending';
                    @endphp
                    <div style="width:40px;height:40px;border-radius:50%;background:{{ $step3Done ? '#10B981' : ($step3Active ? '#F59E0B' : '#E2E8F0') }};color:{{ $step3Done || $step3Active ? '#fff' : '#64748B' }};display:inline-flex;align-items:center;justify-content:center;font-weight:700;margin-bottom:8px;">
                        @if($step3Done) <i class="bi bi-check-lg"></i> @elseif($step3Active) <i class="bi bi-hourglass-split"></i> @else 3 @endif
                    </div>
                    <div style="font-size:var(--font-size-xs);font-weight:700;color:{{ $step3Active ? '#D97706' : 'var(--gray-900)' }};">3. Tinjauan Tim PestaGo</div>
                    <div style="font-size:11px;color:var(--gray-500);">
                        @if($step3Active) Sedang Diverifikasi
                        @elseif($step3Done) Disetujui
                        @else Antrean @endif
                    </div>
                </div>

                {{-- Step 4 --}}
                <div style="text-align:center;">
                    <div style="width:40px;height:40px;border-radius:50%;background:{{ $vendor->status === 'active' ? '#10B981' : '#E2E8F0' }};color:{{ $vendor->status === 'active' ? '#fff' : '#64748B' }};display:inline-flex;align-items:center;justify-content:center;font-weight:700;margin-bottom:8px;">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div style="font-size:var(--font-size-xs);font-weight:700;color:{{ $vendor->status === 'active' ? '#059669' : 'var(--gray-900)' }};">4. Toko Aktif</div>
                    <div style="font-size:11px;color:var(--gray-500);">
                        @if($vendor->status === 'active') Siap Berjualan @else Belum Aktif @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Notification Alerts --}}
        @if($vendor->status === 'needs_revision')
        <div class="alert alert-warning mb-6" style="border-left:5px solid #F59E0B;background:#FFFBEB;color:#92400E;">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:1.5rem;"></i>
            <div>
                <strong style="font-size:var(--font-size-base);">Pengajuan Memerlukan Perbaikan Berkas</strong>
                <p style="margin-top:4px;">Tim Kurasi PestaGo telah memeriksa berkas Anda dan meminta perbaikan berikut:</p>
                <div style="background:rgba(255,255,255,0.8);border:1px dashed #F59E0B;padding:var(--space-3) var(--space-4);border-radius:var(--radius-md);margin-top:6px;font-weight:600;">
                    <i class="bi bi-chat-left-dots"></i> {{ $vendor->admin_notes ?: 'Mohon periksa dan unggah kembali dokumen KTP / legalitas yang valid.' }}
                </div>
                <p style="font-size:var(--font-size-xs);margin-top:6px;">Silakan perbaiki data di formulir bawah ini dan klik tombol <strong>Kirim Ulang Persyaratan</strong>.</p>
            </div>
        </div>
        @elseif($vendor->status === 'pending')
        <div class="alert alert-info mb-6" style="border-left:5px solid #3B82F6;background:#EFF6FF;color:#1E40AF;">
            <i class="bi bi-hourglass-split" style="font-size:1.5rem;"></i>
            <div>
                <strong style="font-size:var(--font-size-base);">Berkas Sedang Diverifikasi oleh Tim Kurasi PestaGo</strong>
                <p style="margin-top:4px;">Dokumen Anda telah masuk ke dalam antrean verifikasi. Proses kurasi biasanya memakan waktu 1–3 hari kerja. Anda tetap dapat memperbarui informasi jika ada koreksi sebelum disetujui.</p>
            </div>
        </div>
        @elseif($vendor->status === 'rejected')
        <div class="alert alert-error mb-6" style="border-left:5px solid #EF4444;background:#FEF2F2;color:#991B1B;">
            <i class="bi bi-x-circle-fill" style="font-size:1.5rem;"></i>
            <div>
                <strong style="font-size:var(--font-size-base);">Pendaftaran Vendor Belum Disetujui</strong>
                <p style="margin-top:4px;">Alasan: {{ $vendor->rejection_reason ?: ($vendor->admin_notes ?: 'Tidak memenuhi kriteria kelayakan vendor.') }}</p>
                <div style="margin-top:10px;">
                    <a href="https://wa.me/6281234567890?text=Halo%20Tim%20PestaGo%2C%20saya%20ingin%20konsultasi%20mengenai%20status%20verifikasi%20vendor%20{{ urlencode($vendor->business_name) }}" target="_blank" class="btn btn-sm btn-outline-danger" style="background:#fff;">
                        <i class="bi bi-whatsapp"></i> Hubungi Customer Care PestaGo
                    </a>
                </div>
            </div>
        </div>
        @elseif($vendor->status === 'active')
        <div class="alert alert-success mb-6">
            <i class="bi bi-check-circle-fill" style="font-size:1.5rem;"></i>
            <div>
                <strong>Selamat! Akun Vendor Anda Telah Terverifikasi & Aktif.</strong>
                <p style="margin-top:4px;">Anda bebas membuat katalog layanan, mengatur jadwal kalender, dan menerima pesanan dari pelanggan.</p>
                <div style="margin-top:8px;">
                    <a href="{{ route('vendor.services.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> Buat Katalog Layanan</a>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-info mb-6">
            <i class="bi bi-shield-lock" style="font-size:1.5rem;"></i>
            <div>
                <strong>Langkah Terakhir Sebelum Membuka Toko</strong>
                <p style="margin-top:4px;">Lengkapi formulir profil usaha, nomor rekening pencairan, dan foto dokumen KTP di bawah ini agar Tim Kurasi PestaGo dapat memverifikasi dan mengaktifkan fitur katalog layanan Anda.</p>
            </div>
        </div>
        @endif

        {{-- Verification Form --}}
        <form action="{{ route('vendor.verification.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- 1. Profil Usaha --}}
            <div class="card mb-6">
                <div class="card-body">
                    <h3 style="font-weight:700;margin-bottom:var(--space-2);display:flex;align-items:center;gap:8px;">
                        <span style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:14px;">1</span>
                        Profil & Lokasi Usaha
                    </h3>
                    <p class="text-muted mb-4" style="font-size:var(--font-size-sm);">Informasi usaha yang akan ditampilkan kepada calon pelanggan.</p>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label" for="business_name">Nama Usaha / Brand <span class="text-danger">*</span></label>
                            <input type="text" id="business_name" name="business_name" class="form-control" value="{{ old('business_name', $vendor->business_name) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="category_id">Kategori Usaha Utama <span class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">— Pilih Kategori —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $vendor->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label" for="phone">No. Telepon Kantor / Toko <span class="text-danger">*</span></label>
                            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $vendor->phone) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="whatsapp">No. WhatsApp Fast Response</label>
                            <input type="text" id="whatsapp" name="whatsapp" class="form-control" value="{{ old('whatsapp', $vendor->whatsapp ?: $vendor->phone) }}" placeholder="cth: 081234567890">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address">Alamat Lengkap Workshop / Kantor <span class="text-danger">*</span></label>
                        <textarea id="address" name="address" class="form-control" rows="2" placeholder="Nama jalan, gedung, nomor, RT/RW, kelurahan, kecamatan" required>{{ old('address', $vendor->address) }}</textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label" for="city">Kota / Kabupaten <span class="text-danger">*</span></label>
                            <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $vendor->city) }}" placeholder="cth: Jakarta Selatan" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="province">Provinsi</label>
                            <input type="text" id="province" name="province" class="form-control" value="{{ old('province', $vendor->province) }}" placeholder="cth: DKI Jakarta">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Deskripsi Usaha & Portofolio Singkat <span class="text-danger">*</span></label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Ceritakan pengalaman, keunggulan layanan, dan jenis acara yang biasa ditangani (min. 20 karakter)..." required>{{ old('description', $vendor->description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- 2. Rekening Bank --}}
            <div class="card mb-6">
                <div class="card-body">
                    <h3 style="font-weight:700;margin-bottom:var(--space-2);display:flex;align-items:center;gap:8px;">
                        <span style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:14px;">2</span>
                        Rekening Bank Pencairan Dana
                    </h3>
                    <p class="text-muted mb-4" style="font-size:var(--font-size-sm);">Penghasilan dari pesanan acara akan ditransfer ke rekening bank ini setelah pekerjaan selesai.</p>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label" for="bank_name">Nama Bank <span class="text-danger">*</span></label>
                            <select id="bank_name" name="bank_name" class="form-control" required>
                                <option value="">— Pilih Bank —</option>
                                @php
                                    $banks = ['BCA', 'Bank Mandiri', 'BRI', 'BNI', 'BSI (Bank Syariah Indonesia)', 'CIMB Niaga', 'Bank Permata', 'Bank Danamon', 'Bank Tabungan Negara (BTN)', 'Bank Jago', 'Seabank'];
                                    $curBank = old('bank_name', $vendor->bank_name);
                                @endphp
                                @foreach($banks as $b)
                                    <option value="{{ $b }}" {{ $curBank == $b ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="bank_account_number">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" id="bank_account_number" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $vendor->bank_account_number) }}" placeholder="cth: 5310293812" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="bank_account_holder">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                            <input type="text" id="bank_account_holder" name="bank_account_holder" class="form-control" value="{{ old('bank_account_holder', $vendor->bank_account_holder) }}" placeholder="Harus sesuai buku tabungan" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Dokumen Legalitas --}}
            <div class="card mb-6">
                <div class="card-body">
                    <h3 style="font-weight:700;margin-bottom:var(--space-2);display:flex;align-items:center;gap:8px;">
                        <span style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:14px;">3</span>
                        Dokumen Verifikasi Legalitas
                    </h3>
                    <p class="text-muted mb-4" style="font-size:var(--font-size-sm);">Dokumen rahasia hanya dapat dilihat oleh tim verifikator PestaGo untuk menjamin keamanan transaksi pelanggan.</p>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        {{-- KTP --}}
                        <div class="card" style="border:1px dashed var(--gray-300);background:var(--gray-50);padding:var(--space-4);">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <div>
                                    <strong style="display:block;font-size:var(--font-size-sm);"><i class="bi bi-person-badge"></i> Foto KTP Pemilik Usaha <span class="text-danger">*</span></strong>
                                    <span class="text-muted" style="font-size:11px;">Format JPG, PNG, atau PDF (Maks. 4MB)</span>
                                </div>
                                @if(isset($documents['ktp']))
                                    <span class="badge badge-success"><i class="bi bi-check-circle"></i> Sudah Diunggah</span>
                                @else
                                    <span class="badge badge-warning">Wajib</span>
                                @endif
                            </div>

                            @if(isset($documents['ktp']))
                            <div style="margin-top:10px;font-size:var(--font-size-xs);background:#fff;padding:6px 10px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:space-between;">
                                <span><i class="bi bi-file-earmark-check"></i> {{ $documents['ktp']->file_name }}</span>
                                <a href="{{ asset('storage/' . $documents['ktp']->file_path) }}" target="_blank" class="text-primary" style="font-weight:600;"><i class="bi bi-eye"></i> Lihat</a>
                            </div>
                            @endif

                            <div style="margin-top:12px;">
                                <label class="form-label" style="font-size:12px;">{{ isset($documents['ktp']) ? 'Ganti Berkas KTP:' : 'Pilih Berkas KTP:' }}</label>
                                <input type="file" name="doc_ktp" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" {{ isset($documents['ktp']) ? '' : 'required' }}>
                            </div>
                        </div>

                        {{-- NIB / SIUP / SKU --}}
                        <div class="card" style="border:1px dashed var(--gray-300);background:var(--gray-50);padding:var(--space-4);">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <div>
                                    <strong style="display:block;font-size:var(--font-size-sm);"><i class="bi bi-award"></i> NIB / SIUP / Surat Izin Usaha / SKU</strong>
                                    <span class="text-muted" style="font-size:11px;">Surat izin resmi / SKU dari kelurahan (Maks. 5MB)</span>
                                </div>
                                @if(isset($documents['siup']))
                                    <span class="badge badge-success"><i class="bi bi-check-circle"></i> Sudah Diunggah</span>
                                @else
                                    <span class="badge badge-gray">Disarankan</span>
                                @endif
                            </div>

                            @if(isset($documents['siup']))
                            <div style="margin-top:10px;font-size:var(--font-size-xs);background:#fff;padding:6px 10px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:space-between;">
                                <span><i class="bi bi-file-earmark-check"></i> {{ $documents['siup']->file_name }}</span>
                                <a href="{{ asset('storage/' . $documents['siup']->file_path) }}" target="_blank" class="text-primary" style="font-weight:600;"><i class="bi bi-eye"></i> Lihat</a>
                            </div>
                            @endif

                            <div style="margin-top:12px;">
                                <label class="form-label" style="font-size:12px;">{{ isset($documents['siup']) ? 'Ganti Berkas Izin Usaha:' : 'Pilih Berkas Izin Usaha:' }}</label>
                                <input type="file" name="doc_siup" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                        </div>
                    </div>

                    {{-- Portofolio Tambahan --}}
                    <div class="card mt-4" style="border:1px dashed var(--gray-300);background:var(--gray-50);padding:var(--space-4);">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                            <div>
                                <strong style="display:block;font-size:var(--font-size-sm);"><i class="bi bi-images"></i> Portofolio Acara / Foto Tempat Usaha / Brosur (Opsional)</strong>
                                <span class="text-muted" style="font-size:11px;">Membantu Tim Kurasi memverifikasi kredibilitas bisnis Anda lebih cepat (Maks. 5MB)</span>
                            </div>
                            @if(isset($documents['portfolio']))
                                <span class="badge badge-success"><i class="bi bi-check-circle"></i> Sudah Diunggah</span>
                            @endif
                        </div>

                        @if(isset($documents['portfolio']))
                        <div style="margin-top:10px;font-size:var(--font-size-xs);background:#fff;padding:6px 10px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:space-between;">
                            <span><i class="bi bi-file-earmark-check"></i> {{ $documents['portfolio']->file_name }}</span>
                            <a href="{{ asset('storage/' . $documents['portfolio']->file_path) }}" target="_blank" class="text-primary" style="font-weight:600;"><i class="bi bi-eye"></i> Lihat</a>
                        </div>
                        @endif

                        <div style="margin-top:12px;">
                            <input type="file" name="doc_portfolio" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Action Bar --}}
            <div style="display:flex;justify-content:space-between;align-items:center;background:var(--white);padding:var(--space-4) var(--space-6);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);">
                <div>
                    <span class="text-muted" style="font-size:var(--font-size-xs);"><i class="bi bi-shield-check"></i> Dengan mengirimkan berkas, Anda menyatakan bahwa data yang diberikan adalah benar dan valid.</span>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Kirim persyaratan untuk diverifikasi oleh Tim Kurasi PestaGo?')">
                        <i class="bi bi-send-check"></i> {{ $vendor->status === 'needs_revision' ? 'Kirim Ulang Persyaratan' : 'Kirim Persyaratan Verifikasi' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
