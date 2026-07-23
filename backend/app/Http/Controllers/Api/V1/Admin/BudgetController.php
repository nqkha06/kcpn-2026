<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BudgetRequest;
use App\Http\Requests\Api\V1\Admin\BudgetIndexRequest;
use App\Http\Resources\Api\V1\Admin\BudgetResource;
use App\Http\Responses\ApiResponse;
use App\Models\Budget;
use App\Services\Admin\AdminBudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class BudgetController extends Controller
{
    public function __construct(
        private readonly AdminBudgetService $budgetService,
    ) {}

    public function index(BudgetIndexRequest $request): JsonResponse
    {
        $budgets = $this->budgetService->paginate($request->validated());

        return ApiResponse::paginated(
            BudgetResource::collection($budgets->getCollection()),
            $budgets,
        );
    }

    public function options(): JsonResponse
    {
        Gate::authorize('viewAny', Budget::class);

        return ApiResponse::success($this->budgetService->options());
    }

    public function store(BudgetRequest $request): JsonResponse
    {
        $budget = $this->budgetService->create($request->validated());

        return ApiResponse::success(
            new BudgetResource($budget),
            'Budget created successfully',
            201,
        );
    }

    public function show(Budget $budget): JsonResponse
    {
        Gate::authorize('view', $budget);

        return ApiResponse::success(new BudgetResource($this->budgetService->find($budget)));
    }

    public function update(BudgetRequest $request, Budget $budget): JsonResponse
    {
        $budget = $this->budgetService->update($budget, $request->validated());

        return ApiResponse::success(new BudgetResource($budget), 'Budget updated successfully');
    }

    public function destroy(Budget $budget): JsonResponse
    {
        Gate::authorize('delete', $budget);
        $this->budgetService->delete($budget);

        return ApiResponse::success(message: 'Budget deleted successfully');
    }
}
