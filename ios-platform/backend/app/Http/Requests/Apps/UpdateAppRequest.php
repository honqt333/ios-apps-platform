<?php

namespace App\Http\Requests\Apps;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('app.update') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('app')?->id;
        $maxMb = (int) config('platform.storage.max_size_mb', 512);

        return [
            'name'                => ['sometimes', 'required', 'string', 'max:191'],
            'slug'                => ['sometimes', 'nullable', 'string', 'max:191', Rule::unique('apps', 'slug')->ignore($id)],
            'developer'           => ['sometimes', 'required', 'string', 'max:191'],
            'description'         => ['sometimes', 'nullable', 'string', 'max:1000'],
            'long_description'    => ['sometimes', 'nullable', 'string'],
            'bundle_id'           => ['sometimes', 'required', 'string', 'max:191', 'regex:/^[a-zA-Z0-9.-]+$/', Rule::unique('apps', 'bundle_id')->ignore($id)],
            'version'             => ['sometimes', 'required', 'string', 'max:32'],
            'build_number'        => ['sometimes', 'nullable', 'string', 'max:32'],
            'minimum_ios_version' => ['sometimes', 'required', 'string', 'max:16'],
            'category_id'         => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'icon'                => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'ipa'                 => ['sometimes', 'nullable', 'file', 'mimes:ipa', "max:{$maxMb}000"],
            'screenshots'         => ['sometimes', 'nullable', 'array', 'max:10'],
            'screenshots.*'       => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'changelog'           => ['sometimes', 'nullable', 'string'],
            'is_active'           => ['sometimes', 'boolean'],
            'is_archived'         => ['sometimes', 'boolean'],
            'is_featured'         => ['sometimes', 'boolean'],
            'localized'           => ['sometimes', 'nullable', 'array'],
        ];
    }
}
