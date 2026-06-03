<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('user.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:191', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:60', 'alpha_dash', 'unique:users,username'],
            'phone'    => ['nullable', 'string', 'max:32'],
            'locale'   => ['nullable', 'in:en,ar'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role'     => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
