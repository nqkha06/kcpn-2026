<?php

namespace App\Http\Requests\Admin;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $menu = $this->route('menu');

        return $menu instanceof Menu
            ? ($this->user()?->can('update', $menu) ?? false)
            : ($this->user()?->can('create', Menu::class) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $menuId = $this->route('menu')?->id;

        return [
            'title' => ['required', 'string', 'max:120'],
            'url' => ['nullable', 'string', 'max:255'],
            'canonical' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(\.[a-z0-9_-]+)+$/'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('menus', 'id'),
                Rule::notIn(array_filter([$menuId])),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a menu title.',
            'canonical.required' => 'Please choose where this menu should be displayed.',
            'canonical.regex' => 'Canonical must look like home.header, home.footer, or user.header.',
            'parent_id.exists' => 'Selected parent menu does not exist.',
            'sort_order.integer' => 'Sort order must be a number.',
            'target.in' => 'Target must be _self or _blank.',
            'status.required' => 'Please choose menu status.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $parentId = $this->input('parent_id');

        $this->merge([
            'parent_id' => $parentId === '' ? null : $parentId,
            'sort_order' => (int) $this->input('sort_order', 0),
            'canonical' => trim((string) $this->input('canonical', '')),
        ]);
    }
}
