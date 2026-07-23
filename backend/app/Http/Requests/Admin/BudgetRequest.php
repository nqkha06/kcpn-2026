<?php

namespace App\Http\Requests\Admin;

use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $budget = $this->route('budget');

        if ($budget instanceof Budget) {
            return $this->user()?->can('update', $budget) ?? false;
        }

        return $this->user()?->can('create', Budget::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Budget|null $budget */
        $budget = $this->route('budget');

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query): void {
                    $userId = (int) $this->input('user_id');
                    $query->where(function ($scope) use ($userId): void {
                        $scope->whereNull('user_id')->orWhere('user_id', $userId);
                    });
                }),
                Rule::unique('budgets')
                    ->where(function ($query) {
                        return $query
                            ->where('user_id', (int) $this->input('user_id'))
                            ->where('category_id', (int) $this->input('category_id'))
                            ->where('period', (string) $this->input('period'));
                    })
                    ->ignore($budget?->id),
            ],
            'amount_limit' => ['required', 'numeric', 'between:0.01,9999999999.99'],
            'period' => ['required', Rule::in(['monthly', 'yearly'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a user.',
            'user_id.exists' => 'The selected user does not exist.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
            'amount_limit.required' => 'Please provide a budget limit.',
            'amount_limit.numeric' => 'Budget limit must be a number.',
            'amount_limit.between' => 'Budget limit is out of range.',
            'period.required' => 'Please select a period.',
            'period.in' => 'Period must be monthly or yearly.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Status must be active or inactive.',
            'note.max' => 'Note may not be greater than 255 characters.',
            'unique' => 'A budget for this user, category, and period already exists.',
        ];
    }
}
