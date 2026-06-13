<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "=== Adding missing columns to blog_posts ===\n\n";

try {
    if (!Schema::hasColumn('blog_posts', 'funnel_id')) {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('funnel_id')->nullable()->after('popup_html');
        });
        echo "✓ Added funnel_id column\n";
    } else {
        echo "✓ funnel_id column already exists\n";
    }
    
    if (!Schema::hasColumn('blog_posts', 'show_popup')) {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->boolean('show_popup')->default(false)->after('published_at');
        });
        echo "✓ Added show_popup column\n";
    } else {
        echo "✓ show_popup column already exists\n";
    }
    
    if (!Schema::hasColumn('blog_posts', 'popup_delay')) {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->integer('popup_delay')->default(10)->after('show_popup');
        });
        echo "✓ Added popup_delay column\n";
    } else {
        echo "✓ popup_delay column already exists\n";
    }
    
    if (!Schema::hasColumn('blog_posts', 'popup_title')) {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('popup_title')->nullable()->after('popup_delay');
        });
        echo "✓ Added popup_title column\n";
    } else {
        echo "✓ popup_title column already exists\n";
    }
    
    if (!Schema::hasColumn('blog_posts', 'popup_html')) {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->text('popup_html')->nullable()->after('popup_title');
        });
        echo "✓ Added popup_html column\n";
    } else {
        echo "✓ popup_html column already exists\n";
    }
    
    echo "\n=== DONE - All columns added! ===\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}