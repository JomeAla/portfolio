<?php
/**
 * Auto-enroll script - Add this to cron to queue all pending leads
 * Run: curl -s http://joala.com.ng/auto_enroll.php
 */

$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "Auto-enrolling leads...<br>";

// Get leads that have sequence_id but NOT in email_queue
$sql = "SELECT l.id, l.email, l.sequence_id
        FROM leads l
        WHERE l.sequence_id IS NOT NULL
        AND l.id NOT IN (SELECT DISTINCT lead_id FROM email_queue WHERE lead_id IS NOT NULL)";
        
$result = $conn->query($sql);

$count = 0;
while ($lead = $result->fetch_assoc()) {
    $lid = $lead['id'];
    $sid = $lead['sequence_id'];
    
    // Get steps for this sequence
    $steps = $conn->query("SELECT id, delay_days FROM sequence_steps WHERE sequence_id = $sid ORDER BY step_order");
    
    while ($step = $steps->fetch_assoc()) {
        $delay = is_numeric($step['delay_days']) ? (int)$step['delay_days'] : 0;
        $sched = date("Y-m-d H:i:s", time() + ($delay * 86400));
        
        $conn->query("INSERT INTO email_queue (lead_id, sequence_step_id, scheduled_send_time, status)
                     VALUES ($lid, {$step['id']}, '$sched', 'pending')");
    }
    
    echo "Enrolled lead $lid into seq $sid<br>";
    $count++;
}

echo "<br><h2>Enrolled $count new leads</h2>";

// Show queue status
$total = $conn->query("SELECT COUNT(*) c FROM email_queue")->fetch_assoc();
$pending = $conn->query("SELECT COUNT(*) c FROM email_queue WHERE status='pending'")->fetch_assoc();
echo "Total queue: {$total['c']}, Pending: {$pending['c']}<br>";

$conn->close();