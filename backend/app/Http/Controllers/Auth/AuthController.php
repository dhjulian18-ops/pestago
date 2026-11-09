<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\OtpCode;
use App\Services\FoonteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check()) {
            return $this->redirectBasedOnRole(auth()->user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi admin.',
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectBasedOnRole($user);
    }

    public function showRegisterCustomer()
    {
        if (auth()->check()) {
            return $this->redirectBasedOnRole(auth()->user());
        }
        return view('auth.register-customer');
    }

    public function registerCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:9|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'phone.required' => 'Nomor WhatsApp / HP wajib diisi untuk verifikasi OTP.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $formattedPhone = FoonteService::formatPhone($request->phone);

        // Check if phone number is already registered to another active user
        if (User::where('phone', $formattedPhone)->orWhere('phone', $request->phone)->exists()) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp ini sudah terdaftar pada akun lain.',
            ])->withInput();
        }

        // Generate OTP
        $otp = OtpCode::generate(
            phone: $formattedPhone,
            action: 'register_customer',
            payload: [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $formattedPhone,
                'display_phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'customer',
            ],
            validMinutes: 10
        );

        // Send OTP via Foonte WhatsApp
        $sendResult = FoonteService::sendOtp($formattedPhone, $otp->code, 'pendaftaran akun Customer PestaGo');

        // Store session for OTP verification
        session([
            'pending_otp' => [
                'phone' => $formattedPhone,
                'display_phone' => $request->phone,
                'action' => 'register_customer',
                'role' => 'customer',
            ]
        ]);

        if (isset($sendResult['simulated']) && $sendResult['simulated']) {
            session()->flash('info', 'Mode Uji Coba: Token Foonte belum diisi di Pengaturan Admin. Kode OTP Anda adalah: ' . $otp->code);
        }

        return redirect()->route('otp.verify')->with('success', 'Kode OTP telah dikirimkan ke WhatsApp Anda (' . $request->phone . ').');
    }

    public function showRegisterVendor()
    {
        if (auth()->check() && auth()->user()->role !== 'vendor') {
            return $this->redirectBasedOnRole(auth()->user());
        }

        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        return view('auth.register-vendor', compact('categories'));
    }

    public function registerVendor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:9|max:20',
            'password' => ['required', 'confirmed', Password::min(8)],
            'business_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'phone.required' => 'No. WhatsApp wajib diisi untuk verifikasi kode OTP.',
            'business_name.required' => 'Nama usaha/brand wajib diisi.',
            'category_id.required' => 'Pilih kategori usaha utama Anda.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $formattedPhone = FoonteService::formatPhone($request->phone);

        if (User::where('phone', $formattedPhone)->orWhere('phone', $request->phone)->exists()) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp ini sudah terdaftar pada akun lain.',
            ])->withInput();
        }

        // Generate vendor slug
        $slug = Str::slug($request->business_name);
        $originalSlug = $slug;
        $counter = 1;
        while (VendorProfile::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        // Generate OTP
        $otp = OtpCode::generate(
            phone: $formattedPhone,
            action: 'register_vendor',
            payload: [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $formattedPhone,
                'display_phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'vendor',
                'business_name' => $request->business_name,
                'category_id' => $request->category_id,
                'slug' => $slug,
            ],
            validMinutes: 10
        );

        // Send OTP via Foonte WhatsApp
        $sendResult = FoonteService::sendOtp($formattedPhone, $otp->code, 'pendaftaran akun Vendor PestaGo');

        session([
            'pending_otp' => [
                'phone' => $formattedPhone,
                'display_phone' => $request->phone,
                'action' => 'register_vendor',
                'role' => 'vendor',
            ]
        ]);

        if (isset($sendResult['simulated']) && $sendResult['simulated']) {
            session()->flash('info', 'Mode Uji Coba: Token Foonte belum diisi di Pengaturan Admin. Kode OTP Anda adalah: ' . $otp->code);
        }

        return redirect()->route('otp.verify')->with('success', 'Kode OTP telah dikirimkan ke WhatsApp Anda (' . $request->phone . ').');
    }

    // ==========================================
    // OTP VERIFICATION (PENDAFTARAN)
    // ==========================================
    public function showVerifyOtp()
    {
        $pending = session('pending_otp');
        if (!$pending) {
            return redirect()->route('register');
        }

        $phone = $pending['display_phone'] ?? $pending['phone'];
        $maskedPhone = $this->maskPhoneNumber($phone);
        $action = $pending['action'] ?? 'register_customer';

        return view('auth.verify-otp', compact('phone', 'maskedPhone', 'action'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ], [
            'otp_code.required' => 'Masukkan 6 digit kode OTP.',
            'otp_code.size' => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        $pending = session('pending_otp');
        if (!$pending) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi verifikasi telah berakhir. Silakan daftar ulang.']);
        }

        $result = OtpCode::verify($pending['phone'], $request->otp_code, $pending['action']);

        if (!$result['success']) {
            return back()->withErrors(['otp_code' => $result['message']]);
        }

        $payload = $result['payload'];

        // Customer Registration Verification Completed
        if ($pending['action'] === 'register_customer') {
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'],
                'password' => $payload['password'], // already hashed
                'role' => 'customer',
            ]);

            session()->forget('pending_otp');
            Auth::login($user);

            return redirect()->route('customer.dashboard')
                ->with('success', 'Nomor WhatsApp Anda berhasil diverifikasi! Selamat datang di PestaGo, ' . $user->name . '!');
        }

        // Vendor Registration Verification Completed
        if ($pending['action'] === 'register_vendor') {
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'],
                'password' => $payload['password'],
                'role' => 'vendor',
            ]);

            VendorProfile::create([
                'user_id' => $user->id,
                'category_id' => $payload['category_id'],
                'business_name' => $payload['business_name'],
                'slug' => $payload['slug'],
                'phone' => $payload['phone'],
                'whatsapp' => $payload['phone'],
                'email' => $payload['email'],
                'status' => 'incomplete',
            ]);

            session()->forget('pending_otp');
            Auth::login($user);

            return redirect()->route('vendor.dashboard')
                ->with('info', 'Verifikasi WhatsApp berhasil! Selamat datang di Dashboard Vendor PestaGo. Silakan lengkapi profil usaha Anda.');
        }

        return redirect()->route('login');
    }

    public function resendOtp(Request $request)
    {
        $pending = session('pending_otp');
        if (!$pending) {
            return redirect()->route('register');
        }

        $formattedPhone = $pending['phone'];
        $action = $pending['action'];

        // Find existing payload from previous OTP
        $previous = OtpCode::where('phone', $formattedPhone)->where('action', $action)->orderBy('id', 'desc')->first();
        $payload = $previous ? $previous->payload : null;

        $otp = OtpCode::generate(
            phone: $formattedPhone,
            action: $action,
            payload: $payload,
            validMinutes: 10
        );

        $purpose = $action === 'register_vendor' ? 'pendaftaran Vendor PestaGo' : 'pendaftaran akun PestaGo';
        $sendResult = FoonteService::sendOtp($formattedPhone, $otp->code, $purpose);

        if (isset($sendResult['simulated']) && $sendResult['simulated']) {
            session()->flash('info', 'Mode Uji Coba: Kode OTP baru Anda adalah: ' . $otp->code);
        }

        return back()->with('success', 'Kode OTP baru telah dikirimkan ke WhatsApp Anda.');
    }

    // ==========================================
    // LUPA PASSWORD (FORGOT PASSWORD) VIA WA OTP
    // ==========================================
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ], [
            'identifier.required' => 'Nomor WhatsApp atau Email wajib diisi.',
        ]);

        $rawIdentifier = trim($request->identifier);
        $formattedPhone = FoonteService::formatPhone($rawIdentifier);

        // Search user by email or by phone
        $user = User::where('email', $rawIdentifier)
            ->orWhere('phone', $rawIdentifier)
            ->orWhere('phone', $formattedPhone)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'identifier' => 'Akun dengan email atau nomor WhatsApp tersebut tidak ditemukan di sistem PestaGo.',
            ])->withInput();
        }

        if (empty($user->phone)) {
            return back()->withErrors([
                'identifier' => 'Akun ini belum memiliki nomor WhatsApp terdaftar. Silakan hubungi Customer Care untuk bantuan.',
            ])->withInput();
        }

        $userPhone = FoonteService::formatPhone($user->phone);

        // Generate OTP for forgot password
        $otp = OtpCode::generate(
            phone: $userPhone,
            action: 'forgot_password',
            userId: $user->id,
            validMinutes: 10
        );

        // Send OTP
        $sendResult = FoonteService::sendOtp($userPhone, $otp->code, 'reset password akun PestaGo');

        session([
            'reset_password_otp' => [
                'user_id' => $user->id,
                'phone' => $userPhone,
                'display_phone' => $user->phone,
            ]
        ]);

        if (isset($sendResult['simulated']) && $sendResult['simulated']) {
            session()->flash('info', 'Mode Uji Coba: Kode OTP reset password Anda adalah: ' . $otp->code);
        }

        return redirect()->route('password.verify-otp')->with('success', 'Kode OTP untuk reset password telah dikirim ke WhatsApp Anda.');
    }

    public function showVerifyForgotPasswordOtp()
    {
        $session = session('reset_password_otp');
        if (!$session) {
            return redirect()->route('password.request');
        }

        $phone = $session['display_phone'] ?? $session['phone'];
        $maskedPhone = $this->maskPhoneNumber($phone);

        return view('auth.forgot-password-verify', compact('phone', 'maskedPhone'));
    }

    public function verifyForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ], [
            'otp_code.required' => 'Masukkan 6 digit kode OTP.',
            'otp_code.size' => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        $session = session('reset_password_otp');
        if (!$session) {
            return redirect()->route('password.request')->withErrors(['identifier' => 'Sesi verifikasi berakhir. Silakan masukkan nomor Anda kembali.']);
        }

        $result = OtpCode::verify($session['phone'], $request->otp_code, 'forgot_password');

        if (!$result['success']) {
            return back()->withErrors(['otp_code' => $result['message']]);
        }

        // Authorize user to reset password
        session([
            'password_reset_authorized' => true,
            'password_reset_user_id' => $session['user_id'],
        ]);
        session()->forget('reset_password_otp');

        return redirect()->route('password.reset')->with('success', 'Kode OTP terverifikasi! Silakan buat password baru Anda.');
    }

    public function showResetPassword()
    {
        if (!session('password_reset_authorized') || !session('password_reset_user_id')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        if (!session('password_reset_authorized') || !session('password_reset_user_id')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = User::findOrFail(session('password_reset_user_id'));
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        session()->forget(['password_reset_authorized', 'password_reset_user_id']);

        return redirect()->route('login')->with('success', 'Password Anda berhasil diperbarui! Silakan masuk dengan password baru Anda.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Anda telah keluar.');
    }

    protected function redirectBasedOnRole(User $user)
    {
        return match($user->role) {
            'super_admin', 'admin' => redirect()->route('admin.dashboard'),
            'vendor' => redirect()->route('vendor.dashboard'),
            default => redirect()->route('customer.dashboard'),
        };
    }

    /**
     * Helper to mask phone numbers like 0812****7890
     */
    protected function maskPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        $len = strlen($clean);
        if ($len <= 6) return $clean;

        $prefix = substr($clean, 0, 4);
        $suffix = substr($clean, -3);
        $stars = str_repeat('*', max(3, $len - 7));

        return $prefix . $stars . $suffix;
    }
}
