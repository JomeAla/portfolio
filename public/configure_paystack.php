<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Configuring Paystack Keys</h1>";

// Add Paystack settings
DB::table('settings')->updateOrInsert(['key' => 'paystack_public_key'], ['value' => 'pk_live_e3c3cb288dc54df3f9006c3998a528f952339f41']);
DB::table('settings')->updateOrInsert(['key' => 'paystack_secret_key'], ['value' => 'sk_live_ebd31b39705c10bae78e88185e055a5981cc3448']);
DB::table('settings')->updateOrInsert(['key' => 'paystack_merchant_email'], ['value' => 'campaigns@joala.com.ng']);

echo "<p>Paystack keys configured successfully!</p>";

// Verify
$keys = ['paystack_public_key', 'paystack_secret_key', 'paystack_merchant_email'];
foreach($keys as $key) {
    $val = DB::table('settings')->where('key', $key)->first();
    echo "<p><strong>{$key}</strong>: " . substr($val->value, 0, 20) . "...</p>";
}

echo "<p>DONE - Paystack is now configured!</p>";