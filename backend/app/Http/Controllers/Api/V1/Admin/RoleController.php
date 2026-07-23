<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\RoleIndexRequest;
use App\Http\Requests\Api\V1\Admin\StoreRoleRequest;
use App\Http\Requests\Api\V1\Admin\UpdateRoleRequest;
use App\Http\Resources\Api\V1\Admin\RoleResource;
use App\Http\Responses\ApiResponse;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly AdminRoleService $roleService,
    ) {}

    public function index(RoleIndexRequest $request): JsonResponse
    {
        $roles = $this->roleService->paginate($request->validated());

        return ApiResponse::paginated(
            RoleResource::collection($roles->getCollection()),
            $roles,
        );
    }

    public function options(): JsonResponse
    {
        Gate::authorize('viewAny', Role::class);

        return ApiResponse::success(RoleResource::collection($this->roleService->options()));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return ApiResponse::success(
            new RoleResource($role),
            'Role created successfully',
            201,
        );
    }

    public function show(Role $role): JsonResponse
    {
        Gate::authorize('view', $role);

        return ApiResponse::success(new RoleResource($this->roleService->find($role)));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->roleService->update($role, $request->validated());

        return ApiResponse::success(new RoleResource($role), 'Role updated successfully');
    }

    public function destroy(Role $role): JsonResponse
    {
        Gate::authorize('delete', $role);
        $this->roleService->delete($role);

        return ApiResponse::success(message: 'Role deleted successfully');
    }
}
