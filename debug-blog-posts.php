<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Blog Posts Debug ===\n\n";

$posts = \App\Models\BlogPost::latest()->take(5)->get();

foreach ($posts as $post) {
    echo "ID: " . $post->id . "\n";
    echo "Title: " . $post->title . "\n";
    echo "Slug: " . $post->slug . "\n";
    echo "Featured Image: " . ($post->featured_image ?? 'NULL') . "\n";
    echo "Body length: " . strlen($post->body ?? '') . " chars\n";
    echo "Body preview: " . substr($post->body ?? '', 0, 200) . "...\n";
    echo "---\n";
}

echo "\n=== DONE ===";