<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Opsi & Paket Iklan (Bisa dikelola Admin & Staf)
        Schema::create('advertisement_plans', function (Blueprint $table) {
            $table->id();
            $table->string('placement_key')->unique();
            $table->string('name');
            $table->string('badge')->nullable();
            $table->string('badge_bg_color')->nullable()->default('#fee2e2');
            $table->string('badge_text_color')->nullable()->default('#d81a24');
            $table->text('description')->nullable();

            // Tarif & Durasi
            $table->decimal('price_basic', 15, 2)->default(149000);
            $table->integer('days_basic')->default(7);

            $table->decimal('price_pro', 15, 2)->default(279000);
            $table->integer('days_pro')->default(14);

            $table->decimal('price_business', 15, 2)->default(499000);
            $table->integer('days_business')->default(30);

            // Panduan Kanvas
            $table->string('canvas_title')->nullable();
            $table->text('canvas_guideline')->nullable();
            $table->string('canvas_ratio')->default('16:9');
            $table->string('canvas_orientation')->default('Lanskap');

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Tambah kolom pembayaran Midtrans ke tabel advertisements
        Schema::table('advertisements', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('payment_status');
            $table->string('payment_gateway_reference')->nullable()->after('snap_token');
            $table->string('payment_type')->nullable()->after('payment_gateway_reference');
            $table->timestamp('paid_at')->nullable()->after('payment_type');
        });

        // 3. Seed data paket iklan awal dari sistem yang ada
        DB::table('advertisement_plans')->insert([
            [
                'placement_key' => 'home_carousel',
                'name' => 'Papan Utama (Besar)',
                'badge' => 'POPULER',
                'badge_bg_color' => '#fee2e2',
                'badge_text_color' => '#d81a24',
                'description' => 'Slide Hero Carousel Beranda. Tampil paling besar dan mencolok di halaman depan.',
                'price_basic' => 149000,
                'days_basic' => 7,
                'price_pro' => 279000,
                'days_pro' => 14,
                'price_business' => 499000,
                'days_business' => 30,
                'canvas_title' => 'Panduan Ukuran Kanvas Papan Utama (Besar)',
                'canvas_guideline' => '&bull; <strong>Ukuran Kanvas Rekomendasi</strong>: <span style="font-weight:700;color:#0c4a6e;">1200 x 675 piksel</span> (Rasio 16:9 Lanskap)<br>&bull; <strong>Alternatif HD</strong>: 1920 x 1080 piksel (Full HD Lanskap)<br>&bull; <strong>Tips Desain</strong>: Sisakan teks utama di area kiri atau tengah dengan margin aman 24px dari tepi.<br>&bull; <strong>Format &amp; Ukuran File</strong>: JPG, PNG, atau WebP (Maksimal 3MB).',
                'canvas_ratio' => '16:9',
                'canvas_orientation' => 'Lanskap',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'placement_key' => 'home_side',
                'name' => 'Papan Samping (Kecil)',
                'badge' => 'STANDOUT',
                'badge_bg_color' => '#e0f2fe',
                'badge_text_color' => '#0369a1',
                'description' => 'Papan Vertikal Kanan Beranda. Selalu terlihat di samping banner slider tanpa bergeser.',
                'price_basic' => 99000,
                'days_basic' => 7,
                'price_pro' => 189000,
                'days_pro' => 14,
                'price_business' => 329000,
                'days_business' => 30,
                'canvas_title' => 'Panduan Ukuran Kanvas Papan Samping (Kecil)',
                'canvas_guideline' => '&bull; <strong>Ukuran Kanvas Rekomendasi</strong>: <span style="font-weight:700;color:#0c4a6e;">600 x 750 piksel</span> (Rasio 4:5 Potret Vertikal)<br>&bull; <strong>Alternatif HD</strong>: 1080 x 1350 piksel<br>&bull; <strong>Tips Desain</strong>: Gunakan teks besar dan kontras tinggi karena dimensi lebar lebih ramping.<br>&bull; <strong>Format &amp; Ukuran File</strong>: JPG, PNG, atau WebP (Maksimal 2MB).',
                'canvas_ratio' => '4:5',
                'canvas_orientation' => 'Potret',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'placement_key' => 'category_banner',
                'name' => 'Banner Kategori',
                'badge' => null,
                'badge_bg_color' => null,
                'badge_text_color' => null,
                'description' => 'Tampil di bagian atas daftar layanan ketika pengunjung membuka kategori terkait.',
                'price_basic' => 69000,
                'days_basic' => 7,
                'price_pro' => 129000,
                'days_pro' => 14,
                'price_business' => 229000,
                'days_business' => 30,
                'canvas_title' => 'Panduan Ukuran Kanvas Banner Halaman Kategori',
                'canvas_guideline' => '&bull; <strong>Ukuran Kanvas Rekomendasi</strong>: <span style="font-weight:700;color:#0c4a6e;">1200 x 300 piksel</span> (Rasio 4:1 Banner Horizontal)<br>&bull; <strong>Tips Desain</strong>: Letakkan logo di kiri dan pesan promosi di tengah.<br>&bull; <strong>Format &amp; Ukuran File</strong>: JPG, PNG, atau WebP (Maksimal 2MB).',
                'canvas_ratio' => '4:1',
                'canvas_orientation' => 'Banner Horisontal',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'placement_key' => 'search_sponsored',
                'name' => 'Pencarian Bersponsor',
                'badge' => null,
                'badge_bg_color' => null,
                'badge_text_color' => null,
                'description' => 'Tampil di posisi paling atas saat calon klien mencari layanan di bilah pencarian.',
                'price_basic' => 49000,
                'days_basic' => 7,
                'price_pro' => 89000,
                'days_pro' => 14,
                'price_business' => 159000,
                'days_business' => 30,
                'canvas_title' => 'Panduan Ukuran Pencarian Bersponsor',
                'canvas_guideline' => '&bull; <strong>Ukuran Kanvas Rekomendasi</strong>: <span style="font-weight:700;color:#0c4a6e;">800 x 450 piksel</span> (Rasio 16:9 Mini Card)<br>&bull; <strong>Format &amp; Ukuran File</strong>: JPG, PNG, atau WebP (Maksimal 1.5MB).',
                'canvas_ratio' => '16:9',
                'canvas_orientation' => 'Lanskap Mini',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_plans');

        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'payment_gateway_reference', 'payment_type', 'paid_at']);
        });
    }
};
