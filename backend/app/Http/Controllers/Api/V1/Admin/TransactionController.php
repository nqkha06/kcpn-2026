<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpenseTransactionRequest;
use App\Http\Requests\Api\V1\Admin\TransactionIndexRequest;
use App\Http\Resources\Api\V1\Admin\TransactionResource;
use App\Http\Responses\ApiResponse;
use App\Models\ExpenseTransaction;
use App\Services\Admin\AdminTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function __construct(
        private readonly AdminTransactionService $transactionService,
    ) {}

    public function index(TransactionIndexRequest $request): JsonResponse
    {
        $transactions = $this->transactionService->paginate($request->validated());

        return ApiResponse::paginated(
            TransactionResource::collection($transactions->getCollection()),
            $transactions,
        );
    }

    public function options(): JsonResponse
    {
        Gate::authorize('viewAny', ExpenseTransaction::class);

        return ApiResponse::success($this->transactionService->options());
    }

    public function store(ExpenseTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionService->create($request->validated());

        return ApiResponse::success(
            new TransactionResource($transaction),
            'Transaction created successfully',
            201,
        );
    }

    public function show(ExpenseTransaction $transaction): JsonResponse
    {
        Gate::authorize('view', $transaction);

        return ApiResponse::success(new TransactionResource($this->transactionService->find($transaction)));
    }

    public function update(ExpenseTransactionRequest $request, ExpenseTransaction $transaction): JsonResponse
    {
        $transaction = $this->transactionService->update($transaction, $request->validated());

        return ApiResponse::success(
            new TransactionResource($transaction),
            'Transaction updated successfully',
        );
    }

    public function destroy(ExpenseTransaction $transaction): JsonResponse
    {
        Gate::authorize('delete', $transaction);
        $this->transactionService->delete($transaction);

        return ApiResponse::success(message: 'Transaction deleted successfully');
    }
}
