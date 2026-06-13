<?php
error_reporting(0);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmailQueue;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

$type = $_GET['type'] ?? 'open';
$id = $_GET['id'] ?? 0;
$email = $_GET['email'] ?? '';

if ($id || $email) {
    $queue = null;
    
    if ($id) {
        $queue = EmailQueue::find($id);
    }
    
    if (!$queue && $email) {
        $lead = Lead::where('email', $email)->first();
        if ($lead) {
            $queue = EmailQueue::where('lead_id', $lead->id)
                ->where('status', 'sent')
                ->orderBy('sent_at', 'desc')
                ->first();
        }
    }
    
    if ($queue) {
        $lead = $queue->lead;
        
        if ($type === 'open') {
            DB::table('email_queue')->where('id', $queue->id)->update([
                'opened' => true,
                'opened_at' => now()
            ]);
            if ($lead) {
                $lead->increment('score', 10);
                
                $conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
                if (!$conn->connect_error) {
                    $webhooks = $conn->query("SELECT * FROM webhooks WHERE is_active = 1");
                    while ($webhook = $webhooks->fetch_assoc()) {
                        $events = json_decode($webhook['events'], true);
                        if (!in_array('email_opened', $events)) continue;
                        
                        $payload = json_encode([
                            'event' => 'email_opened',
                            'timestamp' => date('c'),
                            'data' => [
                                'lead' => ['id' => $lead->id, 'email' => $lead->email],
                                'email' => ['id' => $queue->id, 'subject' => $queue->subject]
                            ]
                        ]);
                        
                        $ch = curl_init($webhook['url']);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        curl_exec($ch);
                        curl_close($ch);
                    }
                    $conn->close();
                }
            }
        } elseif ($type === 'click') {
            DB::table('email_queue')->where('id', $queue->id)->update([
                'clicked' => true,
                'clicked_at' => now()
            ]);
            if ($lead) {
                $lead->increment('score', 20);
            }
        }
    }
}

$pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
header('Content-Type: image/png');
echo $pixel;