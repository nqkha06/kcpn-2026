<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Admin\DashboardResource;
use App\Http\Responses\ApiResponse;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboardService,
    ) {}

    public function __invoke(): JsonResponse
    {
        return ApiResponse::success(new DashboardResource($this->dashboardService->data()));
    }
}
