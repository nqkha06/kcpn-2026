<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ApiResponse
{
    private function __construct() {}

    public static function success(
        mixed $data = null,
        string $message = 'Operation completed successfully',
        int $status = 200,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data ?? (object) [],
        ], $status);
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public static function error(
        string $message,
        array $errors = [],
        int $status = 400,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors === [] ? (object) [] : $errors,
        ], $status);
    }

    public static function paginated(
        AnonymousResourceCollection $resources,
        LengthAwarePaginator $paginator,
        string $message = 'Operation completed successfully',
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resources->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }
}
