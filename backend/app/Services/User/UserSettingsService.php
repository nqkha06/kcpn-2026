<?php

namespace App\Services\User;

use App\Models\User;

final class UserSettingsService
{
    /**
     * @return array<string, mixed>
     */
    public function data(User $user): array
    {
        return [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'preferences' => [
                'currency' => $user->getMeta('currency', 'VND') ?? 'VND',
            ],
            'currency_options' => [
                ['code' => 'VND', 'label' => 'VND (₫)'],
                ['code' => 'USD', 'label' => 'USD ($)'],
                ['code' => 'EUR', 'label' => 'EUR (€)'],
                ['code' => 'GBP', 'label' => 'GBP (£)'],
            ],
        ];
    }

    /**
     * @param  array<string, string>  $attributes
     */
    public function updateProfile(User $user, array $attributes): User
    {
        $user->fill($attributes);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user->refresh();
    }

    public function updateCurrency(User $user, string $currency): void
    {
        $user->setMeta('currency', strtoupper($currency));
    }
}
