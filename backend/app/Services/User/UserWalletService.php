<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class UserWalletService
{
    /**
     * @return Collection<int, UserWallet>
     */
    public function list(User $user): Collection
    {
        return $user->wallets()
            ->withPostedTransactionTotals()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $user, array $attributes): UserWallet
    {
        return DB::transaction(function () use ($user, $attributes): UserWallet {
            $isDefault = (bool) ($attributes['is_default'] ?? false);

            if ($user->wallets()->doesntExist()) {
                $isDefault = true;
            }

            if ($isDefault) {
                $user->wallets()->update(['is_default' => false]);
            }

            $wallet = $user->wallets()->create([
                'name' => $attributes['name'],
                'currency' => strtoupper((string) $attributes['currency']),
                'opening_balance' => (float) ($attributes['opening_balance'] ?? 0),
                'is_default' => $isDefault,
            ]);

            return $this->withBalance($wallet);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, UserWallet $wallet, array $attributes): UserWallet
    {
        return DB::transaction(function () use ($user, $wallet, $attributes): UserWallet {
            $isDefault = (bool) ($attributes['is_default'] ?? false);

            if ($isDefault) {
                $user->wallets()
                    ->whereKeyNot($wallet->id)
                    ->update(['is_default' => false]);
            }

            if (! $isDefault && $wallet->is_default) {
                $isDefault = $user->wallets()
                    ->whereKeyNot($wallet->id)
                    ->where('is_default', true)
                    ->doesntExist();
            }

            $wallet->update([
                'name' => $attributes['name'],
                'currency' => strtoupper((string) $attributes['currency']),
                'opening_balance' => (float) ($attributes['opening_balance'] ?? 0),
                'is_default' => $isDefault,
            ]);

            return $this->withBalance($wallet);
        });
    }

    public function delete(User $user, UserWallet $wallet): void
    {
        DB::transaction(function () use ($user, $wallet): void {
            $wasDefault = $wallet->is_default;

            $wallet->delete();

            if ($wasDefault) {
                $user->wallets()
                    ->oldest('id')
                    ->first()
                    ?->update(['is_default' => true]);
            }
        });
    }

    private function withBalance(UserWallet $wallet): UserWallet
    {
        return UserWallet::query()
            ->withPostedTransactionTotals()
            ->findOrFail($wallet->id);
    }
}
