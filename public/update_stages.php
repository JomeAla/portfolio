<!DOCTYPE html>
<html>
<head><title>Direct Funnel Stages Update</title></head>
<body>
<h1>Direct Funnel Stages Update</h1>
<form method="POST">
    <h2>Stage 1: Landing Page</h2>
    <p>Type: <select name="stages[0][type]">
        <option value="landing_page">Landing Page</option>
        <option value="page">Page</option>
    </select></p>
    <p>Content/URL: <input type="text" name="stages[0][page_slug]" value="free-wordpress-starter-kit" size="50"></p>
    
    <h2>Stage 2: Download Page</h2>
    <p>Type: <select name="stages[1][type]">
        <option value="download">Download</option>
    </select></p>
    <p>Content/File: <input type="text" name="stages[1][file]" value="wordpress-starter-kit.zip" size="50"></p>
    
    <h2>Stage 3: Thank You</h2>
    <p>Type: <select name="stages[2][type]">
        <option value="thankyou">Thank You</option>
    </select></p>
    <p>Message: <input type="text" name="stages[2][message]" value="Check your email for the download link!" size="50"></p>
    
    <br><br>
    <button type="submit" style="padding:10px 20px; background:green; color:white;">Update Stages</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = 'localhost';
    $db   = 'joalacom_joala';
    $user = 'joalacom_joala';
    $pass = 'J0ala@2024!';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stages = $_POST['stages'] ?? [];
        
        // Delete existing
        $pdo->exec("DELETE FROM funnel_stages WHERE funnel_id = 2");
        
        // Insert new stages
        $i = 1;
        foreach ($stages as $stage) {
            $name = $stage['type'] === 'landing_page' ? 'Landing Page' : 
                   ($stage['type'] === 'download' ? 'Download Page' : 'Thank You');
            
            $content = json_encode([
                'page_slug' => $stage['page_slug'] ?? '',
                'file' => $stage['file'] ?? '',
                'message' => $stage['message'] ?? ''
            ]);
            
            $pdo->exec("INSERT INTO funnel_stages (funnel_id, name, type, `order`, content, created_at, updated_at) 
                VALUES (2, '$name', '{$stage['type']}', $i, '$content', NOW(), NOW())");
            
            $i++;
        }
        
        echo "<h2 style='color:green'>✅ Stages Updated Successfully!</h2>";
        
    } catch (PDOException $e) {
        echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    }
}
?>
</body>
</html>