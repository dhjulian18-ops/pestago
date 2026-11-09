@extends('layouts.app')
@section('title', 'Hubungi Kami — PestaGo')

@section('content')
<div style="padding-top:calc(var(--navbar-height) + var(--space-12));">
    <div class="container" style="max-width:800px;">
        <div class="section-header">
            <span class="section-badge"><i class="bi bi-envelope"></i> Kontak</span>
            <h1 class="section-title">Hubungi Kami</h1>
            <p class="section-subtitle">Ada pertanyaan? Tim kami siap membantu Anda</p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:var(--space-5);margin-bottom:var(--space-8);">
            <div class="card text-center" style="padding:var(--space-6);">
                <i class="bi bi-whatsapp" style="font-size:2rem;color:var(--accent);margin-bottom:var(--space-3);display:block;"></i>
                <strong style="font-size:var(--font-size-sm);">WhatsApp</strong>
                <p style="font-size:var(--font-size-sm);color:var(--gray-500);margin-top:var(--space-1);">+62 811-1111-111</p>
            </div>
            <div class="card text-center" style="padding:var(--space-6);">
                <i class="bi bi-envelope" style="font-size:2rem;color:var(--primary);margin-bottom:var(--space-3);display:block;"></i>
                <strong style="font-size:var(--font-size-sm);">Email</strong>
                <p style="font-size:var(--font-size-sm);color:var(--gray-500);margin-top:var(--space-1);">halo@pestago.id</p>
            </div>
            <div class="card text-center" style="padding:var(--space-6);">
                <i class="bi bi-instagram" style="font-size:2rem;color:var(--secondary);margin-bottom:var(--space-3);display:block;"></i>
                <strong style="font-size:var(--font-size-sm);">Instagram</strong>
                <p style="font-size:var(--font-size-sm);color:var(--gray-500);margin-top:var(--space-1);">@pestago.id</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding:var(--space-8);">
                <h2 style="font-size:var(--font-size-xl);font-weight:700;margin-bottom:var(--space-6);color:var(--gray-900);">Kirim Pesan</h2>
                <form>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);">
                        <div class="form-group">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" placeholder="Nama lengkap">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" placeholder="email@contoh.com">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subjek</label>
                        <input type="text" class="form-control" placeholder="Topik pesan Anda">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pesan</label>
                        <textarea class="form-control" rows="5" placeholder="Tulis pesan Anda..."></textarea>
                    </div>
                    <button type="button" class="btn btn-primary btn-lg" onclick="alert('Fitur pengiriman pesan akan segera tersedia!')">
                        <i class="bi bi-send"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<div style="padding-bottom:var(--space-16);"></div>
@endsection
