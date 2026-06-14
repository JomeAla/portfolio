<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SlugGenerator;

class Funnel extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'name';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'funnel_type',
        'goal',
        'product_id',
        'service_id',
        'is_active',
        'automation_enabled',
        'welcome_sequence_id',
        'followup_sequence_id',
        'webhook_url',
        'webhook_enabled',
        'notify_email',
        'upsell_enabled',
        'upsell_product_id',
        'upsell_discount',
        'upsell_timer',
        'facebook_pixel',
        'google_pixel',
        'countdown_enabled',
        'countdown_hours',
        'thank_you_title',
        'thank_you_message',
        'thank_you_video',
        'upsell_button_text',
        'exit_popup_enabled',
        'exit_popup_offer',
        'exit_popup_discount',
        'starts_at',
        'ends_at',
        'order_bumps',
        'refund_policy',
        'refund_period_days',
        'affiliate_enabled',
        'affiliate_commission',
        'affiliate_cookie_days',
        'score_per_page',
        'score_per_email',
        'score_per_checkout',
        'score_per_click',
        'score_hot_threshold',
        'hot_lead_tag',
        'order_bumps_enabled',
        'automation_workflows',
        'ab_testing_enabled',
        'ab_variants',
        'ab_traffic_split',
        'ab_winner',
        'ab_started_at',
        'ab_min_sample_size',
        'ab_confidence_level',
        'is_template',
        'template_category',
        'health_score',
        'health_issues',
        'last_health_check',
        'stage_order',
        'environment',
        'deployed_at',
        'deployment_history',
    ];

protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'automation_enabled' => 'boolean',
        'order_bumps' => 'array',
        'automation_workflows' => 'array',
        'ab_variants' => 'array',
        'ab_traffic_split' => 'array',
        'ab_started_at' => 'datetime',
        'health_issues' => 'array',
        'last_health_check' => 'datetime',
        'stage_order' => 'array',
        'deployed_at' => 'datetime',
        'deployment_history' => 'array',
    ];

    public function getOrderBumpsAttribute($value)
    {
        return is_array($value) ? $value : json_decode($value, true) ?? [];
    }

    public function setOrderBumpsAttribute($value)
    {
        $this->attributes['order_bumps'] = is_array($value) ? json_encode($value) : $value;
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function upsellProduct()
    {
        return $this->belongsTo(\App\Models\Product::class, 'upsell_product_id');
    }

    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class);
    }

    public function getCheckoutUrlAttribute()
    {
        if ($this->product) {
            return '/store/' . $this->product->slug . '?funnel=' . $this->id;
        }
        if ($this->service) {
            return '/contact?funnel=' . $this->id . '&service=' . $this->service->id;
        }
        return null;
    }

    public function getLandingPageUrlAttribute()
    {
        $landingPage = \App\Models\LandingPage::where('funnel_id', $this->id)->first();
        if ($landingPage && $landingPage->slug) {
            return '/l/' . $landingPage->slug;
        }
        return '/l/free-wordpress-starter-kit';
    }

    public function stages()
    {
        return $this->hasMany(\App\Models\FunnelStage::class)->orderBy('order');
    }

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'funnel_leads')
            ->withPivot('stage_id', 'entered_at', 'exited_at', 'converted');
    }

    public function getConversionRateAttribute()
    {
        $total = $this->leads()->count();
        if ($total === 0) return 0;
        $converted = $this->leads()->wherePivot('converted', true)->count();
        return round(($converted / $total) * 100, 1);
    }

    public function getDefaultScorePerPage()
    {
        return $this->score_per_page ?? 5;
    }

    public function getDefaultScorePerEmail()
    {
        return $this->score_per_email ?? 10;
    }

    public function getDefaultScorePerClick()
    {
        return $this->score_per_click ?? 20;
    }

    public function getDefaultScorePerCheckout()
    {
        return $this->score_per_checkout ?? 50;
    }

    public function getHotThreshold()
    {
        return $this->score_hot_threshold ?? 100;
    }

    public function isLeadHot($score)
    {
        return $score >= $this->getHotThreshold();
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    public static function getTemplateCategories()
    {
        return [
            'lead_magnet' => 'Lead Magnet',
            'tripwire' => 'Tripwire',
            'webinar' => 'Webinar',
            'launch' => 'Product Launch',
            'affiliate' => 'Affiliate',
        ];
    }

    public static function getTemplateData()
    {
        return [
            [
                'name' => 'Lead Magnet Funnel',
                'description' => 'Capture leads with a free resource, then nurture them with an email sequence to build trust and drive conversions.',
                'template_category' => 'lead_magnet',
                'stages' => 4,
                'estimated_conversion_rate' => '15-25%',
            ],
            [
                'name' => 'Tripwire Funnel',
                'description' => 'Drive traffic to a low-cost entry product, then upsell to higher-value offers throughout the funnel.',
                'template_category' => 'tripwire',
                'stages' => 3,
                'estimated_conversion_rate' => '20-35%',
            ],
            [
                'name' => 'Webinar Funnel',
                'description' => 'Register attendees for a live or evergreen webinar, then sell your offer during and after the presentation.',
                'template_category' => 'webinar',
                'stages' => 5,
                'estimated_conversion_rate' => '10-20%',
            ],
            [
                'name' => 'Product Launch Funnel',
                'description' => 'Build anticipation with a pre-launch sequence, create urgency during launch, and maximize conversions with bonuses.',
                'template_category' => 'launch',
                'stages' => 6,
                'estimated_conversion_rate' => '8-15%',
            ],
            [
                'name' => 'Affiliate Promo Funnel',
                'description' => 'Quickly promote affiliate products with a streamlined funnel optimized for affiliate link clicks and conversions.',
                'template_category' => 'affiliate',
                'stages' => 3,
                'estimated_conversion_rate' => '12-22%',
            ],
        ];
    }
}