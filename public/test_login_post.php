<?php
// Test login POST
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://joala.com.ng/public/customer/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=admin@joala.com.ng&password=password123');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP Code: $httpCode\n";
echo "Response length: " . strlen($response) . "\n";
if(strpos($response, 'my-courses') !== false || strpos($response, 'dashboard') !== false) {
    echo "SUCCESS - Login worked!\n";
} else {
    echo "FAILED - Page content:\n";
    echo substr($response, 0, 500);
}