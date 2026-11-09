<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    /**
     * Daftar semua modul fitur yang bisa diakses oleh sub-admin
     */
    public static function availablePermissions(): array
    {
        return [
            'chats' => [
                'name' => 'Live Chat & Customer Service (CS)',
                'desc' => 'Melayani live chat bantuan dari customer dan vendor secara real-time.',
                'icon' => 'bi-chat-dots',
                'color' => '#3B82F6',
            ],
            'vendors' => [
                'name' => 'Verifikasi & Kurasi Vendor',
                'desc' => 'Meninjau berkas KTP, izin usaha, approve/reject/minta perbaikan pendaftaran vendor.',
                'icon' => 'bi-shop',
                'color' => '#10B981',
            ],
            'orders' => [
                'name' => 'Manajemen Pesanan',
                'desc' => 'Memantau dan mengecek seluruh riwayat pesanan serta status penyewaan vendor.',
                'icon' => 'bi-bag-check',
                'color' => '#6366F1',
            ],
            'payments' => [
                'name' => 'Verifikasi Pembayaran',
                'desc' => 'Memeriksa mutasi bank & bukti transfer manual dari pelanggan.',
                'icon' => 'bi-credit-card',
                'color' => '#F59E0B',
            ],
            'refunds' => [
                'name' => 'Klaim Pengembalian Dana (Refund)',
                'desc' => 'Memproses dan menyetujui pengembalian dana pesanan yang dibatalkan.',
                'icon' => 'bi-arrow-return-left',
                'color' => '#EF4444',
            ],
            'users' => [
                'name' => 'Manajemen Pengguna (Customer)',
                'desc' => 'Melihat data customer, riwayat login, dan status aktif/nonaktif akun.',
                'icon' => 'bi-people',
                'color' => '#8B5CF6',
            ],
            'categories' => [
                'name' => 'Kategori Layanan',
                'desc' => 'Menambah, mengubah, dan mengelola kategori layanan acara di marketplace.',
                'icon' => 'bi-grid-3x3-gap',
                'color' => '#EC4899',
            ],
            'promos' => [
                'name' => 'Promo & Kupon Diskon',
                'desc' => 'Membuat kode voucher promo diskon dan kupon belanja platform.',
                'icon' => 'bi-percent',
                'color' => '#14B8A6',
            ],
            'advertisements' => [
                'name' => 'Moderasi Iklan & Promosi Vendor',
                'desc' => 'Menyetujui atau menolak materi iklan berbayar yang diajukan vendor.',
                'icon' => 'bi-megaphone',
                'color' => '#F97316',
            ],
            'maps' => [
                'name' => 'Peta Sebaran Vendor & Layanan',
                'desc' => 'Melihat peta interaktif titik koordinat vendor seluruh Indonesia.',
                'icon' => 'bi-geo-alt',
                'color' => '#06B6D4',
            ],
            'reports' => [
                'name' => 'Laporan & Analitik Keuangan',
                'desc' => 'Melihat ringkasan omzet, komisi platform, dan performa transaksi.',
                'icon' => 'bi-bar-chart-line',
                'color' => '#059669',
            ],
        ];
    }

    /**
     * Template role yang sudah siap (Presets)
     */
    public static function rolePresets(): array
    {
        return [
            'cs' => [
                'title' => 'Customer Service (CS)',
                'badge' => 'CS Helpdesk',
                'desc' => 'Fokus melayani pesan bantuan pelanggan & vendor serta memantau pesanan.',
                'permissions' => ['chats', 'orders', 'users'],
            ],
            'verifikator' => [
                'title' => 'Tim Kurasi & Verifikator Vendor',
                'badge' => 'Verifikator',
                'desc' => 'Memeriksa berkas pendaftaran vendor, izin usaha, dan kategori.',
                'permissions' => ['vendors', 'categories', 'maps', 'chats'],
            ],
            'finance' => [
                'title' => 'Finance & Operasional Pembayaran',
                'badge' => 'Finance',
                'desc' => 'Verifikasi bukti transfer pelanggan, refund, dan laporan omzet.',
                'permissions' => ['payments', 'refunds', 'reports', 'orders'],
            ],
            'marketing' => [
                'title' => 'Marketing & Campaign Manager',
                'badge' => 'Marketing',
                'desc' => 'Mengatur kupon promo platform dan memoderasi iklan vendor.',
                'permissions' => ['promos', 'advertisements', 'categories'],
            ],
            'all' => [
                'title' => 'Administrator Lengkap',
                'badge' => 'Full Admin',
                'desc' => 'Akses ke seluruh fitur operasional platform.',
                'permissions' => ['chats', 'vendors', 'orders', 'payments', 'refunds', 'users', 'categories', 'promos', 'advertisements', 'maps', 'reports'],
            ],
        ];
    }

    public function index()
    {
        $this->ensureSuperAdmin();

        $staffs = User::whereIn('role', ['super_admin', 'admin'])
            ->orderByRaw("FIELD(role, 'super_admin', 'admin')")
            ->orderByDesc('created_at')
            ->paginate(15);

        $availablePermissions = self::availablePermissions();

        return view('admin.staff.index', compact('staffs', 'availablePermissions'));
    }

    public function create()
    {
        $this->ensureSuperAdmin();

        $availablePermissions = self::availablePermissions();
        $presets = self::rolePresets();

        return view('admin.staff.create', compact('availablePermissions', 'presets'));
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'admin_role_title' => 'required|string|max:100',
            'password' => ['required', 'confirmed', Password::min(8)],
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:' . implode(',', array_keys(self::availablePermissions())),
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'admin_role_title.required' => 'Jabatan / Nama Role wajib diisi.',
            'permissions.required' => 'Pilih minimal 1 fitur yang diizinkan untuk akun ini.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'admin_role_title' => $request->admin_role_title,
            'permissions' => $request->permissions,
            'is_active' => true,
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', "Akun admin/staf '{$request->name}' berhasil dibuat dengan role '{$request->admin_role_title}'.");
    }

    public function edit(User $user)
    {
        $this->ensureSuperAdmin();

        if ($user->role === 'super_admin' && $user->id !== auth()->id()) {
            return redirect()->route('admin.staff.index')
                ->with('error', 'Akun Super Admin utama tidak dapat diubah dari menu staf.');
        }

        $availablePermissions = self::availablePermissions();
        $presets = self::rolePresets();

        return view('admin.staff.edit', compact('user', 'availablePermissions', 'presets'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureSuperAdmin();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'admin_role_title' => 'required|string|max:100',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'string|in:' . implode(',', array_keys(self::availablePermissions())),
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(8)];
        }

        $request->validate($rules);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'admin_role_title' => $request->admin_role_title,
            'permissions' => $request->permissions,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.staff.index')
            ->with('success', "Data akun staf '{$user->name}' berhasil diperbarui.");
    }

    public function toggle(User $user)
    {
        $this->ensureSuperAdmin();

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        if ($user->role === 'super_admin') {
            return back()->with('error', 'Akun Super Admin tidak dapat dinonaktifkan.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun staf '{$user->name}' berhasil {$statusStr}.");
    }

    public function destroy(User $user)
    {
        $this->ensureSuperAdmin();

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === 'super_admin') {
            return back()->with('error', 'Akun Super Admin tidak dapat dihapus.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Akun staf '{$name}' telah dihapus.");
    }

    protected function ensureSuperAdmin(): void
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses khusus Super Admin.');
        }
    }
}
