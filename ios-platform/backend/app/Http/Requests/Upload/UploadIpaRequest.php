<?php

namespace App\Http\Requests\Upload;

use Illuminate\Foundation\Http\FormRequest;

class UploadIpaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('app.upload') ?? false;
    }

    public function rules(): array
    {
        $maxMb = (int) config('platform.storage.max_size_mb', 512);

        return [
            'app_id' => ['required', 'integer', 'exists:apps,id'],
            'ipa'    => ['required', 'file', 'mimes:ipa', "max:{$maxMb}000"],
        ];
    }
}
