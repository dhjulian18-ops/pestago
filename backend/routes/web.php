<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\EventController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorServiceController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — PestaGo
|--------------------------------------------------------------------------
*/

// ===================== PUBLIC =====================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang', [HomeController::class, 'about'])->name('about');
Route::get('/kontak', [HomeController::class, 'contact'])->name('contact');
Route::get('/cara-pemesanan', [HomeController::class, 'howItWorks'])->name('how-it-works');

// Layanan publik
Route::get('/layanan', [ServiceController::class, 'index'])->name('services.index');
Route::get('/layanan/{slug}', [ServiceController::class, 'show'])->name('services.show');

// Wishlist / Favorit
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// ===================== AUTH =====================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterCustomer'])->name('register');
    Route::post('/register', [AuthController::class, 'registerCustomer']);
    Route::get('/register/vendor', [AuthController::class, 'showRegisterVendor'])->name('register.vendor');
    Route::post('/register/vendor', [AuthController::class, 'registerVendor']);

    // OTP Verification (Pendaftaran)
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('otp.verify');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('otp.resend');

    // Lupa Password (Forgot Password via WhatsApp OTP)
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendForgotPasswordOtp'])->name('password.send-otp');
    Route::get('/forgot-password/verify', [AuthController::class, 'showVerifyForgotPasswordOtp'])->name('password.verify-otp');
    Route::post('/forgot-password/verify', [AuthController::class, 'verifyForgotPasswordOtp']);
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ===================== AUTHENTICATED (ALL ROLES) =====================
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/vendor', [ProfileController::class, 'updateVendorProfile'])->name('profile.vendor');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unreadCount');
    Route::get('/api/notifications/latest', [NotificationController::class, 'latest'])->name('api.notifications.latest');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/start', [ChatController::class, 'startConversation'])->name('chat.start');
    Route::post('/chat/support', [ChatController::class, 'startSupportChat'])->name('chat.support');
});

// ===================== CUSTOMER =====================
Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Acara
    Route::resource('events', EventController::class)->except(['destroy']);

    // Wishlist / Favorit
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::delete('/wishlist/{service}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Keranjang & Checkout
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/direct-checkout', [CartController::class, 'directCheckout'])->name('direct-checkout');
    Route::delete('/cart/{cart}', [CartController::class, 'remove'])->name('cart.remove');
    Route::match(['get', 'post'], '/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/cart/process', [CartController::class, 'processCheckout'])->name('cart.process');

    // Pesanan
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/payment', [OrderController::class, 'payment'])->name('orders.payment');
    Route::get('/orders/{order}/sync-payment', [\App\Http\Controllers\PaymentController::class, 'syncStatus'])->name('orders.syncPayment');
    Route::post('/orders/{order}/confirm-payment', [OrderController::class, 'confirmPayment'])->name('orders.confirmPayment');
    Route::post('/orders/{order}/charge', [OrderController::class, 'createCharge'])->name('orders.charge');
    Route::get('/orders/{order}/check-status', [OrderController::class, 'checkPaymentStatus'])->name('orders.checkStatus');
    Route::get('/orders/{order}/success', [OrderController::class, 'paymentSuccess'])->name('orders.paymentSuccess');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/orders/items/{item}/cancel', [OrderController::class, 'cancelItem'])->name('orders.cancelItem');
    Route::post('/orders/items/{item}/confirm-complete', [OrderController::class, 'confirmItemComplete'])->name('orders.confirmComplete');
    Route::post('/orders/items/{item}/choose-replacement', [OrderController::class, 'chooseReplacement'])->name('orders.chooseReplacement');
    Route::post('/orders/items/{item}/request-refund', [OrderController::class, 'requestItemRefund'])->name('orders.requestRefund');
    Route::post('/orders/items/{item}/upload-proof', [OrderController::class, 'uploadProof'])->name('orders.uploadProof');

    // Reviews
    Route::get('/reviews/{item}/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews/{item}', [ReviewController::class, 'store'])->name('reviews.store');
});

// ===================== MIDTRANS WEBHOOK =====================
Route::post('/midtrans/notification', [\App\Http\Controllers\PaymentController::class, 'handleNotification'])->name('midtrans.notification');
Route::post('/payment/midtrans/notification', [\App\Http\Controllers\PaymentController::class, 'handleNotification']);

