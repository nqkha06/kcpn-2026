<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Api\V1\Admin\CategoryIndexRequest;
use App\Http\Resources\Api\V1\Admin\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Services\Admin\AdminCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function __construct(
        private readonly AdminCategoryService $categoryService,
    ) {}

    public function index(CategoryIndexRequest $request): JsonResponse
    {
        $categories = $this->categoryService->paginate($request->validated());

        return ApiResponse::paginated(
            CategoryResource::collection($categories->getCollection()),
            $categories,
        );
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return ApiResponse::success(
            new CategoryResource($category),
            'Category created successfully',
            201,
        );
    }

    public function show(Category $category): JsonResponse
    {
        Gate::authorize('view', $category);

        return ApiResponse::success(new CategoryResource($category));
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->categoryService->update($category, $request->validated());

        return ApiResponse::success(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        Gate::authorize('delete', $category);
        $this->categoryService->delete($category);

        return ApiResponse::success(message: 'Category deleted successfully');
    }
}
