<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreExpenseTransactionRequest;
use Illuminate\Http\RedirectResponse;

class TransactionStoreController extends Controller
{
    public function __invoke(StoreExpenseTransactionRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $rawLabels = $data['labels'] ?? null;

        if (is_string($rawLabels)) {
            $rawLabels = explode(',', $rawLabels);
        }

        $labels = collect(is_array($rawLabels) ? $rawLabels : [])
            ->map(fn (mixed $label): string => trim((string) $label))
            ->filter(fn (string $label): bool => $label !== '')
            ->unique()
            ->values()
            ->all();

        $note = trim((string) ($data['note'] ?? ''));

        $user->expenseTransactions()->create([
            'wallet_id' => (int) $data['wallet_id'],
            'category_id' => isset($data['category_id']) && $data['category_id'] !== ''
                ? (int) $data['category_id']
                : null,
            'type' => (string) $data['type'],
            'amount' => (float) $data['amount'],
            'transacted_at' => (string) $data['transacted_at'],
            'status' => 'posted',
            'note' => $note === '' ? null : $note,
            'labels' => $labels === [] ? null : $labels,
        ]);

        return redirect()
            ->route('expense.transactions')
            ->with('success', 'Transaction created successfully.');
    }
}