// ===================== VENDOR =====================
Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'role:vendor'])->group(function () {
    Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');

    // Persyaratan & Verifikasi Berkas Usaha
    Route::get('/verification', [VendorDashboardController::class, 'verification'])->name('verification');
    Route::post('/verification', [VendorDashboardController::class, 'submitVerification'])->name('verification.submit');

    // Fitur yang hanya bisa diakses setelah vendor disetujui & aktif
    Route::middleware('vendor.active')->group(function () {
        // Layanan vendor (resolve by ID, not slug)
        Route::resource('services', VendorServiceController::class)->except(['show'])->scoped(['service' => 'id']);
        Route::delete('/services/images/{image}', [VendorServiceController::class, 'deleteImage'])->name('services.deleteImage');

        // Pesanan vendor
        Route::get('/orders', [VendorDashboardController::class, 'orders'])->name('orders');
        Route::post('/orders/{item}/accept', [VendorDashboardController::class, 'acceptOrder'])->name('orders.accept');
        Route::post('/orders/{item}/reject', [VendorDashboardController::class, 'rejectOrder'])->name('orders.reject');
        Route::post('/orders/{item}/start-progress', [VendorDashboardController::class, 'startProgress'])->name('orders.startProgress');
        Route::post('/orders/{item}/upload-proof', [VendorDashboardController::class, 'uploadProof'])->name('orders.uploadProof');
        Route::post('/orders/{item}/complete', [VendorDashboardController::class, 'completeOrder'])->name('orders.complete');

        // Wallet vendor & Penarikan Saldo
        Route::get('/wallet', [VendorDashboardController::class, 'wallet'])->name('wallet');
        Route::post('/wallet/withdraw', [VendorDashboardController::class, 'withdraw'])->name('wallet.withdraw');

        // Vendor reviews
        Route::get('/reviews', [VendorDashboardController::class, 'reviews'])->name('reviews');
        Route::post('/reviews/{review}/reply', [VendorDashboardController::class, 'replyReview'])->name('reviews.reply');

        // Revenue
        Route::get('/revenue', [VendorDashboardController::class, 'revenue'])->name('revenue');

        // Calendar & Availability
        Route::get('/calendar', [VendorDashboardController::class, 'calendar'])->name('calendar');
        Route::post('/calendar/toggle', [VendorDashboardController::class, 'toggleAvailability'])->name('calendar.toggle');

        // Advertisements / Promosi
        Route::get('/advertisements', [VendorDashboardController::class, 'advertisements'])->name('advertisements.index');
        Route::get('/advertisements/create', [VendorDashboardController::class, 'createAdvertisement'])->name('advertisements.create');
        Route::post('/advertisements', [VendorDashboardController::class, 'storeAdvertisement'])->name('advertisements.store');
        Route::get('/advertisements/{advertisement}/edit', [VendorDashboardController::class, 'editAdvertisement'])->name('advertisements.edit');
        Route::put('/advertisements/{advertisement}', [VendorDashboardController::class, 'updateAdvertisement'])->name('advertisements.update');
        Route::get('/advertisements/{advertisement}/payment', [VendorDashboardController::class, 'paymentAdvertisement'])->name('advertisements.payment');
        Route::post('/advertisements/{advertisement}/charge', [VendorDashboardController::class, 'chargeAdvertisement'])->name('advertisements.charge');
        Route::get('/advertisements/{advertisement}/check-status', [VendorDashboardController::class, 'checkAdPaymentStatus'])->name('advertisements.checkStatus');
        Route::post('/advertisements/{advertisement}/simulate-paid', [VendorDashboardController::class, 'simulateAdPaid'])->name('advertisements.simulatePaid');
        Route::get('/advertisements/{advertisement}/sync-payment', [\App\Http\Controllers\PaymentController::class, 'syncAdStatus'])->name('advertisements.syncPayment');
    });
});

