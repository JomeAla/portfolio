<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();
$response = response()->view('front.order.checkout', [
    'product' => \App\Models\Product::where('slug', 'wordpress-starter-kit')->where('is_active', true)->firstOrFail(),
    'paystackKey' => \App\Models\Setting::get('paystack_public_key') ?? 'pk_live_xxxxxxxxxxxx'
], 200);
$response->send();