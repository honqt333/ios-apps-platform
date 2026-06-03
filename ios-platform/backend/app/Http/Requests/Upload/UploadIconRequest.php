<?php

namespace App\Http\Requests\Upload;

use Illuminate\Foundation\Http\FormRequest;

class UploadIconRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('app.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'app_id' => ['required', 'integer', 'exists:apps,id'],
            'icon'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
