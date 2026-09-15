<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecureApiPlan extends Model
{
    protected $table = 'secure_api_plans';

    protected $fillable = [
        'slug', 'name', 'price_monthly', 'quota_monthly', 'rate_rpm',
        'rpm_override', 'service_accounts', 'features', 'is_default', 'trial_days', 'grace_days',
    ];

    protected $casts = [
        'features'     => 'array',
        'price_monthly' => 'decimal:2',
        'is_default'   => 'boolean',
    ];
}
