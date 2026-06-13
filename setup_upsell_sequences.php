<?php
/**
 * Setup Post-Purchase Sequences for Upsell Products
 * Run: https://www.joala.com.ng/setup_upsell_sequences.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailTemplate;
use App\Models\EmailSequence;
use App\Models\SequenceStep;
use Illuminate\Support\Facades\DB;

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "=== Setting Up Upsell Post-Purchase Sequences ===\n\n";

// ============ PREMIUM BUNDLE SEQUENCE ============
echo "1. Creating Premium Bundle Post-Purchase Sequence...\n";

$premiumTemplates = [
    [
        'name' => 'Premium Bundle - Thank You',
        'subject' => '🎉 Thank you for your Premium Bundle order!',
        'body' => "Hi {{name}},

Thank you for purchasing the Email Marketing Premium Bundle!

Your download link:
https://joala.com.ng/downloads/premium-bundle.html

This includes:
✓ Email Templates Pack (24 templates)
✓ Done-For-You Setup Service
✓ Priority Support Access
✓ Bonus Materials

Your Done-For-You setup: We'll contact you within 24 hours to schedule your strategy call.

Questions? Reply to this email.

To your success,

Jome Ala
Founder, JoAla Ventures
joala.com.ng",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
    [
        'name' => 'Premium Bundle - Setup Reminder',
        'subject' => "Let's schedule your Done-For-You setup",
        'body' => "Hi {{name}},

I noticed you haven't scheduled your Done-For-You setup session yet.

Here's what happens next:
1. We have a 30-minute strategy call
2. We build 3 custom email sequences for you
3. We test and optimize everything

Ready to get started? Just reply with your preferred date and time.

No pressure - the templates are yours to keep either way.

Talk soon,

Jome",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
    [
        'name' => 'Premium Bundle - Quick Start',
        'subject' => 'Quick start guide for your templates',
        'body' => "Hi {{name}},

While we schedule your Done-For-You session, here's how to get started with your templates:

STEP 1: Download the pack
STEP 2: Choose ONE template to start with
STEP 3: Replace the placeholders
STEP 4: Send!

Pro tip: Start with the WELCOME email - it's the highest converting.

Need help? Reply \"HELP\" and I'll assist.

To your success,

Jome",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
    [
        'name' => 'Premium Bundle - Upsell',
        'subject' => 'Want to take it to the next level?',
        'body' => "Hi {{name}},

By now you've seen what the templates can do.

But what if you could have a COMPLETE email marketing system?

Our Done-For-You Email Automation service includes:
- 5 Custom sequences (instead of 3)
- Full marketing strategy
- 60 days support (instead of 30)
- Monthly optimization calls

This is our PREMIUM service for serious businesses.

Reply \"UPGRADE\" to learn more.

Or if you're happy with what you have - no worries!

To your success,

Jome

---
JoAla Ventures
joala.com.ng",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
];

foreach($premiumTemplates as $t) {
    EmailTemplate::updateOrCreate(['name' => $t['name']], $t);
    echo "  Created: " . $t['name'] . "\n";
}

// Create Premium Bundle Sequence
$premiumSeq = EmailSequence::updateOrCreate(
    ['name' => 'Post-Purchase - Premium Bundle'],
    [
        'description' => 'Post-purchase sequence for Premium Bundle buyers',
        'is_active' => 1,
    ]
);
echo "  Sequence ID: " . $premiumSeq->id . "\n";

// Create Premium Sequence Steps
$premiumSteps = [
    ['subject' => 'Thank you for your Premium Bundle order!', 'delay_days' => 0, 'step_order' => 1],
    ['subject' => "Let's schedule your Done-For-You setup", 'delay_days' => 2, 'step_order' => 2],
    ['subject' => 'Quick start guide for your templates', 'delay_days' => 4, 'step_order' => 3],
    ['subject' => 'Want to take it to the next level?', 'delay_days' => 7, 'step_order' => 4],
];

SequenceStep::where('sequence_id', $premiumSeq->id)->delete();
foreach($premiumSteps as $i => $s) {
    $s['sequence_id'] = $premiumSeq->id;
    $s['body'] = $premiumTemplates[$i]['body'];
    SequenceStep::create($s);
    echo "  Step " . $s['step_order'] . " (Day " . $s['delay_days'] . ")\n";
}

// ============ DONE-FOR-YOU SERVICE SEQUENCE ============
echo "\n2. Creating Done-For-You Service Post-Purchase Sequence...\n";

$dfyTemplates = [
    [
        'name' => 'Done-For-You - Thank You + Next Steps',
        'subject' => "🎯 Let's get started on your email system!",
        'body' => "Hi {{name}},

Thank you for booking our Done-For-You Email Automation Service!

This is going to be amazing. Here's what happens next:

STEP 1: Discovery Call (30 minutes)
We'll learn about your business, audience, and goals.

STEP 2: We Build (5-7 days)
We create 3 custom email sequences tailored to your business.

STEP 3: Testing & Optimization
We test everything and make adjustments.

STEP 4: Handover
Complete documentation and training.

I'll contact you within 24 hours to schedule your discovery call.

In the meantime, reply with:
- Your website URL
- Your main email marketing goal

See you soon!

Jome Ala
Founder, JoAla Ventures",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
    [
        'name' => 'Done-For-You - Discovery Call Reminder',
        'subject' => "Your discovery call details",
        'body' => "Hi {{name}},

Just a reminder about your upcoming discovery call.

What we'll cover:
- Your current email marketing setup
- Your target audience
- Your goals and challenges
- The best email strategy for your business

To prepare, think about:
1. What are your main email marketing challenges?
2. What's one goal you want to achieve in the next 90 days?

See you on the call!

Jome",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
    [
        'name' => 'Done-For-You - What to Expect',
        'subject' => "What to expect from your new email system",
        'body' => "Hi {{name}},

Exciting news! We're building something great for you.

Here's what your complete email system will include:

✓ Welcome Sequence (3-5 emails)
✓ Cart Abandonment Recovery (2-3 emails)  
✓ Re-engagement Sequence (3-4 emails)
✓ Post-Purchase Follow-up (2-3 emails)
✓ All connected to your email provider

The average results for our Done-For-You clients:
- 40% increase in email open rates
- 25% recovery on abandoned carts
- 3x more repeat customers

Questions before our next call? Just reply.

To your success,

Jome",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
    [
        'name' => 'Done-For-You - Testimonial Request',
        'subject' => "Can you help others?",
        'body' => "Hi {{name}},

I hope your new email system is working amazingly!

If you're happy with the results, would you mind sharing a testimonial?

Your feedback helps other Nigerian entrepreneurs make informed decisions.

Just reply with:
- A few sentences about your experience
- Permission to use your name and business name

Thank you in advance!

And remember - if you ever need anything, I'm just an email away.

To your success,

Jome

---
JoAla Ventures
joala.com.ng",
        'type' => 'post_purchase',
        'is_active' => 1,
    ],
];

foreach($dfyTemplates as $t) {
    EmailTemplate::updateOrCreate(['name' => $t['name']], $t);
    echo "  Created: " . $t['name'] . "\n";
}

// Create Done-For-You Sequence
$dfySeq = EmailSequence::updateOrCreate(
    ['name' => 'Post-Purchase - Done-For-You Service'],
    [
        'description' => 'Post-purchase sequence for Done-For-You service buyers',
        'is_active' => 1,
    ]
);
echo "  Sequence ID: " . $dfySeq->id . "\n";

// Create DFDY Sequence Steps
$dfySteps = [
    ['subject' => "Let's get started on your email system!", 'delay_days' => 0, 'step_order' => 1],
    ['subject' => 'Your discovery call details', 'delay_days' => 1, 'step_order' => 2],
    ['subject' => 'What to expect from your new email system', 'delay_days' => 3, 'step_order' => 3],
    ['subject' => 'Can you help others?', 'delay_days' => 14, 'step_order' => 4],
];

SequenceStep::where('sequence_id', $dfySeq->id)->delete();
foreach($dfySteps as $i => $s) {
    $s['sequence_id'] = $dfySeq->id;
    $s['body'] = $dfyTemplates[$i]['body'];
    SequenceStep::create($s);
    echo "  Step " . $s['step_order'] . " (Day " . $s['delay_days'] . ")\n";
}

// Create sequences table records
DB::table('sequences')->updateOrInsert(['id' => $premiumSeq->id], ['name' => 'Post-Purchase - Premium Bundle', 'created_at' => now(), 'updated_at' => now()]);
DB::table('sequences')->updateOrInsert(['id' => $dfySeq->id], ['name' => 'Post-Purchase - Done-For-You Service', 'created_at' => now(), 'updated_at' => now()]);

echo "\n=== DONE! ===\n";
echo "Premium Bundle Sequence: " . $premiumSeq->id . "\n";
echo "Done-For-You Sequence: " . $dfySeq->id . "\n";
echo "\nTo test: Make a purchase and check email queue\n";
echo "</pre>";