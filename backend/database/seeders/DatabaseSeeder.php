<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Promo;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceImage;
use App\Models\ServiceLocation;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== SUPER ADMIN =====
        User::create([
            'name' => 'Admin PestaGo',
            'email' => 'admin@pestago.id',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'phone' => '08111111111',
            'email_verified_at' => now(),
        ]);

        // ===== CUSTOMERS =====
        $customer1 = User::create([
            'name' => 'Sari Dewi',
            'email' => 'sari@email.com',
            'password' => Hash::make('customer123'),
            'role' => 'customer',
            'phone' => '08122222222',
            'email_verified_at' => now(),
        ]);

        $customer2 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@email.com',
            'password' => Hash::make('customer123'),
            'role' => 'customer',
            'phone' => '08133333333',
            'email_verified_at' => now(),
        ]);

        // ===== CATEGORIES =====
        $categories = [
            ['name' => 'Gedung & Venue', 'slug' => 'gedung-venue', 'description' => 'Gedung, aula, ballroom, taman, dan venue untuk berbagai acara', 'icon' => 'bi-building', 'sort_order' => 1],
            ['name' => 'Catering', 'slug' => 'catering', 'description' => 'Paket menu, prasmanan, buffet, dan layanan makanan acara', 'icon' => 'bi-cup-hot', 'sort_order' => 2],
            ['name' => 'Dekorasi & Makeup', 'slug' => 'dekorasi-makeup', 'description' => 'Dekorasi acara, pelaminan, dan MUA profesional', 'icon' => 'bi-palette', 'sort_order' => 3],
            ['name' => 'Busana & Aksesoris', 'slug' => 'busana-aksesoris', 'description' => 'Busana pengantin, kebaya, jas, dan aksesoris acara', 'icon' => 'bi-handbag', 'sort_order' => 4],
            ['name' => 'Dokumentasi', 'slug' => 'dokumentasi', 'description' => 'Fotografi dan videografi profesional untuk acara', 'icon' => 'bi-camera', 'sort_order' => 5],
            ['name' => 'Hiburan & Entertainment', 'slug' => 'hiburan-entertainment', 'description' => 'Band, DJ, MC, dan hiburan untuk acara', 'icon' => 'bi-music-note-beamed', 'sort_order' => 6],
            ['name' => 'Perlengkapan Acara', 'slug' => 'perlengkapan-acara', 'description' => 'Panggung, sound system, tenda, kursi, dan perlengkapan', 'icon' => 'bi-speaker', 'sort_order' => 7],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // ===== VENDORS =====
        $vendorsData = [
            [
                'user' => ['name' => 'Ahmad Venue', 'email' => 'vendor1@email.com', 'phone' => '08144444441'],
                'profile' => [
                    'category_id' => 1, 'business_name' => 'Grand Ballroom Nusantara', 'slug' => 'grand-ballroom-nusantara',
                    'description' => 'Gedung pernikahan dan acara premium dengan fasilitas lengkap di pusat kota Jakarta. Kapasitas hingga 1000 tamu.', 'phone' => '02187654321',
                    'address' => 'Jl. Gatot Subroto No. 45, Kuningan', 'city' => 'Jakarta Selatan', 'district' => 'Kuningan', 'province' => 'DKI Jakarta',
                    'latitude' => -6.22876, 'longitude' => 106.83606, 'status' => 'active', 'verified_at' => now(), 'rating_avg' => 4.80, 'rating_count' => 45,
                ],
                'services' => [
                    ['name' => 'Paket Wedding Grand Ballroom', 'price' => 75000000, 'price_unit' => 'per_acara', 'description' => 'Paket lengkap untuk acara pernikahan di Grand Ballroom. Termasuk dekorasi dasar, sound system, meja kursi untuk 500 tamu, AC, parkir luas, dan koordinator acara.', 'capacity_min' => 200, 'capacity_max' => 1000, 'facilities' => '["AC Central","Sound System","Proyektor","Parkir 200 mobil","Lift","Aksesibilitas disabilitas","Ruang ganti pengantin","Mushola"]', 'is_featured' => true],
                    ['name' => 'Paket Seminar Hall', 'price' => 25000000, 'price_unit' => 'per_hari', 'description' => 'Ruang seminar modern untuk 300 orang dengan proyektor, sound system, dan ruang registrasi.', 'capacity_min' => 50, 'capacity_max' => 300, 'facilities' => '["AC","Proyektor 4K","Podium","Mic wireless","WiFi"]'],
                ],
                'location_type' => 'fixed',
            ],
            [
                'user' => ['name' => 'Ibu Tuti Catering', 'email' => 'vendor2@email.com', 'phone' => '08144444442'],
                'profile' => [
                    'category_id' => 2, 'business_name' => 'Dapur Nusantara Catering', 'slug' => 'dapur-nusantara-catering',
                    'description' => 'Catering profesional dengan menu nusantara autentik. Melayani berbagai acara dari 50 hingga 3000 porsi.', 'phone' => '08567891234',
                    'address' => 'Jl. Raya Bogor No. 78, Kramat Jati', 'city' => 'Jakarta Timur', 'district' => 'Kramat Jati', 'province' => 'DKI Jakarta',
                    'latitude' => -6.28001, 'longitude' => 106.87165, 'status' => 'active', 'verified_at' => now(), 'rating_avg' => 4.70, 'rating_count' => 62,
                ],
                'services' => [
                    ['name' => 'Paket Prasmanan Premium 500 Porsi', 'price' => 45000000, 'price_unit' => 'per_paket', 'description' => 'Prasmanan premium 500 porsi: 5 menu utama, 3 lauk, 4 sayur, 2 dessert, minuman, dan buah. Termasuk peralatan, taplak, dan waitress.', 'capacity_min' => 300, 'capacity_max' => 500, 'facilities' => '["Waitress","Peralatan lengkap","Ice carving","Fruit carving"]', 'is_featured' => true],
                    ['name' => 'Paket Nasi Box Acara', 'price' => 45000, 'price_unit' => 'per_orang', 'description' => 'Nasi box dengan pilihan ayam bakar/rendang, lauk lengkap, kerupuk, buah, dan air mineral. Minimum 100 box.', 'capacity_min' => 100, 'capacity_max' => 5000, 'facilities' => '["Box premium","Sendok garpu","Tisu"]'],
                ],
                'location_type' => 'mobile',
            ],
            [
                'user' => ['name' => 'Rina Makeup Artist', 'email' => 'vendor3@email.com', 'phone' => '08144444443'],
                'profile' => [
                    'category_id' => 3, 'business_name' => 'Rina Beauty Studio', 'slug' => 'rina-beauty-studio',
                    'description' => 'MUA profesional berpengalaman 10+ tahun. Spesialis makeup pengantin adat Jawa, Sunda, dan modern.', 'phone' => '08123456789',
                    'address' => 'Jl. Kemang Raya No. 15, Mampang Prapatan', 'city' => 'Jakarta Selatan', 'district' => 'Kemang', 'province' => 'DKI Jakarta',
                    'latitude' => -6.26021, 'longitude' => 106.81302, 'status' => 'active', 'verified_at' => now(), 'rating_avg' => 4.90, 'rating_count' => 89,
                ],
                'services' => [
                    ['name' => 'Paket MUA Wedding Premium', 'price' => 8500000, 'price_unit' => 'per_acara', 'description' => 'Makeup pengantin lengkap: akad + resepsi, termasuk hairdo, sanggul, touch up sepanjang acara, dan makeup 2 ibu. Berpengalaman 10+ tahun.', 'capacity_min' => null, 'capacity_max' => null, 'facilities' => '["Makeup akad & resepsi","Hairdo & sanggul","Touch up","Makeup 2 ibu","Free trial"]', 'is_featured' => true],
                    ['name' => 'Paket MUA Wisuda', 'price' => 1500000, 'price_unit' => 'per_acara', 'description' => 'Makeup wisuda natural glam, termasuk hairdo, eyelashes, dan touch up 2 jam.', 'capacity_min' => null, 'capacity_max' => null, 'facilities' => '["Makeup natural glam","Hairdo","Eyelashes","Touch up 2 jam"]'],
                ],
                'location_type' => 'mobile',
            ],
            [
                'user' => ['name' => 'Dedi Photographer', 'email' => 'vendor4@email.com', 'phone' => '08144444444'],
                'profile' => [
                    'category_id' => 5, 'business_name' => 'Lenscape Photography', 'slug' => 'lenscape-photography',
                    'description' => 'Studio fotografi dan videografi profesional. Spesialis pre-wedding, wedding documentation, dan event coverage.', 'phone' => '08198765432',
                    'address' => 'Jl. Tebet Raya No. 30, Tebet', 'city' => 'Jakarta Selatan', 'district' => 'Tebet', 'province' => 'DKI Jakarta',
                    'latitude' => -6.22578, 'longitude' => 106.85523, 'status' => 'active', 'verified_at' => now(), 'rating_avg' => 4.85, 'rating_count' => 73,
                ],
                'services' => [
                    ['name' => 'Paket Foto + Video Wedding Full Day', 'price' => 15000000, 'price_unit' => 'per_acara', 'description' => 'Dokumentasi lengkap akad + resepsi: 2 fotografer, 2 videografer, drone, cinematic video 5-7 menit, album eksklusif 30 halaman, semua file foto HD.', 'capacity_min' => null, 'capacity_max' => null, 'facilities' => '["2 Fotografer","2 Videografer","Drone","Album 30 hal","Cinematic video","USB drive","Semua file HD"]', 'is_featured' => true],
                    ['name' => 'Paket Pre-Wedding Outdoor', 'price' => 5500000, 'price_unit' => 'per_paket', 'description' => 'Foto pre-wedding 1 lokasi outdoor, 100+ foto edit, 20 foto retouching premium, 1 fotografer profesional.', 'capacity_min' => null, 'capacity_max' => null, 'facilities' => '["1 Fotografer","100+ foto edit","20 foto retouching","Konsultasi konsep"]'],
                ],
                'location_type' => 'mobile',
            ],
            [
                'user' => ['name' => 'Haris Entertainment', 'email' => 'vendor5@email.com', 'phone' => '08144444445'],
                'profile' => [
                    'category_id' => 6, 'business_name' => 'Harmoni Entertainment', 'slug' => 'harmoni-entertainment',
                    'description' => 'Penyedia hiburan acara: MC profesional, live band, DJ, dan pertunjukan seni untuk berbagai acara.', 'phone' => '08567890001',
                    'address' => 'Jl. Panglima Polim No. 88, Kebayoran Baru', 'city' => 'Jakarta Selatan', 'district' => 'Kebayoran Baru', 'province' => 'DKI Jakarta',
                    'latitude' => -6.24416, 'longitude' => 106.79712, 'status' => 'active', 'verified_at' => now(), 'rating_avg' => 4.65, 'rating_count' => 31,
                ],
                'services' => [
                    ['name' => 'Paket MC + Live Band Wedding', 'price' => 12000000, 'price_unit' => 'per_acara', 'description' => 'MC profesional bilingual + live band 5 personel, 4 jam pertunjukan, request lagu tidak terbatas. Cocok untuk wedding reception dan gala dinner.', 'capacity_min' => null, 'capacity_max' => null, 'facilities' => '["MC bilingual","Band 5 personel","4 jam pertunjukan","Request lagu bebas","Sound check"]', 'is_featured' => true],
                ],
                'location_type' => 'mobile',
            ],
            [
                'user' => ['name' => 'Sinta Decor', 'email' => 'vendor6@email.com', 'phone' => '08144444446'],
                'profile' => [
                    'category_id' => 3, 'business_name' => 'Sinta Dekorasi & Wedding', 'slug' => 'sinta-dekorasi-wedding',
                    'description' => 'Dekorasi pernikahan dan acara elegan dengan sentuhan modern dan tradisional Jawa.', 'phone' => '08567890002',
                    'address' => 'Jl. Merdeka No. 55, Bandung', 'city' => 'Bandung', 'district' => 'Sumur Bandung', 'province' => 'Jawa Barat',
                    'latitude' => -6.91735, 'longitude' => 107.61935, 'status' => 'active', 'verified_at' => now(), 'rating_avg' => 4.75, 'rating_count' => 54,
                ],
                'services' => [
                    ['name' => 'Paket Dekorasi Pelaminan Mewah', 'price' => 35000000, 'price_unit' => 'per_acara', 'description' => 'Dekorasi pelaminan premium dengan bunga segar, drapery, lighting dekoratif, dan backdrop foto. Termasuk dekorasi meja tamu.', 'capacity_min' => null, 'capacity_max' => null, 'facilities' => '["Pelaminan premium","Bunga segar","Drapery","Lighting dekoratif","Backdrop foto","Dekorasi meja tamu"]', 'is_featured' => true],
                    ['name' => 'Paket Dekorasi Ulang Tahun', 'price' => 5000000, 'price_unit' => 'per_acara', 'description' => 'Dekorasi ulang tahun: balon, backdrop custom, meja kue, dan properti foto.', 'capacity_min' => null, 'capacity_max' => null, 'facilities' => '["Balon helium","Backdrop custom","Meja kue","Properti foto"]'],
                ],
                'location_type' => 'mobile',
            ],
        ];

        foreach ($vendorsData as $vd) {
            $user = User::create([
                'name' => $vd['user']['name'],
                'email' => $vd['user']['email'],
                'password' => Hash::make('vendor123'),
                'role' => 'vendor',
                'phone' => $vd['user']['phone'],
                'email_verified_at' => now(),
            ]);

            $profile = VendorProfile::create(array_merge(
                $vd['profile'],
                ['user_id' => $user->id]
            ));

            foreach ($vd['services'] as $svc) {
                $slug = Str::slug($svc['name']);
                $origSlug = $slug;
                $c = 1;
                while (Service::where('slug', $slug)->exists()) {
                    $slug = $origSlug . '-' . $c++;
                }

                $facilities = is_string($svc['facilities'] ?? null) ? json_decode($svc['facilities'], true) : ($svc['facilities'] ?? null);

                $service = Service::create([
                    'vendor_profile_id' => $profile->id,
                    'category_id' => $vd['profile']['category_id'],
                    'name' => $svc['name'],
                    'slug' => $slug,
                    'description' => $svc['description'],
                    'price' => $svc['price'],
                    'price_unit' => $svc['price_unit'],
                    'capacity_min' => $svc['capacity_min'] ?? null,
                    'capacity_max' => $svc['capacity_max'] ?? null,
                    'facilities' => $facilities,
                    'is_featured' => $svc['is_featured'] ?? false,
                    'status' => 'published',
                    'is_active' => true,
                    'rating_avg' => $profile->rating_avg,
                    'rating_count' => rand(5, 30),
                ]);

                ServiceLocation::create([
                    'service_id' => $service->id,
                    'address' => $vd['profile']['address'],
                    'city' => $vd['profile']['city'],
                    'district' => $vd['profile']['district'] ?? null,
                    'province' => $vd['profile']['province'],
                    'latitude' => $vd['profile']['latitude'],
                    'longitude' => $vd['profile']['longitude'],
                    'location_type' => $vd['location_type'],
                ]);
            }
        }

        // ===== TESTIMONIALS =====
        $testimonials = [
            ['customer_name' => 'Anissa & Reza', 'event_type' => 'Pernikahan', 'content' => 'PestaGo sangat membantu! Kami bisa menemukan semua vendor pernikahan dalam satu platform. Prosesnya mudah dan transparan.', 'rating' => 5],
            ['customer_name' => 'Dina Fitriani', 'event_type' => 'Ulang Tahun', 'content' => 'Acara ulang tahun anak saya jadi sangat berkesan berkat vendor yang saya temukan di PestaGo. Harga jelas, kualitas terjamin!', 'rating' => 5],
            ['customer_name' => 'PT Maju Bersama', 'event_type' => 'Seminar', 'content' => 'Kami menggunakan PestaGo untuk mencari venue dan catering seminar perusahaan. Proses booking multi-vendor sangat efisien.', 'rating' => 4],
            ['customer_name' => 'Keluarga Susanto', 'event_type' => 'Khitanan', 'content' => 'Terima kasih PestaGo! Vendor dekorasi dan catering yang kami pilih memberikan layanan terbaik untuk acara khitanan anak kami.', 'rating' => 5],
            ['customer_name' => 'Sarah & Adi', 'event_type' => 'Pernikahan', 'content' => 'Fitur lokasi peta sangat membantu saat survey venue. Kami bisa langsung lihat alamat dan petunjuk arah ke vendor.', 'rating' => 5],
            ['customer_name' => 'Universitas Karya', 'event_type' => 'Wisuda', 'content' => 'Acara wisuda kampus kami berjalan lancar dengan vendor-vendor pilihan dari PestaGo. Sangat direkomendasikan!', 'rating' => 4],
        ];

        foreach ($testimonials as $idx => $t) {
            Testimonial::create(array_merge($t, ['is_active' => true, 'sort_order' => $idx]));
        }

        // ===== PROMOS =====
        Promo::create([
            'title' => '🎉 Diskon Spesial Vendor Baru!',
            'description' => 'Nikmati diskon hingga 20% untuk booking pertama dari vendor baru di PestaGo. Berlaku untuk semua kategori layanan.',
            'is_active' => true,
            'sort_order' => 1,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(3),
        ]);

        Promo::create([
            'title' => '💍 Paket Pernikahan Impian',
            'description' => 'Rencanakan pernikahan impian Anda dengan paket bundling dari vendor terpercaya. Gratis konsultasi!',
            'is_active' => true,
            'sort_order' => 2,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(6),
        ]);

        // ===== SAMPLE EVENT & ORDERS =====
        $firstService = Service::first();
        $secondService = Service::skip(1)->first();

        if ($firstService) {
            $sampleEvent = \App\Models\Event::create([
                'user_id' => $customer1->id,
                'name' => 'Pernikahan Sari & Dimas',
                'event_type' => 'Pernikahan',
                'event_date' => now()->addDays(20),
                'location_city' => 'Jakarta Selatan',
                'guest_count' => 300,
                'budget' => 50000000,
                'status' => 'planning',
                'notes' => 'Acara resepsi malam hari, nuansa adat modern.',
            ]);

            $sampleOrder = \App\Models\Order::create([
                'user_id' => $customer1->id,
                'event_id' => $sampleEvent->id,
                'subtotal' => $firstService->price,
                'platform_fee' => round($firstService->price * 0.05),
                'total_amount' => round($firstService->price * 1.05),
                'status' => 'confirmed',
                'notes' => 'Harap konfirmasi ketersediaan sound system.',
            ]);

            $orderItem = \App\Models\OrderItem::create([
                'order_id' => $sampleOrder->id,
                'vendor_profile_id' => $firstService->vendor_profile_id,
                'service_id' => $firstService->id,
                'service_name_snapshot' => $firstService->name,
                'price_snapshot' => $firstService->price,
                'price_unit_snapshot' => $firstService->price_unit,
                'quantity' => 1,
                'subtotal' => $firstService->price,
                'booking_date' => now()->addDays(20),
                'status' => 'confirmed',
            ]);

            \App\Models\Payment::create([
                'order_id' => $sampleOrder->id,
                'amount' => $sampleOrder->total_amount,
                'platform_commission' => $sampleOrder->platform_fee,
                'payment_method' => 'transfer',
                'status' => 'paid',
                'paid_at' => now()->subDay(),
            ]);

            // Sample Advertisement for first vendor
            \App\Models\Advertisement::create([
                'vendor_profile_id' => $firstService->vendor_profile_id,
                'title' => 'Promo Spesial Paket All-in Wedding 2026',
                'poster_image' => 'services/demo-service-1.jpg',
                'placement' => 'home_featured',
                'package' => 'business',
                'payment_amount' => 349000,
                'payment_status' => 'paid',
                'review_status' => 'approved',
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ]);
        }

        echo "✅ Seeder berhasil! Akun demo:\n";
        echo "   Admin: admin@pestago.id / admin123\n";
        echo "   Customer: sari@email.com / customer123\n";
        echo "   Customer: budi@email.com / customer123\n";
        echo "   Vendor: vendor1@email.com - vendor6@email.com / vendor123\n";
    }
}
