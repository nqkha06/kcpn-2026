<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserPreferenceUpdateRequest extends FormRequest
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
            'currency' => ['required', 'string', 'size:3', 'in:VND,USD,EUR,GBP'],
        ];
    }

    public function messages(): array
    {
        return [
            'currency.required' => 'Vui lòng chọn đơn vị tiền tệ.',
            'currency.in' => 'Đơn vị tiền tệ không hợp lệ.',
        ];
    }
}
