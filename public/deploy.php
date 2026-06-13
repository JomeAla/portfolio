<?php
/**
 * Deployment Script
 * Upload to: /home/joalacom/public_html/portfolio/public/deploy.php
 * Access via: https://www.joala.com.ng/portfolio/public/deploy.php
 * DELETE after use for security!
 */

$host = $_SERVER['HTTP_HOST'] ?? '';
if (strpos($host, 'joala.com.ng') === false) {
    die('Access denied');
}

// UPDATE THIS PATH to match your actual portfolio installation
$portfolioPath = '/home/joalacom/public_html';

$output = [];
$returnCode = 0;

echo "<pre>\n";
echo "=== Starting Deployment ===\n\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Git pull - use reset to handle conflicts
echo "Running git pull...\n";
exec("cd $portfolioPath && git fetch origin 2>&1", $output);
echo implode("\n", $output) . "\n";

echo "Resetting to origin/master (handles local changes)...\n";
exec("cd $portfolioPath && git reset --hard origin/master 2>&1", $output, $returnCode);
echo implode("\n", $output) . "\n";

if ($returnCode !== 0) {
    echo "Git reset failed! Trying checkout method...\n";
    exec("cd $portfolioPath && git checkout -- . 2>&1 && git pull origin master 2>&1", $output, $returnCode);
    echo implode("\n", $output) . "\n";
    if ($returnCode !== 0) {
        echo "Git pull failed! Check server configuration.\n";
    }
}

// Clear caches
echo "\nClearing caches...\n";
exec("cd $portfolioPath && php artisan cache:clear 2>&1", $output);
exec("cd $portfolioPath && php artisan view:clear 2>&1", $output);
exec("cd $portfolioPath && php artisan config:clear 2>&1", $output);
echo "Done!\n";

// Run migrations
echo "\nRunning migrations...\n";
exec("cd $portfolioPath && php artisan migrate --force 2>&1", $output);
echo implode("\n", $output) . "\n";

