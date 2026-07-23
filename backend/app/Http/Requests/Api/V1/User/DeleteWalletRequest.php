<?php

namespace App\Http\Requests\Api\V1\User;

use App\Models\UserWallet;
use Illuminate\Foundation\Http\FormRequest;

class DeleteWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $wallet = $this->route('wallet');

        return $wallet instanceof UserWallet
            && ($this->user()?->can('delete', $wallet) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
