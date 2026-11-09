<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'placement_key', 'name', 'badge', 'badge_bg_color', 'badge_text_color',
        'description', 'price_basic', 'days_basic', 'price_pro', 'days_pro',
        'price_business', 'days_business', 'price_per_day', 'min_days_custom', 'max_days_custom',
        'canvas_title', 'canvas_guideline', 'canvas_ratio', 'canvas_orientation', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price_basic' => 'decimal:2',
        'price_pro' => 'decimal:2',
        'price_business' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'days_basic' => 'integer',
        'days_pro' => 'integer',
        'days_business' => 'integer',
        'min_days_custom' => 'integer',
        'max_days_custom' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get price for a specific package tier ('basic', 'pro', 'business', 'custom')
     */
    public function getTier(string $package, int $customDays = 1): array
    {
        if ($package === 'custom') {
            return $this->getCustomTier($customDays);
        }

        return match ($package) {
            'pro' => ['price' => (float) $this->price_pro, 'days' => (int) $this->days_pro],
            'business' => ['price' => (float) $this->price_business, 'days' => (int) $this->days_business],
            default => ['price' => (float) $this->price_basic, 'days' => (int) $this->days_basic],
        };
    }

    /**
     * Calculate custom days pricing
     */
    public function getCustomTier(int $days): array
    {
        $minDays = $this->min_days_custom ?: 1;
        $maxDays = $this->max_days_custom ?: 90;
        $days = max($minDays, min($maxDays, $days));
        $pricePerDay = (float) ($this->price_per_day ?: 15000);

        return [
            'price' => $pricePerDay * $days,
            'days' => $days,
            'price_per_day' => $pricePerDay,
        ];
    }
}
