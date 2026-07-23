<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BudgetIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Budget::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'period' => ['nullable', Rule::in(['monthly', 'yearly'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'sort' => ['nullable', Rule::in(['id', 'amount_limit', 'period', 'status', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'period.in' => 'The selected budget period is invalid.',
            'status.in' => 'The selected budget status is invalid.',
            'user_id.exists' => 'The selected user does not exist.',
            'category_id.exists' => 'The selected category does not exist.',
            'sort.in' => 'The selected sort field is invalid.',
            'direction.in' => 'The selected sort direction is invalid.',
            'per_page.between' => 'The page size must be between 1 and 100.',
        ];
    }
}
