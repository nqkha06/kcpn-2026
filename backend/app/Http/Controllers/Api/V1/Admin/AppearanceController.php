<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\AppearanceRequest;
use App\Http\Resources\Api\V1\Admin\AppearanceResource;
use App\Http\Responses\ApiResponse;
use App\Models\Setting;
use App\Services\Admin\AdminAppearanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AppearanceController extends Controller
{
    public function __construct(
        private readonly AdminAppearanceService $appearanceService,
    ) {}

    public function show(): JsonResponse
    {
        Gate::authorize('viewAny', Setting::class);

        return ApiResponse::success(new AppearanceResource($this->appearanceService->data()));
    }

    public function update(AppearanceRequest $request): JsonResponse
    {
        $files = collect(['logo_light', 'logo_dark', 'favicon', 'social_image'])
            ->mapWithKeys(fn (string $field): array => [$field => $request->file($field)])
            ->all();
        $appearance = $this->appearanceService->update($request->validated(), $files);

        return ApiResponse::success(
            new AppearanceResource($appearance),
            'Appearance settings updated successfully',
        );
    }
}
