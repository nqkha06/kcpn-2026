<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
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
            'type' => ['nullable', Rule::in(['income', 'expense'])],
            'status' => ['nullable', Rule::in(['posted', 'pending'])],
            'wallet_id' => [
                'nullable',
                'integer',
                Rule::exists('user_wallets', 'id')
                    ->where(fn ($query) => $query->where('user_id', $this->user()?->id)),
            ],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'sort' => ['nullable', Rule::in(['transacted_at', 'amount', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'wallet_id.exists' => 'The selected wallet is invalid.',
            'category_id.exists' => 'The selected category is invalid.',
            'date_from.date_format' => 'The start date must use the Y-m-d format.',
            'date_to.date_format' => 'The end date must use the Y-m-d format.',
            'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
            'sort.in' => 'The selected sort field is invalid.',
            'direction.in' => 'The selected sort direction is invalid.',
            'per_page.between' => 'The page size must be between 1 and 100.',
        ];
    }
}
