<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$slug = 'free-wordpress-starter-kit';

$content = json_encode([
    'headline' => 'WordPress Starter Kit',
    'subheadline' => 'Everything you need to build a professional WordPress site - themes, plugins, templates & setup guide. No coding required.',
    'items' => [
        'Premium WordPress Theme (worth ₦15,000)',
        'Essential Plugins Bundle',
        '5 Ready-to-Use Page Templates',
        'Step-by-Step Setup Guide',
        'SEO Optimization Checklist',
        'Free Updates for Life'
    ],
    'cta' => 'Get My Free Kit'
]);

$page = DB::table('landing_pages')->where('slug', $slug)->first();

if ($page) {
    DB::table('landing_pages')
        ->where('slug', $slug)
        ->update([
            'title' => 'WordPress Starter Kit - Free Download',
            'custom_html' => $content,
            'is_active' => 1,
            'updated_at' => now()
        ]);
    echo "✅ Updated landing page: $slug\n";
    echo "Headline: WordPress Starter Kit\n";
    echo "Subheadline: Everything you need to build a professional WordPress site...\n";
} else {
    echo "❌ Landing page not found: $slug\n";
}