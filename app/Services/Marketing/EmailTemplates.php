<?php

namespace App\Services\Marketing;

class EmailTemplates
{
    public static function getTemplates(): array
    {
        return [
            'welcome' => [
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{name}}!',
                'body' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; }
        .logo { font-size: 24px; font-weight: bold; color: #2563eb; }
        .content { background: #fff; padding: 30px; border: 1px solid #e5e5e5; border-radius: 8px; }
        .button { display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Joala Ventures</div>
    </div>
    <div class="content">
        <h2>Welcome, {{name}}!</h2>
        <p>Thank you for joining us. We\'re excited to have you on board!</p>
        <p>Here\'s what you can expect from us:</p>
        <ul>
            <li>Regular updates and insights</li>
            <li>Exclusive content and resources</li>
            <li>Special offers and promotions</li>
        </ul>
        <p>If you have any questions, feel free to reply to this email.</p>
        <a href="{{site_url}}" class="button">Visit Our Website</a>
    </div>
    <div class="footer">
        <p>&copy; {{year}} Joala Ventures. All rights reserved.</p>
        <p>You received this email because you subscribed to our newsletter.</p>
    </div>
</body>
</html>',
            ],
            
            'follow_up_1' => [
                'name' => 'Follow Up - Day 1',
                'subject' => 'Following up on your interest',
                'body' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .content { background: #fff; padding: 30px; border: 1px solid #e5e5e5; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="content">
        <h2>Hi {{name}},</h2>
        <p>I wanted to follow up on your recent signup. Hope you\'re finding our content helpful!</p>
        <p>If you have any questions about our services, feel free to reply to this email. I\'d love to help.</p>
        <p>Best regards,<br>The Joala Ventures Team</p>
    </div>
</body>
</html>',
            ],
            
            'newsletter' => [
                'name' => 'Newsletter',
                'subject' => '{{subject}}',
                'body' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 1px solid #e5e5e5; }
        .content { padding: 30px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; border-top: 1px solid #e5e5e5; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Joala Ventures Newsletter</h1>
    </div>
    <div class="content">
        <h2>{{title}}</h2>
        {{content}}
    </div>
    <div class="footer">
        <p>&copy; {{year}} Joala Ventures.</p>
        <p><a href="{{unsubscribe_url}}">Unsubscribe</a></p>
    </div>
</body>
</html>',
            ],
            
            'promotion' => [
                'name' => 'Promotional',
                'subject' => 'Special Offer: {{offer}}',
                'body' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .hero { background: linear-gradient(135deg, #1e3a5f, #2563eb); color: white; padding: 40px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
        .content { background: #fff; padding: 30px; border: 1px solid #e5e5e5; border-radius: 8px; }
        .button { display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="hero">
        <h2>Special Offer!</h2>
        <p>{{offer}}</p>
    </div>
    <div class="content">
        <p>Hi {{name}},</p>
        <p>{{description}}</p>
        <a href="{{cta_url}}" class="button">{{cta_text}}</a>
        <p>Offer expires: {{expiry_date}}</p>
    </div>
</body>
</html>',
            ],
        ];
    }
    
    public static function getTemplate(string $key): ?array
    {
        $templates = self::getTemplates();
        return $templates[$key] ?? null;
    }
}