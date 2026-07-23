<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseTransactionRequest as TransactionRequest;
use App\Models\Category;
use App\Models\ExpenseTransaction as Transaction;
use App\Models\User;
use App\Models\UserWallet;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    protected $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): Response
    {
        $sortBy = (string) ($request->get('sort_by') ?? $request->get('sort') ?? 'transacted_at');
        $sortDirection = (string) ($request->get('sort_direction') ?? $request->get('dir') ?? 'desc');

        $perPage = (int) $request->get('per_page', 10);

        $transactions = $this->service->paginate([
            'search' => $request->get('search'),
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'user_id' => $request->get('user_id'),
            'wallet_id' => $request->get('wallet_id'),
            'category_id' => $request->get('category_id'),
            'transacted_at_from' => $request->get('from_date'),
            'transacted_at_to' => $request->get('to_date'),
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'per_page' => $perPage,
        ]);

        return Inertia::render('Admin/transactions/list', [
            'transactions' => $transactions->getCollection()->map(function (Transaction $transaction): array {
                return $this->mapTransaction($transaction);
            })->values()->all(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
            'filters' => [
                'search' => $request->input('search'),
                'type' => $request->input('type'),
                'status' => $request->input('status'),
                'user_id' => $request->input('user_id'),
                'wallet_id' => $request->input('wallet_id'),
                'category_id' => $request->input('category_id'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'per_page' => $perPage,
            ],
            'formOptions' => $this->buildFormOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/transactions/add', [
            'formOptions' => $this->buildFormOptions(),
        ]);
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $this->service->create($this->normalizePayload($request->validated()));

        return redirect()
            ->route('admin.transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    public function show(Transaction $transaction): RedirectResponse
    {
        return redirect()->route('admin.transactions.edit', $transaction);
    }

    public function edit(Transaction $transaction): Response
    {
        $transaction->load([
            'user:id,name,email',
            'wallet:id,user_id,name,currency',
            'category:id,name,color',
        ]);

        return Inertia::render('Admin/transactions/edit', [
            'transaction' => $this->mapTransaction($transaction),
            'formOptions' => $this->buildFormOptions(),
        ]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->service->update($transaction->id, $this->normalizePayload($request->validated()));

        return redirect()
            ->route('admin.transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return redirect()
            ->route('admin.transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    private function buildFormOptions(): array
    {
        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function (User $user): array {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            })
            ->values()
            ->all();

        $wallets = UserWallet::query()
            ->with('user:id,name')
            ->orderBy('name')
            ->get(['id', 'user_id', 'name', 'currency'])
            ->map(function (UserWallet $wallet): array {
                return [
                    'id' => $wallet->id,
                    'user_id' => $wallet->user_id,
                    'name' => $wallet->name,
                    'currency' => $wallet->currency,
                    'user_name' => $wallet->user?->name,
                ];
            })
            ->values()
            ->all();

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'status'])
            ->map(function (Category $category): array {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color,
                    'status' => $category->status,
                ];
            })
            ->values()
            ->all();

        return [
            'users' => $users,
            'wallets' => $wallets,
            'categories' => $categories,
            'types' => ['income', 'expense'],
            'statuses' => ['posted', 'pending', 'cancelled'],
        ];
    }

    private function normalizePayload(array $validated): array
    {
        $rawLabels = $validated['labels'] ?? null;

        if (is_string($rawLabels)) {
            $rawLabels = explode(',', $rawLabels);
        }

        $labels = collect(is_array($rawLabels) ? $rawLabels : [])
            ->map(fn (mixed $label): string => trim((string) $label))
            ->filter(fn (string $label): bool => $label !== '')
            ->unique()
            ->values()
            ->all();

        $note = trim((string) ($validated['note'] ?? ''));

        return [
            'user_id' => (int) $validated['user_id'],
            'wallet_id' => (int) $validated['wallet_id'],
            'category_id' => isset($validated['category_id']) && $validated['category_id'] !== ''
                ? (int) $validated['category_id']
                : null,
            'type' => strtolower((string) $validated['type']),
            'amount' => (float) $validated['amount'],
            'transacted_at' => (string) $validated['transacted_at'],
            'status' => strtolower((string) $validated['status']),
            'note' => $note === '' ? null : $note,
            'labels' => $labels === [] ? null : $labels,
        ];
    }

    private function mapTransaction(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'wallet_id' => $transaction->wallet_id,
            'category_id' => $transaction->category_id,
            'type' => $transaction->type,
            'amount' => (float) $transaction->amount,
            'status' => $transaction->status,
            'note' => $transaction->note,
            'labels' => $transaction->labels ?? [],
            'transacted_at' => $transaction->transacted_at?->format('Y-m-d'),
            'created_at' => $transaction->created_at?->toISOString(),
            'user' => $transaction->user ? [
                'id' => $transaction->user->id,
                'name' => $transaction->user->name,
                'email' => $transaction->user->email,
            ] : null,
            'wallet' => $transaction->wallet ? [
                'id' => $transaction->wallet->id,
                'name' => $transaction->wallet->name,
                'currency' => $transaction->wallet->currency,
            ] : null,
            'category' => $transaction->category ? [
                'id' => $transaction->category->id,
                'name' => $transaction->category->name,
                'color' => $transaction->category->color,
            ] : null,
        ];
    }
}
