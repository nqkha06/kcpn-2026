<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class AdminTransactionService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<ExpenseTransaction>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = ExpenseTransaction::query()->with($this->relations());
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('note', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn (Builder $userQuery) => $userQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%'))
                    ->orWhereHas('wallet', fn (Builder $walletQuery) => $walletQuery
                        ->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery
                        ->where('name', 'like', '%'.$search.'%'));

                if (ctype_digit($search)) {
                    $builder->orWhereKey((int) $search);
                }
            });
        }

        foreach (['type', 'status', 'user_id', 'wallet_id', 'category_id'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('transacted_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('transacted_at', '<=', $filters['to_date']);
        }

        return $query
            ->orderBy(
                (string) ($filters['sort'] ?? 'transacted_at'),
                (string) ($filters['direction'] ?? 'desc'),
            )
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function find(ExpenseTransaction $transaction): ExpenseTransaction
    {
        return $transaction->load($this->relations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): ExpenseTransaction
    {
        $transaction = ExpenseTransaction::query()->create($this->payload($attributes));

        return $this->find($transaction);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(ExpenseTransaction $transaction, array $attributes): ExpenseTransaction
    {
        $transaction->update($this->payload($attributes));

        return $this->find($transaction);
    }

    public function delete(ExpenseTransaction $transaction): void
    {
        $transaction->delete();
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
            'wallets' => UserWallet::query()
                ->with('user:id,name')
                ->orderBy('name')
                ->get(['id', 'user_id', 'name', 'currency'])
                ->map(fn (UserWallet $wallet): array => [
                    'id' => $wallet->id,
                    'user_id' => $wallet->user_id,
                    'name' => $wallet->name,
                    'currency' => $wallet->currency,
                    'user_name' => $wallet->user?->name,
                ])
                ->values(),
            'categories' => Category::query()
                ->orderBy('name')
                ->get(['id', 'user_id', 'name', 'color', 'status'])
                ->map->only(['id', 'user_id', 'name', 'color', 'status'])
                ->values(),
            'types' => ['income', 'expense'],
            'statuses' => ['posted', 'pending', 'cancelled'],
        ];
    }

    /** @return array<int, string> */
    private function relations(): array
    {
        return [
            'user:id,name,email',
            'wallet:id,user_id,name,currency',
            'category:id,name,color',
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function payload(array $attributes): array
    {
        $rawLabels = $attributes['labels'] ?? null;

        if (is_string($rawLabels)) {
            $rawLabels = explode(',', $rawLabels);
        }

        $labels = collect(is_array($rawLabels) ? $rawLabels : [])
            ->map(fn (mixed $label): string => trim((string) $label))
            ->filter(fn (string $label): bool => $label !== '')
            ->unique()
            ->values()
            ->all();
        $note = trim((string) ($attributes['note'] ?? ''));

        return [
            'user_id' => (int) $attributes['user_id'],
            'wallet_id' => (int) $attributes['wallet_id'],
            'category_id' => isset($attributes['category_id']) && $attributes['category_id'] !== ''
                ? (int) $attributes['category_id']
                : null,
            'type' => strtolower((string) $attributes['type']),
            'amount' => (float) $attributes['amount'],
            'transacted_at' => (string) $attributes['transacted_at'],
            'status' => strtolower((string) $attributes['status']),
            'note' => $note === '' ? null : $note,
            'labels' => $labels === [] ? null : $labels,
        ];
    }
}
