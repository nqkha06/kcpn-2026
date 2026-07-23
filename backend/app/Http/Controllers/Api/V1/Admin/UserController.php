<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreUserRequest;
use App\Http\Requests\Api\V1\Admin\UpdateUserRequest;
use App\Http\Requests\Api\V1\Admin\UserIndexRequest;
use App\Http\Resources\Api\V1\Admin\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $userService,
    ) {}

    public function index(UserIndexRequest $request): JsonResponse
    {
        $users = $this->userService->paginate($request->validated());

        return ApiResponse::paginated(
            UserResource::collection($users->getCollection()),
            $users,
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return ApiResponse::success(
            new UserResource($user),
            'User created successfully',
            201,
        );
    }

    public function show(User $user): JsonResponse
    {
        Gate::authorize('view', $user);

        return ApiResponse::success(new UserResource($this->userService->find($user)));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->update($user, $request->validated());

        return ApiResponse::success(new UserResource($user), 'User updated successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);
        $this->userService->delete($user);

        return ApiResponse::success(message: 'User deleted successfully');
    }
}
