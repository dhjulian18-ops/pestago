<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'label', 'description', 'group'];

    /**
     * Get a setting value by key, with optional default
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("platform_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return ($setting !== null && $setting->value !== null) ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key
     */
    public static function setValue(string $key, ?string $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) ($value ?? '')]
        );
        Cache::forget("platform_setting_{$key}");
    }

    /**
     * Alias for setting with description/label/group
     */
    public static function set(string $key, $value, ?string $description = null, ?string $label = null, ?string $group = null): void
    {
        $data = ['value' => (string) ($value ?? '')];
        if ($description !== null) $data['description'] = $description;
        if ($label !== null) $data['label'] = $label;
        if ($group !== null) $data['group'] = $group;

        self::updateOrCreate(['key' => $key], $data);
        Cache::forget("platform_setting_{$key}");
    }

    /**
     * Get tax percentage (cached)
     */
    public static function taxPercentage(): float
    {
        return (float) self::getValue('tax_percentage', 2.5);
    }

    /**
     * Get platform fee percentage (cached)
     */
    public static function platformFeePercentage(): float
    {
        return (float) self::getValue('platform_fee_percentage', 5);
    }

    /**
     * Get escrow auto-release days (cached)
     */
    public static function escrowAutoReleaseDays(): int
    {
        return (int) self::getValue('escrow_auto_release_days', 3);
    }

    /**
     * Foonte WhatsApp Gateway Settings
     */
    public static function foonteToken(): ?string
    {
        $val = self::getValue('foonte_token');
        return !empty($val) ? trim($val) : (env('FOONTE_TOKEN') ?: env('FONNTE_TOKEN'));
    }

    public static function isFoonteActive(): bool
    {
        $val = self::getValue('foonte_status', '1');
        return $val === '1' || $val === 'true' || $val === true;
    }

    /**
     * Midtrans Gateway Settings
     */
    public static function midtransServerKey(): ?string
    {
        $val = self::getValue('midtrans_server_key');
        return !empty($val) ? trim($val) : config('services.midtrans.server_key');
    }

    public static function midtransClientKey(): ?string
    {
        $val = self::getValue('midtrans_client_key');
        return !empty($val) ? trim($val) : config('services.midtrans.client_key');
    }

    public static function midtransMerchantId(): ?string
    {
        $val = self::getValue('midtrans_merchant_id');
        return !empty($val) ? trim($val) : config('services.midtrans.merchant_id');
    }

    public static function isMidtransProduction(): bool
    {
        $val = self::getValue('midtrans_is_production');
        if ($val !== null && $val !== '') {
            return (bool) $val;
        }
        return (bool) config('services.midtrans.is_production', false);
    }
}
