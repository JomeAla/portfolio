<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutomationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|string|in:email_opened,email_clicked,link_clicked,score_reached,tag_added,page_visited,form_submitted,campaign_enrolled,lead_created',
            'trigger_value' => 'nullable|string|max:500',
            'action_type' => 'required|string|in:enroll_sequence,add_tag,remove_tag,send_email,update_score,notify_admin,webhook',
            'action_config' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ];
    }
}