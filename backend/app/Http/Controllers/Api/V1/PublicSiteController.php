<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PublicSiteConfigurationRequest;
use App\Http\Resources\Api\V1\PublicPageResource;
use App\Http\Resources\Api\V1\PublicSiteConfigurationResource;
use App\Http\Responses\ApiResponse;
use App\Services\PublicSiteService;
use Illuminate\Http\JsonResponse;

class PublicSiteController extends Controller
{
    public function __construct(
        private readonly PublicSiteService $publicSiteService,
    ) {}

    public function configuration(PublicSiteConfigurationRequest $request): JsonResponse
    {
        $locale = $request->validated('locale') ?? config('app.locale', 'en');
        $configuration = $this->publicSiteService->configuration($locale);

        return ApiResponse::success(new PublicSiteConfigurationResource($configuration))
            ->header('Cache-Control', 'public, max-age=60');
    }

    public function page(string $slug): JsonResponse
    {
        $page = $this->publicSiteService->publishedPage($slug);

        return ApiResponse::success(new PublicPageResource($page))
            ->header('Cache-Control', 'public, max-age=60');
    }
}
