<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
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
        $userId = $this->user()?->id;

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($userId) {
                    $query->where('status', 'active')
                        ->where(function ($scope) use ($userId): void {
                            $scope->whereNull('user_id')->orWhere('user_id', $userId);
                        });
                }),
                Rule::unique('budgets')
                    ->where(function ($query) use ($userId) {
                        return $query
                            ->where('user_id', $userId)
                            ->where('category_id', (int) $this->input('category_id'))
                            ->where('period', (string) $this->input('period'));
                    }),
            ],
            'amount_limit' => ['required', 'numeric', 'between:0.01,9999999999.99'],
            'period' => ['required', Rule::in(['monthly', 'yearly'])],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'category_id.unique' => 'You already have a budget for this category and period.',
            'amount_limit.required' => 'Please provide a budget limit.',
            'amount_limit.numeric' => 'Budget limit must be a number.',
            'amount_limit.between' => 'Budget limit is out of range.',
            'period.required' => 'Please select a period.',
            'period.in' => 'Period must be monthly or yearly.',
            'note.max' => 'Note may not be greater than 255 characters.',
        ];
    }
}
