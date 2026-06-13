<?php
/**
 * Setup Cron Job via cPanel API
 * Run this file once to create the scheduler cron
 */

// cPanel credentials
$cpuser = 'joalacom';
$cppass = '4fu359TgAMi-O+';
$domain = 'joalacom';

// Build cron command
// Run every minute: * * * * *
$croncmd = '*/15 * * * * /usr/local/bin/php /home/joalacom/public_html/artisan schedule:run >> /dev/null 2>&1';

// Encode for API
$cron_enc = base64_encode($croncmd);
$user_enc = base64_encode($cpuser);
$pass_enc = base64_encode($cppass);

// cPanel API URL
$url = "https://www.joala.com.ng:2083/json-api/cron_add_api?api_version=1&cmd=" . urlencode($cron_enc) . "&user=" . $user_enc;

// Try using curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERPWD, "$cpuser:$cppass");

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpcode\n";
echo "Response: $response\n";

// Alternative: Just output instructions
echo "\n--- Alternative ---\n";
echo "If API failed, set up cron manually in cPanel:\n";
echo "1. Go to cPanel > Cron Jobs\n";
echo "2. Add new cron job\n";
echo "3. Set frequency: Every minute (* * * * *)\n";
echo "4. Command: /usr/local/bin/php /home/joalacom/public_html/start-scheduler.sh\n";
echo "5. Save\n";