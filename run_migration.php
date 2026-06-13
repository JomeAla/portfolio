<?php
/**
 * Complete Marketing Module Migration
 * Upload to public_html and access via browser
 * URL: https://joala.com.ng/run_migration.php
 * 
 * Run this once to create all marketing tables
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Running Complete Marketing Module Migration...</h2>";
    echo "<pre style='background:#1a202c;color:#e2e8f0;padding:20px;border-radius:8px;'>";
    
    // 1. Email Sequences (create first - others depend on it)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS email_sequences (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
    ");
    echo "✅ email_sequences\n";
    
    // 2. Landing Pages
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS landing_pages (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        custom_html TEXT,
        countdown_enabled TINYINT(1) DEFAULT 0,
        countdown_end VARCHAR(255) DEFAULT NULL,
        countdown_message VARCHAR(255) DEFAULT NULL,
        countdown_hours INT DEFAULT 24,
        sequence_id BIGINT UNSIGNED NULL,
        funnel_id BIGINT UNSIGNED NULL,
        popup_enabled TINYINT(1) DEFAULT 0,
        popup_delay INT DEFAULT 30,
        popup_content TEXT,
        popup_discount DECIMAL(5,2) DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE SET NULL
    ) ENGINE=InnoDB
    ");
    echo "✅ landing_pages\n";
    
    // 3. Leads
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS leads (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        name VARCHAR(255),
        phone VARCHAR(50),
        landing_page_id BIGINT UNSIGNED NULL,
        sequence_id BIGINT UNSIGNED NULL,
        status ENUM('active','unsubscribed') DEFAULT 'active',
        score INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (landing_page_id) REFERENCES landing_pages(id) ON DELETE SET NULL,
        FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE SET NULL
    ) ENGINE=InnoDB
    ");
    echo "✅ leads\n";
    
    // 4. Sequence Steps
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS sequence_steps (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        sequence_id BIGINT UNSIGNED NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body TEXT,
        delay_days INT DEFAULT 0,
        step_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
    ");
    echo "✅ sequence_steps\n";
    
    // 5. Email Queue
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS email_queue (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lead_id BIGINT UNSIGNED NOT NULL,
        step_id BIGINT UNSIGNED NOT NULL,
        scheduled_send_time TIMESTAMP NOT NULL,
        status ENUM('pending','sent','failed') DEFAULT 'pending',
        error_message TEXT,
        sent_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
        FOREIGN KEY (step_id) REFERENCES sequence_steps(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
    ");
    echo "✅ email_queue\n";
    
    // 6. Blog Posts
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS blog_posts (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        excerpt TEXT,
        body LONGTEXT,
        meta_title VARCHAR(255),
        meta_description TEXT,
        featured_image VARCHAR(500),
        funnel_id BIGINT UNSIGNED NULL,
        popup_enabled TINYINT(1) DEFAULT 0,
        popup_content TEXT,
        is_published TINYINT(1) DEFAULT 0,
        post_to_twitter TINYINT(1) DEFAULT 0,
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
    ");
    echo "✅ blog_posts\n";
    
    // 7. Tweet Queue
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS tweet_queue (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        content TEXT NOT NULL,
        blog_post_id BIGINT UNSIGNED NULL,
        scheduled_send_time TIMESTAMP NULL,
        status ENUM('draft','scheduled','sent','failed') DEFAULT 'draft',
        twitter_response TEXT,
        error_message TEXT,
        sent_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (blog_post_id) REFERENCES blog_posts(id) ON DELETE SET NULL
    ) ENGINE=InnoDB
    ");
    echo "✅ tweet_queue\n";
    
    // 8. Email Opens (for tracking)
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS email_opens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email_queue_id BIGINT UNSIGNED NOT NULL,
        opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ip_address VARCHAR(45),
        user_agent TEXT,
        FOREIGN KEY (email_queue_id) REFERENCES email_queue(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
    ");
    echo "✅ email_opens\n";
    
    // 9. Twitter Settings
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS twitter_settings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        access_token TEXT,
        refresh_token TEXT,
        token_type VARCHAR(50),
        expires_at INT,
        client_id TEXT,
        client_secret TEXT,
        oauth_token VARCHAR(255),
        oauth_token_secret VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
    ");
    echo "✅ twitter_settings\n";
    
    // 10. F funnels
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS funnels (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) DEFAULT NULL,
        description TEXT,
        funnel_type VARCHAR(50) DEFAULT NULL,
        goal VARCHAR(50) DEFAULT NULL,
        product_id BIGINT UNSIGNED NULL,
        service_id BIGINT UNSIGNED NULL,
        is_active TINYINT(1) DEFAULT 1,
        automation_enabled TINYINT(1) DEFAULT 0,
        welcome_sequence_id BIGINT UNSIGNED NULL,
        followup_sequence_id BIGINT UNSIGNED NULL,
        webhook_url TEXT,
        webhook_enabled TINYINT(1) DEFAULT 0,
        notify_email VARCHAR(255) DEFAULT NULL,
        upsell_enabled TINYINT(1) DEFAULT 0,
        upsell_product_id BIGINT UNSIGNED NULL,
        upsell_discount DECIMAL(5,2) DEFAULT NULL,
        upsell_timer INT DEFAULT NULL,
        facebook_pixel TEXT,
        google_pixel TEXT,
        countdown_enabled TINYINT(1) DEFAULT 0,
        countdown_hours INT DEFAULT NULL,
        thank_you_title VARCHAR(255) DEFAULT NULL,
        thank_you_message TEXT,
        thank_you_video VARCHAR(255) DEFAULT NULL,
        upsell_button_text VARCHAR(255) DEFAULT NULL,
        exit_popup_enabled TINYINT(1) DEFAULT 0,
        exit_popup_offer TEXT,
        exit_popup_discount DECIMAL(5,2) DEFAULT NULL,
        starts_at TIMESTAMP NULL,
        ends_at TIMESTAMP NULL,
        order_bumps JSON DEFAULT NULL,
        refund_policy VARCHAR(50) DEFAULT 'days',
        refund_period_days INT DEFAULT 30,
        affiliate_enabled TINYINT(1) DEFAULT 0,
        affiliate_commission DECIMAL(5,2) DEFAULT 20.00,
        affiliate_cookie_days INT DEFAULT 30,
        score_per_page INT DEFAULT 5,
        score_per_email INT DEFAULT 10,
        score_per_checkout INT DEFAULT 20,
        score_hot_threshold INT DEFAULT 100,
        hot_lead_tag VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
    ");
    echo "✅ funnels\n";
    
    // 11. Funnel Stages
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS funnel_stages (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        funnel_id BIGINT UNSIGNED NOT NULL,
        name VARCHAR(255) NOT NULL,
        type VARCHAR(50) DEFAULT NULL,
        content LONGTEXT,
        item_order INT DEFAULT 0,
        delay_days INT DEFAULT 0,
        is_required TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (funnel_id) REFERENCES funnels(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
    ");
    echo "✅ funnel_stages\n";
    
    // 12. Funnel Leads
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS funnel_leads (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        funnel_id BIGINT UNSIGNED NOT NULL,
        lead_id BIGINT UNSIGNED NULL,
        stage_id BIGINT UNSIGNED NULL,
        email VARCHAR(255) DEFAULT NULL,
        source VARCHAR(255) DEFAULT NULL,
        converted TINYINT(1) DEFAULT 0,
        entered_at TIMESTAMP NULL,
        exited_at TIMESTAMP NULL,
        score INT DEFAULT 0,
        last_activity DATETIME DEFAULT NULL,
        times_visited INT DEFAULT 0,
        pages_viewed INT DEFAULT 0,
        email_opens INT DEFAULT 0,
        is_tagged_hot TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (funnel_id) REFERENCES funnels(id) ON DELETE CASCADE,
        FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL
    ) ENGINE=InnoDB
    ");
    echo "✅ funnel_leads\n";
    
    echo "\n🎉 All marketing tables created successfully!\n";
    echo "\nTables created:\n";
    echo "  • email_sequences\n";
    echo "  • landing_pages\n";
    echo "  • leads\n";
    echo "  • sequence_steps\n";
    echo "  • email_queue\n";
    echo "  • blog_posts\n";
    echo "  • tweet_queue\n";
    echo "  • email_opens\n";
    echo "  • twitter_settings\n";
    echo "  • funnels\n";
    echo "  • funnel_stages\n";
    echo "  • funnel_leads\n";
    
    // Show next steps
    echo "\n📋 Next steps:\n";
    echo "  1. Go to Admin > Marketing > Landing Pages to create a page\n";
    echo "  2. Go to Admin > Marketing > Funnels to create a funnel\n";
    echo "  3. Visit /l/your-slug to test landing page\n";
    echo "  4. Visit /f/your-funnel to test funnel\n";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
echo "</pre>";

echo '<div style="margin-top:20px;">';
echo '<a href="/admin/marketing/landing-pages" style="display:inline-block;background:#2563eb;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;margin-right:10px;">Go to Landing Pages</a>';
echo '<a href="/admin/marketing/funnels" style="display:inline-block;background:#059669;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;">Go to Funnels</a>';
echo '</div>';