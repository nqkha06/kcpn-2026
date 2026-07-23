<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Models\User;
use App\Services\User\UserCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function __construct(
        private readonly UserCategoryService $categoryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success(
            CategoryResource::collection($this->categoryService->active($user)),
        );
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $category = $this->categoryService->create($user, $request->validated());

        return ApiResponse::success(new CategoryResource($category), 'Đã tạo danh mục riêng.', 201);
    }

    public function show(Request $request, Category $category): JsonResponse
    {
        Gate::authorize('view', $category);

        return ApiResponse::success(new CategoryResource($category));
    }

    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->categoryService->update($category, $request->validated());

        return ApiResponse::success(new CategoryResource($category), 'Đã cập nhật danh mục riêng.');
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        Gate::authorize('delete', $category);

        if ($category->expenseTransactions()->exists() || $category->budgets()->exists()) {
            return ApiResponse::error(
                'Không thể xóa danh mục đang được sử dụng.',
                ['category' => ['Hãy chuyển các giao dịch và ngân sách sang danh mục khác trước.']],
                409,
            );
        }

        $this->categoryService->delete($category);

        return ApiResponse::success(message: 'Đã xóa danh mục riêng.');
    }
}
