<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\ExpenseTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', ExpenseTransaction::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['income', 'expense'])],
            'status' => ['nullable', Rule::in(['posted', 'pending', 'cancelled'])],
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'wallet_id' => ['nullable', 'integer', Rule::exists('user_wallets', 'id')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'sort' => ['nullable', Rule::in(['id', 'type', 'amount', 'status', 'transacted_at', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'The selected transaction type is invalid.',
            'status.in' => 'The selected transaction status is invalid.',
            'to_date.after_or_equal' => 'The end date must be on or after the start date.',
            'sort.in' => 'The selected sort field is invalid.',
            'direction.in' => 'The selected sort direction is invalid.',
            'per_page.between' => 'The page size must be between 1 and 100.',
        ];
    }
}
