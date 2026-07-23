<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\DeleteWalletRequest;
use App\Http\Requests\User\StoreWalletRequest;
use App\Http\Requests\User\UpdateWalletRequest;
use App\Http\Resources\Api\V1\WalletResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\UserWallet;
use App\Services\User\UserWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WalletController extends Controller
{
    public function __construct(
        private readonly UserWalletService $walletService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', UserWallet::class);

        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success(
            WalletResource::collection($this->walletService->list($user)),
        );
    }

    public function store(StoreWalletRequest $request): JsonResponse
    {
        Gate::authorize('create', UserWallet::class);

        /** @var User $user */
        $user = $request->user();
        $wallet = $this->walletService->create($user, $request->validated());

        return ApiResponse::success(
            new WalletResource($wallet),
            'Wallet created successfully',
            201,
        );
    }

    public function update(UpdateWalletRequest $request, UserWallet $wallet): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $wallet = $this->walletService->update($user, $wallet, $request->validated());

        return ApiResponse::success(
            new WalletResource($wallet),
            'Wallet updated successfully',
        );
    }

    public function destroy(DeleteWalletRequest $request, UserWallet $wallet): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->walletService->delete($user, $wallet);

        return ApiResponse::success(message: 'Wallet deleted successfully');
    }
}