// ===================== ADMIN & SUPER ADMIN =====================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Live Chat & CS Helpdesk
    Route::get('/chats', [AdminDashboardController::class, 'supportChats'])->name('chats.index');
    Route::get('/chats/{conversation}', [AdminDashboardController::class, 'showSupportChat'])->name('chats.show');
    Route::post('/chats/{conversation}/reply', [AdminDashboardController::class, 'replySupportChat'])->name('chats.reply');

    // Vendor Management (CRUD: Mantau, Edit, Hapus, Verifikasi, Toggle)
    Route::get('/vendors', [AdminDashboardController::class, 'vendors'])->name('vendors');
    Route::get('/vendors/{vendor}', [AdminDashboardController::class, 'showVendor'])->name('vendors.show');
    Route::put('/vendors/{vendor}', [AdminDashboardController::class, 'updateVendor'])->name('vendors.update');
    Route::delete('/vendors/{vendor}', [AdminDashboardController::class, 'destroyVendor'])->name('vendors.destroy');
    Route::post('/vendors/{vendor}/verify', [AdminDashboardController::class, 'verifyVendor'])->name('vendors.verify');
    Route::post('/vendors/{vendor}/toggle', [AdminDashboardController::class, 'toggleVendorStatus'])->name('vendors.toggle');

    // Customer Management (CRUD: Mantau, Edit, Hapus, Toggle)
    Route::get('/customers', [AdminDashboardController::class, 'customers'])->name('customers.index');
    Route::get('/customers/{customer}', [AdminDashboardController::class, 'showCustomer'])->name('customers.show');
    Route::put('/customers/{customer}', [AdminDashboardController::class, 'updateCustomer'])->name('customers.update');
    Route::delete('/customers/{customer}', [AdminDashboardController::class, 'destroyCustomer'])->name('customers.destroy');
    Route::post('/customers/{customer}/toggle', [AdminDashboardController::class, 'toggleCustomer'])->name('customers.toggle');

    // Users alias (backward compatibility)
    Route::get('/users', [AdminDashboardController::class, 'customers'])->name('users');
    Route::post('/users/{user}/toggle', [AdminDashboardController::class, 'toggleCustomer'])->name('users.toggle');

    // Categories
    Route::get('/categories', [AdminDashboardController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminDashboardController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminDashboardController::class, 'updateCategory'])->name('categories.update');

    // Orders
    Route::get('/orders', [AdminDashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [AdminDashboardController::class, 'showOrder'])->name('orders.show');

    // Payments
    Route::get('/payments', [AdminDashboardController::class, 'payments'])->name('payments');
    Route::post('/payments/{payment}/verify', [AdminDashboardController::class, 'verifyPayment'])->name('payments.verify');

    // Promos
    Route::get('/promos', [AdminDashboardController::class, 'promos'])->name('promos');
    Route::post('/promos', [AdminDashboardController::class, 'storePromo'])->name('promos.store');
    Route::put('/promos/{promo}', [AdminDashboardController::class, 'updatePromo'])->name('promos.update');
    Route::delete('/promos/{promo}', [AdminDashboardController::class, 'destroyPromo'])->name('promos.destroy');

    // Refunds
    Route::get('/refunds', [AdminDashboardController::class, 'refunds'])->name('refunds');
    Route::post('/refunds/{refund}/process', [AdminDashboardController::class, 'processRefund'])->name('refunds.process');

    // Advertisements CRUD, moderation & Plans Management
    Route::get('/advertisements', [AdminDashboardController::class, 'advertisements'])->name('advertisements.index');
    Route::get('/advertisements/create', [AdminDashboardController::class, 'createAdvertisement'])->name('advertisements.create');
    Route::post('/advertisements', [AdminDashboardController::class, 'storeAdvertisement'])->name('advertisements.store');
    Route::get('/advertisements/{advertisement}/edit', [AdminDashboardController::class, 'editAdvertisement'])->name('advertisements.edit');
    Route::put('/advertisements/{advertisement}', [AdminDashboardController::class, 'updateAdvertisement'])->name('advertisements.update');
    Route::delete('/advertisements/{advertisement}', [AdminDashboardController::class, 'destroyAdvertisement'])->name('advertisements.destroy');
    Route::post('/advertisements/{advertisement}/process', [AdminDashboardController::class, 'processAdvertisement'])->name('advertisements.process');
    Route::resource('ad-plans', \App\Http\Controllers\Admin\AdvertisementPlanController::class)
        ->parameters(['ad-plans' => 'plan'])
        ->names('ad-plans');
    Route::post('ad-plans/{plan}/toggle', [\App\Http\Controllers\Admin\AdvertisementPlanController::class, 'toggle'])->name('ad-plans.toggle');

    // Maps
    Route::get('/maps', [AdminDashboardController::class, 'maps'])->name('maps.index');

    // Reports
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');

    // Platform Settings (Pajak & Biaya Layanan, Foonte WhatsApp, Midtrans)
    Route::get('/settings/platform', [AdminDashboardController::class, 'platformSettings'])->name('settings.platform');
    Route::post('/settings/platform', [AdminDashboardController::class, 'updatePlatformSettings'])->name('settings.platform.update');
    Route::post('/settings/test-foonte', [AdminDashboardController::class, 'testFoonte'])->name('settings.foonte.test');

    // Monitoring Saldo Vendor (Escrow) & Konfirmasi Pencairan Saldo (Manual Transfer)
    Route::get('/wallets', [AdminDashboardController::class, 'vendorWallets'])->name('wallets.index');
    Route::post('/wallets/withdrawals/{transaction}/approve', [AdminDashboardController::class, 'approveWithdrawal'])->name('wallets.withdrawals.approve');
    Route::post('/wallets/withdrawals/{transaction}/reject', [AdminDashboardController::class, 'rejectWithdrawal'])->name('wallets.withdrawals.reject');

    // Live Notifications & Real-Time Sidebar Badges API (Polling)
    Route::get('/api/live-notifications', [AdminDashboardController::class, 'liveNotifications'])->name('api.liveNotifications');
    Route::post('/api/notifications/{notification}/read', [AdminDashboardController::class, 'markNotificationRead'])->name('api.markNotificationRead');

    // Kelola Akun Staf / Sub-Admin (Khusus Super Admin)
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::get('/create', [StaffController::class, 'create'])->name('create');
        Route::post('/', [StaffController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [StaffController::class, 'edit'])->name('edit');
        Route::put('/{user}', [StaffController::class, 'update'])->name('update');
        Route::delete('/{user}', [StaffController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle', [StaffController::class, 'toggle'])->name('toggle');
    });
});
