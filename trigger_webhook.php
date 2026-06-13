<?php
function triggerWebhook($event, $data) {
    $conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
    if ($conn->connect_error) return;
    
    $webhooks = $conn->query("SELECT * FROM webhooks WHERE is_active = 1");
    
    while ($webhook = $webhooks->fetch_assoc()) {
        $events = json_decode($webhook['events'], true);
        if (!in_array($event, $events)) continue;
        
        $payload = json_encode([
            'event' => $event,
            'timestamp' => date('c'),
            'data' => $data
        ]);
        
        $ch = curl_init($webhook['url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Webhook-Event: ' . $event
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        curl_close($ch);
    }
    
    $conn->close();
}