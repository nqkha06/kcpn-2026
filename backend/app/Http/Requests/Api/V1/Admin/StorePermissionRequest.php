<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Permission::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->where('guard_name', 'web')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A permission with this name already exists.',
        ];
    }
}
