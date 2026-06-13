<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SequenceStep;

header('Content-Type: text/plain');
echo "=== Updating Email Sequence with Upsell Links ===\n\n";

$step4 = SequenceStep::where('sequence_id', 2)->where('step_order', 4)->first();
$step4->update([
    'body' => "Hi {{name}},

I have to share this...

Sarah runs an online store in Lagos. Last month, she was losing ₦50,000+ every week to cart abandonment.

She implemented the CART ABANDONMENT template from her pack.

In just 2 weeks? She recovered ₦180,000 in lost sales.

That is the power of good email marketing.

Want to accelerate your results?

Check out our Done-For-You Email Service:
https://www.joala.com.ng/done-for-you

We build your complete email marketing system while you focus on your business.

No pressure - but this offer is only available for a limited time.

To your success,

Jome

---
JoAla Ventures
www.joala.com.ng"
]);
echo "Updated Day 4: Social proof + Upsell\n";

$step5 = SequenceStep::where('sequence_id', 2)->where('step_order', 5)->first();
$step5->update([
    'body' => "Hi {{name}},

By now you have seen the power of automated emails.

What if you could set up COMPLETE email sequences that run on autopilot?

We offer two options:

OPTION 1: Premium Bundle (₦65,000)
- Email Templates Pack + Done-For-You Setup + Priority Support
- https://www.joala.com.ng/premium-bundle

OPTION 2: Done-For-You Service (₦150,000)
- 3 Custom sequences built for you
- Full implementation & testing
- 30 days support
- https://www.joala.com.ng/done-for-you

Both options include setup assistance. Choose what works best for you.

Reply \"PREMIUM\" for the bundle or \"SERVICE\" for the full service.

To your success,

Jome

---
JoAla Ventures
www.joala.com.ng"
]);
echo "Updated Day 5: Main Upsell with links\n";

echo "\n=== Done ===\n";