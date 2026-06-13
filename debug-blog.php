<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Testing Blog Post Creation ===\n\n";

try {
    echo "1. Testing BlogPost model...\n";
    $count = \App\Models\BlogPost::count();
    echo "   ✓ BlogPost table exists, count: $count\n";
} catch (Exception $e) {
    echo "   ✗ BlogPost error: " . $e->getMessage() . "\n";
}

try {
    echo "2. Testing Funnel model...\n";
    $funnels = \App\Models\Funnel::where('is_active', true)->get();
    echo "   ✓ Funnel table exists, count: " . count($funnels) . "\n";
} catch (Exception $e) {
    echo "   ✗ Funnel error: " . $e->getMessage() . "\n";
}

try {
    echo "3. Testing BlogPost fillable...\n";
    $post = new \App\Models\BlogPost();
    echo "   ✓ BlogPost model loaded\n";
    echo "   Fillable: " . implode(', ', $post->getFillable()) . "\n";
} catch (Exception $e) {
    echo "   ✗ BlogPost model error: " . $e->getMessage() . "\n";
}

try {
    echo "4. Testing blog creation...\n";
    $data = [
        'title' => 'Test Post',
        'slug' => 'test-post-' . time(),
        'body' => '<p>Test content</p>',
        'is_published' => false
    ];
    $post = \App\Models\BlogPost::create($data);
    echo "   ✓ Blog post created, ID: " . $post->id . "\n";
    $post->delete();
    echo "   ✓ Test post deleted\n";
} catch (Exception $e) {
    echo "   ✗ Create error: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===";