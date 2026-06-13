<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "<h2>Direct Queue Fix</h2>";

// Get ALL leads with sequence_id
$sql = "SELECT l.id, l.email, l.sequence_id
       FROM leads l 
       WHERE l.sequence_id IS NOT NULL";
$result = $conn->query($sql);

echo "Found leads with sequences:<br>";
while ($lead = $result->fetch_assoc()) {
    echo "Lead {$lead['id']}: {$lead['email']} → Seq {$lead['sequence_id']}<br>";
}

echo "<br>Queueing emails...<br>";

// Reset result
$result = $conn->query($sql);
$count = 0;
while ($lead = $result->fetch_assoc()) {
    $lead_id = $lead['id'];
    $seq_id = $lead['sequence_id'];
    
    // Get all steps
    $steps = $conn->query("SELECT id, step_order, delay_days FROM sequence_steps WHERE sequence_id = $seq_id ORDER BY step_order");
    
    while ($step = $steps->fetch_assoc()) {
        $step_id = $step['id'];
        $delay = $step['delay_days'];
        $delay = is_numeric($delay) ? $delay : 0;
        
        $scheduled = date('Y-m-d H:i:s', time() + ($delay * 3600));
        
        $conn->query("INSERT INTO email_queue (lead_id, sequence_step_id, scheduled_send_time, status) 
                VALUES ($lead_id, $step_id, '$scheduled', 'pending')");
        
        echo "Queued lead $lead_id → step $step_id<br>";
        $count++;
    }
}

echo "<br><h2>Done! Queued $count emails</h2>";

$conn->close();