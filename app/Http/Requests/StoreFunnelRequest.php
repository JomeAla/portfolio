<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFunnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'funnel_type' => 'nullable|string|in:lead_magnet,tripwire,webinar,launch,affiliate',
            'is_active' => 'nullable|boolean',
            'automation_enabled' => 'nullable|boolean',
            'product_id' => 'nullable|integer|exists:products,id',
            'service_id' => 'nullable|integer|exists:services,id',
        ];
    }
}