<?php

namespace App\Http\Requests\Upload;

use Illuminate\Foundation\Http\FormRequest;

class UploadScreenshotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('app.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'app_id'        => ['required', 'integer', 'exists:apps,id'],
            'screenshots'   => ['required', 'array', 'min:1', 'max:10'],
            'screenshots.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
