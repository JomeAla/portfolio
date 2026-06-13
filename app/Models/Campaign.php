<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\SlugGenerator;

class Campaign extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'name';
    
    protected $fillable = [
        'name',
        'description',
        'sequence_ids',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'sequence_ids' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function campaignLeads(): HasMany
    {
        return $this->hasMany(CampaignLead::class, 'campaign_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'campaign_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getTotalLeadsCountAttribute(): int
    {
        return $this->leads()->count();
    }

    public function getActiveLeadsCountAttribute(): int
    {
        return $this->leads()->where('status', 'active')->count();
    }
}

class CampaignLead extends Model
{
    protected $table = 'campaign_leads';
    
    protected $fillable = [
        'campaign_id',
        'lead_id',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
}