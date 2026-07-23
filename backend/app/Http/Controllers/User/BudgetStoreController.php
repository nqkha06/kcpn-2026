<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreBudgetRequest;
use Illuminate\Http\RedirectResponse;

class BudgetStoreController extends Controller
{
    public function __invoke(StoreBudgetRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $note = trim((string) ($data['note'] ?? ''));

        $user->budgets()->create([
            'category_id' => (int) $data['category_id'],
            'amount_limit' => (float) $data['amount_limit'],
            'period' => (string) $data['period'],
            'status' => 'active',
            'note' => $note === '' ? null : $note,
        ]);

        return redirect()
            ->route('expense.budgets')
            ->with('success', 'Budget created successfully.');
    }
}
