<?php
// Create customer_notifications table migration
// Run: curl -s https://joala.com.ng/create_notifications_table.php

$host = 'localhost';
$db = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if table exists
    $check = $pdo->query("SHOW TABLES LIKE 'customer_notifications'");
    if ($check->rowCount() > 0) {
        echo "✅ Table 'customer_notifications' already exists\n";
    } else {
        $sql = "CREATE TABLE customer_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_email VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT,
            link VARCHAR(500),
            is_read TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_customer (customer_email),
            INDEX idx_read (is_read),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($sql);
        echo "✅ Table 'customer_notifications' created\n";
    }
    
    // Check customer_accounts exists
    $check = $pdo->query("SHOW TABLES LIKE 'customer_accounts'");
    if ($check->rowCount() == 0) {
        $sql = "CREATE TABLE customer_accounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        echo "✅ Table 'customer_accounts' created\n";
    }
    
    // Check customer_sessions exists
    $check = $pdo->query("SHOW TABLES LIKE 'customer_sessions'");
    if ($check->rowCount() == 0) {
        $sql = "CREATE TABLE customer_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            token VARCHAR(255) UNIQUE NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        echo "✅ Table 'customer_sessions' created\n";
    }
    
    // Check customer_referrals exists
    $check = $pdo->query("SHOW TABLES LIKE 'customer_referrals'");
    if ($check->rowCount() == 0) {
        $sql = "CREATE TABLE customer_referrals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_email VARCHAR(255) NOT NULL,
            referral_code VARCHAR(50) UNIQUE NOT NULL,
            referred_by VARCHAR(255),
            referral_count INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        echo "✅ Table 'customer_referrals' created\n";
    }
    
    // Check customer_achievements exists
    $check = $pdo->query("SHOW TABLES LIKE 'customer_achievements'");
    if ($check->rowCount() == 0) {
        $sql = "CREATE TABLE customer_achievements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_email VARCHAR(255) NOT NULL,
            achievement_id INT NOT NULL,
            awarded TINYINT(1) DEFAULT 0,
            awarded_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        echo "✅ Table 'customer_achievements' created\n";
    }
    
    // Check refund_requests exists
    $check = $pdo->query("SHOW TABLES LIKE 'refund_requests'");
    if ($check->rowCount() == 0) {
        $sql = "CREATE TABLE refund_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            reason VARCHAR(100),
            details TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        echo "✅ Table 'refund_requests' created\n";
    }
    
    // Check course_enrollments exists
    $check = $pdo->query("SHOW TABLES LIKE 'course_enrollments'");
    if ($check->rowCount() == 0) {
        $sql = "CREATE TABLE course_enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_email VARCHAR(255) NOT NULL,
            course_id INT NOT NULL,
            order_id INT,
            progress INT DEFAULT 0,
            enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME DEFAULT NULL,
            INDEX idx_customer (customer_email),
            UNIQUE KEY unique_enrollment (customer_email, course_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $pdo->exec($sql);
        echo "✅ Table 'course_enrollments' created\n";
    }
    
    echo "\n✅ All customer tables ready!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}