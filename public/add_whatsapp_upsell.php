<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailSequence;
use App\Models\SequenceStep;

header('Content-Type: text/plain');
echo "=== Adding WhatsApp upsell to Email Templates sequence ===\n\n";

try {
    $sequence = EmailSequence::where('name', 'LIKE', '%Post Purchase%')
        ->orWhere('name', 'LIKE', '%Email Templates Pack%')
        ->first();
    
    if (!$sequence) {
        echo "Sequence not found!\n";
        exit;
    }
    
    echo "Found sequence: {$sequence->name} (ID: {$sequence->id})\n";
    
    // Add upsell step
    $step = SequenceStep::create([
        'sequence_id' => $sequence->id,
        'step_order' => 6,
        'delay_days' => 7,
        'subject' => 'Add WhatsApp to your marketing mix',
        'body' => "Hi {{name}},

Hope the email templates are going well!

Here's something that can multiply your results:

Add WHATSAPP to your marketing mix.

Customers who use WhatsApp + Email together see 2-3x better engagement than email alone.

Get the WhatsApp Marketing Bundle:
https://www.joala.com.ng/whatsapp-marketing-bundle

Includes:
- 12 Broadcast templates
- 8 Auto-replies
- 10 Status templates
- 6 Chatbot flows
- Business profile guide

Special price: ₦8,000 (instead of ₦15,000)

Questions? Reply to this email!

To your success,

Jome
joala.com.ng",
    ]);
    
    echo "Added WhatsApp upsell step!\n";
    echo "=== DONE ===\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}