<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('permission');

        return $permission instanceof Permission && ($this->user()?->can('update', $permission) ?? false);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Permission $permission */
        $permission = $this->route('permission');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->where('guard_name', 'web')->ignore($permission)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A permission with this name already exists.',
        ];
    }
}
