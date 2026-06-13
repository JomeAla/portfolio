<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunnelStage extends Model
{
    protected $fillable = [
        'funnel_id',
        'name',
        'type',
        'content',
        'order',
        'delay_days',
        'is_required',
        'sequence_id',
        'email_template',
        'delay_hours',
        'condition_type',
        'condition_value',
        'is_skippable',
        'action_on_complete',
        'action_config',
        'points_to_award',
        'wait_duration_hours',
        'wait_until_type',
        'wait_until_value',
        'redirect_type',
        'conditional_stages',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_skippable' => 'boolean',
        'content' => 'array',
        'condition_value' => 'array',
        'action_config' => 'array',
        'conditional_stages' => 'array',
        'wait_until_value' => 'array',
    ];

    public function funnel()
    {
        return $this->belongsTo(Funnel::class);
    }

    public function nextStage()
    {
        return $this->hasOne(FunnelStage::class)
            ->where('order', $this->order + 1);
    }

    public function previousStage()
    {
        return $this->hasOne(FunnelStage::class)
            ->where('order', $this->order - 1);
    }

    public function hasCondition()
    {
        return !empty($this->condition_type) && $this->condition_type !== 'none';
    }

    public function hasAction()
    {
        return !empty($this->action_on_complete) && $this->action_on_complete !== 'advance';
    }

    public function getConditionLabel()
    {
        $labels = [
            'none' => 'No condition',
            'email_opens' => 'Wait for email opens',
            'clicks' => 'Wait for clicks',
            'score_above' => 'Wait for score threshold',
            'wait' => 'Wait duration',
            'tag_has' => 'Has tag',
        ];
        return $labels[$this->condition_type] ?? $this->condition_type;
    }

    public function getActionLabel()
    {
        $labels = [
            'advance' => 'Advance to next stage',
            'email' => 'Send email',
            'tag' => 'Add tag',
            'notify' => 'Send notification',
            'wait' => 'Wait',
        ];
        return $labels[$this->action_on_complete] ?? $this->action_on_complete;
    }

    public function isWaitStage()
    {
        return $this->type === 'delay' || $this->condition_type === 'wait';
    }

    public function shouldSkip($lead)
    {
        if (!$this->is_skippable) {
            return false;
        }
        
        // Could add logic here to determine if lead qualifies to skip
        // For now, just check the is_skippable flag
        return true;
    }

    public function getWaitDescription()
    {
        if ($this->wait_duration_hours > 0) {
            if ($this->wait_duration_hours < 24) {
                return $this->wait_duration_hours . ' hour(s)';
            }
            return round($this->wait_duration_hours / 24, 1) . ' day(s)';
        }
        
        if ($this->wait_until_type === 'specific_datetime' && !empty($this->wait_until_value)) {
            return 'Until ' . $this->wait_until_value;
        }
        
        if ($this->wait_until_type === 'day_of_week' && !empty($this->wait_until_value)) {
            return 'Every ' . $this->wait_until_value;
        }
        
        return 'No wait';
    }
}