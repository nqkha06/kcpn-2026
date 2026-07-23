<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class BudgetsController extends Controller
{
    public function __invoke(): Response
    {
        $user = request()->user();

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

        $budgets = $user?->budgets()
            ->with('category:id,name,color')
            ->where('status', 'active')
            ->orderBy('period')
            ->orderByDesc('id')
            ->get() ?? collect();

        $monthlyCategoryIds = $budgets
            ->where('period', 'monthly')
            ->pluck('category_id')
            ->unique()
            ->values();
        $yearlyCategoryIds = $budgets
            ->where('period', 'yearly')
            ->pluck('category_id')
            ->unique()
            ->values();

        $monthlySpent = $user
            ? ExpenseTransaction::query()
                ->selectRaw('category_id, SUM(amount) as spent_amount')
                ->where('user_id', $user->id)
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->whereIn('category_id', $monthlyCategoryIds)
                ->whereBetween('transacted_at', [
                    Carbon::now()->startOfMonth()->toDateString(),
                    Carbon::now()->endOfMonth()->toDateString(),
                ])
                ->groupBy('category_id')
                ->pluck('spent_amount', 'category_id')
                ->map(fn ($value): float => (float) $value)
                ->all()
            : [];

        $yearlySpent = $user
            ? ExpenseTransaction::query()
                ->selectRaw('category_id, SUM(amount) as spent_amount')
                ->where('user_id', $user->id)
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->whereIn('category_id', $yearlyCategoryIds)
                ->whereBetween('transacted_at', [
                    Carbon::now()->startOfYear()->toDateString(),
                    Carbon::now()->endOfYear()->toDateString(),
                ])
                ->groupBy('category_id')
                ->pluck('spent_amount', 'category_id')
                ->map(fn ($value): float => (float) $value)
                ->all()
            : [];

        $mappedBudgets = $budgets
            ->map(function ($budget) use ($monthlySpent, $yearlySpent): array {
                $spentLookup = $budget->period === 'yearly' ? $yearlySpent : $monthlySpent;

                return [
                    'id' => (string) $budget->id,
                    'categoryId' => (string) $budget->category_id,
                    'limit' => (float) $budget->amount_limit,
                    'spent' => (float) ($spentLookup[$budget->category_id] ?? 0),
                    'period' => $budget->period,
                ];
            })
            ->values()
            ->all();

        return Inertia::render('User/Budgets', [
            'data' => [
                'categories' => $categories,
                'budgets' => $mappedBudgets,
            ],
        ]);
    }
}
