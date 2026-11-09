<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'code',
        'action',
        'user_id',
        'payload',
        'attempts',
        'is_used',
        'expires_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Generate a new 6-digit OTP code for a given phone and action
     */
    public static function generate(string $phone, string $action, ?array $payload = null, ?int $userId = null, int $validMinutes = 10): self
    {
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);

        // Invalidate older unused OTPs for this phone & action
        self::where('phone', $normalizedPhone)
            ->where('action', $action)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate cryptographically secure 6-digit number
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'phone' => $normalizedPhone,
            'code' => $code,
            'action' => $action,
            'user_id' => $userId,
            'payload' => $payload,
            'attempts' => 0,
            'is_used' => false,
            'expires_at' => Carbon::now()->addMinutes($validMinutes),
        ]);
    }

    /**
     * Verify OTP code with attempt limiting and expiry checks
     */
    public static function verify(string $phone, string $code, string $action): array
    {
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);
        $cleanCode = trim($code);

        $record = self::where('phone', $normalizedPhone)
            ->where('action', $action)
            ->where('is_used', false)
            ->orderBy('id', 'desc')
            ->first();

        if (!$record) {
            return [
                'success' => false,
                'message' => 'Kode OTP tidak ditemukan atau sudah kadaluarsa. Silakan minta kode baru.',
            ];
        }

        if ($record->isExpired()) {
            $record->update(['is_used' => true]);
            return [
                'success' => false,
                'message' => 'Kode OTP telah kadaluarsa. Silakan klik kirim ulang kode.',
            ];
        }

        if ($record->attempts >= 5) {
            $record->update(['is_used' => true]);
            return [
                'success' => false,
                'message' => 'Terlalu banyak percobaan salah. Kode OTP ini dibatalkan. Silakan minta kode baru.',
            ];
        }

        if ($record->code !== $cleanCode) {
            $record->increment('attempts');
            $remaining = 5 - $record->attempts;
            return [
                'success' => false,
                'message' => "Kode OTP salah. Sisa percobaan: {$remaining} kali.",
            ];
        }

        // Mark as used
        $record->update(['is_used' => true]);

        return [
            'success' => true,
            'otp' => $record,
            'payload' => $record->payload,
            'user_id' => $record->user_id,
        ];
    }
}

