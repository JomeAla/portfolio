<?php
/**
 * Database Export Script
 * Access via: https://joala.com.ng/export_db.php
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $tables = ['pages', 'services', 'settings', 'products', 'orders', 'invoices', 
              'blog_posts', 'landing_pages', 'leads', 'email_sequences', 
              'sequences', 'sequence_steps', 'email_queue', 'email_templates',
              'campaigns', 'segments', 'leads', 'tags', 'lead_scores',
              'funnels', 'funnel_stages', 'funnel_leads', 'contacts'];
    
    $sql = "-- Database Export\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        try {
            // Get table data
            $stmt = $pdo->query("SELECT * FROM $table");
            $rows = $stmt->fetchAll();
            
            if (count($rows) > 0) {
                $sql .= "-- Table: $table (" . count($rows) . " rows)\n";
                
                // Get create statement
                $createStmt = $pdo->query("SHOW CREATE TABLE $table");
                $createRow = $createStmt->fetch();
                $sql .= "DROP TABLE IF EXISTS `$table`;\n";
                $sql .= $createRow['Create Table'] . ";\n\n";
                
                // Insert statements
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        $values[] = $value === null ? 'NULL' : "'" . addslashes($value) . "'";
                    }
                    $cols = array_keys($row);
                    $sql .= "INSERT INTO `$table` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        } catch (Exception $e) {
            $sql .= "-- Table $table: Error - " . $e->getMessage() . "\n\n";
        }
    }
    
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Save to file
    file_put_contents('/home/joalacom/public_html/joala_export.sql', $sql);
    echo "<h1>Database Exported!</h1>";
    echo "<p>File: joala_export.sql</p>";
    echo "<p>Size: " . filesize('/home/joalacom/public_html/joala_export.sql') . " bytes</p>";
    echo "<a href='/joala_export.sql'>Download</a>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}