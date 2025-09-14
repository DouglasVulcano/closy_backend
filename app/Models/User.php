<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable;

/**
 * @method \Laravel\Cashier\Subscription|null subscription(string $name = 'default')
 * @method bool subscribed(string $name = 'default', string $price = null)
 * @method \Laravel\Cashier\SubscriptionBuilder newSubscription(string $name, string $price)
 * @method \Stripe\BillingPortal\Session redirectToBillingPortal(string $returnUrl = null)
 */
class User extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'celular',
        'role',
        'trial_ends_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is USER role
     */
    public function isUser(): bool
    {
        return $this->hasRole('USER');
    }

    /**
     * Check if user is STARTER role
     */
    public function isStarter(): bool
    {
        return $this->hasRole('STARTER');
    }

    /**
     * Check if user is PRO role
     */
    public function isPro(): bool
    {
        return $this->hasRole('PRO');
    }

    /**
     * Check if user has premium access (STARTER or PRO)
     */
    public function hasPremiumAccess(): bool
    {
        return in_array($this->role, ['STARTER', 'PRO']);
    }
}
