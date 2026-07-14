<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    Illuminate\Http\Request::capture()
);

try {
    $finder = app('view')->getFinder();
    $path = $finder->find('layouts.app');
    echo "View path: $path\n\n";
    
    $compiler = app('blade.compiler');
    $compiledPath = $compiler->getCompiledPath($path);
    echo "Compiled path: $compiledPath\n\n";
    
    // Delete old compiled
    if (file_exists($compiledPath)) {
        unlink($compiledPath);
        echo "Deleted old compiled file.\n\n";
    }
    
    $compiler->compile($path);
    echo "Compilation succeeded!\n\n";
    
    $content = file_get_contents($compiledPath);
    echo "Compiled output (first 30 lines):\n";
    $lines = explode("\n", $content);
    for ($i = 0; $i < min(30, count($lines)); $i++) {
        printf("%3d: %s\n", $i+1, $lines[$i]);
    }
} catch (Throwable $e) {
    echo "COMPILATION ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
