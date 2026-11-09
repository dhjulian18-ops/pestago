<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PlatformSetting;
use App\Models\Service;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Pastikan tidak ada cart tanpa event_id (auto-heal orphan cart)
        $orphanCarts = Cart::where('user_id', $user->id)->whereNull('event_id')->get();
        if ($orphanCarts->isNotEmpty()) {
            $latestEvent = Event::where('user_id', $user->id)->where('status', 'planning')->latest()->first();
            if (!$latestEvent) {
                $latestEvent = Event::create([
                    'user_id' => $user->id,
                    'name' => 'Acara Saya',
                    'event_type' => 'lainnya',
                    'event_date' => now()->addDays(14)->format('Y-m-d'),
                    'status' => 'planning',
                ]);
            }
            foreach ($orphanCarts as $oc) {
                $oc->update(['event_id' => $latestEvent->id]);
            }
        }

        // Group cart items by event (1 keranjang = 1 acara)
        $carts = Cart::with(['service.vendorProfile', 'service.primaryImage', 'service.location', 'event'])
            ->where('user_id', $user->id)
            ->get()
            ->groupBy('event_id');

        $events = Event::where('user_id', $user->id)
            ->whereIn('status', ['planning'])
            ->orderBy('event_date')
            ->get();

        // Total per event group
        $totals = [];
        foreach ($carts as $eventId => $eventCarts) {
            $totals[$eventId] = $eventCarts->sum(fn($c) => $c->price_snapshot * $c->quantity);
        }

        $grandTotal = Cart::where('user_id', $user->id)
            ->sum(DB::raw('price_snapshot * quantity'));

        $platformFeePercentage = PlatformSetting::platformFeePercentage();

        return view('customer.cart', compact('carts', 'events', 'totals', 'grandTotal', 'platformFeePercentage'));
    }

    /**
     * Helper cek apakah suatu tanggal sudah terisi/dibooking untuk layanan tertentu
     */
    public static function isDateBooked($serviceId, $date): bool
    {
        if (!$date) return false;
        $d = date('Y-m-d', strtotime($date));

        $hasAvail = \App\Models\ServiceAvailability::where('service_id', $serviceId)
            ->whereDate('date', $d)
            ->whereIn('status', ['booked', 'blocked', 'pending'])
            ->exists();

        if ($hasAvail) return true;

        $hasOrder = \App\Models\OrderItem::where('service_id', $serviceId)
            ->whereDate('booking_date', $d)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->exists();

        return $hasOrder;
    }

    public function add(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'event_id' => 'nullable|exists:events,id',
            'booking_date' => 'nullable|date|after_or_equal:today',
            'quantity' => 'integer|min:1|max:10',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $service = Service::with('vendorProfile')
            ->where('status', 'published')
            ->where('is_active', true)
            ->findOrFail($request->service_id);

        // Cari atau validasi event
        if ($request->filled('event_id')) {
            $event = Event::where('user_id', $user->id)->findOrFail($request->event_id);
        } else {
            // Ambil event planning terbaru milik user, atau buatkan otomatis jika belum ada
            $event = Event::where('user_id', $user->id)
                ->where('status', 'planning')
                ->latest()
                ->first();

            if (!$event) {
                $targetDate = $request->booking_date ?? now()->addDays(14)->format('Y-m-d');
                $event = Event::create([
                    'user_id' => $user->id,
                    'name' => 'Acara Saya (' . date('d M Y', strtotime($targetDate)) . ')',
                    'event_type' => 'lainnya',
                    'event_date' => $targetDate,
                    'status' => 'planning',
                ]);
            }
        }

        // Validasi: Cek apakah tanggal yang dipilih sudah terisi / dibooking
        $targetBookingDate = $request->booking_date ?? ($event?->event_date ? $event->event_date->format('Y-m-d') : null);
        if ($targetBookingDate && self::isDateBooked($service->id, $targetBookingDate)) {
            $formattedDate = date('d M Y', strtotime($targetBookingDate));
            return back()->with('error', "Tanggal {$formattedDate} sudah terisi / tidak tersedia untuk layanan ini. Silakan pilih tanggal lain yang tersedia di kalender.")->withInput();
        }

        // Cek apakah sudah ada di keranjang (sama event + service)
        $existingCart = Cart::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existingCart) {
            $existingCart->increment('quantity', $request->get('quantity', 1));
            $msg = 'Jumlah layanan di keranjang berhasil diperbarui!';
        } else {
            Cart::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'service_id' => $service->id,
                'booking_date' => $request->booking_date ?? $event->event_date,
                'quantity' => $request->get('quantity', 1),
                'price_snapshot' => $service->price,
                'notes' => $request->notes,
            ]);
            $msg = 'Layanan berhasil ditambahkan ke keranjang acara: ' . $event->name;
        }

        if ($request->boolean('redirect_to_cart')) {
            return redirect()->route('customer.cart')->with('success', $msg);
        }

        return back()->with('success', $msg);
    }

    public function remove(Cart $cart)
    {
        if ($cart->user_id !== auth()->id()) {
            abort(403);
        }

        $cart->delete();

        return back()->with('success', 'Layanan dihapus dari keranjang.');
    }

    /**
     * Direct Checkout — Pesan langsung 1 layanan tanpa masuk ke keranjang umum
     */
    public function directCheckout(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'event_id' => 'nullable|exists:events,id',
            'booking_date' => 'nullable|date|after_or_equal:today',
            'quantity' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $service = Service::with('vendorProfile')
            ->where('status', 'published')
            ->where('is_active', true)
            ->findOrFail($request->service_id);

        // Cari atau validasi event yang dipilih
        if ($request->filled('event_id')) {
            $event = Event::where('user_id', $user->id)->findOrFail($request->event_id);
        } else {
            // Ambil event planning terbaru milik user, atau buatkan otomatis jika belum ada
            $event = Event::where('user_id', $user->id)
                ->where('status', 'planning')
                ->latest()
                ->first();

            if (!$event) {
                $targetDate = $request->booking_date ?? now()->addDays(14)->format('Y-m-d');
                $event = Event::create([
                    'user_id' => $user->id,
                    'name' => 'Acara Saya (' . date('d M Y', strtotime($targetDate)) . ')',
                    'event_type' => 'lainnya',
                    'event_date' => $targetDate,
                    'status' => 'planning',
                ]);
            }
        }

        // Validasi: Cek apakah tanggal yang dipilih sudah terisi / dibooking
        $targetBookingDate = $request->booking_date ?? ($event?->event_date ? $event->event_date->format('Y-m-d') : null);
        if ($targetBookingDate && self::isDateBooked($service->id, $targetBookingDate)) {
            $formattedDate = date('d M Y', strtotime($targetBookingDate));
            return back()->with('error', "Tanggal {$formattedDate} sudah terisi / tidak tersedia untuk layanan '{$service->name}'. Silakan pilih tanggal lain yang tersedia di kalender.")->withInput();
        }

        // Buat objek Cart in-memory sementara khusus pesanan langsung ini (tanpa disimpan ke database tabel carts)
        $cart = new Cart([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'service_id' => $service->id,
            'booking_date' => $request->booking_date ?? $event->event_date,
            'quantity' => $request->get('quantity', 1),
            'price_snapshot' => $service->price,
            'notes' => $request->notes,
        ]);
        $cart->setRelation('service', $service);
        $cart->setRelation('event', $event);

        $carts = collect([$cart]);
        $subtotal = $service->price * $cart->quantity;
        $platformFeePercentage = PlatformSetting::platformFeePercentage();
        $platformFee = round($subtotal * ($platformFeePercentage / 100));
        $total = $subtotal + $platformFee;
        $isDirectCheckout = true;

        return view('customer.checkout', compact('carts', 'event', 'subtotal', 'platformFee', 'platformFeePercentage', 'total', 'isDirectCheckout', 'service'));
    }

    /**
     * Checkout — Hanya item dari 1 event per checkout
     */
    public function checkout(Request $request)
    {
        $user = auth()->user();
        $cartIds = $request->get('cart_ids', []);

        if (empty($cartIds)) {
            return back()->with('error', 'Pilih minimal satu layanan untuk checkout.');
        }

        $carts = Cart::with(['service.vendorProfile', 'event'])
            ->where('user_id', $user->id)
            ->whereIn('id', $cartIds)
            ->get();

        if ($carts->isEmpty()) {
            return back()->with('error', 'Keranjang tidak valid.');
        }

        // Validasi: pastikan event terisi
        $event = $carts->first()->event;
        if (!$event) {
            $event = Event::where('user_id', $user->id)->where('status', 'planning')->latest()->first();
            if (!$event) {
                $event = Event::create([
                    'user_id' => $user->id,
                    'name' => 'Acara Saya',
                    'event_type' => 'lainnya',
                    'event_date' => now()->addDays(14)->format('Y-m-d'),
                    'status' => 'planning',
                ]);
            }
            foreach ($carts as $c) {
                if (!$c->event_id) {
                    $c->update(['event_id' => $event->id]);
                }
            }
        }

        // Validasi: semua cart harus dari event yang sama
        $eventIds = $carts->pluck('event_id')->unique();
        if ($eventIds->count() > 1) {
            return back()->with('error', 'Checkout hanya bisa untuk satu acara sekaligus. Pilih layanan dari satu acara saja.');
        }

        // Validasi ulang harga
        foreach ($carts as $cart) {
            $currentService = Service::where('status', 'published')->where('is_active', true)->find($cart->service_id);
            if (!$currentService) {
                return back()->with('error', "Layanan '{$cart->service->name}' tidak lagi tersedia.");
            }
            $cart->price_snapshot = $currentService->price;
            $cart->save();
        }

        $subtotal = $carts->sum(fn($c) => $c->price_snapshot * $c->quantity);
        $platformFeePercentage = PlatformSetting::platformFeePercentage();
        $platformFee = round($subtotal * ($platformFeePercentage / 100));
        $total = $subtotal + $platformFee;

        return view('customer.checkout', compact('carts', 'event', 'subtotal', 'platformFee', 'platformFeePercentage', 'total'));
    }

    /**
     * Process checkout — buat order + payment
     */
    public function processCheckout(Request $request)
    {
        $isDirect = $request->boolean('is_direct');

        $rules = [
            'event_id' => 'required|exists:events,id',
            'payment_type' => 'required|in:non_tunai,tunai',
            'payment_method' => 'nullable|in:midtrans,transfer,gopay,ovo,dana,manual,bca,bni,bri,mandiri,permata,qris',
            'cash_meeting_notes' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'event_name' => 'nullable|string|max:255',
            'event_date' => 'nullable|date',
            'location_city' => 'nullable|string|max:100',
            'location_address' => 'nullable|string|max:500',
        ];

        if ($isDirect) {
            $rules['direct_service_id'] = 'required|exists:services,id';
            $rules['quantity'] = 'nullable|integer|min:1|max:10';
            $rules['booking_date'] = 'nullable|date';
        } else {
            $rules['cart_ids'] = 'required|array|min:1';
            $rules['cart_ids.*'] = 'exists:carts,id';
        }

        $request->validate($rules);

        $user = auth()->user();

        // Update kontak pemesan jika diisi
        if ($request->filled('customer_name') && $request->customer_name !== $user->name) {
            $user->update(['name' => $request->customer_name]);
        }
        if ($request->filled('customer_phone') && $request->customer_phone !== $user->phone) {
            $user->update(['phone' => $request->customer_phone]);
        }

        // Validasi event milik customer
        $event = Event::where('user_id', $user->id)->findOrFail($request->event_id);

        // Update detail event jika disesuaikan saat checkout
        $eventUpdates = [];
        if ($request->filled('event_name')) {
            $eventUpdates['name'] = $request->event_name;
        }
        if ($request->filled('event_date')) {
            $eventUpdates['event_date'] = $request->event_date;
        }
        if ($request->filled('location_city')) {
            $eventUpdates['location_city'] = $request->location_city;
        }
        if ($request->filled('location_address')) {
            $eventUpdates['location_address'] = $request->location_address;
        }
        if (!empty($eventUpdates)) {
            $event->update($eventUpdates);
        }

        if ($isDirect) {
            $service = Service::with('vendorProfile')
                ->where('status', 'published')
                ->where('is_active', true)
                ->findOrFail($request->direct_service_id);

            $cart = new Cart([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'service_id' => $service->id,
                'booking_date' => $request->event_date ?? ($request->booking_date ?? $event->event_date),
                'quantity' => $request->get('quantity', 1),
                'price_snapshot' => $service->price,
                'notes' => $request->notes,
            ]);
            $cart->setRelation('service', $service);
            $cart->setRelation('event', $event);
            $carts = collect([$cart]);
        } else {
            $carts = Cart::with(['service.vendorProfile'])
                ->where('user_id', $user->id)
                ->whereIn('id', $request->cart_ids)
                ->get();

            if ($carts->isEmpty()) {
                return back()->with('error', 'Keranjang tidak valid.');
            }

            // Validasi semua cart dari event yang sama
            $eventIds = $carts->pluck('event_id')->unique();
            if ($eventIds->count() > 1 || $eventIds->first() != $event->id) {
                return back()->with('error', 'Semua item harus dari acara yang sama.');
            }
        }

        // Validasi: Pastikan tidak ada layanan yang tanggalnya sudah terisi / dibooking
        $requestedEventDate = $request->event_date;
        foreach ($carts as $c) {
            $checkDate = $requestedEventDate ?? ($c->booking_date ? date('Y-m-d', strtotime($c->booking_date)) : null);
            if ($checkDate && self::isDateBooked($c->service_id, $checkDate)) {
                $formattedDate = date('d M Y', strtotime($checkDate));
                return back()->with('error', "Maaf, tanggal {$formattedDate} untuk layanan '{$c->service->name}' sudah terisi / dibooking. Silakan pilih jadwal lain di kalender.")->withInput();
            }
        }

        DB::transaction(function () use ($carts, $request, $user, $event, $isDirect) {
            // Hitung total
            $subtotal = 0;
            foreach ($carts as $cart) {
                $service = Service::where('status', 'published')->where('is_active', true)->findOrFail($cart->service_id);
                $cart->price_snapshot = $service->price;
                $subtotal += $cart->price_snapshot * $cart->quantity;
            }

            $platformFeePercentage = PlatformSetting::platformFeePercentage();
            $platformFee = round($subtotal * ($platformFeePercentage / 100));
            $total = $subtotal + $platformFee;

            // Buat order
            $order = Order::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'subtotal' => $subtotal,
                'platform_fee' => $platformFee,
                'total_amount' => $total,
                'status' => $request->payment_type === 'tunai' ? 'paid' : 'pending_payment',
                'payment_type' => $request->payment_type,
                'cash_meeting_notes' => $request->cash_meeting_notes,
                'notes' => $request->notes,
            ]);

            // Buat order items per vendor & sinkronkan ketersediaan jadwal
            foreach ($carts as $cart) {
                $itemBookingDate = $request->event_date ?? ($cart->booking_date ?? $event->event_date);

                OrderItem::create([
                    'order_id' => $order->id,
                    'vendor_profile_id' => $cart->service->vendor_profile_id,
                    'service_id' => $cart->service_id,
                    'service_name_snapshot' => $cart->service->name,
                    'price_snapshot' => $cart->price_snapshot,
                    'price_unit_snapshot' => $cart->service->price_unit,
                    'quantity' => $cart->quantity,
                    'subtotal' => $cart->price_snapshot * $cart->quantity,
                    'booking_date' => $itemBookingDate,
                    'customer_notes' => $cart->notes,
                    'status' => 'pending_confirmation',
                ]);

                // Tandai tanggal di kalender sebagai booked
                if ($itemBookingDate) {
                    \App\Models\ServiceAvailability::updateOrCreate(
                        [
                            'service_id' => $cart->service_id,
                            'date' => date('Y-m-d', strtotime($itemBookingDate)),
                        ],
                        [
                            'status' => 'booked',
                            'notes' => 'Pesanan #' . $order->order_number,
                        ]
                    );
                }
            }

            // Buat payment record
            if ($request->payment_type === 'non_tunai') {
                Payment::create([
                    'order_id' => $order->id,
                    'gateway' => 'midtrans',
                    'amount' => $total,
                    'platform_commission' => $platformFee,
                    'payment_method' => $request->payment_method ?: 'midtrans',
                    'status' => 'pending',
                    'expired_at' => now()->addHours(24),
                ]);
            } else {
                // Tunai — payment langsung dianggap "paid" karena akan bayar langsung
                Payment::create([
                    'order_id' => $order->id,
                    'gateway' => 'cash',
                    'amount' => $total,
                    'platform_commission' => $platformFee,
                    'payment_method' => 'tunai',
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            // Hapus dari keranjang jika checkout dari keranjang (bukan pesan langsung)
            if (!$isDirect && !empty($request->cart_ids)) {
                Cart::where('user_id', $user->id)->whereIn('id', $request->cart_ids)->delete();
            }

            // Notifikasi ke vendor
            foreach ($carts->groupBy(fn($c) => $c->service->vendor_profile_id) as $vendorId => $vendorCarts) {
                $vendor = $vendorCarts->first()->service->vendorProfile;
                $paymentLabel = $request->payment_type === 'tunai' ? ' (Pembayaran Tunai)' : '';
                \App\Models\Notification::create([
                    'user_id' => $vendor->user_id,
                    'type' => 'order_new',
                    'title' => 'Pesanan Baru!',
                    'message' => "Ada pesanan baru dari {$user->name} untuk " . $vendorCarts->count() . " layanan{$paymentLabel}.",
                    'data' => ['order_id' => $order->id],
                ]);
            }

            session(['checkout_order_id' => $order->id]);
        });

        $orderId = session('checkout_order_id');
        $order = Order::find($orderId);

        if ($order && $order->payment_type === 'tunai') {
            return redirect()->route('customer.orders.show', $orderId)
                ->with('success', 'Pesanan berhasil dibuat dengan pembayaran tunai! Silakan janjian dengan vendor.');
        }

        return redirect()->route('customer.orders.payment', $orderId)
            ->with('success', 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.');
    }
}
