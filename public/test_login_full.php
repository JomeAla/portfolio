<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

// Simulate a login POST
$request = Request::create(
    '/customer/login',
    'POST',
    [
        'email' => 'admin@joala.com.ng',
        'password' => 'password123',
        '_token' => csrf_token()
    ],
    [], // cookies
    [], // files
    [
        'HTTP_ACCEPT' => 'text/html',
        'HTTP_REFERER' => 'https://joala.com.ng/customer/login'
    ]
);

try {
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Redirect URL: " . ($response->getTargetUrl() ?? 'none') . "\n";
    
    // Check for cookies
    echo "\nCookies set:\n";
    foreach($response->headers->getCookies() as $cookie) {
        echo "- " . $cookie->getName() . " = " . $cookie->getValue() . "\n";
    }
    
    echo "\nContent preview: " . substr($response->getContent(), 0, 200) . "\n";
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}