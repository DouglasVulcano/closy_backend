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
        'profile_picture',
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
        'stripe_id',
        'pm_type',
        'pm_last_four'
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

    public function getCelularAttribute(): ?string
    {
        $celular = $this->attributes['celular'] ?? null;
        
        if (!$celular) {
            return null;
        }
        
        // Remove qualquer formatação existente
        $celular = preg_replace('/\D/', '', $celular);
        
        // Verifica se tem 11 dígitos (formato brasileiro com DDD)
        if (strlen($celular) === 11) {
            // Formato: (11) 91338-0413
            return sprintf('(%s) %s%s-%s',
                substr($celular, 0, 2),  // DDD
                substr($celular, 2, 1),  // 9 do celular
                substr($celular, 3, 4),  // primeiros 4 dígitos
                substr($celular, 7, 4)   // últimos 4 dígitos
            );
        }
        
        // Verifica se tem 10 dígitos (formato antigo sem o 9)
        if (strlen($celular) === 10) {
            // Formato: (11) 1338-0413
            return sprintf('(%s) %s-%s',
                substr($celular, 0, 2),  // DDD
                substr($celular, 2, 4),  // primeiros 4 dígitos
                substr($celular, 6, 4)   // últimos 4 dígitos
            );
        }
        
        // Se não tem o formato esperado, retorna como está
        return $celular;
    }
}
