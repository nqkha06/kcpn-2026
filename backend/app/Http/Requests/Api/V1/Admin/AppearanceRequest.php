<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class AppearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Setting::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'logo_light' => $this->imageRules(),
            'logo_dark' => $this->imageRules(),
            'favicon' => $this->imageRules(),
            'social_image' => $this->imageRules(),
            'general' => ['nullable', 'array'],
        ];

        foreach ($this->languageCodes() as $code) {
            $rules["general.$code.site_name"] = ['nullable', 'string', 'max:255'];
            $rules["general.$code.site_title"] = ['nullable', 'string', 'max:255'];
            $rules["general.$code.tagline"] = ['nullable', 'string', 'max:255'];
            $rules["general.$code.meta_description"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            '*.mimes' => 'The uploaded file must be a supported image type.',
            '*.mimetypes' => 'The uploaded file content is not a supported image type.',
            '*.max' => 'Each uploaded image may not be greater than 4 MB.',
            'general.*.site_name.max' => 'The site name may not be greater than 255 characters.',
            'general.*.meta_description.max' => 'The meta description may not be greater than 500 characters.',
        ];
    }

    /** @return array<int, string> */
    private function imageRules(): array
    {
        return [
            'nullable',
            'mimes:jpg,jpeg,png,gif,webp,svg,ico',
            'mimetypes:image/jpeg,image/png,image/gif,image/webp,image/svg+xml,image/x-icon,image/vnd.microsoft.icon',
            'max:4096',
        ];
    }

    /** @return array<int, string> */
    private function languageCodes(): array
    {
        return collect(config('app.supported_locales', ['vi', 'en']))
            ->filter(fn (mixed $code): bool => is_string($code) && trim($code) !== '')
            ->map(fn (string $code): string => strtolower($code))
            ->values()
            ->all();
    }
}
