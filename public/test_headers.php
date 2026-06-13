<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://joala.com.ng/public/customer/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=admin@joala.com.ng&password=password123');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($ch);
echo "=== HEADERS ===\n";
echo $headers;
echo "\n=== BODY Contains 'my-courses'? ===\n";
echo (strpos($body, 'my-courses') !== false) ? "YES" : "NO";