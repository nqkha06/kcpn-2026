<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'password',
        'referred_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(UserWallet::class);
    }

    public function expenseTransactions(): HasMany
    {
        return $this->hasMany(ExpenseTransaction::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function metas(): HasMany
    {
        return $this->hasMany(UserMeta::class);
    }

    public function getMeta(string $key, ?string $default = null): ?string
    {
        $meta = $this->metas()
            ->where('meta_key', $key)
            ->value('meta_value');

        return $meta ?? $default;
    }

    public function setMeta(string $key, ?string $value): void
    {
        $this->metas()->updateOrCreate(
            ['meta_key' => $key],
            ['meta_value' => $value],
        );
    }
}
