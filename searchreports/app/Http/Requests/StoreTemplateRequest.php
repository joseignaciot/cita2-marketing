<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['boolean'],
            'config' => ['required', 'array'],
            'config.layout' => ['required', 'in:grid,sidebar,full'],
            'config.widgets' => ['required', 'array', 'min:1'],
            'config.color_scheme' => ['sometimes', 'array'],
            'thumbnail_url' => ['nullable', 'url'],
        ];
    }
}