// Update remaining product descriptions
echo "\nUpdating product descriptions...\n";
$descriptions = [
    'local-business-digital-kit' => [
        'Comprehensive digital toolkit for local businesses to attract more customers online.',
        '<h2 class="text-2xl font-bold text-slate-900 mb-4">Get More Customers Through Your Door</h2>
<p class="text-lg text-slate-600 mb-6">A practical, no-nonsense toolkit for local businesses ready to compete online. Focus on strategies that actually bring foot traffic and phone calls.</p>
<h3 class="text-xl font-semibold text-slate-800 mb-3">Local Growth Strategies</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Google Business Profile</strong> - Get listed and rank in local search</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Facebook Page Setup</strong> - Optimize for local discovery and engagement</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>WhatsApp Business</strong> - Turn messages into appointments</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Review Strategy</strong> - Get more 5-star reviews</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Local SEO</strong> - Target customers in your area</span></li>
</ul>
<h3 class="text-xl font-semibold text-slate-800 mb-3">What\'s Included</h3>
<ul class="space-y-2 mb-6">
<li>Step-by-step implementation guides</li>
<li>Ready-to-use templates</li>
<li>Social media content calendar</li>
<li>Local SEO checklist</li>
</ul>
<div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-lg">
<p class="text-orange-800 font-medium"><i class="fas fa-store mr-2"></i>Perfect for restaurants, salons, shops, service businesses.</p>
</div>'
    ],
    'course-creator-kit' => [
        'Everything you need to package and sell your expertise as an online course.',
        '<h2 class="text-2xl font-bold text-slate-900 mb-4">Turn Your Knowledge Into a Revenue Stream</h2>
<p class="text-lg text-slate-600 mb-6">From content creation to course delivery, this kit walks you through every step of launching a profitable online course. No tech skills required.</p>
<h3 class="text-xl font-semibold text-slate-800 mb-3">Course Creation Framework</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Topic Selection</strong> - Find your profitable course idea</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Curriculum Builder</strong> - Structure your content for maximum learning</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Recording Guide</strong> - Create professional videos with basic equipment</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Landing Page Template</strong> - Convert visitors to students</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Pricing Strategy</strong> - How to price your course for profit</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Launch Checklist</strong> - Everything to do before going live</span></li>
</ul>
<h3 class="text-xl font-semibold text-slate-800 mb-3">50+ Templates Included</h3>
<p class="text-slate-600 mb-4">Slide templates, worksheet templates, checklist templates, and more to speed up your course creation.</p>
<div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded-r-lg">
<p class="text-teal-800 font-medium"><i class="fas fa-play mr-2"></i>Includes Notion template for organizing your course content.</p>
</div>'
    ],
    'whatsapp-marketing-bundle' => [
        'Complete WhatsApp marketing toolkit with 48 ready-to-send templates for Nigerian businesses.',
        '<h2 class="text-2xl font-bold text-slate-900 mb-4">WhatsApp Marketing Made Easy</h2>
<p class="text-lg text-slate-600 mb-6">Stop staring at a blank screen. 48 proven WhatsApp message templates ready to copy, paste, and send. Perfect for Nigerian businesses.</p>
<h3 class="text-xl font-semibold text-slate-800 mb-3">What\'s Inside</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>48 Message Templates</strong> - Sales, follow-up, promotion, and retention</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Sales Scripts</strong> - Convert WhatsApp chats into paying customers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Automated Responses</strong> - Set up instant replies for common questions</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Broadcast Strategies</strong> - How to send bulk messages without getting banned</span></li>
</ul>
<h3 class="text-xl font-semibold text-slate-800 mb-3">Template Categories</h3>
<ul class="space-y-2 mb-6">
<li>New customer introduction messages</li>
<li>Product promotion broadcasts</li>
<li>Follow-up and reminder messages</li>
<li>Customer testimonial requests</li>
<li>Flash sale announcements</li>
<li>Abandoned cart recovery</li>
</ul>
<div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
<p class="text-green-800 font-medium"><i class="fab fa-whatsapp mr-2"></i>Designed specifically for Nigerian businesses and Nigerian customers.</p>
</div>'
    ],
    'email-marketing-premium-bundle' => [
        'Complete email marketing system with all templates, sequences, and automation workflows.',
        '<h2 class="text-2xl font-bold text-slate-900 mb-4">Your Complete Email Marketing Solution</h2>
<p class="text-lg text-slate-600 mb-6">Everything you need for complete email marketing success. Templates, sequences, automation, and the strategies that actually convert.</p>
<h3 class="text-xl font-semibold text-slate-800 mb-3">System Components</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Email Templates</strong> - 100+ professionally designed emails</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Sales Sequences</strong> - Automated 7-email sales sequences</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Newsletter Templates</strong> - Weekly and monthly content formats</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Welcome Series</strong> - New subscriber onboarding flow</span></li>
</ul>
<h3 class="text-xl font-semibold text-slate-800 mb-3">Bonus Materials</h3>
<ul class="space-y-2 mb-6">
<li>Subject line swipe file (200+ proven subject lines)</li>
<li>Segmentation guide</li>
<li>Delivery rate optimization tips</li>
<li>Paystack email integration setup</li>
</ul>
<div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg">
<p class="text-amber-800 font-medium"><i class="fas fa-gem mr-2"></i>Premium bundle - best value for serious marketers.</p>
</div>'
    ],
    'done-for-you-email-automation' => [
        'I build your complete email marketing automation system while you focus on your business.',
        '<h2 class="text-2xl font-bold text-slate-900 mb-4">Done For You Email Automation</h2>
<p class="text-lg text-slate-600 mb-6">Stop wasting time manually sending emails. Let me build your complete email marketing automation system - set up correctly and ready to convert.</p>
<h3 class="text-xl font-semibold text-slate-800 mb-3">What You Get</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Email Sequences</strong> - 5-10 automated email sequences tailored to your business</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Landing Pages</strong> - High-converting lead capture pages</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>List Segmentation</strong> - Organize subscribers for better targeting</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Autoresponder Setup</strong> - Timed, triggered email sequences</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1"><i class="fas fa-check-circle"></i></span><span><strong>Integration</strong> - Connect to your email provider and funnel tools</span></li>
</ul>
<h3 class="text-xl font-semibold text-slate-800 mb-3">The Process</h3>
<ul class="space-y-2 mb-6">
<li>Step 1: Discovery call (30 minutes)</li>
<li>Step 2: Strategy document creation</li>
<li>Step 3: Email sequences written</li>
<li>Step 4: Automation flows built</li>
<li>Step 5: Testing and handover</li>
</ul>
<div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-lg">
<p class="text-indigo-800 font-medium"><i class="fas fa-rocket mr-2"></i>Delivery in 5-7 business days. Unlimited revisions until you\'re satisfied.</p>
</div>'
    ],
];

require_once $portfolioPath . '/vendor/autoload.php';
$app = require $portfolioPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$count = 0;
foreach ($descriptions as $slug => $data) {
    $updated = Illuminate\Support\Facades\DB::table('products')
        ->where('slug', $slug)
        ->update(['description' => $data[0], 'full_description' => $data[1]]);
    if ($updated) {
        $count++;
        echo "Updated: $slug\n";
    } else {
        echo "No change (already updated?): $slug\n";
    }
}
echo "Total updated: $count\n";

// Debug: test email builder view
echo "\n=== Testing Email Builder View ===\n";
$test = \App\Models\EmailTemplate::count();
echo "EmailTemplate count: $test\n";

// Check if view exists
$viewPath = $portfolioPath . '/resources/views/admin/marketing/email_builder/create.blade.php';
echo "View exists: " . (file_exists($viewPath) ? 'yes' : 'no') . "\n";

// Try to render view
try {
    $view = view('admin.marketing.email_builder.create');
    echo "View render: OK\n";
} catch (\Exception $e) {
    echo "View render error: " . $e->getMessage() . "\n";
}

echo "\n=== Deployment Complete ===\n";