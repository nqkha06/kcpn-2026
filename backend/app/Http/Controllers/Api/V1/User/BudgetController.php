<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreBudgetRequest;
use App\Http\Resources\Api\V1\BudgetResource;
use App\Http\Responses\ApiResponse;
use App\Models\Budget;
use App\Models\User;
use App\Services\User\UserBudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BudgetController extends Controller
{
    public function __construct(
        private readonly UserBudgetService $budgetService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Budget::class);

        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success(
            BudgetResource::collection($this->budgetService->list($user)),
        );
    }

    public function store(StoreBudgetRequest $request): JsonResponse
    {
        Gate::authorize('create', Budget::class);

        /** @var User $user */
        $user = $request->user();
        $budget = $this->budgetService->create($user, $request->validated());

        return ApiResponse::success(
            new BudgetResource($budget),
            'Budget created successfully',
            201,
        );
    }
}
