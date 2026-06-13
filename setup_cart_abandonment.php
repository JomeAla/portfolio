<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailSequence;
use App\Models\SequenceStep;
use App\Models\AutomationRule;
use App\Models\Lead;
use App\Models\EmailQueue;

echo "Setting up Cart Abandonment Recovery Sequence...\n\n";

// Check if sequence already exists
$existingSequence = EmailSequence::where('trigger_type', 'cart_abandonment')->first();

if ($existingSequence) {
    echo "Cart abandonment sequence already exists (ID: {$existingSequence->id})\n";
    $sequence = $existingSequence;
} else {
    // Create the sequence
    $sequence = EmailSequence::create([
        'name' => 'Cart Abandonment Recovery',
        'description' => 'Automated sequence to recover abandoned shopping carts - sends reminder after 1 hour and 24 hours',
        'trigger_type' => 'cart_abandonment',
        'is_active' => true,
    ]);
    
    echo "Created sequence: {$sequence->name} (ID: {$sequence->id})\n";
    
    // Step 1: 1 Hour reminder
    $step1 = SequenceStep::create([
        'sequence_id' => $sequence->id,
        'step_number' => 1,
        'step_order' => 1,
        'subject' => 'Did you forget something? Your cart is waiting!',
        'body' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4F46E5; color: white; padding: 20px; text-align: center; }
        .content { background: #fff; padding: 20px; border: 1px solid #e5e5e5; }
        .button { display: inline-block; background: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>You Left Something Behind!</h1>
        </div>
        <div class="content">
            <p>Hi {{customer_name}},</p>
            <p>We noticed you left our store without completing your purchase. Your items are still waiting for you!</p>
            
            <div style="background: #f9f9f9; padding: 15px; margin: 20px 0;">
                <h3>Your Cart Items:</h3>
                {{cart_items}}
            </div>
            
            <p><strong>Total:</strong> {{cart_total}}</p>
            
            <center>
                <a href="{{checkout_url}}" class="button">Complete Your Purchase</a>
            </center>
            
            <p>Need help? Reply to this email or contact us at support@joala.com.ng</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 JoAla Ventures. All rights reserved.</p>
            <p><a href="{{unsubscribe_url}}">Unsubscribe</a></p>
        </div>
    </div>
</body>
</html>
HTML
,
        'delay_hours' => 1,
    ]);
    echo "Created Step 1: 1-hour reminder\n";
    
    // Step 2: 24 Hour final reminder
    $step2 = SequenceStep::create([
        'sequence_id' => $sequence->id,
        'step_number' => 2,
        'step_order' => 2,
        'subject' => 'Last Chance! Your cart expires soon',
        'body' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #DC2626; color: white; padding: 20px; text-align: center; }
        .content { background: #fff; padding: 20px; border: 1px solid #e5e5e5; }
        .button { display: inline-block; background: #DC2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .offer { background: #FEF3C7; border: 2px solid #F59E0B; padding: 15px; margin: 20px 0; text-align: center; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Final Reminder!</h1>
        </div>
        <div class="content">
            <p>Hi {{customer_name}},</p>
            
            <div class="offer">
                <h3>Special Offer!</h3>
                <p>Complete your purchase in the next 24 hours and get <strong>10% OFF</strong> your order!</p>
                <p>Use code: <strong>COMEBACK10</strong></p>
            </div>
            
            <div style="background: #f9f9f9; padding: 15px; margin: 20px 0;">
                <h3>Your Cart Items:</h3>
                {{cart_items}}
            </div>
            
            <p><strong>Total:</strong> {{cart_total}}</p>
            <p><strong>With 10% OFF:</strong> {{discounted_total}}</p>
            
            <center>
                <a href="{{checkout_url}}?coupon=COMEBACK10" class="button">Complete Purchase & Save</a>
            </center>
            
            <p>This is your last chance - your cart will expire soon!</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 JoAla Ventures. All rights reserved.</p>
            <p><a href="{{unsubscribe_url}}">Unsubscribe</a></p>
        </div>
    </div>
</body>
</html>
HTML
,
        'delay_hours' => 24,
    ]);
    echo "Created Step 2: 24-hour reminder\n";
}

// Create automation rule
$existingAutomation = AutomationRule::where('trigger_type', 'cart_abandoned')->first();

if (!$existingAutomation) {
    AutomationRule::create([
        'name' => 'Cart Abandonment Trigger',
        'trigger_type' => 'cart_abandoned',
        'trigger_event' => 'order.cart_abandoned',
        'action_type' => 'enroll_sequence',
        'action_sequence_id' => $sequence->id,
        'is_active' => true,
    ]);
    echo "Created automation rule\n";
}

echo "Cart Abandonment Recovery Sequence is now active!\n";
echo "The system will automatically:\n";
echo "  1. Send first reminder 1 hour after cart is abandoned\n";
echo "  2. Send final reminder 24 hours after cart is abandoned\n";
echo "\nMake sure to run the email scheduler cron job:\n";
echo "  * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1\n";