<?php

namespace App\Services\User;

use App\Models\User;

final readonly class UserDashboardService
{
    public function __construct(
        private UserWalletService $walletService,
        private UserTransactionService $transactionService,
        private UserCategoryService $categoryService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function data(User $user): array
    {
        return [
            'categories' => $this->categoryService->active($user),
            'wallets' => $this->walletService->list($user),
            'transactions' => $this->transactionService->allForDashboard($user),
        ];
    }
}
