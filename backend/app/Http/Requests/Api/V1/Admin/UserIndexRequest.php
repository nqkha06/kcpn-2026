<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'created_date' => ['nullable', 'date_format:Y-m-d'],
            'created_from' => ['nullable', 'date_format:Y-m-d'],
            'created_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:created_from'],
            'sort' => ['nullable', Rule::in(['id', 'name', 'email', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.exists' => 'The selected role does not exist.',
            'created_date.date_format' => 'The created date must use the Y-m-d format.',
            'created_to.after_or_equal' => 'The end date must be on or after the start date.',
            'sort.in' => 'The selected sort field is invalid.',
            'direction.in' => 'The selected sort direction is invalid.',
            'per_page.between' => 'The page size must be between 1 and 100.',
        ];
    }
}
