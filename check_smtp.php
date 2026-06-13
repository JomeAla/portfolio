<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "<h2>SMTP Settings Check</h2>";

$res = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%' OR setting_key LIKE 'mail_%'");

if ($res && $res->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Setting</th><th>Value</th></tr>";
    while ($r = $res->fetch_assoc()) {
        $val = $r['setting_key'] === 'smtp_password' || $r['setting_key'] === 'mail_password' ? '******' : $r['setting_value'];
        echo "<tr><td>{$r['setting_key']}</td><td>$val</td></tr>";
    }
    echo "</table>";
} else {
    echo "No SMTP settings found in 'settings' table<br>";
}

// Try settings table without prefix
$res2 = $conn->query("SELECT `key`, value FROM settings WHERE `key` LIKE '%smtp%' OR `key` LIKE '%mail%'");
echo "<br>Also checking with 'key' column:<br>";
if ($res2 && $res2->num_rows > 0) {
    while ($r = $res2->fetch_assoc()) {
        echo "- {$r['key']}: {$r['value']}<br>";
    }
}

$conn->close();