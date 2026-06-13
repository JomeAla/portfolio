<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Email Analytics Dashboard</h1>
<style>
body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #f8f9fa; padding: 20px; }
pre { background: #1a1a1a; color: #00ff00; padding: 20px; border-radius: 8px; }
.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
.stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
.stat-card h3 { margin: 0 0 10px 0; font-size: 14px; color: #666; }
.stat-card .value { font-size: 32px; font-weight: bold; color: #333; }
table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
th { background: #f8f9fa; font-weight: 600; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-green { background: #d4edda; color: #155724; }
.badge-red { background: #f8d7da; color: #721c24; }
</style>
";

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$baseUrl = 'https://joala.com.ng';

$stats = [
    'total' => 0,
    'sent' => 0,
    'pending' => 0,
    'failed' => 0,
    'opened' => 0,
    'clicked' => 0,
];

$result = $conn->query("SELECT COUNT(*) as cnt FROM email_queue");
$stats['total'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM email_queue WHERE status = 'sent'");
$stats['sent'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM email_queue WHERE status = 'pending'");
$stats['pending'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM email_queue WHERE status = 'failed'");
$stats['failed'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM email_queue WHERE opened = 1");
$stats['opened'] = $result->fetch_assoc()['cnt'];

$result = $conn->query("SELECT COUNT(*) as cnt FROM email_queue WHERE clicked = 1");
$stats['clicked'] = $result->fetch_assoc()['cnt'];

echo "<div class='stats'>";
echo "<div class='stat-card'><h3>Total Emails</h3><div class='value'>{$stats['total']}</div></div>";
echo "<div class='stat-card'><h3>Sent</h3><div class='value'>{$stats['sent']}</div></div>";
echo "<div class='stat-card'><h3>Pending</h3><div class='value'>{$stats['pending']}</div></div>";
echo "<div class='stat-card'><h3>Failed</h3><div class='value'>{$stats['failed']}</div></div>";
echo "<div class='stat-card'><h3>Opened</h3><div class='value'>{$stats['opened']}</div></div>";
echo "<div class='stat-card'><h3>Clicked</h3><div class='value'>{$stats['clicked']}</div></div>";
echo "</div>";

if ($stats['sent'] > 0) {
    $openRate = round(($stats['opened'] / $stats['sent']) * 100, 1);
    $clickRate = round(($stats['clicked'] / $stats['sent']) * 100, 1);
    echo "<p style='font-size: 18px; margin: 20px 0;'><strong>Open Rate:</strong> {$openRate}% &nbsp;&nbsp; <strong>Click Rate:</strong> {$clickRate}%</p>";
}

echo "<h2>Recent Sent Emails</h2>";
echo "<table><tr><th>Email</th><th>Subject</th><th>Sent</th><th>Opened</th><th>Clicked</th></tr>";

$result = $conn->query("SELECT e.*, l.email as recipient_email, l.name as recipient_name 
    FROM email_queue e 
    LEFT JOIN leads l ON e.lead_id = l.id 
    WHERE e.status = 'sent' 
    ORDER BY e.sent_at DESC 
    LIMIT 10");

while ($row = $result->fetch_assoc()) {
    $opened = $row['opened'] ? '<span class="badge badge-green">✓</span>' : '<span class="badge badge-red">✗</span>';
    $clicked = $row['clicked'] ? '<span class="badge badge-green">✓</span>' : '<span class="badge badge-red">✗</span>';
    echo "<tr>";
    echo "<td>{$row['recipient_email']}</td>";
    echo "<td>{$row['subject']}</td>";
    echo "<td>{$row['sent_at']}</td>";
    echo "<td>$opened</td>";
    echo "<td>$clicked</td>";
    echo "</tr>";
}

echo "</table>";
