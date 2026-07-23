<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User && ($this->user()?->can('update', $user) ?? false);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['integer', 'distinct', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'A user with this email already exists.',
            'password.confirmed' => 'The password confirmation does not match.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
            'roles.*.distinct' => 'Each selected role must be unique.',
        ];
    }
}
