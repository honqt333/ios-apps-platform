<?php

namespace App\Http\Requests\Apps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('app.create') ?? false;
    }

    public function rules(): array
    {
        $maxMb = (int) config('platform.storage.max_size_mb', 512);

        return [
            'name'                  => ['required', 'string', 'max:191'],
            'slug'                  => ['nullable', 'string', 'max:191', 'unique:apps,slug'],
            'developer'             => ['required', 'string', 'max:191'],
            'description'           => ['nullable', 'string', 'max:1000'],
            'long_description'      => ['nullable', 'string'],
            'bundle_id'             => ['required', 'string', 'max:191', 'regex:/^[a-zA-Z0-9.-]+$/', 'unique:apps,bundle_id'],
            'version'               => ['required', 'string', 'max:32'],
            'build_number'          => ['nullable', 'string', 'max:32'],
            'minimum_ios_version'   => ['required', 'string', 'max:16'],
            'category_id'           => ['nullable', 'integer', 'exists:categories,id'],
            'icon'                  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'ipa'                   => ['nullable', 'file', 'mimes:ipa', "max:{$maxMb}000"],
            'manifest'              => ['nullable', 'file', 'mimes:plist', 'max:128'],
            'screenshots'           => ['nullable', 'array', 'max:10'],
            'screenshots.*'         => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'changelog'             => ['nullable', 'string'],
            'is_active'             => ['nullable', 'boolean'],
            'is_archived'           => ['nullable', 'boolean'],
            'is_featured'           => ['nullable', 'boolean'],
            'localized'             => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'bundle_id.regex' => 'Bundle ID may only contain letters, numbers, dots, and hyphens.',
            'ipa.mimes'       => 'The IPA file must be a valid .ipa archive.',
        ];
    }
}
