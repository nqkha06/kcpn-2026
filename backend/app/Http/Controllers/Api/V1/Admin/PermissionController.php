<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\PermissionIndexRequest;
use App\Http\Requests\Api\V1\Admin\StorePermissionRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePermissionRequest;
use App\Http\Resources\Api\V1\Admin\PermissionResource;
use App\Http\Responses\ApiResponse;
use App\Services\Admin\AdminPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(
        private readonly AdminPermissionService $permissionService,
    ) {}

    public function index(PermissionIndexRequest $request): JsonResponse
    {
        $permissions = $this->permissionService->paginate($request->validated());

        return ApiResponse::paginated(
            PermissionResource::collection($permissions->getCollection()),
            $permissions,
        );
    }

    public function options(): JsonResponse
    {
        Gate::authorize('viewAny', Permission::class);

        return ApiResponse::success(PermissionResource::collection($this->permissionService->options()));
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->permissionService->create($request->validated());

        return ApiResponse::success(
            new PermissionResource($permission),
            'Permission created successfully',
            201,
        );
    }

    public function show(Permission $permission): JsonResponse
    {
        Gate::authorize('view', $permission);

        return ApiResponse::success(new PermissionResource($this->permissionService->find($permission)));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission = $this->permissionService->update($permission, $request->validated());

        return ApiResponse::success(
            new PermissionResource($permission),
            'Permission updated successfully',
        );
    }

    public function destroy(Permission $permission): JsonResponse
    {
        Gate::authorize('delete', $permission);
        $this->permissionService->delete($permission);

        return ApiResponse::success(message: 'Permission deleted successfully');
    }
}
