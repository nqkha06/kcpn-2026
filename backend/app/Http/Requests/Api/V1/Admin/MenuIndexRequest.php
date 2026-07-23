<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', \App\Models\Menu::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'canonical' => ['nullable', 'string', 'max:80'],
            'parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'sort' => ['nullable', Rule::in(['id', 'title', 'canonical', 'sort_order', 'status', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'The selected menu status is invalid.',
            'parent_id.exists' => 'The selected parent menu does not exist.',
            'sort.in' => 'The selected sort field is invalid.',
            'direction.in' => 'The selected sort direction is invalid.',
            'per_page.between' => 'The page size must be between 1 and 100.',
        ];
    }
}
