<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "<h2>Final Status Check</h2>";

// Landing pages
$sql = "SELECT lp.slug, lp.funnel_id, lp.sequence_id, f.name as fname
       FROM landing_pages lp
       LEFT JOIN funnels f ON lp.funnel_id = f.id
       WHERE lp.slug = 'free-email-checklist'";
$r = $conn->query($sql)->fetch_assoc();
echo "<p>free-email-checklist: Funnel={$r['funnel_id']}, Seq={$r['sequence_id']}, Funnel={$r['fname']}</p>";

// All lead magnets
$sql = "SELECT COUNT(*) cnt FROM landing_pages WHERE slug LIKE 'free-%'";
$c = $conn->query($sql)->fetch_assoc();
echo "<p>Total lead magnets: {$c['cnt']}</p>";

// All sequences
$sql = "SELECT COUNT(*) cnt FROM email_sequences";
$c = $conn->query($sql)->fetch_assoc();
echo "<p>Total sequences: {$c['cnt']}</p>";

// Funnels
$sql = "SELECT COUNT(*) cnt FROM funnels WHERE is_active=1";
$c = $conn->query($sql)->fetch_assoc();
echo "<p>Active funnels: {$c['cnt']}</p>";

echo "<h2 style='color:green'>✓ COMPLETE!</h2>";

$conn->close();