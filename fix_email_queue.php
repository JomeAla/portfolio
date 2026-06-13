<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "<h2>Fix: Queue Emails for All Leads</h2>";

// Get all leads with sequence_id but no queue entries
$sql = "SELECT l.id, l.email, l.sequence_id
       FROM leads l 
       WHERE l.sequence_id IS NOT NULL
       AND l.id NOT IN (SELECT DISTINCT lead_id FROM email_queue WHERE lead_id IS NOT NULL)";
$result = $conn->query($sql);

$count = 0;
while ($lead = $result->fetch_assoc()) {
    $lead_id = $lead['id'];
    $seq_id = $lead['sequence_id'];
    
    // Get all steps for this sequence
    $steps = $conn->query("SELECT id, step_order, delay_days FROM sequence_steps WHERE sequence_id = $seq_id ORDER BY step_order");
    
    while ($step = $steps->fetch_assoc()) {
        $step_id = $step['id'];
        $delay = $step['delay_days'];
        
        // Calculate scheduled time (delay days from now)
        $scheduled = date('Y-m-d H:i:s', strtotime("+$delay days"));
        
        // Insert into queue
        $sql = "INSERT INTO email_queue (lead_id, sequence_step_id, scheduled_send_time, status) 
                VALUES ($lead_id, $step_id, '$scheduled', 'pending')";
        
        if ($conn->query($sql)) {
            echo "Queued lead $lead_id, step $step_id (delay: $delay days)<br>";
            $count++;
        }
    }
}

echo "<br><h2>Result: Queued $count emails</h2>";

// Verify
$total = $conn->query("SELECT COUNT(*) cnt FROM email_queue")->fetch_assoc();
$pending = $conn->query("SELECT COUNT(*) cnt FROM email_queue WHERE status='pending'")->fetch_assoc();
echo "Total in queue: {$total['cnt']}<br>";
echo "Pending: {$pending['cnt']}<br>";

$conn->close();