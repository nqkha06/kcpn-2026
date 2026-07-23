<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryListController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'color'])
            ->map(fn (Category $category) => [
                'id' => (string) $category->id,
                'name' => $category->name,
                'color' => $category->color,
            ]);

        return response()->json($categories);
    }
}
