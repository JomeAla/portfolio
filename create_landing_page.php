<?php
/**
 * Create Test Landing Page
 * Upload to public_html and access via browser
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create landing page for Free Email Checklist
    $html = '<div class="text-center py-20">
        <h1 class="text-5xl font-bold text-slate-800 mb-6">Free Email Marketing Checklist</h1>
        <p class="text-xl text-slate-600 mb-8">Get my proven 10-step email checklist that gets results</p>
        <form method="POST" action="/l/free-email-checklist/submit" class="max-w-md mx-auto">
            <input type="text" name="name" placeholder="Your Name" required class="w-full border border-slate-300 rounded-lg px-4 py-3 mb-4">
            <input type="email" name="email" placeholder="Your Email" required class="w-full border border-slate-300 rounded-lg px-4 py-3 mb-4">
            <button type="submit" class="w-full bg-orange-500 text-white px-8 py-4 rounded-lg font-bold hover:bg-orange-600">Get My Free Checklist</button>
        </form>
    </div>';
    
    $stmt = $pdo->prepare("INSERT INTO landing_pages (title, slug, custom_html, is_active, created_at, updated_at) VALUES (?, ?, ?, 1, NOW(), NOW())");
    $stmt->execute(['Free Email Checklist', 'free-email-checklist', $html]);
    
    // Create email sequence
    $stmt = $pdo->prepare("INSERT INTO email_sequences (name, description, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())");
    $stmt->execute(['Welcome Sequence', 'Automated welcome emails for new leads']);
    
    echo "<h2>✅ Landing Page Created!</h2>";
    echo "<p>Visit: <a href='https://joala.com.ng/l/free-email-checklist' style='color:blue;'>https://joala.com.ng/l/free-email-checklist</a></p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}