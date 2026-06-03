<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('category.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('category')?->id;

        return [
            'name'        => ['sometimes', 'required', 'string', 'max:120'],
            'slug'        => ['sometimes', 'nullable', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($id)],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'icon'        => ['sometimes', 'nullable', 'string', 'max:64'],
            'color'       => ['sometimes', 'nullable', 'string', 'max:16'],
            'sort_order'  => ['sometimes', 'integer', 'min:0'],
            'is_active'   => ['sometimes', 'boolean'],
            'parent_id'   => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
        ];
    }
}
