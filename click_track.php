<?php
error_reporting(0);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmailQueue;
use Illuminate\Support\Facades\DB;

$queueId = $_GET['q'] ?? 0;
$url = $_GET['url'] ?? '';

if ($queueId && $url) {
    $url = base64_decode($url);
    
    $queue = EmailQueue::find($queueId);
    if ($queue) {
        DB::table('email_queue')->where('id', $queue->id)->update([
            'clicked' => true,
            'clicked_at' => now()
        ]);
        
        $lead = $queue->lead;
        if ($lead) {
            $lead->increment('score', 20);
        }
        
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            header('Location: ' . $url);
            exit;
        }
    }
}

http_response_code(400);
echo 'Invalid click tracking link';