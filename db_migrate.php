<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "<h1>Running Migrations...</h1>";

try {
    // Add newsletter fields to leads
    if (!Schema::hasColumn('leads', 'is_newsletter')) {
        Schema::table('leads', function ($t) {
            $t->boolean('is_newsletter')->default(false)->after('status');
            $t->boolean('confirmed')->default(false)->after('is_newsletter');
            $t->string('confirmation_token', 255)->nullable()->unique()->after('confirmed');
            $t->timestamp('confirmed_at')->nullable()->after('confirmation_token');
        });
        echo "<p style='color:green'>✓ Added newsletter fields to leads</p>";
    }
    
    // Add timer and popup to landing_pages
    if (!Schema::hasColumn('landing_pages', 'countdown_end')) {
        Schema::table('landing_pages', function ($t) {
            $t->timestamp('countdown_end')->nullable()->after('is_active');
            $t->string('countdown_message')->nullable()->after('countdown_end');
            $t->boolean('show_popup')->default(false)->after('countdown_message');
            $t->integer('popup_delay')->default(5)->after('show_popup');
            $t->string('popup_title')->nullable()->after('popup_delay');
            $t->text('popup_html')->nullable()->after('popup_title');
        });
        echo "<p style='color:green'>✓ Added timer/popup to landing_pages</p>";
    }
    
    // Add popup to blog_posts
    if (!Schema::hasColumn('blog_posts', 'show_popup')) {
        Schema::table('blog_posts', function ($t) {
            $t->boolean('show_popup')->default(false)->after('is_published');
            $t->integer('popup_delay')->default(10)->after('show_popup');
            $t->string('popup_title')->nullable()->after('popup_delay');
            $t->text('popup_html')->nullable()->after('popup_title');
        });
        echo "<p style='color:green'>✓ Added popup to blog_posts</p>";
    }
    
    echo "<h2 style='color:green'>✓ All migrations complete!</h2>";
    echo "<p><a href='/'>Go to home page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}