<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\User;
use App\Models\VendorDocument;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorDashboardController extends Controller
{
    public function verification()
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (!$vendor) {
            return redirect()->route('register.vendor');
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $documents = $vendor->documents()->get()->keyBy('document_type');

        return view('vendor.verification', compact('vendor', 'categories', 'documents'));
    }

    public function submitVerification(Request $request)
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (!$vendor) {
            return redirect()->route('register.vendor');
        }

        $hasKtp = $vendor->documents()->where('document_type', 'ktp')->exists();

        $request->validate([
            'business_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'phone' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'required|string|max:1000',
            'city' => 'required|string|max:100',
            'province' => 'nullable|string|max:100',
            'description' => 'required|string|min:20|max:3000',
            'bank_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_holder' => 'required|string|max:150',
            'doc_ktp' => ($hasKtp ? 'nullable' : 'required') . '|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'doc_siup' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'doc_portfolio' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'business_name.required' => 'Nama usaha wajib diisi.',
            'category_id.required' => 'Kategori usaha wajib dipilih.',
            'phone.required' => 'Nomor kontak / WhatsApp wajib diisi.',
            'address.required' => 'Alamat lengkap lokasi usaha wajib diisi.',
            'city.required' => 'Kota / kabupaten usaha wajib diisi.',
            'description.required' => 'Deskripsi usaha wajib diisi.',
            'description.min' => 'Deskripsi minimal 20 karakter untuk menjelaskan layanan Anda.',
            'bank_name.required' => 'Nama bank wajib diisi untuk pencairan saldo.',
            'bank_account_number.required' => 'Nomor rekening wajib diisi.',
            'bank_account_holder.required' => 'Nama pemilik rekening wajib diisi.',
            'doc_ktp.required' => 'Foto KTP pemilik usaha wajib diunggah untuk verifikasi identitas.',
            'doc_ktp.mimes' => 'Format file KTP harus berupa JPG, PNG, atau PDF.',
        ]);

        // Update profil usaha
        $vendor->update([
            'business_name' => $request->business_name,
            'category_id' => $request->category_id,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp ?: $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'description' => $request->description,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_holder' => $request->bank_account_holder,
            'status' => 'pending', // Menunggu persetujuan admin
        ]);

        // Simpan dokumen KTP
        if ($request->hasFile('doc_ktp')) {
            $path = $request->file('doc_ktp')->store('documents/vendors', 'public');
            VendorDocument::updateOrCreate(
                ['vendor_profile_id' => $vendor->id, 'document_type' => 'ktp'],
                [
                    'file_path' => $path,
                    'file_name' => $request->file('doc_ktp')->getClientOriginalName(),
                    'status' => 'pending',
                ]
            );
        }

        // Simpan dokumen NIB / SIUP
        if ($request->hasFile('doc_siup')) {
            $path = $request->file('doc_siup')->store('documents/vendors', 'public');
            VendorDocument::updateOrCreate(
                ['vendor_profile_id' => $vendor->id, 'document_type' => 'siup'],
                [
                    'file_path' => $path,
                    'file_name' => $request->file('doc_siup')->getClientOriginalName(),
                    'status' => 'pending',
                ]
            );
        }

        // Simpan Portofolio / Brosur
        if ($request->hasFile('doc_portfolio')) {
            $path = $request->file('doc_portfolio')->store('documents/vendors', 'public');
            VendorDocument::updateOrCreate(
                ['vendor_profile_id' => $vendor->id, 'document_type' => 'portfolio'],
                [
                    'file_path' => $path,
                    'file_name' => $request->file('doc_portfolio')->getClientOriginalName(),
                    'status' => 'pending',
                ]
            );
        }

        // Notifikasi ke Admin
        $admins = User::where('role', 'super_admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'vendor_verification',
                'title' => 'Pengajuan Persyaratan Vendor Baru',
                'message' => "Vendor '{$vendor->business_name}' telah melengkapi berkas persyaratan dan menunggu verifikasi Anda.",
                'data' => ['vendor_id' => $vendor->id],
            ]);
        }

        return redirect()->route('vendor.dashboard')
            ->with('success', 'Persyaratan berhasil dikirim! Tim Admin PestaGo akan meninjau kelengkapan berkas usaha Anda dalam 1-3 hari kerja.');
    }
    public function index()
    {
        $user = auth()->user();
        $vendor = $user->vendorProfile;

        if (!$vendor) {
            return redirect()->route('register.vendor')
                ->with('info', 'Silakan lengkapi profil vendor Anda.');
        }

        $pendingOrders = OrderItem::with(['order.user', 'service'])
            ->where('vendor_profile_id', $vendor->id)
            ->where('status', 'pending_confirmation')
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        $recentOrders = OrderItem::with(['order.user', 'service'])
            ->where('vendor_profile_id', $vendor->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_services' => Service::where('vendor_profile_id', $vendor->id)->count(),
            'published_services' => Service::where('vendor_profile_id', $vendor->id)->where('status', 'published')->count(),
            'pending_orders' => OrderItem::where('vendor_profile_id', $vendor->id)->where('status', 'pending_confirmation')->count(),
            'active_orders' => OrderItem::where('vendor_profile_id', $vendor->id)->whereIn('status', ['confirmed', 'in_progress'])->count(),
            'completed_orders' => OrderItem::where('vendor_profile_id', $vendor->id)->where('status', 'completed')->count(),
            'total_revenue' => OrderItem::where('vendor_profile_id', $vendor->id)->where('status', 'completed')->sum('subtotal'),
            'rating_avg' => $vendor->rating_avg,
        ];

        return view('vendor.dashboard', compact('vendor', 'pendingOrders', 'recentOrders', 'stats'));
    }

    public function orders(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        $status = $request->get('status', '');

        $query = OrderItem::with(['order.user', 'service', 'order.payment', 'progressProofs'])
            ->where('vendor_profile_id', $vendor->id);

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderByDesc('created_at')->paginate(15);

        return view('vendor.orders.index', compact('orders', 'status'));
    }

    public function acceptOrder(OrderItem $item)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($item->vendor_profile_id !== $vendor->id)
            abort(403);

        if ($item->status !== 'pending_confirmation') {
            return back()->with('error', 'Pesanan sudah tidak bisa dikonfirmasi.');
        }

        try {
            $workflow = app(\App\Services\OrderWorkflowService::class);
            $workflow->vendorConfirm($item);

            // Blokir tanggal di kalender
            if ($item->booking_date) {
                \App\Models\ServiceAvailability::updateOrCreate(
                    ['service_id' => $item->service_id, 'date' => $item->booking_date],
                    ['status' => 'booked']
                );
            }

            // Jika non-tunai dan sudah paid, masukkan escrow
            $order = $item->order;
            if (!$order->isCashPayment() && $order->payment?->status === 'paid') {
                $escrow = app(\App\Services\EscrowService::class);
                $escrow->escrowIn($item);
            }

            return back()->with('success', 'Pesanan berhasil dikonfirmasi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectOrder(Request $request, OrderItem $item)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($item->vendor_profile_id !== $vendor->id)
            abort(403);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($item->status !== 'pending_confirmation') {
            return back()->with('error', 'Pesanan sudah tidak bisa ditolak.');
        }

        try {
            $workflow = app(\App\Services\OrderWorkflowService::class);
            $workflow->vendorReject($item, $request->reason);
            return back()->with('success', 'Pesanan berhasil ditolak. Customer akan diberikan opsi vendor pengganti.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Vendor mulai mengerjakan pesanan
     */
    public function startProgress(OrderItem $item)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($item->vendor_profile_id !== $vendor->id)
            abort(403);

        try {
            $workflow = app(\App\Services\OrderWorkflowService::class);
            $workflow->vendorStartProgress($item);
            return back()->with('success', 'Status pesanan diubah ke "Sedang Dikerjakan".');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Vendor upload foto bukti progres
     */
    public function uploadProof(Request $request, OrderItem $item)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($item->vendor_profile_id !== $vendor->id)
            abort(403);

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'caption' => 'nullable|string|max:500',
            'type' => 'required|in:setup,in_progress,completion',
        ]);

        $path = $request->file('image')->store('progress_proofs', 'public');

        \App\Models\OrderProgressProof::create([
            'order_item_id' => $item->id,
            'uploaded_by' => auth()->id(),
            'type' => $request->type,
            'image_path' => $path,
            'caption' => $request->caption,
        ]);

        return back()->with('success', 'Foto bukti progres berhasil diunggah.');
    }

    public function completeOrder(OrderItem $item)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($item->vendor_profile_id !== $vendor->id)
            abort(403);

        if (!in_array($item->status, ['confirmed', 'in_progress'])) {
            return back()->with('error', 'Pesanan tidak bisa diselesaikan pada status ini.');
        }

        $item->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Sync order status
        $workflow = app(\App\Services\OrderWorkflowService::class);
        $workflow->syncOrderStatus($item->order);

        // Notifikasi customer
        \App\Models\Notification::create([
            'user_id' => $item->order->user_id,
            'type' => 'order_completed',
            'title' => 'Layanan Selesai — Konfirmasi Acara ✅',
            'message' => "Vendor '{$vendor->business_name}' telah menyelesaikan \"{$item->service_name_snapshot}\". Silakan konfirmasi acara selesai dan berikan ulasan.",
            'data' => ['order_id' => $item->order_id, 'order_item_id' => $item->id],
        ]);

        return back()->with('success', 'Pesanan ditandai selesai. Menunggu konfirmasi customer.');
    }

    /**
     * Halaman wallet / saldo vendor
     */
    public function wallet()
    {
        $vendor = auth()->user()->vendorProfile;
        $wallet = $vendor->getOrCreateWallet();
        $transactions = $wallet->transactions()->orderByDesc('created_at')->paginate(20);

        return view('vendor.wallet.index', compact('vendor', 'wallet', 'transactions'));
    }

    /**
     * Proses penarikan saldo (Withdrawal) ke rekening bank vendor
     */
    public function withdraw(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        $wallet = $vendor->getOrCreateWallet();

        // Cek kelengkapan informasi rekening bank vendor
        if (empty($vendor->bank_name) || empty($vendor->bank_account_number) || empty($vendor->bank_account_holder)) {
            return back()->with('error', 'Silakan lengkapi informasi Rekening Bank Anda terlebih dahulu di menu Verifikasi Usaha / Profil sebelum melakukan penarikan saldo.');
        }

        $minWithdrawal = 50000;
        $maxWithdrawal = (float) $wallet->balance;

        if ($maxWithdrawal < $minWithdrawal) {
            return back()->with('error', 'Saldo Tersedia belum mencukupi untuk dicairkan (minimal penarikan Rp ' . number_format($minWithdrawal, 0, ',', '.') . '). Saldo Anda saat ini: Rp ' . number_format($maxWithdrawal, 0, ',', '.') . '.');
        }

        $request->validate([
            'amount' => "required|numeric|min:{$minWithdrawal}|max:{$maxWithdrawal}",
            'notes' => 'nullable|string|max:255',
        ], [
            'amount.min' => 'Jumlah penarikan minimal Rp ' . number_format($minWithdrawal, 0, ',', '.'),
            'amount.max' => 'Jumlah penarikan tidak boleh melebihi saldo tersedia (Rp ' . number_format($maxWithdrawal, 0, ',', '.') . ')',
        ]);

        $amount = (float) $request->amount;

        DB::transaction(function () use ($wallet, $vendor, $amount, $request) {
            // Kurangi saldo tersedia sementara menunggu transfer admin
            $wallet->decrement('balance', $amount);

            // Buat transaksi pencairan berstatus PENDING (menunggu approval transfer manual admin)
            $txn = WalletTransaction::create([
                'vendor_wallet_id' => $wallet->id,
                'type' => 'withdrawn',
                'amount' => $amount,
                'status' => 'pending',
                'description' => "Pengajuan pencairan ke rekening {$vendor->bank_name} ({$vendor->bank_account_number}) a/n {$vendor->bank_account_holder}" . ($request->notes ? " — Catatan: {$request->notes}" : ''),
            ]);

            // Kirim notifikasi ke Admin & Super Admin
            $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'withdrawal_request',
                    'title' => 'Permintaan Pencairan Saldo Vendor 💸',
                    'message' => "Vendor {$vendor->business_name} mengajukan penarikan saldo sebesar Rp " . number_format($amount, 0, ',', '.') . " ke rekening {$vendor->bank_name} ({$vendor->bank_account_number}) a/n {$vendor->bank_account_holder}. Menunggu konfirmasi transfer manual.",
                    'data' => ['wallet_id' => $wallet->id, 'transaction_id' => $txn->id, 'amount' => $amount],
                ]);
            }
        });

        return back()->with('success', 'Permintaan penarikan saldo sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil diajukan! Admin akan memverifikasi dan mentransfer dana secara manual ke rekening ' . $vendor->bank_name . ' (' . $vendor->bank_account_number . ' a/n ' . $vendor->bank_account_holder . ').');
    }

    public function reviews()
    {
        $vendor = auth()->user()->vendorProfile;
        $reviews = \App\Models\Review::with(['user', 'service', 'orderItem'])
            ->where('vendor_profile_id', $vendor->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('vendor.reviews.index', compact('reviews'));
    }

    public function replyReview(Request $request, \App\Models\Review $review)
    {
        $vendor = auth()->user()->vendorProfile;
        if ($review->vendor_profile_id !== $vendor->id)
            abort(403);

        $request->validate([
            'vendor_reply' => 'required|string|max:1000',
        ]);

        $review->update([
            'vendor_reply' => $request->vendor_reply,
            'vendor_replied_at' => now(),
        ]);

        return back()->with('success', 'Balasan ulasan berhasil disimpan.');
    }

    public function revenue(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        $period = $request->get('period', 'all');

        $query = OrderItem::where('vendor_profile_id', $vendor->id)
            ->where('status', 'completed');

        if ($period === 'today') {
            $query->whereDate('updated_at', today());
        } elseif ($period === 'week') {
            $query->where('updated_at', '>=', now()->subWeek());
        } elseif ($period === 'month') {
            $query->where('updated_at', '>=', now()->subMonth());
        } elseif ($period === 'year') {
            $query->where('updated_at', '>=', now()->subYear());
        }

        $transactions = $query->with(['order.user', 'service'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        $totalRevenue = (clone $query)->sum('subtotal');

        // Monthly breakdown for the current year
        $monthlyRevenue = OrderItem::where('vendor_profile_id', $vendor->id)
            ->where('status', 'completed')
            ->whereYear('updated_at', now()->year)
            ->selectRaw('MONTH(updated_at) as month, SUM(subtotal) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return view('vendor.revenue.index', compact('transactions', 'totalRevenue', 'monthlyRevenue', 'period'));
    }

    public function calendar(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        $services = Service::where('vendor_profile_id', $vendor->id)->get();
        $selectedServiceId = $request->get('service_id');

        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Query availabilities
        $availabilityQuery = \App\Models\ServiceAvailability::whereHas('service', fn($q) => $q->where('vendor_profile_id', $vendor->id))
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($selectedServiceId) {
            $availabilityQuery->where('service_id', $selectedServiceId);
        }
        $availabilities = $availabilityQuery->with('service')->get()->keyBy(fn($item) => $item->date->format('Y-m-d'));

        // Query orders on this month
        $ordersQuery = OrderItem::with(['order.user', 'service'])
            ->where('vendor_profile_id', $vendor->id)
            ->whereBetween('booking_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($selectedServiceId) {
            $ordersQuery->where('service_id', $selectedServiceId);
        }
        $bookedOrders = $ordersQuery->get()->groupBy(fn($item) => $item->booking_date->format('Y-m-d'));

        return view('vendor.calendar.index', compact('vendor', 'services', 'selectedServiceId', 'month', 'year', 'startDate', 'endDate', 'availabilities', 'bookedOrders'));
    }

    public function toggleAvailability(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
            'status' => 'required|in:available,blocked',
            'notes' => 'nullable|string|max:255',
        ]);

        $service = Service::where('vendor_profile_id', $vendor->id)->findOrFail($request->service_id);

        if ($request->status === 'available') {
            \App\Models\ServiceAvailability::where('service_id', $service->id)
                ->where('date', $request->date)
                ->where('status', 'blocked')
                ->delete();
            return back()->with('success', 'Tanggal ' . \Carbon\Carbon::parse($request->date)->translatedFormat('d M Y') . ' telah dibuka kembali.');
        } else {
            \App\Models\ServiceAvailability::updateOrCreate(
                ['service_id' => $service->id, 'date' => $request->date],
                ['status' => 'blocked', 'notes' => $request->notes ?? 'Diblokir oleh vendor']
            );
            return back()->with('success', 'Tanggal ' . \Carbon\Carbon::parse($request->date)->translatedFormat('d M Y') . ' berhasil diblokir.');
        }
    }

    public function advertisements()
    {
        $vendor = auth()->user()->vendorProfile;
        $advertisements = \App\Models\Advertisement::where('vendor_profile_id', $vendor->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('vendor.advertisements.index', compact('vendor', 'advertisements'));
    }

    public function createAdvertisement()
    {
        $vendor = auth()->user()->vendorProfile;
        $plans = \App\Models\AdvertisementPlan::where('is_active', true)->orderBy('sort_order')->get();
        $plan = \App\Models\AdvertisementPlan::where('placement_key', 'vendor_billboard')->first()
            ?? $plans->first();
        return view('vendor.advertisements.create', compact('vendor', 'plans', 'plan'));
    }

    public function storeAdvertisement(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        $request->validate([
            'title' => 'required|string|max:255',
            'poster_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
            'placement' => 'required|exists:advertisement_plans,placement_key',
            'package' => 'required|in:basic,pro,business,custom',
            'custom_days' => 'nullable|required_if:package,custom|integer|min:1|max:365',
            'starts_at' => 'required|date|after_or_equal:today',
            'button_option' => 'nullable|string',
            'custom_button_text' => 'nullable|string|max:40',
            'target_url' => 'nullable|string|max:255',
        ]);

        $plan = \App\Models\AdvertisementPlan::where('placement_key', $request->placement)
            ->where('is_active', true)
            ->firstOrFail();

        $customDays = (int) $request->input('custom_days', 1);
        $tier = $plan->getTier($request->package, $customDays);
        $amount = $tier['price'];
        $days = $tier['days'];

        $startDate = \Carbon\Carbon::parse($request->starts_at);
        $endDate = $startDate->copy()->addDays($days);

        $path = $request->file('poster_image')->store('advertisements', 'public');

        // Button text resolution
        $buttonText = null;
        if ($request->button_option === 'custom' && $request->filled('custom_button_text')) {
            $buttonText = $request->custom_button_text;
        } elseif (!empty($request->button_option) && $request->button_option !== 'none') {
            $buttonText = $request->button_option;
        }

        $badgeText = null;
        $showBadge = $request->boolean('show_badge');
        if ($showBadge) {
            if ($request->badge_option === 'custom' && $request->filled('custom_badge_text')) {
                $badgeText = $request->custom_badge_text;
            } elseif (!empty($request->badge_option) && $request->badge_option !== 'none') {
                $badgeText = $request->badge_option;
            } else {
                $badgeText = 'Sponsor';
            }
        }

        $ad = \App\Models\Advertisement::create([
            'vendor_profile_id' => $vendor->id,
            'title' => $request->title,
            'poster_image' => $path,
            'placement' => $plan->placement_key,
            'show_overlay_text' => $request->boolean('show_overlay_text'),
            'button_text' => $buttonText,
            'show_badge' => $showBadge,
            'badge_text' => $badgeText,
            'target_url' => $request->target_url,
            'package' => $request->package,
            'payment_amount' => $amount,
            'payment_status' => 'pending',
            'review_status' => 'pending_payment',
            'starts_at' => $startDate,
            'ends_at' => $endDate,
        ]);

        return redirect()->route('vendor.advertisements.payment', $ad)
            ->with('success', 'Pengajuan iklan berhasil dibuat! Silakan selesaikan pembayaran QRIS.');
    }

    /**
     * Show QRIS payment page for an advertisement (UNIQLO Style, matching customer payment UI)
     */
    public function paymentAdvertisement(\App\Models\Advertisement $advertisement, \App\Services\MidtransService $midtransService)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor || $advertisement->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        if ($advertisement->payment_status === 'paid') {
            return redirect()->route('vendor.advertisements.index')
                ->with('info', 'Slot iklan ini sudah dibayar dan sedang menunggu kurasi.');
        }

        // Auto-create or fetch existing QRIS charge data
        $chargeData = $midtransService->createAdCoreCharge($advertisement, 'qris');
        $plan = \App\Models\AdvertisementPlan::where('placement_key', $advertisement->placement)->first();

        return view('vendor.advertisements.payment', compact('vendor', 'advertisement', 'chargeData', 'plan'));
    }

    /**
     * Create / refresh QRIS charge via AJAX
     */
    public function chargeAdvertisement(Request $request, \App\Models\Advertisement $advertisement, \App\Services\MidtransService $midtransService)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor || $advertisement->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        if ($advertisement->payment_status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Iklan ini sudah dibayar.'], 400);
        }

        $result = $midtransService->createAdCoreCharge($advertisement, 'qris');
        return response()->json($result);
    }

    /**
     * Check payment status via AJAX polling
     */
    public function checkAdPaymentStatus(\App\Models\Advertisement $advertisement, \App\Services\MidtransService $midtransService)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor || $advertisement->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        // If not paid, attempt check status from Midtrans directly
        if ($advertisement->payment_status !== 'paid' && $advertisement->payment_gateway_reference) {
            $statusData = $midtransService->checkAdStatus($advertisement);
            if ($statusData) {
                $midtransService->handleNotification($statusData);
            }
        }

        $advertisement->refresh();

        return response()->json([
            'payment_status' => $advertisement->payment_status,
            'review_status' => $advertisement->review_status,
            'is_paid' => $advertisement->payment_status === 'paid',
        ]);
    }

    /**
     * Simulate successful payment (Dev / Testing convenience)
     */
    public function simulateAdPaid(\App\Models\Advertisement $advertisement)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor || $advertisement->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        $advertisement->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_type' => 'qris',
            'review_status' => 'pending_review',
        ]);

        // Notify admins
        $admins = \App\Models\User::where('role', 'super_admin')
            ->orWhere(function ($q) {
                $q->where('role', 'admin')->where('permissions', 'like', '%advertisements%');
            })->get();

        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'new_ad_paid',
                'title' => 'Pengajuan Iklan Baru Lunas (Simulasi)',
                'message' => "Vendor {$vendor->business_name} telah membayar slot iklan '{$advertisement->title}'. Silakan tinjau dan setujui penayangan.",
                'data' => ['advertisement_id' => $advertisement->id],
            ]);
        }

        return redirect()->route('vendor.advertisements.index')
            ->with('success', 'Pembayaran QRIS berhasil dikonfirmasi! Iklan kini masuk antrean kurasi Admin.');
    }

    /**
     * Show form to edit / revise an advertisement
     */
    public function editAdvertisement(\App\Models\Advertisement $advertisement)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor || $advertisement->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        // Only allow editing if pending, needs_revision, or rejected
        if (!in_array($advertisement->review_status, ['needs_revision', 'rejected', 'pending_review', 'pending_payment'])) {
            return redirect()->route('vendor.advertisements.index')
                ->with('info', 'Iklan yang sudah disetujui atau sedang aktif tidak dapat diedit langsung.');
        }

        $plan = \App\Models\AdvertisementPlan::where('placement_key', $advertisement->placement)->first();

        return view('vendor.advertisements.edit', compact('vendor', 'advertisement', 'plan'));
    }

    /**
     * Submit revised advertisement
     */
    public function updateAdvertisement(Request $request, \App\Models\Advertisement $advertisement)
    {
        $vendor = auth()->user()->vendorProfile;
        if (!$vendor || $advertisement->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'poster_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'button_option' => 'nullable|string',
            'custom_button_text' => 'nullable|string|max:40',
            'target_url' => 'nullable|string|max:255',
        ]);

        $updateData = [
            'title' => $request->title,
            'show_overlay_text' => $request->boolean('show_overlay_text'),
            'target_url' => $request->target_url,
            'review_status' => 'pending_review', // Set back to pending review
        ];

        // Button text resolution
        if ($request->button_option === 'custom' && $request->filled('custom_button_text')) {
            $updateData['button_text'] = $request->custom_button_text;
        } elseif (!empty($request->button_option) && $request->button_option !== 'none') {
            $updateData['button_text'] = $request->button_option;
        } else {
            $updateData['button_text'] = null;
        }

        // Badge options resolution
        $badgeText = null;
        $showBadge = $request->boolean('show_badge');
        if ($showBadge) {
            if ($request->badge_option === 'custom' && $request->filled('custom_badge_text')) {
                $badgeText = $request->custom_badge_text;
            } elseif (!empty($request->badge_option) && $request->badge_option !== 'none') {
                $badgeText = $request->badge_option;
            } else {
                $badgeText = 'Sponsor';
            }
        }
        $updateData['show_badge'] = $showBadge;
        $updateData['badge_text'] = $badgeText;

        // Handle replacement poster upload if provided
        if ($request->hasFile('poster_image')) {
            $path = $request->file('poster_image')->store('advertisements', 'public');
            $updateData['poster_image'] = $path;
        }

        $advertisement->update($updateData);

        // Notifikasi ke staf admin / tim kurasi
        $admins = \App\Models\User::where('role', 'super_admin')
            ->orWhere(function ($q) {
                $q->where('role', 'admin')->where('permissions', 'like', '%advertisements%');
            })->get();

        foreach ($admins as $adm) {
            \App\Models\Notification::create([
                'user_id' => $adm->id,
                'type' => 'ad_revision_submitted',
                'title' => 'Revisi Iklan Telah Dikirim',
                'message' => "Vendor {$vendor->business_name} telah mengirimkan perbaikan materi iklan '{$advertisement->title}'. Silakan periksa kembali.",
                'data' => ['advertisement_id' => $advertisement->id],
            ]);
        }

        return redirect()->route('vendor.advertisements.index')
            ->with('success', 'Perbaikan materi iklan berhasil dikirim ulang! Tim Kurasi PestaGo akan segera meninjaunya kembali.');
    }
}
