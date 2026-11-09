<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FoonteService
{
    /**
     * Get active Foonte token from database or fallback to .env
     */
    public static function getToken(): ?string
    {
        $token = PlatformSetting::getValue('foonte_token');
        if (!empty($token)) {
            return trim($token);
        }
        return env('FOONTE_TOKEN') ? trim(env('FOONTE_TOKEN')) : null;
    }

    /**
     * Check if Foonte WhatsApp Gateway is active
     */
    public static function isEnabled(): bool
    {
        $status = PlatformSetting::getValue('foonte_status', '1');
        return $status === '1' || $status === true || $status === 1;
    }

    /**
     * Format phone number to standard international / Indonesian format
     */
    public static function formatPhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 08..., convert to 628...
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Send OTP verification message via Foonte WhatsApp API
     */
    public static function sendOtp(string $phone, string $code, string $purpose = 'pendaftaran akun'): array
    {
        $token = self::getToken();
        $formattedPhone = self::formatPhone($phone);

        $message = "*PestaGo — Kode Verifikasi WhatsApp (OTP)*\n\n"
            . "Kode OTP Anda untuk {$purpose}:\n"
            . "👉 *{$code}* 👈\n\n"
            . "• Kode ini berlaku selama *10 menit*.\n"
            . "• *JANGAN* bagikan kode ini kepada siapa pun demi keamanan akun Anda.\n\n"
            . "_Pesan otomatis dari platform PestaGo_";

        if (empty($token) || !self::isEnabled()) {
            Log::warning("Foonte is not configured or disabled. Simulated OTP [{$code}] for [{$formattedPhone}].");
            return [
                'success' => false,
                'simulated' => true,
                'code' => $code,
                'message' => 'Token WhatsApp Foonte belum diisi di Pengaturan Admin. Hubungi administrator.',
            ];
        }

        return self::sendMessage($formattedPhone, $message);
    }

    /**
     * Send generic message via Foonte API
     */
    public static function sendMessage(string $phone, string $message): array
    {
        $token = self::getToken();
        $formattedPhone = self::formatPhone($phone);

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token Foonte belum dikonfigurasi di Pengaturan Admin.',
            ];
        }

        try {
            $response = Http::asForm()->withHeaders([
                'Authorization' => $token,
            ])->timeout(20)->post('https://api.fonnte.com/send', [
                'target' => $formattedPhone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            $json = $response->json();

            if ($response->successful() && isset($json['status']) && $json['status'] == true) {
                Log::info("Fonnte message sent successfully to {$formattedPhone}", ['response' => $json]);
                return [
                    'success' => true,
                    'message' => 'Pesan WhatsApp berhasil dikirim.',
                    'data' => $json,
                ];
            }

            $reason = $json['reason'] ?? $json['detail'] ?? $json['message'] ?? 'Gagal mengirim pesan melalui Fonnte.';
            Log::error("Fonnte error response for {$formattedPhone}: {$reason}", ['response' => $json]);

            return [
                'success' => false,
                'message' => 'Fonnte Error: ' . $reason,
                'data' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error("Fonnte HTTP Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server WhatsApp Fonnte: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test connection to Foonte with a test message
     */
    public static function testConnection(string $phone): array
    {
        $token = self::getToken();
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token Foonte masih kosong. Silakan masukkan token terlebih dahulu.',
            ];
        }

        $testMsg = "*PestaGo System Test*\n\n"
            . "Koneksi WhatsApp Gateway Foonte berhasil terhubung dengan PestaGo! 🚀\n"
            . "Waktu uji coba: " . now()->translatedFormat('d M Y, H:i:s') . "\n\n"
            . "_Pesan uji coba dari Admin PestaGo._";

        return self::sendMessage($phone, $testMsg);
    }
}

