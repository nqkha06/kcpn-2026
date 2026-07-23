<?php

namespace App\Http\Requests\User;

use App\Models\UserWallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $wallet = $this->route('wallet');

        return $wallet instanceof UserWallet
            && ($this->user()?->can('update', $wallet) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;
        $wallet = $this->route('wallet');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('user_wallets', 'name')
                    ->where(fn ($query) => $query->where('user_id', $userId))
                    ->ignore($wallet),
            ],
            'currency' => ['required', 'string', 'size:3'],
            'opening_balance' => ['nullable', 'numeric', 'between:-9999999999.99,9999999999.99'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a wallet name.',
            'name.unique' => 'This wallet name already exists.',
            'currency.required' => 'Please choose a currency.',
            'currency.size' => 'Currency must be a 3-letter code.',
            'opening_balance.numeric' => 'Opening balance must be a number.',
            'opening_balance.between' => 'Opening balance is out of range.',
        ];
    }
}
