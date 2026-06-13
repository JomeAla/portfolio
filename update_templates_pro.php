<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailTemplate;

$templates = [
    1 => [
        'name' => 'Checklist Download - Instant Delivery',
        'subject' => 'Your Download Link - Email Marketing Checklist',
        'body' => "Hi {{name}},

Thank you for downloading the Email Marketing Checklist!

Your download link:
https://www.joala.com.ng/downloads/email-marketing-checklist-templates.html

This 12-page guide includes:
- Complete email marketing checklist (printable)
- Welcome email template (68% open rate)
- Cart abandonment template (35% recovery)
- Re-engagement template (22% engagement)

Save this email - you will refer to it often.

Quick question: What is your biggest challenge with email marketing? Just hit reply and let me know. I read every response.

To your success,

Jome Ala
Founder, JoAla Ventures
joala.com.ng",
    ],
    2 => [
        'name' => 'Welcome - Lead Magnet Nurture',
        'subject' => 'Welcome! Let me share something valuable...',
        'body' => "Hi {{name}},

Welcome aboard! 

I am Jome Ala, founder of JoAla Ventures. I personally read every email that lands in my inbox.

Here is what you will get from me:
- Practical strategies to grow your business
- Real case studies from Nigerian entrepreneurs
- Results-driven marketing tips that actually work

Now, go through that checklist you downloaded.

If I could suggest ONE action: Start with the subject lines.

Why? Your subject line determines if your email even gets opened.

Here is my tested formula:
[Curiosity] + [Specific Benefit] = Opens

Example: The mistake costing you N50,000/month

That triggers curiosity + shows specific value = open.

Try this and let me know how your open rates change.

Questions? Reply directly. I respond to every email.

To your success,

Jome Ala
Founder, JoAla Ventures",
    ],
    3 => [
        'name' => 'Nurture Day 2 - Value Tip',
        'subject' => 'The #1 email mistake costing you sales',
        'body' => "Hi {{name}},

I want to share something I see costing Nigerian businesses thousands every month...

Most business owners send emails that are ALL about selling:
Buy now!
Limited offer!
Last chance!

The truth? People are tired of being sold to.

The solution: The 80/20 rule
80% value
20% offer

Share 4 helpful tips WITHOUT asking for anything.
Then 1 soft offer.

Your readers will start to trust you. And when you DO have an offer? They will actually open it.

Try this: Send your next email focusing ONLY on helping your readers.

Reply and let me know: What is one helpful thing you could share with your audience?

I read every response.

To your success,

Jome",
    ],
    4 => [
        'name' => 'Nurture Day 4 - Soft Offer',
        'subject' => 'I made something for you...',
        'body' => "Hi {{name}},

Hope you are having a great week!

I created something that might help accelerate your email marketing...

Remember the checklist I sent you? It covers the fundamentals.

But many people have asked: Can I get the full system?

So I put together the Email Sequence Templates Pack:

- 10 professionally written email templates
- Detailed setup instructions for each
- Automation workflow guide
- Examples from Nigerian businesses

Current price: N15,000 (one-time)
Instant download

Get it here:
https://www.joala.com.ng/store/email-sequence-templates-pack

No pressure at all. Just letting you know it is available.

Questions? Reply. I am happy to help.

To your success,

Jome",
    ],
    5 => [
        'name' => 'Nurture Day 7 - Social Proof',
        'subject' => 'What Nigerian entrepreneurs are achieving',
        'body' => "Hi {{name}},

Quick update...

A client recovered N180,000 in lost sales LAST MONTH using just one template from the pack.

The cart abandonment email.

That is the power of automated email marketing.

If you have not tried any templates yet, start here:

1. Set up a welcome email for new subscribers
2. Add cart abandonment (if you sell online)
3. Track your open rates weekly
4. Test and improve

Small consistent actions = big results over time.

What is currently holding you back? Reply and let me know. Maybe I can help.

To your success,

Jome

---
JoAla Ventures
Transforming Nigerian Businesses
www.joala.com.ng",
    ],
];

foreach($templates as $id => $template) {
    EmailTemplate::where('id', $id)->update([
        'subject' => $template['subject'],
        'body' => $template['body'],
    ]);
    echo "Updated template $id\n";
}

echo "Done!";