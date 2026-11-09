@extends('layouts.app')
@section('title', 'Cara Pemesanan — PestaGo')

@section('content')
<div class="uq-how-page">
    {{-- Hero Section (Minimalist Clean Header) --}}
    <header class="uq-how-hero">
        <div class="container">
            <span class="uq-badge-label">PANDUAN LENGKAP</span>
            <h1 class="uq-how-title">Cara Pemesanan di PestaGo</h1>
            <p class="uq-how-subtitle">
                Wujudkan acara impian tanpa rasa khawatir. Empat langkah mudah, transparan, dan terlindungi garansi pembayaran aman.
            </p>
        </div>
    </header>

    {{-- Main Steps Section (Clean UNIQLO Step Cards) --}}
    <section class="uq-how-steps-section">
        <div class="container">
            <div class="uq-steps-grid">
                {{-- Step 01 --}}
                <article class="uq-step-card">
                    <div class="uq-step-header">
                        <span class="uq-step-num">01</span>
                        <span class="uq-step-pill">LANGKAH PERTAMA</span>
                    </div>
                    <div class="uq-step-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <h2 class="uq-step-name">Tentukan Acara &amp; Jadwal</h2>
                    <p class="uq-step-desc">
                        Tentukan jenis acara Anda (pernikahan, lamaran, ulang tahun, gathering kantor), estimasi tanggal pelaksanaan, dan lokasi kota yang diinginkan.
                    </p>
                    <ul class="uq-step-points">
                        <li><i class="bi bi-check2"></i> Filter cerdas berdasarkan kota &amp; tanggal</li>
                        <li><i class="bi bi-check2"></i> Cek ketersediaan vendor secara real-time</li>
                        <li><i class="bi bi-check2"></i> Sesuaikan estimasi anggaran acara Anda</li>
                    </ul>
                </article>

                {{-- Step 02 --}}
                <article class="uq-step-card">
                    <div class="uq-step-header">
                        <span class="uq-step-num">02</span>
                        <span class="uq-step-pill">EKSPLORASI</span>
                    </div>
                    <div class="uq-step-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h2 class="uq-step-name">Pilih &amp; Bandingkan Vendor</h2>
                    <p class="uq-step-desc">
                        Telusuri katalog ribuan vendor terverifikasi. Pelajari portofolio asli, paket harga yang transparan, dan ulasan terpercaya dari pelanggan sebelumnya.
                    </p>
                    <ul class="uq-step-points">
                        <li><i class="bi bi-check2"></i> 100% vendor terverifikasi identitas &amp; legalitas</li>
                        <li><i class="bi bi-check2"></i> Portofolio foto &amp; video asli tanpa rekayasa</li>
                        <li><i class="bi bi-check2"></i> Chat langsung untuk tanya rincian kebutuhan</li>
                    </ul>
                </article>

                {{-- Step 03 --}}
                <article class="uq-step-card">
                    <div class="uq-step-header">
                        <span class="uq-step-num">03</span>
                        <span class="uq-step-pill">PEMBAYARAN AMAN</span>
                    </div>
                    <div class="uq-step-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h2 class="uq-step-name">Booking &amp; Bayar Aman (Escrow)</h2>
                    <p class="uq-step-desc">
                        Kunci jadwal vendor pilihan Anda dengan transaksi instan via Midtrans (BCA, QRIS, Virtual Account). Dana Anda diamankan di rekening bersama PestaGo.
                    </p>
                    <ul class="uq-step-points">
                        <li><i class="bi bi-check2"></i> Perlindungan rekening penampung (Escrow)</li>
                        <li><i class="bi bi-check2"></i> Dana baru diteruskan ke vendor setelah acara terlaksana</li>
                        <li><i class="bi bi-check2"></i> Invoice dan bukti bayar resmi otomatis terbit</li>
                    </ul>
                </article>

                {{-- Step 04 --}}
                <article class="uq-step-card">
                    <div class="uq-step-header">
                        <span class="uq-step-num">04</span>
                        <span class="uq-step-pill">HARI H SPESIAL</span>
                    </div>
                    <div class="uq-step-icon">
                        <i class="bi bi-stars"></i>
                    </div>
                    <h2 class="uq-step-name">Nikmati Momen Spesial Anda</h2>
                    <p class="uq-step-desc">
                        Vendor mitra hadir tepat waktu dan memberikan performa terbaik sesuai komitmen. Rayakan hari bahagia Anda dengan tenang bersama keluarga.
                    </p>
                    <ul class="uq-step-points">
                        <li><i class="bi bi-check2"></i> Vendor tiba tepat waktu sesuai jadwal</li>
                        <li><i class="bi bi-check2"></i> Customer Care PestaGo siap siaga membantu</li>
                        <li><i class="bi bi-check2"></i> Berikan rating &amp; ulasan setelah acara tuntas</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    {{-- Trust & Escrow Guarantee Box --}}
    <section class="uq-how-guarantee-section">
        <div class="container">
            <div class="uq-guarantee-card">
                <div class="uq-guarantee-header">
                    <i class="bi bi-patch-check-fill uq-guarantee-badge-icon"></i>
                    <div>
                        <span class="uq-badge-sub">JAMINAN KEAMANAN</span>
                        <h2 class="uq-guarantee-title">Bagaimana Rekening Bersama PestaGo Bekerja?</h2>
                    </div>
                </div>
                <div class="uq-guarantee-flow">
                    <div class="uq-g-col">
                        <div class="uq-g-num">A</div>
                        <h4>1. Customer Membayar</h4>
                        <p>Pembayaran masuk ke sistem rekening penampung resmi PestaGo yang terenkripsi dan diawasi.</p>
                    </div>
                    <div class="uq-g-arrow"><i class="bi bi-arrow-right"></i></div>
                    <div class="uq-g-col">
                        <div class="uq-g-num">B</div>
                        <h4>2. Vendor Dikonfirmasi</h4>
                        <p>Vendor menerima notifikasi jadwal pasti dan mempersiapkan seluruh kebutuhan acara Anda.</p>
                    </div>
                    <div class="uq-g-arrow"><i class="bi bi-arrow-right"></i></div>
                    <div class="uq-g-col">
                        <div class="uq-g-num">C</div>
                        <h4>3. Dana Diteruskan</h4>
                        <p>Setelah acara selesai terlaksana dengan memuaskan, dana diteruskan secara resmi ke vendor.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ Pemesanan Singkat --}}
    <section class="uq-how-faq-section">
        <div class="container" style="max-width: 820px;">
            <div class="uq-section-head">
                <span class="uq-badge-label">PERTANYAAN UMUM</span>
                <h2 class="uq-how-title" style="font-size: 1.5rem;">Sering Ditanyakan Seputar Pemesanan</h2>
            </div>

            <div class="uq-faq-list">
                <details class="uq-faq-item" open>
                    <summary class="uq-faq-question">
                        <span>Apakah bisa pesan beberapa vendor berbeda sekaligus?</span>
                        <i class="bi bi-chevron-down"></i>
                    </summary>
                    <div class="uq-faq-answer">
                        Ya, Anda dapat menambahkan beberapa layanan dari vendor yang berbeda ke dalam keranjang, lalu melakukan proses checkout bersamaan secara praktis.
                    </div>
                </details>

                <details class="uq-faq-item">
                    <summary class="uq-faq-question">
                        <span>Bagaimana jika tanggal acara perlu dijadwalkan ulang?</span>
                        <i class="bi bi-chevron-down"></i>
                    </summary>
                    <div class="uq-faq-answer">
                        Anda dapat menghubungi vendor melalui fitur Chat di PestaGo atau mengajukan perubahan jadwal melalui Customer Care kami sebelum tanggal yang disepakati.
                    </div>
                </details>

                <details class="uq-faq-item">
                    <summary class="uq-faq-question">
                        <span>Metode pembayaran apa saja yang didukung?</span>
                        <i class="bi bi-chevron-down"></i>
                    </summary>
                    <div class="uq-faq-answer">
                        PestaGo mendukung transfer Bank Virtual Account (BCA, Mandiri, BNI, BRI, Permata), QRIS (GoPay, OVO, ShopeePay), dan Kartu Kredit dengan keamanan Midtrans.
                    </div>
                </details>

                <details class="uq-faq-item">
                    <summary class="uq-faq-question">
                        <span>Bagaimana jika vendor tidak hadir di hari acara?</span>
                        <i class="bi bi-chevron-down"></i>
                    </summary>
                    <div class="uq-faq-answer">
                        Dengan sistem rekening bersama PestaGo, dana Anda 100% aman dan dilindungi garansi pengembalian dana (refund) penuh jika vendor wanprestasi.
                    </div>
                </details>
            </div>
        </div>
    </section>

    {{-- Bottom Clean Action Strip --}}
    <section class="uq-how-cta-section">
        <div class="container">
            <div class="uq-clean-action-box">
                <h2 class="uq-action-title">Siap Memulai Perjalanan Acara Anda?</h2>
                <p class="uq-action-subtitle">Temukan vendor terbaik di kota Anda sekarang dengan jaminan harga transparan dan proteksi pembayaran.</p>
                <div class="uq-action-buttons">
                    <a href="{{ route('services.index') }}" class="uq-btn-solid">Mulai Cari Layanan</a>
                    <a href="{{ route('contact') }}" class="uq-btn-outline">Tanya Customer Care</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
