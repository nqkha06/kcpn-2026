<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\TransactionIndexRequest;
use App\Http\Requests\User\StoreExpenseTransactionRequest;
use App\Http\Resources\Api\V1\TransactionResource;
use App\Http\Responses\ApiResponse;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Services\User\UserTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function __construct(
        private readonly UserTransactionService $transactionService,
    ) {}

    public function index(TransactionIndexRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', ExpenseTransaction::class);

        /** @var User $user */
        $user = $request->user();
        $transactions = $this->transactionService->paginate($user, $request->validated());

        return ApiResponse::paginated(
            TransactionResource::collection($transactions->getCollection()),
            $transactions,
        );
    }

    public function store(StoreExpenseTransactionRequest $request): JsonResponse
    {
        Gate::authorize('create', ExpenseTransaction::class);

        /** @var User $user */
        $user = $request->user();
        $transaction = $this->transactionService->create($user, $request->validated());

        return ApiResponse::success(
            new TransactionResource($transaction),
            'Transaction created successfully',
            201,
        );
    }
}
