<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

// Create funnel for free-email-checklist
$conn->query("INSERT INTO funnels (name, slug, funnel_type, product_id, upsell_enabled, welcome_sequence_id, is_active)
            VALUES ('Email Checklist Launch', 'email-checklist-launch', 'lead_magnet', 16, 1, 22, 1)");
$funnel_id = $conn->insert_id;

// Update landing page
$conn->query("UPDATE landing_pages SET funnel_id=$funnel_id WHERE slug='free-email-checklist'");

echo "Created funnel $funnel_id for free-email-checklist";

// Verify
$r = $conn->query("SELECT lp.funnel_id, f.name FROM landing_pages lp JOIN funnels f ON lp.funnel_id=f.id WHERE lp.slug='free-email-checklist'")->fetch_assoc();
echo "<br>free-email-checklist -> Funnel: {$r['funnel_id']} ({$r['name']})";

$conn->close();