<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreWalletRequest;
use App\Http\Requests\User\UpdateWalletRequest;
use App\Models\Category;
use App\Models\UserWallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WalletsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $wallets = $user?->wallets()
            ->withPostedTransactionTotals()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(fn ($wallet): array => [
                'id' => (string) $wallet->id,
                'name' => $wallet->name,
                'balance' => $wallet->currentBalance(),
                'openingBalance' => (float) $wallet->opening_balance,
                'type' => 'cash',
                'currency' => $wallet->currency,
                'isDefault' => (bool) $wallet->is_default,
            ])
            ->values()
            ->all() ?? [];

        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'color'])
            ->map(fn (Category $category): array => [
                'id' => (string) $category->id,
                'name' => $category->name,
                'color' => $category->color,
            ])
            ->values()
            ->all();

        $transactions = $user?->expenseTransactions()
            ->latest('transacted_at')
            ->latest('id')
            ->get()
            ->map(fn ($transaction): array => [
                'id' => (string) $transaction->id,
                'amount' => (float) $transaction->amount,
                'type' => $transaction->type,
                'categoryId' => $transaction->category_id ? (string) $transaction->category_id : '',
                'walletId' => (string) $transaction->wallet_id,
                'date' => $transaction->transacted_at?->format('Y-m-d') ?? $transaction->created_at?->format('Y-m-d'),
                'note' => $transaction->note,
                'labels' => is_array($transaction->labels) ? $transaction->labels : [],
                'status' => $transaction->status,
            ])
            ->values()
            ->all() ?? [];

        return Inertia::render('User/Wallets', [
            'data' => [
                'categories' => $categories,
                'wallets' => $wallets,
                'transactions' => $transactions,
            ],
        ]);
    }

    public function store(StoreWalletRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();
        $isDefault = (bool) ($data['is_default'] ?? false);

        if ($user->wallets()->doesntExist()) {
            $isDefault = true;
        }

        if ($isDefault) {
            $user->wallets()->update(['is_default' => false]);
        }

        $user->wallets()->create([
            'name' => $data['name'],
            'currency' => strtoupper($data['currency']),
            'opening_balance' => (float) ($data['opening_balance'] ?? 0),
            'is_default' => $isDefault,
        ]);

        return redirect()->route('expense.wallets')
            ->with('success', 'Wallet created successfully.');
    }

    public function update(UpdateWalletRequest $request, UserWallet $wallet): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $isDefault = (bool) ($data['is_default'] ?? false);

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
            'name' => $data['name'],
            'currency' => strtoupper($data['currency']),
            'opening_balance' => (float) ($data['opening_balance'] ?? 0),
            'is_default' => $isDefault,
        ]);

        return redirect()->route('expense.wallets')
            ->with('success', 'Wallet updated successfully.');
    }

    public function destroy(Request $request, UserWallet $wallet): RedirectResponse
    {
        abort_unless($request->user()?->id === $wallet->user_id, 403);

        $wasDefault = $wallet->is_default;

        $wallet->delete();

        if ($wasDefault) {
            $request->user()
                ->wallets()
                ->oldest('id')
                ->first()
                ?->update(['is_default' => true]);
        }

        return redirect()->route('expense.wallets')
            ->with('success', 'Wallet deleted successfully.');
    }
}
