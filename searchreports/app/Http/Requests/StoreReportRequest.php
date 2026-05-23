<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'property_id' => ['required', 'integer', 'exists:gsc_properties,id'],
            'template_id' => ['nullable', 'integer', 'exists:report_templates,id'],
            'date_from' => ['required', 'date', 'before_or_equal:date_to'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'output_format' => ['sometimes', 'in:pdf,html,json'],
        ];
    }
}
