<?php

namespace App\Services\Admin;

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class AdminBudgetService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Budget>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Budget::query()->with([
            'user:id,name,email',
            'category:id,name,color',
        ]);
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('note', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%'))
                    ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery
                        ->where('name', 'like', '%'.$search.'%'));

                if (ctype_digit($search)) {
                    $builder->orWhereKey((int) $search);
                }
            });
        }

        foreach (['period', 'status', 'user_id', 'category_id'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        $budgets = $query
            ->orderBy(
                (string) ($filters['sort'] ?? 'created_at'),
                (string) ($filters['direction'] ?? 'desc'),
            )
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();

        $this->attachSpentAmounts($budgets->getCollection());

        return $budgets;
    }

    public function find(Budget $budget): Budget
    {
        $budget->load(['user:id,name,email', 'category:id,name,color']);
        $this->attachSpentAmounts(collect([$budget]));

        return $budget;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Budget
    {
        $budget = Budget::query()->create($this->payload($attributes));

        return $this->find($budget);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Budget $budget, array $attributes): Budget
    {
        $budget->update($this->payload($attributes));

        return $this->find($budget);
    }

    public function delete(Budget $budget): void
    {
        $budget->delete();
    }

    /** @return array<string, mixed> */
    public function options(): array
    {
        return [
            'users' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map->only(['id', 'name', 'email'])
                ->values(),
            'categories' => Category::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'user_id', 'name', 'color'])
                ->map->only(['id', 'user_id', 'name', 'color'])
                ->values(),
            'periods' => ['monthly', 'yearly'],
            'statuses' => ['active', 'inactive'],
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function payload(array $attributes): array
    {
        $note = trim((string) ($attributes['note'] ?? ''));

        return [
            'user_id' => (int) $attributes['user_id'],
            'category_id' => (int) $attributes['category_id'],
            'amount_limit' => (float) $attributes['amount_limit'],
            'period' => strtolower((string) $attributes['period']),
            'status' => strtolower((string) $attributes['status']),
            'note' => $note === '' ? null : $note,
        ];
    }

    /** @param Collection<int, Budget> $budgets */
    private function attachSpentAmounts(Collection $budgets): void
    {
        if ($budgets->isEmpty()) {
            return;
        }

        $now = now();
        $rangesByPeriod = [
            'monthly' => [
                Carbon::parse($now)->startOfMonth()->toDateString(),
                Carbon::parse($now)->endOfMonth()->toDateString(),
            ],
            'yearly' => [
                Carbon::parse($now)->startOfYear()->toDateString(),
                Carbon::parse($now)->endOfYear()->toDateString(),
            ],
        ];

        foreach ($rangesByPeriod as $period => $range) {
            $subset = $budgets->where('period', $period)->values();

            if ($subset->isEmpty()) {
                continue;
            }

            $spentLookup = ExpenseTransaction::query()
                ->selectRaw('user_id, category_id, SUM(amount) as spent_amount')
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->whereIn('user_id', $subset->pluck('user_id')->unique())
                ->whereIn('category_id', $subset->pluck('category_id')->unique())
                ->whereBetween('transacted_at', $range)
                ->groupBy('user_id', 'category_id')
                ->get()
                ->mapWithKeys(fn ($row): array => [
                    $row->user_id.'-'.$row->category_id => (float) $row->spent_amount,
                ]);

            foreach ($subset as $budget) {
                $budget->setAttribute(
                    'spent',
                    (float) $spentLookup->get($budget->user_id.'-'.$budget->category_id, 0),
                );
            }
        }
    }
}
