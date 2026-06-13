<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "<h2>Sequence Steps Check</h2>";

foreach ([21, 22] as $seq_id) {
    $sql = "SELECT id, subject, step_order, delay_days FROM sequence_steps WHERE sequence_id = $seq_id ORDER BY step_order";
    $result = $conn->query($sql);
    
    echo "<h3>Sequence $seq_id</h3>";
    if ($result && $result->num_rows > 0) {
        while ($r = $result->fetch_assoc()) {
            echo "Step {$r['step_order']}: {$r['subject']} (delay_days: {$r['delay_days']})<br>";
        }
    } else {
        echo "NO STEPS FOUND!<br>";
    }
}

echo "<br><h3>Sequence 21 - EmailSequence table check</h3>";
$res = $conn->query("SELECT * FROM email_sequences WHERE id = 21");
if ($r = $res->fetch_assoc()) {
    print_r($r);
}

$conn->close();