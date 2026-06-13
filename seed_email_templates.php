<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmailTemplate;

echo "Creating email templates...\n";

$templates = [
    [
        'name' => 'Checklist Download - Instant Delivery',
        'subject' => '🎁 Your Download Link - Email Marketing Checklist',
        'body' => "Hi {{name}},

Thank you for signing up! 

Here is your instant download link:
{{download_link}}

This PDF contains:
✅ Complete Email Marketing Checklist (12 pages)
✅ 3 Ready-to-use templates (Welcome, Cart Abandonment, Re-engagement)

Simply click the link above to download your copy.

To your success,

Jome Ala
Founder, JoAla Ventures

---
P.S. - Reply 'help' if you have any questions about using these templates.",
        'type' => 'lead_magnet',
        'is_active' => true,
    ],
    [
        'name' => 'Welcome Email - Lead Magnet Nurture',
        'subject' => '👋 Welcome! Here\'s what to expect...',
        'body' => "Hi {{name}},

Welcome aboard!

I'm Jome, founder of JoAla Ventures. I personally read every email that comes in.

Here's what you'll get from me:
• Practical tips to grow your business
• Real examples from Nigerian entrepreneurs  
• Occasional behind-the-scenes insights

Now, go through that checklist you downloaded. Take action on just ONE thing this week.

Questions? Just reply to this email. I'm here to help.

To your success,

Jome Ala
Founder, JoAla Ventures
Joala.com.ng",
        'type' => 'nurture',
        'is_active' => true,
    ],
    [
        'name' => 'Nurture Email - Day 2 - Value Tip',
        'subject' => 'The #1 email mistake most Nigerian businesses make',
        'body' => "Hi {{name}},

I wanted to share something that I see often...

Most businesses send emails that are ALL about selling.

The problem? People get tired of being sold to.

Try this instead:
Share one helpful tip WITHOUT asking for anything.

Example: 'Here are 3 ways to increase your email open rate...'

No link. No offer. Just value.

Your readers will start to trust you more. And when you DO have an offer, they'll actuallyopen it.

Try this and let me know how it goes.

Reply and tell me: What's one helpful thing you could share with your audience?

To your success,

Jome",
        'type' => 'nurture',
        'is_active' => true,
    ],
    [
        'name' => 'Nurture Email - Day 4 - Soft Offer',
        'subject' => '📦 Something you might find useful...',
        'body' => "Hi {{name}},

Hope you're having a great week!

I created something that might help you...

Remember that checklist I sent you? It covers the basics.

But many people have asked me: 'Can I get more?'

So I put together the full Email Sequence Templates Pack:
• 10 professional email templates
• Setup instructions for each
• Automation guide included

It's currently ₦15,000 (one-time payment).

You can check it out here:
https://www.joala.com.ng/store/email-sequence-templates-pack

No pressure at all. Just letting you know it's available.

If you have questions, just reply. I'm happy to help.

To your success,

Jome",
        'type' => 'nurture',
        'is_active' => true,
    ],
    [
        'name' => 'Nurture Email - Day 7 - Social Proof',
        'subject' => 'What other Nigerian entrepreneurs are achieving',
        'body' => "Hi {{name}},

Quick update...

A client just told me they recovered ₦180,000 in lost sales using the cart abandonment email template from your download pack.

That's the power of good email marketing.

If you haven't tried any of the templates yet, start with ONE:
1. Send the welcome email to new subscribers
2. Set up cart abandonment (if you have an online store)
3. Track your open rates

Small actions = big results over time.

What's holding you back? Reply and let me know. Maybe I can help.

To your success,

Jome

---
JoAla Ventures - Transforming Nigerian Businesses
www.joala.com.ng",
        'type' => 'nurture',
        'is_active' => true,
    ],
];

foreach ($templates as $template) {
    $existing = EmailTemplate::where('name', $template['name'])->first();
    if (!$existing) {
        EmailTemplate::create($template);
        echo "✅ Created: " . $template['name'] . "\n";
    } else {
        echo "⏭️  Already exists: " . $template['name'] . "\n";
    }
}

echo "\nDone! Created " . count($templates) . " email templates.\n";