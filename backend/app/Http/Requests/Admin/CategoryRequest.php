<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            : ($this->user()?->can('create', Category::class) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('categories', 'name')
                    ->whereNull('user_id')
                    ->ignore($categoryId),
            ],
            'color' => [
                'required',
                'string',
                'max:20',
                'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/',
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a category name.',
            'name.unique' => 'This category name already exists.',
            'color.required' => 'Please choose a color for the category.',
            'color.regex' => 'Color must be a valid hex value like #94A3B8.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }
}
