<?php

namespace App\Services\User;

use App\Models\Budget;
use App\Models\ExpenseTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

final class UserBudgetService
{
    /**
     * @return Collection<int, Budget>
     */
    public function list(User $user): Collection
    {
        $budgets = $user->budgets()
            ->with('category:id,name,color,description')
            ->where('status', 'active')
            ->orderBy('period')
            ->orderByDesc('id')
            ->get();

        $monthlySpent = $this->spentByCategory(
            $user,
            $budgets->where('period', 'monthly')->pluck('category_id')->all(),
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        );
        $yearlySpent = $this->spentByCategory(
            $user,
            $budgets->where('period', 'yearly')->pluck('category_id')->all(),
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear(),
        );

        return $budgets->each(function (Budget $budget) use ($monthlySpent, $yearlySpent): void {
            $spent = $budget->period === 'yearly' ? $yearlySpent : $monthlySpent;
            $budget->setAttribute('spent', (float) ($spent[$budget->category_id] ?? 0));
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $user, array $attributes): Budget
    {
        $note = trim((string) ($attributes['note'] ?? ''));

        $budget = $user->budgets()->create([
            'category_id' => (int) $attributes['category_id'],
            'amount_limit' => (float) $attributes['amount_limit'],
            'period' => (string) $attributes['period'],
            'status' => 'active',
            'note' => $note === '' ? null : $note,
        ]);

        $budget->setAttribute('spent', 0);

        return $budget->load('category:id,name,color,description');
    }

    /**
     * @param  array<int, int|string>  $categoryIds
     * @return array<int|string, float>
     */
    private function spentByCategory(User $user, array $categoryIds, Carbon $from, Carbon $to): array
    {
        if ($categoryIds === []) {
            return [];
        }

        return ExpenseTransaction::query()
            ->selectRaw('category_id, SUM(amount) as spent_amount')
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('status', 'posted')
            ->whereIn('category_id', $categoryIds)
            ->whereBetween('transacted_at', [$from->toDateString(), $to->toDateString()])
            ->groupBy('category_id')
            ->pluck('spent_amount', 'category_id')
            ->map(fn (mixed $value): float => (float) $value)
            ->all();
    }
}
