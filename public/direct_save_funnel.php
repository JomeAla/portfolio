<!DOCTYPE html>
<html>
<head><title>Direct Save Funnel</title></head>
<body>
<h1>Direct Save Funnel</h1>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = 'localhost';
    $db   = 'joalacom_joala';
    $user = 'joalacom_joala';
    $pass = 'J0ala@2024!';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get fields from POST
        $fields = ['name', 'description', 'goal', 'funnel_type', 'welcome_sequence_id', 
                   'followup_sequence_id', 'notify_email', 'product_id', 'service_id',
                   'upsell_product_id', 'upsell_discount', 'upsell_timer', 'countdown_hours'];
        $update = [];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
                $update[$field] = $_POST[$field];
            }
        }
        
        $update['updated_at'] = date('Y-m-d H:i:s');
        
        // Get funnel ID from hidden field or default to 2
        $funnelId = isset($_POST['funnel_id']) ? intval($_POST['funnel_id']) : 2;
        
        // Build SQL
        $sql = "UPDATE funnels SET ";
        $sets = [];
        foreach ($update as $k => $v) {
            $sets[] = "$k = :" . $k;
        }
        $sql .= implode(', ', $sets) . " WHERE id = :id";
        $update['id'] = $funnelId;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($update);
        
        echo "<p style='color:green'>✅ SAVED! Funnel ID: $funnelId</p>";
        echo "<pre>";
        print_r($update);
        echo "</pre>";
        echo "<p><a href='/admin/marketing/funnels'>Back to Funnels</a></p>";
        
    } catch (PDOException $e) {
        echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>No POST data</p>";
}
?>
</body>
</html>