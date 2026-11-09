@extends('layouts.app')
@section('title', 'Detail Vendor — Admin PestaGo')

@section('content')
<div class="dashboard">
    @include('admin._sidebar', ['active' => 'vendors'])

    <div class="dashboard-content">
        <div style="margin-bottom:12px;">
            <a href="{{ route('admin.vendors') }}" style="color:#666666;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Vendor
            </a>
        </div>

        <div class="dashboard-header" style="display:flex;align-items:center;gap:16px;">
            @if($vendor->logo_url)
                <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->business_name }}" style="width:54px;height:54px;border-radius:2px;object-fit:cover;border:1px solid #e5e5e5;flex-shrink:0;">
            @else
                <div style="width:54px;height:54px;background:#111111;color:#ffffff;border-radius:2px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:22px;flex-shrink:0;">
                    {{ strtoupper(substr($vendor->business_name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h1 class="dashboard-title" style="margin:0;">{{ $vendor->business_name }}</h1>
                <p class="dashboard-subtitle" style="margin-top:2px;">
                    Status:
                    @php 
                        $vs = [
                            'incomplete'=>['Belum Lengkap Persyaratan','warning'],
                            'pending'=>['Menunggu Review Admin','warning'],
                            'active'=>['Aktif & Terverifikasi','success'],
                            'rejected'=>['Ditolak','danger'],
                            'needs_revision'=>['Perlu Revisi','warning'],
                            'suspended'=>['Suspended','danger']
                        ]; 
                        $s = $vs[$vendor->status] ?? [$vendor->status,'gray']; 
                    @endphp
                    <span class="badge badge-{{ $s[1] }}">{{ $s[0] }}</span>
                </p>
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

        @if($vendor->admin_notes)
        <div class="alert alert-warning mb-6">
            <i class="bi bi-chat-left-dots"></i>
            <div>
                <strong>Catatan Admin Sebelumnya:</strong>
                <p style="margin-top:2px;">{{ $vendor->admin_notes }}</p>
            </div>
        </div>
        @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--space-6);">
            <div>
                {{-- Data Usaha --}}
                <div class="card mb-6">
                    <div class="card-body">
                        <h3 style="font-weight:700;margin-bottom:var(--space-4);"><i class="bi bi-shop"></i> Informasi Usaha</h3>
                        <div style="display:grid;gap:var(--space-3);">
                            <div><strong>Pemilik:</strong> {{ $vendor->user->name }} ({{ $vendor->user->email }})</div>
                            @php
                                $mainPhone = $vendor->whatsapp ?: ($vendor->phone ?: ($vendor->user->phone ?? ''));
                                $cleanPhone = preg_replace('/[^0-9]/', '', (string)$mainPhone);
                                if (str_starts_with($cleanPhone, '0')) $waNumber = '62' . substr($cleanPhone, 1);
                                elseif (str_starts_with($cleanPhone, '8')) $waNumber = '62' . $cleanPhone;
                                else $waNumber = $cleanPhone;
                            @endphp
                            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                <strong>Telepon / WhatsApp:</strong>
                                <span style="font-family:monospace;font-weight:700;">{{ $vendor->phone ?: ($vendor->whatsapp ?: '-') }}</span>
                                @if(!empty($cleanPhone))
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="btn btn-sm" style="background:#25D366;color:#ffffff;padding:2px 8px;font-size:11px;font-weight:700;border-radius:2px;display:inline-flex;align-items:center;gap:4px;text-decoration:none;">
                                    <i class="bi bi-whatsapp"></i> Chat WhatsApp
                                </a>
                                @endif
                            </div>
                            <div><strong>Kategori:</strong> {{ $vendor->category->name ?? '-' }}</div>
                            <div><strong>Alamat:</strong> {{ $vendor->address ?: 'Belum diisi' }}</div>
                            <div><strong>Kota & Provinsi:</strong> {{ $vendor->city ?: '-' }}, {{ $vendor->province ?: '-' }}</div>
                            <div><strong>Deskripsi Usaha:</strong> {{ $vendor->description ?: 'Belum diisi' }}</div>
                            <div><strong>Terdaftar:</strong> {{ $vendor->created_at->translatedFormat('d M Y H:i') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Rekening Bank --}}
                <div class="card mb-6">
                    <div class="card-body">
                        <h3 style="font-weight:700;margin-bottom:var(--space-4);"><i class="bi bi-bank"></i> Rekening Bank Pencairan</h3>
                        <div style="display:grid;gap:var(--space-3);">
                            <div><strong>Bank:</strong> {{ $vendor->bank_name ?: 'Belum diisi' }}</div>
                            <div><strong>Nomor Rekening:</strong> {{ $vendor->bank_account_number ?: 'Belum diisi' }}</div>
                            <div><strong>Nama Pemilik Rekening:</strong> {{ $vendor->bank_account_holder ?: 'Belum diisi' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Dokumen Persyaratan --}}
                <div class="card mb-6">
                    <div class="card-body">
                        <h3 style="font-weight:700;margin-bottom:var(--space-4);"><i class="bi bi-file-earmark-text"></i> Dokumen Legalitas & Persyaratan</h3>
                        
                        @if($vendor->documents->count() > 0)
                        <div style="display:grid;gap:var(--space-3);">
                            @foreach($vendor->documents as $doc)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:var(--space-3);background:var(--gray-50);border-radius:var(--radius-md);border:1px solid var(--gray-200);">
                                <div>
                                    <strong style="text-transform:uppercase;font-size:var(--font-size-xs);color:var(--primary);display:block;">
                                        {{ $doc->document_type === 'ktp' ? 'KTP Pemilik Usaha' : ($doc->document_type === 'siup' ? 'NIB / SIUP / SKU' : 'Portofolio / Brosur') }}
                                    </strong>
                                    <span style="font-size:var(--font-size-sm);"><i class="bi bi-paperclip"></i> {{ $doc->file_name }}</span>
                                    <div style="font-size:11px;color:var(--gray-500);">Diupload: {{ $doc->updated_at->translatedFormat('d M Y H:i') }}</div>
                                </div>
                                <div>
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka / Unduh Berkas
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="alert alert-warning" style="margin:0;">
                            <i class="bi bi-exclamation-circle"></i>
                            <div>Vendor ini belum mengunggah berkas persyaratan verifikasi.</div>
                        </div>
                        @endif
                    </div>
                </div>

                @if($vendor->services->count() > 0)
                <div class="card mb-6">
                    <div class="card-body">
                        <h3 style="font-weight:700;margin-bottom:var(--space-4);">Layanan Terdaftar ({{ $vendor->services->count() }})</h3>
                        @foreach($vendor->services as $svc)
                        <div style="padding:var(--space-3) 0;border-bottom:1px solid var(--gray-100);">
                            <strong>{{ $svc->name }}</strong> — Rp {{ number_format($svc->price, 0, ',', '.') }}/{{ str_replace('per_', '', $svc->price_unit) }}
                            <span class="badge badge-{{ $svc->status === 'published' ? 'success' : 'gray' }}" style="margin-left:var(--space-2);">{{ $svc->status }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Verification Action --}}
            <div>
                <div class="card">
                    <div class="card-body">
                        <h3 style="font-weight:700;margin-bottom:var(--space-4);"><i class="bi bi-shield-check"></i> Tindakan Verifikasi</h3>

                        <form action="{{ route('admin.vendors.verify', $vendor) }}" method="POST" id="verifyForm">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Catatan Admin / Alasan</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Tulis alasan jika menolak atau meminta perbaikan berkas...">{{ old('notes', $vendor->admin_notes) }}</textarea>
                                <span class="form-hint">Catatan ini akan dilihat langsung oleh vendor di dashboard mereka.</span>
                            </div>

                            <div style="display:flex;flex-direction:column;gap:var(--space-3);margin-top:var(--space-4);">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-block" onclick="return confirm('Setujui vendor ini? Vendor akan langsung aktif dan bisa membuat katalog layanan.')">
                                    <i class="bi bi-check-circle"></i> Setujui (Aktifkan Vendor)
                                </button>
                                <button type="submit" name="action" value="revision" class="btn btn-warning btn-block" style="background:#F59E0B;border-color:#F59E0B;color:#fff;" onclick="return confirm('Minta perbaikan dokumen? Vendor akan diminta mengunggah ulang berkas.')">
                                    <i class="bi bi-pencil-square"></i> Minta Revisi / Perbaikan
                                </button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-block" onclick="return confirm('Yakin tolak vendor ini secara permanen?')">
                                    <i class="bi bi-x-circle"></i> Tolak Pendaftaran
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($vendor->status === 'active')
                <div class="card mt-4">
                    <div class="card-body text-center">
                        <i class="bi bi-patch-check-fill" style="font-size:3rem;color:var(--success);"></i>
                        <h3 style="margin-top:var(--space-3);font-weight:700;color:var(--success);">Vendor Aktif</h3>
                        <p class="text-muted" style="font-size:var(--font-size-sm);">Diverifikasi pada: {{ $vendor->verified_at?->translatedFormat('d M Y H:i') ?? '-' }}</p>
                    </div>
                </div>
                @endif

                <div class="card mt-4">
                    <div class="card-body">
                        <h3 style="font-weight:700;margin-bottom:var(--space-3);">Statistik</h3>
                        <div style="font-size:var(--font-size-sm);display:grid;gap:var(--space-2);">
                            <div>Rating: <strong>⭐ {{ number_format($vendor->rating_avg, 1) }}</strong> ({{ $vendor->rating_count }} review)</div>
                            <div>Total Layanan: <strong>{{ $vendor->services->count() }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.vendors') }}" class="btn btn-ghost mt-6"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
</div>
@endsection
