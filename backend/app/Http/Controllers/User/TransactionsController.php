<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class TransactionsController extends Controller
{
    public function __invoke(): Response
    {
        $user = request()->user();
        $wallets = $user?->wallets()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'currency'])
            ->map(fn ($wallet): array => [
                'id' => (string) $wallet->id,
                'name' => $wallet->name,
                'currency' => $wallet->currency,
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
            ->with([
                'wallet:id,name,currency',
                'category:id,name,color',
            ])
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

        return Inertia::render('User/Transactions', [
            'data' => [
                'categories' => $categories,
                'wallets' => $wallets,
                'transactions' => $transactions,
            ],
        ]);
    }
}
