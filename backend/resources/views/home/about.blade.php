@extends('layouts.app')
@section('title', 'Tentang Kami — PestaGo')

@section('content')
    <div style="padding-top:calc(var(--navbar-height) + var(--space-12));">
        <div class="container" style="max-width:800px;">
            <div class="section-header">
                <span class="section-badge"><i class="bi bi-info-circle"></i> Tentang</span>
                <h1 class="section-title">Tentang PestaGo</h1>
                <p class="section-subtitle">Platform marketplace vendor acara terlengkap di Indonesia</p>
            </div>

            <div class="card" style="margin-bottom:var(--space-8);">
                <div class="card-body" style="padding:var(--space-8);">
                    <h2
                        style="font-size:var(--font-size-xl);font-weight:700;margin-bottom:var(--space-4);color:var(--gray-900);">
                        Visi Kami</h2>
                    <p
                        style="font-size:var(--font-size-base);color:var(--gray-600);line-height:1.8;margin-bottom:var(--space-6);">
                        PestaGo hadir untuk menjadi <strong>solusi satu pintu</strong> bagi siapa saja yang ingin
                        merencanakan acara spesial — mulai dari pernikahan, ulang tahun, wisuda, seminar, hingga acara
                        perusahaan. Kami menghubungkan pelanggan dengan vendor acara terverifikasi di seluruh Indonesia.
                    </p>

                    <h2
                        style="font-size:var(--font-size-xl);font-weight:700;margin-bottom:var(--space-4);color:var(--gray-900);">
                        Mengapa PestaGo?</h2>
                    <div style="display:grid;gap:var(--space-4);margin-bottom:var(--space-6);">
                        <div style="display:flex;gap:var(--space-3);align-items:flex-start;">
                            <i class="bi bi-shield-check" style="color:var(--primary);font-size:1.3rem;margin-top:2px;"></i>
                            <div>
                                <strong>Vendor Terverifikasi</strong>
                                <p style="color:var(--gray-500);font-size:var(--font-size-sm);">Semua vendor melalui proses
                                    verifikasi ketat oleh tim PestaGo</p>
                            </div>
                        </div>
                        <div style="display:flex;gap:var(--space-3);align-items:flex-start;">
                            <i class="bi bi-cart-check" style="color:var(--primary);font-size:1.3rem;margin-top:2px;"></i>
                            <div>
                                <strong>Multi-Vendor Checkout</strong>
                                <p style="color:var(--gray-500);font-size:var(--font-size-sm);">Pesan dari banyak vendor
                                    sekaligus dalam satu transaksi</p>
                            </div>
                        </div>
                        <div style="display:flex;gap:var(--space-3);align-items:flex-start;">
                            <i class="bi bi-geo-alt" style="color:var(--primary);font-size:1.3rem;margin-top:2px;"></i>
                            <div>
                                <strong>Peta Lokasi Interaktif</strong>
                                <p style="color:var(--gray-500);font-size:var(--font-size-sm);">Lihat lokasi vendor di peta
                                    OpenStreetMap dengan Leaflet.js</p>
                            </div>
                        </div>
                        <div style="display:flex;gap:var(--space-3);align-items:flex-start;">
                            <i class="bi bi-cash-coin" style="color:var(--primary);font-size:1.3rem;margin-top:2px;"></i>
                            <div>
                                <strong>Harga Transparan</strong>
                                <p style="color:var(--gray-500);font-size:var(--font-size-sm);">Tidak ada biaya tersembunyi,
                                    semua harga tampil jelas</p>
                            </div>
                        </div>
                    </div>

                    <h2
                        style="font-size:var(--font-size-xl);font-weight:700;margin-bottom:var(--space-4);color:var(--gray-900);">
                        Tim Kami</h2>
                    <p style="font-size:var(--font-size-base);color:var(--gray-600);line-height:1.8;">
                        PestaGo dikembangkan oleh tim yang passionate di bidang teknologi dan event management. Kami percaya
                        bahwa setiap orang berhak mendapatkan acara yang berkesan tanpa stres berlebih dalam proses
                        perencanaannya.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div style="padding-bottom:var(--space-16);"></div>
@endsection