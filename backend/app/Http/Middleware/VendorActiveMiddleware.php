<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorActiveMiddleware
{
    /**
     * Ensure vendor is verified/active before accessing vendor features
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'vendor') {
            abort(403, 'Akses tidak diizinkan.');
        }

        $vendor = $user->vendorProfile;

        if (!$vendor) {
            return redirect()->route('register.vendor')
                ->with('info', 'Silakan lengkapi profil vendor Anda terlebih dahulu.');
        }

        if ($vendor->status !== 'active') {
            $msg = match($vendor->status) {
                'incomplete' => 'Lengkapi persyaratan verifikasi usaha Anda terlebih dahulu agar dapat membuat dan menyewakan katalog layanan.',
                'pending' => 'Akun vendor Anda sedang dalam antrean peninjauan oleh admin. Anda akan dapat membuat katalog setelah disetujui.',
                'needs_revision' => 'Pengajuan verifikasi vendor Anda memerlukan perbaikan. Silakan periksa catatan dari admin dan unggah ulang berkas.',
                'rejected' => 'Pendaftaran vendor Anda belum disetujui oleh admin. Silakan periksa alasan penolakan di dashboard atau hubungi admin.',
                default => 'Akun vendor Anda belum aktif.',
            };

            return redirect()->route('vendor.verification')->with('warning', $msg);
        }

        return $next($request);
    }
}
