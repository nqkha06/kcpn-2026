<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Http\Requests\Api\V1\Admin\PageIndexRequest;
use App\Http\Resources\Api\V1\Admin\PageResource;
use App\Http\Responses\ApiResponse;
use App\Models\Page;
use App\Models\User;
use App\Services\Admin\AdminPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PageController extends Controller
{
    public function __construct(
        private readonly AdminPageService $pageService,
    ) {}

    public function index(PageIndexRequest $request): JsonResponse
    {
        $pages = $this->pageService->paginate($request->validated());

        return ApiResponse::paginated(
            PageResource::collection($pages->getCollection()),
            $pages,
        );
    }

    public function store(PageRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $page = $this->pageService->create($user, $request->validated());

        return ApiResponse::success(
            new PageResource($page),
            'Page created successfully',
            201,
        );
    }

    public function show(Page $page): JsonResponse
    {
        Gate::authorize('view', $page);

        return ApiResponse::success(new PageResource($this->pageService->find($page)));
    }

    public function update(PageRequest $request, Page $page): JsonResponse
    {
        $page = $this->pageService->update($page, $request->validated());

        return ApiResponse::success(new PageResource($page), 'Page updated successfully');
    }

    public function destroy(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('delete', $page);
        $this->pageService->delete($page);

        return ApiResponse::success(message: 'Page deleted successfully');
    }
}
