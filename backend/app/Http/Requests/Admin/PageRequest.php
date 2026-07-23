<?php

namespace App\Http\Requests\Admin;

use App\Enums\BaseStatusEnum;
use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $page instanceof Page
            ? ($this->user()?->can('update', $page) ?? false)
            : ($this->user()?->can('create', Page::class) ?? false);
    }

    public function rules(): array
    {
        $pageId = $this->route('page')?->id;

        return [
            'status' => ['required', Rule::in(array_column(BaseStatusEnum::cases(), 'value'))],
            'image' => ['nullable', 'string', 'max:2048'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'tags' => ['nullable'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($pageId)],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select a status.',
            'status.in' => 'Selected status is invalid.',
            'title.required' => 'Title is required.',
            'slug.unique' => 'This slug is already in use.',
        ];
    }
}
