<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserDashboardResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\User\UserDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserDashboardService $dashboardService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success(
            new UserDashboardResource($this->dashboardService->data($user)),
        );
    }
}
