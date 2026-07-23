<?php

namespace App\Http\Requests\User;

use App\Models\UserWallet;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseTransactionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $labels = $this->input('labels');

        if (is_string($labels)) {
            $labels = collect(explode(',', $labels))
                ->map(fn (string $label): string => trim($label))
                ->filter(fn (string $label): bool => $label !== '')
                ->values()
                ->all();
        }

        $this->merge([
            'category_id' => $this->input('category_id') === 'none' || $this->input('category_id') === ''
                ? null
                : $this->input('category_id'),
            'labels' => $labels,
        ]);
    }

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
            'wallet_id' => [
                'required',
                'integer',
                'exists:user_wallets,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $userId = $this->user()?->id;

                    if ($userId === null) {
                        $fail('You must be logged in to create a transaction.');

                        return;
                    }

                    $walletBelongsToUser = UserWallet::query()
                        ->whereKey((int) $value)
                        ->where('user_id', $userId)
                        ->exists();

                    if (! $walletBelongsToUser) {
                        $fail('The selected wallet is invalid.');
                    }
                },
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($userId) {
                    $query->where('status', 'active')
                        ->where(function ($scope) use ($userId): void {
                            $scope->whereNull('user_id')->orWhere('user_id', $userId);
                        });
                }),
            ],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'amount' => ['required', 'numeric', 'between:0.01,9999999999.99'],
            'transacted_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'wallet_id.required' => 'Please select a wallet.',
            'wallet_id.exists' => 'The selected wallet does not exist.',
            'category_id.exists' => 'The selected category is invalid.',
            'type.required' => 'Please select a transaction type.',
            'type.in' => 'Transaction type must be income or expense.',
            'amount.required' => 'Please enter an amount.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.between' => 'Amount is out of range.',
            'transacted_at.required' => 'Please select a transaction date.',
            'transacted_at.date' => 'Transaction date is invalid.',
            'note.max' => 'Note may not be greater than 255 characters.',
            'labels.array' => 'Labels must be a list.',
            'labels.*.max' => 'Each label may not be greater than 30 characters.',
        ];
    }
}
