<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "Checking Brevo Settings in Database:\n\n";

$settings = \DB::table('settings')->where('key', 'like', 'mail_%')->orWhere('key', 'like', 'brevo_%')->get();

foreach ($settings as $s) {
    echo "$s->key = " . ($s->key === 'mail_password' || $s->key === 'brevo_api_key' ? substr($s->value, 0, 10) . '...' : $s->value) . "\n";
}

echo "\n\n=== Testing Brevo API ===\n\n";

$apiKey = \DB::table('settings')->where('key', 'brevo_api_key')->value('value');

if (empty($apiKey)) {
    echo "ERROR: No Brevo API key found!\n";
    echo "Need to add brevo_api_key to settings table.\n";
} else {
    echo "API Key found: " . substr($apiKey, 0, 15) . "...\n";
    
    // Test API - send a simple transaction email
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: $apiKey",
        "content-type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "sender" => ["name" => "JoAla", "email" => "noreply@joala.com.ng"],
        "to" => [["email" => "test@example.com", "name" => "Test"]],
        "subject" => "Brevo API Test",
        "htmlContent" => "<html><body><h1>Test</h1><p>If you receive this, Brevo API is working!</p></body></html>"
    ]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Response: $httpCode\n";
    echo "Response: $response\n";
}

echo "</pre>";