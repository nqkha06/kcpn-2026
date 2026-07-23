<?php

namespace App\Services\User;

use App\Models\ExpenseTransaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class UserTransactionService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<ExpenseTransaction>
     */
    public function paginate(User $user, array $filters): LengthAwarePaginator
    {
        $query = $user->expenseTransactions()
            ->with([
                'wallet:id,user_id,name,currency,opening_balance,is_default',
                'category:id,name,color,description',
            ]);

        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where('note', 'like', '%'.$search.'%');
        }

        foreach (['type', 'status', 'wallet_id', 'category_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('transacted_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('transacted_at', '<=', $filters['date_to']);
        }

        $sort = (string) ($filters['sort'] ?? 'transacted_at');
        $direction = (string) ($filters['direction'] ?? 'desc');

        return $query
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    /**
     * @return Collection<int, ExpenseTransaction>
     */
    public function allForDashboard(User $user): Collection
    {
        return $user->expenseTransactions()
            ->with([
                'wallet:id,user_id,name,currency,opening_balance,is_default',
                'category:id,name,color,description',
            ])
            ->latest('transacted_at')
            ->latest('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $user, array $attributes): ExpenseTransaction
    {
        $labels = collect($attributes['labels'] ?? [])
            ->map(fn (mixed $label): string => trim((string) $label))
            ->filter(fn (string $label): bool => $label !== '')
            ->unique()
            ->values()
            ->all();

        $note = trim((string) ($attributes['note'] ?? ''));

        $transaction = $user->expenseTransactions()->create([
            'wallet_id' => (int) $attributes['wallet_id'],
            'category_id' => isset($attributes['category_id']) && $attributes['category_id'] !== ''
                ? (int) $attributes['category_id']
                : null,
            'type' => (string) $attributes['type'],
            'amount' => (float) $attributes['amount'],
            'transacted_at' => (string) $attributes['transacted_at'],
            'status' => 'posted',
            'note' => $note === '' ? null : $note,
            'labels' => $labels === [] ? null : $labels,
        ]);

        return $transaction->load([
            'wallet:id,user_id,name,currency,opening_balance,is_default',
            'category:id,name,color,description',
        ]);
    }
}
