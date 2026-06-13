<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "Queue all emails...<br>";

// Get leads with sequence_id
$leads = $conn->query("SELECT id, sequence_id FROM leads WHERE sequence_id IS NOT NULL");
while ($l = $leads->fetch_assoc()) {
    $lid = $l['id'];
    $sid = $l['sequence_id'];
    
    // Get steps
    $steps = $conn->query("SELECT id, delay_days FROM sequence_steps WHERE sequence_id = $sid ORDER BY step_order");
    while ($s = $steps->fetch_assoc()) {
        $delay = (int)$s['delay_days'];
        $sched = date("Y-m-d H:i:s", time() + ($delay * 86400));
        
        $conn->query("INSERT INTO email_queue (lead_id, sequence_step_id, scheduled_send_time, status) 
                     VALUES ($lid, {$s['id']}, '$sched', 'pending')");
        echo "Q:$lid step:{$s['id']}<br>";
    }
}

echo "Done!<br>";

$tot = $conn->query("SELECT COUNT(*) c FROM email_queue")->fetch_assoc();
echo "Total: {$tot['c']}<br>";

$conn->close();