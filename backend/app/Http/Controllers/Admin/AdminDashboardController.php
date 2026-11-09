<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Service;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorWallet;
use App\Models\WalletTransaction;
use App\Models\Advertisement;
use App\Models\AdvertisementPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_customers' => User::where('role', 'customer')->count(),
            'total_vendors' => User::where('role', 'vendor')->count(),
            'pending_vendors' => VendorProfile::where('status', 'pending')->count(),
            'active_vendors' => VendorProfile::where('status', 'active')->count(),
            'total_services' => Service::count(),
            'published_services' => Service::where('status', 'published')->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::whereIn('status', ['pending_payment', 'payment_processing'])->count(),
            'active_orders' => Order::whereIn('status', ['paid', 'partially_confirmed', 'confirmed', 'in_progress'])->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'platform_commission' => Payment::where('status', 'paid')->sum('platform_commission'),
            'pending_refunds' => Refund::where('status', 'pending')->count(),
            'pending_withdrawals' => WalletTransaction::where('type', 'withdrawn')->where('status', 'pending')->count(),
        ];

        $recentOrders = Order::with(['user', 'items.vendorProfile', 'payment'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $pendingVendors = VendorProfile::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        $pendingWithdrawals = WalletTransaction::with(['wallet.vendorProfile.user'])
            ->where('type', 'withdrawn')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'pendingVendors', 'pendingWithdrawals'));
    }

    // --- Vendor Management (CRUD) ---
    public function vendors(Request $request)
    {
        $status = $request->get('status', '');
        $search = $request->get('search', '');
        $category_id = $request->get('category_id', '');

        $query = VendorProfile::with(['user', 'category', 'documents'])
            ->withCount(['services', 'orderItems']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $vendors = $query->orderByDesc('created_at')->paginate(15);
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.vendors.index', compact('vendors', 'status', 'search', 'category_id', 'categories'));
    }

    public function showVendor(VendorProfile $vendor)
    {
        $vendor->load(['user', 'category', 'documents', 'services', 'reviews', 'orderItems.order.user']);
        $totalEarned = $vendor->wallet ? $vendor->wallet->total_earned : 0;
        $activeOrdersCount = $vendor->orderItems()->whereHas('order', function ($q) {
            $q->whereIn('status', ['paid', 'in_progress', 'confirmed']);
        })->count();

        return view('admin.vendors.show', compact('vendor', 'totalEarned', 'activeOrdersCount'));
    }

    public function updateVendor(Request $request, VendorProfile $vendor)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $vendor->user_id,
            'phone' => 'required|string|max:25',
            'whatsapp' => 'nullable|string|max:25',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:incomplete,pending,needs_revision,active,rejected,suspended',
            'is_active' => 'nullable|boolean',
        ], [
            'business_name.required' => 'Nama usaha wajib diisi.',
            'category_id.required' => 'Kategori usaha wajib dipilih.',
            'owner_name.required' => 'Nama pemilik wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
        ]);

        // Update vendor profile
        $vendor->update([
            'business_name' => $request->business_name,
            'category_id' => $request->category_id,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp ?: $request->phone,
            'city' => $request->city,
            'address' => $request->address,
            'status' => $request->status,
        ]);

        // Update user account
        if ($vendor->user) {
            $userData = [
                'name' => $request->owner_name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];
            if ($request->has('is_active')) {
                $userData['is_active'] = $request->boolean('is_active');
            }
            $vendor->user->update($userData);
        }

        return back()->with('success', "Data vendor {$vendor->business_name} berhasil diperbarui.");
    }

    public function destroyVendor(VendorProfile $vendor)
    {
        // Check for active orders
        $hasActiveOrders = $vendor->orderItems()->whereHas('order', function ($q) {
            $q->whereIn('status', ['paid', 'in_progress', 'confirmed']);
        })->exists();

        if ($hasActiveOrders) {
            return back()->with('error', "Vendor {$vendor->business_name} tidak dapat dihapus karena memiliki pesanan aktif yang sedang berjalan. Anda bisa mengubah statusnya menjadi 'Suspended' atau menonaktifkan akun pemilik.");
        }

        $businessName = $vendor->business_name;
        $user = $vendor->user;

        // Clean up documents & services
        $vendor->documents()->delete();
        $vendor->services()->delete();
        $vendor->delete();

        // Delete user if role is vendor
        if ($user && $user->role === 'vendor') {
            $user->delete();
        }

        return redirect()->route('admin.vendors')->with('success', "Vendor {$businessName} berhasil dihapus.");
    }

    public function toggleVendorStatus(VendorProfile $vendor)
    {
        if ($vendor->user) {
            $vendor->user->update(['is_active' => !$vendor->user->is_active]);
            $status = $vendor->user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return back()->with('success', "Akun vendor {$vendor->business_name} berhasil {$status}.");
        }
        return back()->with('error', 'Akun pengguna vendor tidak ditemukan.');
    }

    public function verifyVendor(Request $request, VendorProfile $vendor)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,revision',
            'notes' => 'nullable|string|max:1000',
        ]);

        $action = $request->action;
        $notifTitle = '';
        $notifMessage = '';

        if ($action === 'approve') {
            $vendor->update([
                'status' => 'active',
                'verified_at' => now(),
                'admin_notes' => $request->notes,
            ]);
            $notifTitle = 'Vendor Disetujui!';
            $notifMessage = 'Selamat! Akun vendor Anda telah diverifikasi. Anda sekarang dapat mengelola layanan.';
        } elseif ($action === 'reject') {
            $vendor->update([
                'status' => 'rejected',
                'rejection_reason' => $request->notes,
                'admin_notes' => $request->notes,
            ]);
            $notifTitle = 'Vendor Ditolak';
            $notifMessage = 'Pendaftaran vendor Anda ditolak. Alasan: ' . ($request->notes ?: 'Tidak memenuhi kriteria.');
        } else {
            $vendor->update([
                'status' => 'needs_revision',
                'admin_notes' => $request->notes,
            ]);
            $notifTitle = 'Perbaikan Diperlukan';
            $notifMessage = 'Pendaftaran vendor Anda memerlukan perbaikan: ' . ($request->notes ?: 'Periksa data Anda.');
        }

        Notification::create([
            'user_id' => $vendor->user_id,
            'type' => 'vendor_verification',
            'title' => $notifTitle,
            'message' => $notifMessage,
            'data' => ['vendor_id' => $vendor->id, 'action' => $action],
        ]);

        return back()->with('success', 'Status vendor berhasil diperbarui.');
    }

    // --- Customer Management (CRUD) ---
    public function customers(Request $request)
    {
        $search = $request->get('search', '');
        $status = $request->get('status', '');

        $query = User::where('role', 'customer')
            ->withCount(['orders', 'events']);

        if ($status !== '' && $status !== null) {
            $query->where('is_active', $status == '1');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderByDesc('created_at')->paginate(15);
        $totalCustomers = User::where('role', 'customer')->count();
        $activeCustomers = User::where('role', 'customer')->where('is_active', true)->count();

        return view('admin.customers.index', compact('customers', 'search', 'status', 'totalCustomers', 'activeCustomers'));
    }

    public function showCustomer(User $customer)
    {
        if ($customer->role !== 'customer') {
            return redirect()->route('admin.customers.index')->with('error', 'User tersebut bukan customer.');
        }

        $customer->loadCount(['orders', 'events', 'reviews']);
        $orders = $customer->orders()->with(['items.vendorProfile'])->orderByDesc('created_at')->paginate(10);
        $events = $customer->events()->orderByDesc('created_at')->get();
        $totalSpent = $customer->orders()->whereIn('status', ['paid', 'in_progress', 'completed'])->sum('total_amount');

        return view('admin.customers.show', compact('customer', 'orders', 'events', 'totalSpent'));
    }

    public function updateCustomer(Request $request, User $customer)
    {
        if ($customer->role !== 'customer') {
            return back()->with('error', 'Hanya data customer yang dapat diubah di menu ini.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'phone' => 'required|string|max:25',
            'is_active' => 'nullable|boolean',
            'new_password' => 'nullable|string|min:8',
        ], [
            'name.required' => 'Nama lengkap customer wajib diisi.',
            'email.required' => 'Email customer wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'phone.required' => 'Nomor WhatsApp / telepon customer wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('new_password')) {
            $data['password'] = Hash::make($request->new_password);
        }

        $customer->update($data);

        return back()->with('success', "Data customer {$customer->name} berhasil diperbarui.");
    }

    public function destroyCustomer(User $customer)
    {
        if ($customer->role !== 'customer') {
            return back()->with('error', 'Hanya akun customer yang dapat dihapus di menu ini.');
        }

        // Check active orders
        $hasActiveOrders = $customer->orders()->whereIn('status', ['paid', 'in_progress', 'pending_payment', 'partially_confirmed'])->exists();

        if ($hasActiveOrders) {
            return back()->with('error', "Customer {$customer->name} tidak dapat dihapus karena memiliki pesanan yang sedang berjalan. Anda dapat menonaktifkan akun customer ini sebagai alternatif.");
        }

        $customerName = $customer->name;
        $customer->carts()->delete();
        $customer->notifications()->delete();
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', "Customer {$customerName} berhasil dihapus.");
    }

    public function toggleCustomer(User $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        $status = $customer->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun customer {$customer->name} berhasil {$status}.");
    }

    // Backward compatibility for users route
    public function users(Request $request)
    {
        return $this->customers($request);
    }

    public function toggleUser(User $user)
    {
        return $this->toggleCustomer($user);
    }

    // --- Categories ---
    public function categories()
    {
        $categories = Category::withCount('services')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->name);
        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'icon' => $request->icon,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);
        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    // --- Orders ---
    public function orders(Request $request)
    {
        $status = $request->get('status', '');
        $search = $request->get('search', '');
        $query = Order::with(['user', 'items.vendorProfile', 'payment']);

        if ($status)
            $query->where('status', $status);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->orderByDesc('created_at')->paginate(20);
        return view('admin.orders.index', compact('orders', 'status', 'search'));
    }

    public function showOrder(Order $order)
    {
        $order->load(['user', 'items.vendorProfile', 'items.service.location', 'payment', 'refunds', 'event']);
        return view('admin.orders.show', compact('order'));
    }

    // --- Payments ---
    public function payments(Request $request)
    {
        $status = $request->get('status', '');
        $query = Payment::with(['order.user']);

        if ($status)
            $query->where('status', $status);

        $payments = $query->orderByDesc('created_at')->paginate(20);
        return view('admin.payments.index', compact('payments', 'status'));
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($request->action === 'approve') {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            $payment->order->update(['status' => 'paid']);

            Notification::create([
                'user_id' => $payment->order->user_id,
                'type' => 'payment_confirmed',
                'title' => 'Pembayaran Dikonfirmasi',
                'message' => "Pembayaran untuk pesanan #{$payment->order->order_number} telah dikonfirmasi.",
                'data' => ['order_id' => $payment->order_id],
            ]);

            // Notifikasi vendor
            foreach ($payment->order->items as $item) {
                Notification::create([
                    'user_id' => $item->vendorProfile->user_id,
                    'type' => 'payment_confirmed',
                    'title' => 'Pembayaran Diterima',
                    'message' => "Pembayaran untuk pesanan '{$item->service_name_snapshot}' telah dikonfirmasi. Silakan proses pesanan.",
                    'data' => ['order_item_id' => $item->id],
                ]);
            }
        } else {
            $payment->update(['status' => 'failed']);
            $payment->order->update(['status' => 'pending_payment']);

            Notification::create([
                'user_id' => $payment->order->user_id,
                'type' => 'payment_rejected',
                'title' => 'Pembayaran Ditolak',
                'message' => 'Bukti pembayaran Anda ditolak. ' . ($request->notes ?: 'Silakan kirim ulang.'),
                'data' => ['order_id' => $payment->order_id],
            ]);
        }

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    // --- Promos ---
    public function promos()
    {
        $promos = \App\Models\Promo::orderByDesc('created_at')->paginate(15);
        return view('admin.promos.index', compact('promos'));
    }

    public function storePromo(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:2048',
            'link' => 'nullable|url|max:255',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data = $request->only(['title', 'description', 'link', 'starts_at', 'ends_at']);
        $data['is_active'] = true;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('promos', 'public');
        }

        \App\Models\Promo::create($data);
        return back()->with('success', 'Promo berhasil ditambahkan.');
    }

    public function updatePromo(Request $request, \App\Models\Promo $promo)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:2048',
            'link' => 'nullable|url|max:255',
            'is_active' => 'nullable',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data = $request->only(['title', 'description', 'link', 'starts_at', 'ends_at']);
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('promos', 'public');
        }

        $promo->update($data);
        return back()->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroyPromo(\App\Models\Promo $promo)
    {
        $promo->delete();
        return back()->with('success', 'Promo berhasil dihapus.');
    }

    // --- Refunds ---
    public function refunds(Request $request)
    {
        $status = $request->get('status', '');
        $query = Refund::with(['order.user', 'orderItem', 'payment']);

        if ($status)
            $query->where('status', $status);

        $refunds = $query->orderByDesc('created_at')->paginate(20);
        return view('admin.refunds.index', compact('refunds', 'status'));
    }

    public function processRefund(Request $request, Refund $refund)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($request->action === 'approve') {
            $refund->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'processed_at' => now(),
            ]);

            Notification::create([
                'user_id' => $refund->order->user_id,
                'type' => 'refund_approved',
                'title' => 'Refund Disetujui',
                'message' => "Refund #{$refund->refund_number} sebesar Rp " . number_format($refund->amount, 0, ',', '.') . " telah disetujui.",
                'data' => ['refund_id' => $refund->id],
            ]);
        } else {
            $refund->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'processed_at' => now(),
            ]);

            Notification::create([
                'user_id' => $refund->order->user_id,
                'type' => 'refund_rejected',
                'title' => 'Refund Ditolak',
                'message' => "Refund #{$refund->refund_number} ditolak. " . ($request->admin_notes ?: ''),
                'data' => ['refund_id' => $refund->id],
            ]);
        }

        return back()->with('success', 'Refund berhasil diproses.');
    }

    // --- Reports ---
    public function reports(Request $request)
    {
        $period = $request->get('period', 'month');

        // Revenue trend
        $revenueByMonth = Payment::where('status', 'paid')
            ->whereYear('paid_at', now()->year)
            ->selectRaw('MONTH(paid_at) as month, SUM(amount) as total, SUM(platform_commission) as commission')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top vendors
        $topVendors = \App\Models\VendorProfile::withCount(['reviews'])
            ->withSum([
                'orderItems as total_revenue' => function ($q) {
                    $q->where('status', 'completed');
                }
            ], 'subtotal')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // Category breakdown
        $categoryStats = Category::withCount(['services'])
            ->get();

        // Order status breakdown
        $orderStats = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('admin.reports.index', compact('revenueByMonth', 'topVendors', 'categoryStats', 'orderStats', 'period'));
    }

    // --- Advertisements CRUD & Moderation ---
    public function advertisements(Request $request)
    {
        $status = $request->get('status', '');
        $type = $request->get('type', '');
        $query = Advertisement::with('vendorProfile.user');

        if ($status) {
            $query->where('review_status', $status);
        }

        if ($type === 'admin') {
            $query->where('is_admin', true);
        } elseif ($type === 'vendor') {
            $query->where('is_admin', false);
        }

        $advertisements = $query->orderByDesc('created_at')->paginate(15);
        return view('admin.advertisements.index', compact('advertisements', 'status', 'type'));
    }

    public function createAdvertisement()
    {
        return view('admin.advertisements.create');
    }

    public function storeAdvertisement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'poster_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'target_url' => 'nullable|string|max:500',
        ]);

        $path = $request->file('poster_image')->store('advertisements', 'public');

        Advertisement::create([
            'is_admin' => true,
            'vendor_profile_id' => null,
            'title' => $request->title,
            'poster_image' => $path,
            'placement' => 'home_carousel',
            'show_overlay_text' => false,
            'button_text' => null,
            'show_badge' => false,
            'badge_text' => null,
            'target_url' => $request->target_url,
            'package' => 'internal_admin',
            'payment_amount' => 0,
            'payment_status' => 'paid',
            'review_status' => 'approved',
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ]);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Banner iklan Papan Utama berhasil dibuat dan siap ditayangkan.');
    }

    public function editAdvertisement(Advertisement $advertisement)
    {
        $vendors = VendorProfile::with('user')->orderBy('business_name')->get();
        $plans = AdvertisementPlan::all();
        return view('admin.advertisements.edit', compact('advertisement', 'vendors', 'plans'));
    }

    public function updateAdvertisement(Request $request, Advertisement $advertisement)
    {
        if ($advertisement->is_admin) {
            $request->validate([
                'title' => 'required|string|max:255',
                'poster_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
                'starts_at' => 'required|date',
                'ends_at' => 'required|date|after_or_equal:starts_at',
                'target_url' => 'nullable|string|max:500',
                'review_status' => 'required|in:approved,completed,rejected',
            ]);

            $data = [
                'title' => $request->title,
                'target_url' => $request->target_url,
                'starts_at' => $request->starts_at,
                'ends_at' => $request->ends_at,
                'review_status' => $request->review_status,
                'placement' => 'home_carousel',
                'vendor_profile_id' => null,
            ];

            if ($request->hasFile('poster_image')) {
                if ($advertisement->poster_image && Storage::disk('public')->exists($advertisement->poster_image)) {
                    Storage::disk('public')->delete($advertisement->poster_image);
                }
                $data['poster_image'] = $request->file('poster_image')->store('advertisements', 'public');
            }

            $advertisement->update($data);

            return redirect()->route('admin.advertisements.index')
                ->with('success', 'Banner iklan Papan Utama berhasil diperbarui.');
        }

        // Moderasi Iklan Vendor oleh Admin
        $request->validate([
            'vendor_profile_id' => 'nullable|exists:vendor_profiles,id',
            'title' => 'required|string|max:255',
            'poster_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'placement' => 'required|string',
            'package' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'target_url' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:40',
            'badge_text' => 'nullable|string|max:40',
            'review_status' => 'required|in:pending_payment,pending_review,needs_revision,approved,scheduled,active,rejected,completed',
            'payment_status' => 'required|in:pending,paid,failed',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $data = [
            'vendor_profile_id' => $request->vendor_profile_id ?: null,
            'title' => $request->title,
            'placement' => $request->placement,
            'show_overlay_text' => $request->boolean('show_overlay_text'),
            'button_text' => $request->button_text,
            'show_badge' => $request->boolean('show_badge') && !empty($request->badge_text),
            'badge_text' => ($request->boolean('show_badge') && !empty($request->badge_text)) ? $request->badge_text : null,
            'target_url' => $request->target_url,
            'package' => $request->package ?: $advertisement->package,
            'payment_status' => $request->payment_status,
            'review_status' => $request->review_status,
            'rejection_reason' => $request->rejection_reason,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ];

        if ($request->hasFile('poster_image')) {
            if ($advertisement->poster_image && Storage::disk('public')->exists($advertisement->poster_image)) {
                Storage::disk('public')->delete($advertisement->poster_image);
            }
            $data['poster_image'] = $request->file('poster_image')->store('advertisements', 'public');
        }

        $advertisement->update($data);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Iklan berhasil diperbarui.');
    }

    public function destroyAdvertisement(Advertisement $advertisement)
    {
        if ($advertisement->poster_image && Storage::disk('public')->exists($advertisement->poster_image)) {
            Storage::disk('public')->delete($advertisement->poster_image);
        }
        $advertisement->delete();

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Iklan berhasil dihapus secara permanen.');
    }

    public function processAdvertisement(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,revision',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        if ($request->action === 'approve') {
            $advertisement->update([
                'review_status' => 'approved',
                'rejection_reason' => null,
            ]);
            Notification::create([
                'user_id' => $advertisement->vendorProfile->user_id,
                'type' => 'ad_approved',
                'title' => 'Iklan Disetujui!',
                'message' => "Iklan '{$advertisement->title}' telah disetujui dan dijadwalkan tayang.",
                'data' => ['ad_id' => $advertisement->id],
            ]);
        } elseif ($request->action === 'reject') {
            $advertisement->update([
                'review_status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);
            Notification::create([
                'user_id' => $advertisement->vendorProfile->user_id,
                'type' => 'ad_rejected',
                'title' => 'Iklan Ditolak',
                'message' => "Iklan '{$advertisement->title}' ditolak. Alasan: {$request->rejection_reason}",
                'data' => ['ad_id' => $advertisement->id],
            ]);
        } elseif ($request->action === 'revision') {
            $advertisement->update([
                'review_status' => 'needs_revision',
                'rejection_reason' => $request->rejection_reason,
            ]);
            Notification::create([
                'user_id' => $advertisement->vendorProfile->user_id,
                'type' => 'ad_revision',
                'title' => 'Iklan Perlu Revisi',
                'message' => "Iklan '{$advertisement->title}' memerlukan perbaikan: {$request->rejection_reason}",
                'data' => ['ad_id' => $advertisement->id],
            ]);
        }

        return back()->with('success', 'Status iklan berhasil diperbarui.');
    }

    // --- Maps Explorer ---
    public function maps(Request $request)
    {
        $vendors = VendorProfile::with(['category', 'user'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount('services')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'type' => 'vendor',
                    'name' => $v->business_name,
                    'slug' => $v->slug,
                    'category' => $v->category->name ?? 'Vendor',
                    'category_id' => $v->category_id,
                    'status' => $v->status,
                    'city' => $v->city ?? 'Indonesia',
                    'address' => $v->address ?? '',
                    'phone' => $v->phone ?: $v->whatsapp,
                    'rating' => (float) ($v->rating_avg ?: 5.0),
                    'services_count' => $v->services_count,
                    'latitude' => (float) $v->latitude,
                    'longitude' => (float) $v->longitude,
                    'logo' => $v->logo_url,
                    'avatar' => $v->user?->avatar_url,
                    'url' => route('admin.vendors.show', $v->id),
                    'public_url' => route('services.index', ['vendor' => $v->slug]),
                ];
            });

        $services = Service::with(['category', 'vendorProfile', 'location', 'primaryImage'])
            ->whereHas('location', fn($q) => $q->whereNotNull('latitude')->whereNotNull('longitude'))
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'type' => 'service',
                    'name' => $s->name,
                    'slug' => $s->slug,
                    'vendor_name' => $s->vendorProfile->business_name ?? 'Vendor',
                    'category' => $s->category->name ?? 'Layanan',
                    'category_id' => $s->category_id,
                    'price' => number_format($s->price, 0, ',', '.'),
                    'price_raw' => (float) $s->price,
                    'price_unit' => str_replace('per_', '', $s->price_unit),
                    'rating' => (float) ($s->rating_avg ?: 5.0),
                    'city' => $s->location->city ?? '-',
                    'address' => $s->location->address ?? '-',
                    'latitude' => (float) $s->location->latitude,
                    'longitude' => (float) $s->location->longitude,
                    'thumbnail' => $s->thumbnail_url,
                    'public_url' => route('services.show', $s->slug),
                ];
            });

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $cities = VendorProfile::whereNotNull('city')->pluck('city')
            ->merge(\App\Models\ServiceLocation::whereNotNull('city')->pluck('city'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.maps.index', compact('vendors', 'services', 'categories', 'cities'));
    }

    // --- Live Chat & Customer Service Helpdesk ---
    public function supportChats(Request $request)
    {
        if (!auth()->user()->hasPermission('chats')) {
            abort(403, 'Anda tidak memiliki hak akses ke modul Live Chat & CS.');
        }

        $query = Conversation::with(['customer.vendorProfile', 'admin', 'latestMessage'])
            ->where('type', 'support');

        $filter = $request->get('filter', 'all');
        if ($filter === 'unread') {
            $query->whereHas('messages', function ($q) {
                $q->where('sender_id', '!=', auth()->id())->where('is_read', false);
            });
        }

        $conversations = $query->orderByDesc('last_message_at')->paginate(20);

        return view('admin.chats.index', compact('conversations', 'filter'));
    }

    public function showSupportChat(Conversation $conversation)
    {
        if (!auth()->user()->hasPermission('chats')) {
            abort(403, 'Anda tidak memiliki hak akses ke modul Live Chat & CS.');
        }

        if ($conversation->type !== 'support') {
            abort(404);
        }

        if (!$conversation->admin_id) {
            $conversation->update(['admin_id' => auth()->id()]);
        }

        $conversation->load(['customer.vendorProfile', 'admin', 'messages.sender']);

        // Mark incoming messages as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('admin.chats.show', compact('conversation'));
    }

    public function replySupportChat(Request $request, Conversation $conversation)
    {
        if (!auth()->user()->hasPermission('chats')) {
            abort(403, 'Anda tidak memiliki hak akses ke modul Live Chat & CS.');
        }

        if ($conversation->type !== 'support') {
            abort(404);
        }

        $request->validate([
            'message' => 'required|string|max:3000',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment')->store('chat_attachments', 'public');
        }

        $admin = auth()->user();

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->id,
            'message' => $request->message,
            'attachment' => $attachment,
        ]);

        $conversation->update([
            'admin_id' => $admin->id,
            'last_message_at' => now(),
        ]);

        // Kirim notifikasi ke pelanggan / vendor
        Notification::create([
            'user_id' => $conversation->customer_id,
            'type' => 'support_reply',
            'title' => 'Pesan dari Customer Care PestaGo',
            'message' => \Illuminate\Support\Str::limit($request->message, 60),
            'data' => ['conversation_id' => $conversation->id],
        ]);

        return back()->with('success', 'Balasan berhasil dikirim ke ' . $conversation->customer->name . '.');
    }

    // --- Platform & Integration Settings (Pajak, Komisi, Foonte, Midtrans) ---
    public function platformSettings()
    {
        $taxPercentage = \App\Models\PlatformSetting::taxPercentage();
        $platformFeePercentage = \App\Models\PlatformSetting::platformFeePercentage();
        $escrowAutoReleaseDays = \App\Models\PlatformSetting::escrowAutoReleaseDays();

        $foonteToken = \App\Models\PlatformSetting::foonteToken();
        $foonteStatus = \App\Models\PlatformSetting::isFoonteActive();

        $midtransServerKey = \App\Models\PlatformSetting::midtransServerKey();
        $midtransClientKey = \App\Models\PlatformSetting::midtransClientKey();
        $midtransMerchantId = \App\Models\PlatformSetting::midtransMerchantId();
        $midtransIsProduction = \App\Models\PlatformSetting::isMidtransProduction();

        return view('admin.settings.platform', compact(
            'taxPercentage',
            'platformFeePercentage',
            'escrowAutoReleaseDays',
            'foonteToken',
            'foonteStatus',
            'midtransServerKey',
            'midtransClientKey',
            'midtransMerchantId',
            'midtransIsProduction'
        ));
    }

    public function updatePlatformSettings(Request $request)
    {
        $request->validate([
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'platform_fee_percentage' => 'required|numeric|min:0|max:100',
            'escrow_auto_release_days' => 'nullable|integer|min:1|max:30',
            'foonte_token' => 'nullable|string|max:255',
            'foonte_status' => 'nullable|in:0,1',
            'midtrans_server_key' => 'nullable|string|max:255',
            'midtrans_client_key' => 'nullable|string|max:255',
            'midtrans_merchant_id' => 'nullable|string|max:255',
            'midtrans_is_production' => 'nullable|in:0,1',
        ]);

        \App\Models\PlatformSetting::set('tax_percentage', $request->tax_percentage, 'Persentase pemotongan pajak vendor', 'Pajak Vendor (%)', 'tax');
        \App\Models\PlatformSetting::set('platform_fee_percentage', $request->platform_fee_percentage, 'Persentase biaya platform customer', 'Biaya Platform (%)', 'payment');
        if ($request->filled('escrow_auto_release_days')) {
            \App\Models\PlatformSetting::set('escrow_auto_release_days', $request->escrow_auto_release_days, 'Jumlah hari auto-release dana escrow ke vendor', 'Auto-Release Escrow (Hari)', 'payment');
        }

        \App\Models\PlatformSetting::set('foonte_token', $request->foonte_token, 'Token API Foonte WhatsApp Gateway', 'Token Foonte WhatsApp', 'whatsapp');
        \App\Models\PlatformSetting::set('foonte_status', $request->input('foonte_status', '1'), 'Status aktif WhatsApp Gateway Foonte', 'Status Foonte', 'whatsapp');

        \App\Models\PlatformSetting::set('midtrans_server_key', $request->midtrans_server_key, 'Server Key Midtrans Payment Gateway', 'Midtrans Server Key', 'payment');
        \App\Models\PlatformSetting::set('midtrans_client_key', $request->midtrans_client_key, 'Client Key Midtrans Payment Gateway', 'Midtrans Client Key', 'payment');
        \App\Models\PlatformSetting::set('midtrans_merchant_id', $request->midtrans_merchant_id, 'Merchant ID Midtrans Payment Gateway', 'Midtrans Merchant ID', 'payment');
        \App\Models\PlatformSetting::set('midtrans_is_production', $request->input('midtrans_is_production', '0'), 'Mode Lingkungan Midtrans (0=Sandbox, 1=Production)', 'Midtrans Environment', 'payment');

        return back()->with('success', 'Pengaturan platform, WhatsApp Foonte, dan Midtrans berhasil disimpan.');
    }

    public function testFoonte(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:9|max:20',
        ], [
            'phone.required' => 'Nomor WhatsApp tujuan wajib diisi.',
        ]);

        $result = \App\Services\FoonteService::testConnection($request->phone);

        if ($result['success']) {
            return back()->with('success', 'Pesan uji coba WhatsApp berhasil dikirim ke ' . $request->phone . '!');
        }

        return back()->with('error', 'Gagal mengirim pesan uji coba WhatsApp: ' . ($result['message'] ?? 'Kesalahan tidak diketahui.'));
    }

    // --- Monitoring Saldo Vendor (Escrow) & Konfirmasi Pencairan Saldo (Manual Transfer) ---
    public function vendorWallets(Request $request)
    {
        $status = $request->get('status', 'all');

        // Daftar permintaan penarikan yang pending (menunggu aksi admin)
        $pendingWithdrawals = WalletTransaction::with(['wallet.vendorProfile.user'])
            ->where('type', 'withdrawn')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        // Seluruh riwayat transaksi penarikan
        $allWithdrawals = WalletTransaction::with(['wallet.vendorProfile.user', 'processedBy'])
            ->where('type', 'withdrawn')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'withdrawals_page');

        $wallets = VendorWallet::with(['vendorProfile.user', 'vendorProfile.category'])->paginate(15, ['*'], 'wallets_page');
        $totalEscrow = VendorWallet::sum('pending_balance');
        $totalAvailable = VendorWallet::sum('balance');
        $totalEarned = VendorWallet::sum('total_earned');
        $totalPendingWithdrawal = WalletTransaction::where('type', 'withdrawn')->where('status', 'pending')->sum('amount');

        return view('admin.wallets.index', compact(
            'wallets', 'totalEscrow', 'totalAvailable', 'totalEarned',
            'pendingWithdrawals', 'allWithdrawals', 'totalPendingWithdrawal', 'status'
        ));
    }

    /**
     * Konfirmasi transfer manual penarikan saldo vendor (Approve)
     */
    public function approveWithdrawal(Request $request, WalletTransaction $transaction)
    {
        if ($transaction->type !== 'withdrawn' || $transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini tidak dalam status menunggu konfirmasi penarikan.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
            'proof_image' => 'nullable|image|max:3072',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $proofPath = $request->file('proof_image')->store('payout_proofs', 'public');
        }

        DB::transaction(function () use ($transaction, $request, $proofPath) {
            $transaction->update([
                'status' => 'completed',
                'admin_notes' => $request->admin_notes,
                'proof_image' => $proofPath,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Kirim notifikasi ke Vendor
            $vendor = $transaction->wallet->vendorProfile;
            Notification::create([
                'user_id' => $vendor->user_id,
                'type' => 'withdrawal_approved',
                'title' => 'Pencairan Saldo Berhasil Ditransfer! 💸✅',
                'message' => "Penarikan saldo sebesar Rp " . number_format($transaction->amount, 0, ',', '.') . " telah berhasil ditransfer oleh Admin ke rekening {$vendor->bank_name} ({$vendor->bank_account_number}) a/n {$vendor->bank_account_holder}." . ($request->admin_notes ? " Catatan Admin: {$request->admin_notes}" : ""),
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'proof_image' => $proofPath,
                ],
            ]);
        });

        return back()->with('success', 'Penarikan saldo sebesar Rp ' . number_format($transaction->amount, 0, ',', '.') . ' berhasil disetujui & dikonfirmasi telah ditransfer!');
    }

    /**
     * Tolak penarikan saldo vendor & kembalikan dana ke saldo vendor (Reject)
     */
    public function rejectWithdrawal(Request $request, WalletTransaction $transaction)
    {
        if ($transaction->type !== 'withdrawn' || $transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini tidak dalam status menunggu konfirmasi penarikan.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ], [
            'rejection_reason.required' => 'Mohon sertakan alasan penolakan agar vendor mengetahui kendala transfer.',
            'rejection_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        DB::transaction(function () use ($transaction, $request) {
            $wallet = $transaction->wallet;

            // KEMBALIKAN SALDO SECARA UTUH KE SALDO TERSEDIA VENDOR
            $wallet->increment('balance', $transaction->amount);

            // Update status transaksi menjadi failed (ditolak)
            $transaction->update([
                'status' => 'failed',
                'admin_notes' => "DITOLAK ADMIN: " . $request->rejection_reason,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Catat mutasi pengembalian saldo (refunded)
            WalletTransaction::create([
                'vendor_wallet_id' => $wallet->id,
                'type' => 'refunded',
                'amount' => $transaction->amount,
                'status' => 'completed',
                'description' => "Pengembalian saldo dari penarikan #{$transaction->transaction_number} yang ditolak admin. Alasan: {$request->rejection_reason}",
            ]);

            // Kirim notifikasi ke Vendor bahwa saldo telah dikembalikan
            $vendor = $wallet->vendorProfile;
            Notification::create([
                'user_id' => $vendor->user_id,
                'type' => 'withdrawal_rejected',
                'title' => 'Penarikan Saldo Ditolak & Saldo Dikembalikan ⚠️',
                'message' => "Permintaan penarikan saldo sebesar Rp " . number_format($transaction->amount, 0, ',', '.') . " ditolak oleh admin. Alasan: {$request->rejection_reason}. Saldo telah dikembalikan secara utuh ke Saldo Tersedia Anda.",
                'data' => [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'reason' => $request->rejection_reason,
                ],
            ]);
        });

        return back()->with('success', 'Penarikan saldo berhasil ditolak. Saldo sebesar Rp ' . number_format($transaction->amount, 0, ',', '.') . ' telah dikembalikan secara utuh ke akun vendor.');
    }

    /**
     * API Real-time polling notifikasi & update badge sidebar untuk admin
     */
    public function liveNotifications(Request $request)
    {
        $u = auth()->user();
        $lastId = (int) $request->get('last_id', 0);

        // Ambil notifikasi baru untuk admin ini yang belum dibaca
        $newQuery = Notification::where('user_id', $u->id)
            ->where('is_read', false);

        if ($lastId > 0) {
            $newQuery->where('id', '>', $lastId);
        } else {
            // Pada poll pertama (initial load), jangan kirim notif popup agar tidak berisik
            $newQuery->whereRaw('1 = 0');
        }

        $newNotifs = $newQuery->orderBy('id', 'asc')->limit(10)->get();

        $notificationsPayload = $newNotifs->map(function ($notif) {
            $url = match($notif->type) {
                'withdrawal_request' => route('admin.wallets.index', ['status' => 'pending']),
                'support_chat' => route('admin.chats.index'),
                'vendor_verification' => route('admin.vendors', ['status' => 'pending']),
                'payment_submitted', 'new_paid_order', 'order_new' => route('admin.orders'),
                'refund_requested' => route('admin.refunds'),
                'new_ad_paid', 'ad_revision_submitted' => route('admin.advertisements.index'),
                default => route('admin.dashboard'),
            };

            $icon = match($notif->type) {
                'withdrawal_request' => 'bi-cash-stack',
                'support_chat' => 'bi-chat-dots-fill',
                'vendor_verification' => 'bi-shop',
                'payment_submitted', 'new_paid_order', 'order_new' => 'bi-box-seam',
                'refund_requested' => 'bi-arrow-return-left',
                'new_ad_paid', 'ad_revision_submitted' => 'bi-megaphone-fill',
                default => 'bi-bell-fill',
            };

            return [
                'id' => $notif->id,
                'type' => $notif->type,
                'title' => $notif->title,
                'message' => $notif->message,
                'url' => $url,
                'icon' => $icon,
                'time' => $notif->created_at->diffForHumans(),
            ];
        });

        // Hitung ID notifikasi terbesar saat ini untuk user ini
        $latestId = Notification::where('user_id', $u->id)->max('id') ?? 0;

        // Hitung real-time counts untuk bulat merah di sidebar
        $unreadChats = Conversation::where('type', 'support')
            ->whereHas('messages', function ($q) use ($u) {
                $q->where('sender_id', '!=', $u->id)->where('is_read', false);
            })->count();

        $pendingWithdrawals = WalletTransaction::where('type', 'withdrawn')
            ->where('status', 'pending')
            ->count();

        $pendingVendors = VendorProfile::where('status', 'pending')->count();

        $pendingOrders = Order::whereIn('status', ['pending_payment', 'payment_processing'])->count();

        $pendingRefunds = Refund::where('status', 'pending')->count();

        $pendingAds = Advertisement::where('review_status', 'pending_review')->count();

        return response()->json([
            'success' => true,
            'latest_id' => max($lastId, $latestId),
            'notifications' => $notificationsPayload,
            'counts' => [
                'chats' => $unreadChats,
                'wallets' => $pendingWithdrawals,
                'vendors' => $pendingVendors,
                'orders' => $pendingOrders,
                'refunds' => $pendingRefunds,
                'ads' => $pendingAds,
            ]
        ]);
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca via AJAX
     */
    public function markNotificationRead(Notification $notification)
    {
        if ($notification->user_id === auth()->id()) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }
}
