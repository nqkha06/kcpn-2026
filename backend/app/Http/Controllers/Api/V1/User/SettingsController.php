<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserPreferenceUpdateRequest;
use App\Http\Requests\User\UserProfileUpdateRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\UserSettingsResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\User\UserSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        private readonly UserSettingsService $settingsService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success(
            new UserSettingsResource($this->settingsService->data($user)),
        );
    }

    public function updateProfile(UserProfileUpdateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user = $this->settingsService->updateProfile($user, $request->validated());

        return ApiResponse::success(
            new UserResource($user->loadMissing('roles.permissions')),
            'Đã cập nhật thông tin hồ sơ.',
        );
    }

    public function updatePreferences(UserPreferenceUpdateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->settingsService->updateCurrency(
            $user,
            $request->string('currency')->toString(),
        );

        return ApiResponse::success(
            new UserSettingsResource($this->settingsService->data($user)),
            'Đã cập nhật đơn vị tiền tệ.',
        );
    }
}
