<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['boolean'],
            'config' => ['sometimes', 'array'],
            'config.layout' => ['sometimes', 'in:grid,sidebar,full'],
            'config.widgets' => ['sometimes', 'array'],
            'thumbnail_url' => ['nullable', 'url'],
        ];
    }
}
