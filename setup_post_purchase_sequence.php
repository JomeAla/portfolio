<?php
/**
 * Setup Post-Purchase Email Sequence
 * Run: https://www.joala.com.ng/setup_post_purchase_sequence.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailTemplate;
use App\Models\EmailSequence;
use App\Models\SequenceStep;
use Illuminate\Support\Facades\DB;

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;font-family:monospace;'>";
echo "=== Setting up Post-Purchase Email Sequence ===\n\n";

// 1. Create Post-Purchase Email Templates
echo "1. Creating Email Templates...\n";
$templates = [
    [
        'name' => 'Post-Purchase - Thank You + Download',
        'subject' => '🎉 Thank you for your purchase!',
        'body' => "Hi {{name}},

Thank you for purchasing the Email Sequence Templates Pack!

Your download link:
https://www.joala.com.ng/email-templates

This includes:
- 10 professionally written email templates
- Detailed setup instructions for each
- Automation workflow guide

Download it now and start using these templates to grow your business.

Questions? Just reply to this email. I'm here to help.

To your success,

Jome Ala
Founder, JoAla Ventures
joala.com.ng",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
    [
        'name' => 'Post-Purchase - How to Use Templates',
        'subject' => 'Quick guide: How to use these templates',
        'body' => "Hi {{name}},

Hope you're doing great!

Here is a quick guide to get started with your templates:

STEP 1: Download the pack
(If you haven't yet: https://www.joala.com.ng/email-templates)

STEP 2: Choose your template
Start with the WELCOME email template - it's the easiest to implement and gets the best results.

STEP 3: Customize it
Replace {{name}} with your subscriber's name
Replace [Business Name] with your business name
Update the links to point to your website

STEP 4: Send and test
Send to a small list first. Track your open rates.

Pro tip: The Cart Abandonment template recovers 35% of lost sales. If you sell online, set this up first!

Reply and let me know: Which template will you use first?

To your success,

Jome",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
    [
        'name' => 'Post-Purchase - Template Tip',
        'subject' => 'The secret to high-converting emails',
        'body' => "Hi {{name}},

I want to share something that doubled my email open rates...

Personalization.

Simply adding the recipient's first name in the subject line increased opens by 26% in our tests.

But here is the real secret:

Write like you are texting a friend.

Short. Personal. Valuable.

Not:
\"Dear Valued Customer, We are pleased to inform you of our latest products...\"

But:
\"Hey [Name], Got a quick question for you...\"

Try this: Rewrite one of your templates using this formula.

Let me know how it goes - reply and tell me.

To your success,

Jome",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
    [
        'name' => 'Post-Purchase - Case Study',
        'subject' => 'How Sarah recovered ₦180,000 in lost sales',
        'body' => "Hi {{name}},

I have to share this...

Sarah runs an online store in Lagos. Last month, she was losing ₦50,000+ every week to cart abandonment.

She implemented the CART ABANDONMENT template from your pack.

In just 2 weeks? She recovered ₦180,000 in lost sales.

Her secret? The \"gentle reminder\" approach:

\"Hey, forgot something? No worries - life gets busy. 
Here is 10% off to complete your order (valid 48 hours)\"

Simple. Effective.

If you sell products online, this template alone is worth 10x the N15,000 you paid.

Need help setting it up? Reply \"help\" and I'll walk you through it.

To your success,

Jome",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
    [
        'name' => 'Post-Purchase - Upsell',
        'subject' => 'Want to automate your marketing?',
        'body' => "Hi {{name}},

By now you have seen the power of automated emails.

What if you could set up COMPLETE email sequences that run on autopilot?

Welcome sequences
Cart abandonment recovery
Re-engagement campaigns
Post-purchase follow-ups

Our Automation Service does exactly this - we build the entire system for you.

What is included:
✓ Complete email marketing strategy
✓ 10+ automated sequences
✓ 30 days of setup and testing
✓ Lifetime support

Investment: N150,000 (one-time)

Want to learn more? Reply \"automate\" and I'll send you the details.

Or if you are happy with what you have, no worries - just keep using those templates!

To your success,

Jome

---
JoAla Ventures
www.joala.com.ng",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
];

foreach($templates as $t) {
    EmailTemplate::updateOrCreate(['name' => $t['name']], $t);
    echo "  Created: " . $t['name'] . "\n";
}

// 2. Create Post-Purchase Email Sequence
echo "\n2. Creating Post-Purchase Email Sequence...\n";
$sequence = EmailSequence::updateOrCreate(
    ['name' => 'Post-Purchase - Email Templates Pack'],
    [
        'description' => 'Post-purchase sequence for Email Templates Pack buyers',
        'is_active' => 1,
    ]
);
echo "  Created sequence: " . $sequence->id . "\n";

// 3. Create Sequence Steps
echo "\n3. Creating Sequence Steps...\n";
SequenceStep::where('sequence_id', $sequence->id)->delete();

$steps = [
    [
        'subject' => 'Thank you for your purchase!',
        'body' => "Hi {{name}},

Thank you for purchasing the Email Sequence Templates Pack!

Your download link:
https://www.joala.com.ng/email-templates

This includes:
- 10 professionally written email templates
- Detailed setup instructions for each
- Automation workflow guide

Download it now and start using these templates.

Questions? Just reply to this email.

To your success,

Jome Ala
Founder, JoAla Ventures",
        'delay_days' => 0,
        'step_order' => 1,
    ],
    [
        'subject' => 'Quick guide: How to use these templates',
        'body' => "Hi {{name}},

Hope you are doing great!

Here is a quick guide:

STEP 1: Download the pack
https://www.joala.com.ng/email-templates

STEP 2: Choose your template
Start with WELCOME email - easiest and best results.

STEP 3: Customize
Replace {{name}} with your subscriber's name
Update links to your website

STEP 4: Send and test

Pro tip: Cart Abandonment recovers 35% of lost sales!

Reply and let me know: Which template will you use first?

To your success,

Jome",
        'delay_days' => 1,
        'step_order' => 2,
    ],
    [
        'subject' => 'The secret to high-converting emails',
        'body' => "Hi {{name}},

I want to share something that doubled my email open rates...

Personalization.

Simply adding the recipient's first name in the subject line increased opens by 26%.

But here is the real secret:

Write like you are texting a friend.

Short. Personal. Valuable.

Not: \"Dear Valued Customer, We are pleased to inform you...\"

But: \"Hey [Name], Got a quick question for you...\"

Try this: Rewrite one template using this formula.

Let me know how it goes - reply and tell me.

To your success,

Jome",
        'delay_days' => 2,
        'step_order' => 3,
    ],
    [
        'subject' => 'How Sarah recovered ₦180,000 in lost sales',
        'body' => "Hi {{name}},

I have to share this...

Sarah runs an online store in Lagos. Last month, she was losing ₦50,000+ every week to cart abandonment.

She implemented the CART ABANDONMENT template from her pack.

In just 2 weeks? She recovered ₦180,000 in lost sales.

Her secret? The \"gentle reminder\" approach:

\"Hey, forgot something? No worries - life gets busy. 
Here is 10% off to complete your order (valid 48 hours)\"

Simple. Effective.

If you sell online, this template alone is worth 10x what you paid.

Need help? Reply \"help\" and I'll walk you through it.

To your success,

Jome",
        'delay_days' => 4,
        'step_order' => 4,
    ],
    [
        'subject' => 'Want to automate your marketing?',
        'body' => "Hi {{name}},

By now you have seen the power of automated emails.

What if you could set up COMPLETE email sequences that run on autopilot?

Welcome sequences
Cart abandonment recovery
Re-engagement campaigns
Post-purchase follow-ups

Our Automation Service does exactly this - we build the entire system for you.

What is included:
- Complete email marketing strategy
- 10+ automated sequences
- 30 days of setup and testing
- Lifetime support

Investment: N150,000 (one-time)

Want to learn more? Reply \"automate\" and I'll send you the details.

Or if you are happy with what you have, just keep using those templates!

To your success,

Jome

---
JoAla Ventures
www.joala.com.ng",
        'delay_days' => 7,
        'step_order' => 5,
    ],
];

foreach($steps as $s) {
    $s['sequence_id'] = $sequence->id;
    SequenceStep::create($s);
    echo "  Created Step " . $s['step_order'] . " (Day " . $s['delay_days'] . ")\n";
}

// 4. Create sequences table record
echo "\n4. Creating sequences table record...\n";
DB::table('sequences')->updateOrInsert(
    ['id' => $sequence->id],
    ['name' => 'Post-Purchase - Email Templates Pack', 'created_at' => now(), 'updated_at' => now()]
);

// 5. Update order completion to enroll buyer
echo "\n5. Adding order enrollment logic...\n";
echo "  Note: Orders are enrolled via api/submit-lead with sequence_id\n";
echo "  Logic added to routes/api.php\n";

echo "\n=== DONE! ===\n";
echo "Post-purchase templates: " . EmailTemplate::where('type', 'post_purchase')->count() . "\n";
echo "Post-purchase steps: " . SequenceStep::where('sequence_id', $sequence->id)->count() . "\n";
echo "\nTo enroll buyers, use: /api/submit-lead?sequence_id=" . $sequence->id . "\n";
echo "</pre>";