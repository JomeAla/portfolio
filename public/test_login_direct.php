<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create(
    '/customer/login',
    'POST',
    ['email' => 'admin@joala.com.ng', 'password' => 'password123']
);
$request->headers->set('X-CSRF-TOKEN', 'test');

$controller = new App\Http\Controllers\Front\CustomerController();
try {
    $response = $controller->login($request);
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Redirect location: " . $response->getTargetUrl() . "\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}