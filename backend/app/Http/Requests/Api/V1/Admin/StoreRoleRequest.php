<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Role::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['integer', 'distinct', Rule::exists('permissions', 'id')->where('guard_name', 'web')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A role with this name already exists.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.',
            'permissions.*.distinct' => 'Each selected permission must be unique.',
        ];
    }
}
