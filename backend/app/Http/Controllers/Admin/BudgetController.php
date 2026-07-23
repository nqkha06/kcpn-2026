<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BudgetRequest;
use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    protected $service;

    public function __construct(BudgetService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): Response
    {
        $sortBy = (string) $request->get('sort_by', 'created_at');
        $sortDirection = (string) $request->get('sort_direction', 'desc');
        $perPage = (int) $request->get('per_page', 10);

        $budgets = $this->service->paginate([
            'search' => $request->get('search'),
            'period' => $request->get('period'),
            'status' => $request->get('status'),
            'user_id' => $request->get('user_id'),
            'category_id' => $request->get('category_id'),
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'per_page' => $perPage,
        ]);

        $spentByBudget = $this->calculateSpentForBudgets($budgets->getCollection());

        return Inertia::render('Admin/budgets/list', [
            'budgets' => $budgets->getCollection()->map(function (Budget $budget) use ($spentByBudget): array {
                return $this->mapBudget($budget, $spentByBudget[(string) $budget->id] ?? 0.0);
            })->values()->all(),
            'pagination' => [
                'current_page' => $budgets->currentPage(),
                'last_page' => $budgets->lastPage(),
                'per_page' => $budgets->perPage(),
                'total' => $budgets->total(),
                'from' => $budgets->firstItem(),
                'to' => $budgets->lastItem(),
            ],
            'filters' => [
                'search' => $request->input('search'),
                'period' => $request->input('period'),
                'status' => $request->input('status'),
                'user_id' => $request->input('user_id'),
                'category_id' => $request->input('category_id'),
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
                'per_page' => $perPage,
            ],
            'formOptions' => $this->buildFormOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/budgets/add', [
            'formOptions' => $this->buildFormOptions(),
        ]);
    }

    public function store(BudgetRequest $request): RedirectResponse
    {
        $this->service->create($this->normalizePayload($request->validated()));

        return redirect()
            ->route('admin.budgets.index')
            ->with('success', 'Budget created successfully.');
    }

    public function show(Budget $budget): RedirectResponse
    {
        return redirect()->route('admin.budgets.edit', $budget);
    }

    public function edit(Budget $budget): Response
    {
        $budget->load([
            'user:id,name,email',
            'category:id,name,color',
        ]);

        $spentByBudget = $this->calculateSpentForBudgets(collect([$budget]));

        return Inertia::render('Admin/budgets/edit', [
            'budget' => $this->mapBudget($budget, $spentByBudget[(string) $budget->id] ?? 0.0),
            'formOptions' => $this->buildFormOptions(),
        ]);
    }

    public function update(BudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->service->update($budget->id, $this->normalizePayload($request->validated()));

        return redirect()
            ->route('admin.budgets.index')
            ->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $budget->delete();

        return redirect()
            ->route('admin.budgets.index')
            ->with('success', 'Budget deleted successfully.');
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

        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'color'])
            ->map(function (Category $category): array {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color,
                ];
            })
            ->values()
            ->all();

        return [
            'users' => $users,
            'categories' => $categories,
            'periods' => ['monthly', 'yearly'],
            'statuses' => ['active', 'inactive'],
        ];
    }

    private function normalizePayload(array $validated): array
    {
        $note = trim((string) ($validated['note'] ?? ''));

        return [
            'user_id' => (int) $validated['user_id'],
            'category_id' => (int) $validated['category_id'],
            'amount_limit' => (float) $validated['amount_limit'],
            'period' => strtolower((string) $validated['period']),
            'status' => strtolower((string) $validated['status']),
            'note' => $note === '' ? null : $note,
        ];
    }

    private function mapBudget(Budget $budget, float $spent): array
    {
        return [
            'id' => $budget->id,
            'user_id' => $budget->user_id,
            'category_id' => $budget->category_id,
            'amount_limit' => (float) $budget->amount_limit,
            'spent' => round($spent, 2),
            'period' => $budget->period,
            'status' => $budget->status,
            'note' => $budget->note,
            'created_at' => $budget->created_at?->toISOString(),
            'user' => $budget->user ? [
                'id' => $budget->user->id,
                'name' => $budget->user->name,
                'email' => $budget->user->email,
            ] : null,
            'category' => $budget->category ? [
                'id' => $budget->category->id,
                'name' => $budget->category->name,
                'color' => $budget->category->color,
            ] : null,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Budget>  $budgets
     * @return array<string, float>
     */
    private function calculateSpentForBudgets($budgets): array
    {
        if ($budgets->isEmpty()) {
            return [];
        }

        $now = now();
        $rangesByPeriod = [
            'monthly' => [
                'start' => Carbon::parse($now)->startOfMonth()->toDateString(),
                'end' => Carbon::parse($now)->endOfMonth()->toDateString(),
            ],
            'yearly' => [
                'start' => Carbon::parse($now)->startOfYear()->toDateString(),
                'end' => Carbon::parse($now)->endOfYear()->toDateString(),
            ],
        ];

        $result = [];

        foreach (['monthly', 'yearly'] as $period) {
            $subset = $budgets->filter(fn (Budget $budget): bool => $budget->period === $period)->values();

            if ($subset->isEmpty()) {
                continue;
            }

            $userIds = $subset->pluck('user_id')->unique()->values();
            $categoryIds = $subset->pluck('category_id')->unique()->values();

            $spentRows = ExpenseTransaction::query()
                ->selectRaw('user_id, category_id, SUM(amount) as spent_amount')
                ->where('type', 'expense')
                ->where('status', 'posted')
                ->whereIn('user_id', $userIds)
                ->whereIn('category_id', $categoryIds)
                ->whereBetween('transacted_at', [
                    $rangesByPeriod[$period]['start'],
                    $rangesByPeriod[$period]['end'],
                ])
                ->groupBy('user_id', 'category_id')
                ->get();

            $spentLookup = $spentRows
                ->mapWithKeys(function ($row): array {
                    $key = sprintf('%s-%s', (string) $row->user_id, (string) $row->category_id);

                    return [$key => (float) $row->spent_amount];
                })
                ->all();

            foreach ($subset as $budget) {
                $lookupKey = sprintf('%s-%s', (string) $budget->user_id, (string) $budget->category_id);
                $result[(string) $budget->id] = (float) ($spentLookup[$lookupKey] ?? 0.0);
            }
        }

        return $result;
    }
}
