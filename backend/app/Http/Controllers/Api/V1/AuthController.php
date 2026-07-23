<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\TwoFactorChallengeRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use App\Services\Auth\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authenticationService->login($request);

        if ($result->requiresTwoFactor) {
            return ApiResponse::success(
                data: ['requires_two_factor' => true, 'user' => null],
                message: 'Two-factor authentication is required',
            );
        }

        return ApiResponse::success([
            'requires_two_factor' => false,
            'user' => new UserResource($result->user),
        ], 'Login successful');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authenticationService->register($request->validated());

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'Registration successful', 201);
    }

    public function twoFactorChallenge(TwoFactorChallengeRequest $request): JsonResponse
    {
        $user = $this->authenticationService->completeTwoFactorChallenge($request);

        return ApiResponse::success([
            'requires_two_factor' => false,
            'user' => new UserResource($user),
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authenticationService->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return ApiResponse::success(message: 'Logout successful');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles.permissions');

        return ApiResponse::success([
            'user' => new UserResource($user),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->authenticationService->sendPasswordResetLink(
            $request->string('email')->toString(),
        );

        return ApiResponse::success(message: trans($status));
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authenticationService->resetPassword($request->validated());

        return ApiResponse::success(message: trans($status));
    }
}
