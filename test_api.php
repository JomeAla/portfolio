<?php
/**
 * Test Brevo via API (HTTP)
 */

// NOTE: Replace with your actual Brevo API key
$apiKey = 'YOUR_BREVO_API_KEY';
$toEmail = 'jomealea@gmail.com';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'api-key: ' . $apiKey,
    'content-type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'sender' => [
        'name' => 'Joala',
        'email' => 'campaigns@joala.com.ng'
    ],
    'to' => [
        ['email' => $toEmail, 'name' => 'Test User']
    ],
    'subject' => 'Test Email - Brevo API Working!',
    'htmlContent' => '<html><body><h1>Success!</h1><p>Brevo API is configured and working!</p><p>Your email system is ready.</p></body></html>',
    'textContent' => 'Success! Brevo API is configured and working!'
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<h2>Testing Brevo API...</h2>";
echo "<p>API Endpoint: https://api.brevo.com/v3/smtp/email</p>";
echo "<p>HTTP Code: $httpCode</p>";

if ($httpCode == 201 || $httpCode == 200) {
    echo "<h3 style='color:green'>✅ SUCCESS! Email sent via Brevo API!</h3>";
    echo "<p>Check inbox (and spam) at: <strong>$toEmail</strong></p>";
    echo "<p>Response: $response</p>";
} else {
    echo "<p>Error: $error</p>";
    echo "<p>Response: $response</p>";
}