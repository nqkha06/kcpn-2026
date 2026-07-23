<?php

namespace App\Http\Requests\User;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $category instanceof Category
            ? ($this->user()?->can('update', $category) ?? false)
            : $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;
        $userId = $this->user()?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                function (string $attribute, mixed $value, Closure $fail) use ($categoryId, $userId): void {
                    $visibleCategoryExists = Category::query()
                        ->where('name', trim((string) $value))
                        ->where(function ($query) use ($userId): void {
                            $query->whereNull('user_id')->orWhere('user_id', $userId);
                        })
                        ->when($categoryId !== null, fn ($query) => $query->whereKeyNot($categoryId))
                        ->exists();

                    if ($visibleCategoryExists) {
                        $fail('Bạn đã có thể sử dụng danh mục với tên này.');
                    }
                },
            ],
            'color' => [
                'required',
                'string',
                'max:20',
                'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/',
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'color.required' => 'Vui lòng chọn màu cho danh mục.',
            'color.regex' => 'Màu phải là mã hex hợp lệ, ví dụ #94A3B8.',
        ];
    }
}
