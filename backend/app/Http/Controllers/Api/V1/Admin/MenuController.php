<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuRequest;
use App\Http\Requests\Api\V1\Admin\MenuIndexRequest;
use App\Http\Requests\Api\V1\Admin\MenuParentOptionsRequest;
use App\Http\Resources\Api\V1\Admin\MenuResource;
use App\Http\Responses\ApiResponse;
use App\Models\Menu;
use App\Services\Admin\AdminMenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MenuController extends Controller
{
    public function __construct(
        private readonly AdminMenuService $menuService,
    ) {}

    public function index(MenuIndexRequest $request): JsonResponse
    {
        $menus = $this->menuService->paginate($request->validated());

        return ApiResponse::paginated(
            MenuResource::collection($menus->getCollection()),
            $menus,
        );
    }

    public function parentOptions(MenuParentOptionsRequest $request): JsonResponse
    {
        $excludedMenuId = $request->validated('exclude');

        return ApiResponse::success(MenuResource::collection(
            $this->menuService->parentOptions(
                $excludedMenuId === null ? null : (int) $excludedMenuId,
            ),
        ));
    }

    public function store(MenuRequest $request): JsonResponse
    {
        $menu = $this->menuService->create($request->validated());

        return ApiResponse::success(
            new MenuResource($menu),
            'Menu created successfully',
            201,
        );
    }

    public function show(Menu $menu): JsonResponse
    {
        Gate::authorize('view', $menu);

        return ApiResponse::success(new MenuResource($this->menuService->find($menu)));
    }

    public function update(MenuRequest $request, Menu $menu): JsonResponse
    {
        $menu = $this->menuService->update($menu, $request->validated());

        return ApiResponse::success(new MenuResource($menu), 'Menu updated successfully');
    }

    public function destroy(Request $request, Menu $menu): JsonResponse
    {
        Gate::authorize('delete', $menu);
        $this->menuService->delete($menu);

        return ApiResponse::success(message: 'Menu deleted successfully');
    }
}
