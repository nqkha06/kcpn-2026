<?php

namespace App\Services\Admin;

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Carbon\CarbonImmutable;

final class AdminDashboardService
{
    /** @return array<string, mixed> */
    public function data(): array
    {
        $monthStart = CarbonImmutable::now()->startOfMonth();
        $monthEnd = CarbonImmutable::now()->endOfMonth();
        $trendStart = CarbonImmutable::now()->startOfMonth()->subMonths(5);
        $monthlyIncome = $this->postedTransactionSum('income', $monthStart, $monthEnd);
        $monthlyExpense = $this->postedTransactionSum('expense', $monthStart, $monthEnd);

        return [
            'stats' => [
                'users' => User::query()->count(),
                'wallets' => UserWallet::query()->count(),
                'activeCategories' => Category::query()
                    ->whereNull('user_id')
                    ->where('status', 'active')
                    ->count(),
                'activeBudgets' => Budget::query()->where('status', 'active')->count(),
                'postedIncomeThisMonth' => $monthlyIncome,
                'postedExpenseThisMonth' => $monthlyExpense,
                'netThisMonth' => $monthlyIncome - $monthlyExpense,
                'pendingTransactions' => ExpenseTransaction::query()->where('status', 'pending')->count(),
            ],
            'monthlyFlow' => $this->monthlyFlow($trendStart),
            'topExpenseCategories' => $this->topExpenseCategories(),
            'recentTransactions' => $this->recentTransactions(),
        ];
    }

    private function postedTransactionSum(string $type, CarbonImmutable $from, CarbonImmutable $to): float
    {
        return (float) ExpenseTransaction::query()
            ->where('status', 'posted')
            ->where('type', $type)
            ->whereBetween('transacted_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    /** @return array<int, array<string, float|string>> */
    private function monthlyFlow(CarbonImmutable $from): array
    {
        $transactions = ExpenseTransaction::query()
            ->where('status', 'posted')
            ->whereIn('type', ['income', 'expense'])
            ->whereDate('transacted_at', '>=', $from->toDateString())
            ->get(['type', 'amount', 'transacted_at'])
            ->groupBy(fn (ExpenseTransaction $transaction): string => $transaction->transacted_at->format('Y-m'));

        return collect(range(0, 5))
            ->map(function (int $offset) use ($from, $transactions): array {
                $month = $from->addMonths($offset);
                $monthTransactions = $transactions->get($month->format('Y-m'), collect());
                $income = (float) $monthTransactions->where('type', 'income')->sum('amount');
                $expense = (float) $monthTransactions->where('type', 'expense')->sum('amount');

                return [
                    'month' => $month->format('M Y'),
                    'income' => $income,
                    'expense' => $expense,
                    'net' => $income - $expense,
                ];
            })
            ->values()
            ->all();
    }

    /** @return array<int, array<string, float|int|string|null>> */
    private function topExpenseCategories(): array
    {
        return Category::query()
            ->withSum([
                'expenseTransactions as posted_expense_total' => fn ($query) => $query
                    ->where('status', 'posted')
                    ->where('type', 'expense'),
            ], 'amount')
            ->orderByDesc('posted_expense_total')
            ->limit(5)
            ->get(['id', 'name', 'color'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'color' => $category->color,
                'amount' => (float) ($category->posted_expense_total ?? 0),
            ])
            ->filter(fn (array $category): bool => $category['amount'] > 0)
            ->values()
            ->all();
    }

    /** @return array<int, array<string, float|int|string|null>> */
    private function recentTransactions(): array
    {
        return ExpenseTransaction::query()
            ->with([
                'user:id,name,email',
                'wallet:id,name,currency',
                'category:id,name,color',
            ])
            ->latest('transacted_at')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (ExpenseTransaction $transaction): array => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => (float) $transaction->amount,
                'status' => $transaction->status,
                'transactedAt' => $transaction->transacted_at?->format('Y-m-d'),
                'userName' => $transaction->user?->name,
                'walletName' => $transaction->wallet?->name,
                'currency' => $transaction->wallet?->currency,
                'categoryName' => $transaction->category?->name,
                'categoryColor' => $transaction->category?->color,
            ])
            ->values()
            ->all();
    }
}
