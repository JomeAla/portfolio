<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    
    protected $fillable = [
        'name',
        'subject',
        'body',
        'description',
        'is_active',
        'category',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function categories(): array
    {
        return [
            'welcome' => 'Welcome',
            'follow_up' => 'Follow Up',
            'newsletter' => 'Newsletter',
            'promotional' => 'Promotional',
            'transactional' => 'Transactional',
            'notification' => 'Notification',
        ];
    }

    public static function variables(): array
    {
        return [
            '{{name}}' => 'Recipient name',
            '{{email}}' => 'Recipient email',
            '{{site_url}}' => 'Website URL',
            '{{year}}' => 'Current year',
            '{{date}}' => 'Current date',
            '{{unsubscribe_url}}' => 'Unsubscribe URL',
            '{{subject}}' => 'Custom subject',
            '{{title}}' => 'Content title',
            '{{content}}' => 'Content body',
            '{{cta_url}}' => 'CTA button URL',
            '{{cta_text}}' => 'CTA button text',
            '{{offer}}' => 'Offer details',
            '{{expiry_date}}' => 'Expiry date',
        ];
    }

    public static function getDefaultTemplates(): array
    {
        return [
            [
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{name}}!',
                'body' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .content { background: #fff; padding: 30px; border: 1px solid #e5e5e5; border-radius: 8px; }
        .button { display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="content">
        <h2>Welcome, {{name}}!</h2>
        <p>Thank you for joining us!</p>
        <a href="{{site_url}}" class="button">Visit Our Website</a>
    </div>
</body>
</html>',
                'category' => 'welcome',
                'description' => 'Welcome email for new subscribers',
            ],
            [
                'name' => 'Newsletter',
                'subject' => '{{subject}}',
                'body' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <h2>{{title}}</h2>
    {{content}}
</body>
</html>',
                'category' => 'newsletter',
                'description' => 'Newsletter template',
            ],
        ];
    }
}