<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('user.update') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id;

        return [
            'name'     => ['sometimes', 'required', 'string', 'max:120'],
            'email'    => ['sometimes', 'required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($id)],
            'username' => ['sometimes', 'nullable', 'string', 'max:60', 'alpha_dash', Rule::unique('users', 'username')->ignore($id)],
            'phone'    => ['sometimes', 'nullable', 'string', 'max:32'],
            'locale'   => ['sometimes', 'in:en,ar'],
            'password' => ['sometimes', 'nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'role'     => ['sometimes', 'string', 'exists:roles,name'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
