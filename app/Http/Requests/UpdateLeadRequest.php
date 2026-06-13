<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,unsubscribed,bounced,new',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
            'score' => 'nullable|integer|min:0|max:9999',
            'sequence_id' => 'nullable|integer|exists:email_sequences,id',
        ];
    }
}