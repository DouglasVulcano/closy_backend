<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'price',
        'stripe_price_id',
        'role',
        'active',
        'description',
        'features',
        'trial_days',
        'monthly_leads_limit',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var list<string>
     */
    protected $hidden = [
        'stripe_price_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'active' => 'boolean',
            'features' => 'array',
        ];
    }

    /**
     * Get users subscribed to this plan
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(\Laravel\Cashier\Subscription::class, 'stripe_price', 'stripe_price_id');
    }

    /**
     * Scope to get only active plans
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get plan by Stripe price ID
     */
    public static function findByStripePriceId(string $stripePriceId): ?self
    {
        return static::where('stripe_price_id', $stripePriceId)->first();
    }
}
