<?php
/**
 * Update Landing Page with Download Link
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Updated HTML with download link
    $html = '<div class="text-center py-20">
        <h1 class="text-5xl font-bold text-slate-800 mb-6">Free Email Marketing Checklist</h1>
        <p class="text-xl text-slate-600 mb-8">Get my proven 10-step email checklist that gets results</p>
        
        <form method="POST" action="/l/free-email-checklist/submit" class="max-w-md mx-auto">
            <input type="text" name="name" placeholder="Your Name" required class="w-full border border-slate-300 rounded-lg px-4 py-3 mb-4">
            <input type="email" name="email" placeholder="Your Email" required class="w-full border border-slate-300 rounded-lg px-4 py-3 mb-4">
            <button type="submit" class="w-full bg-orange-500 text-white px-8 py-4 rounded-lg font-bold hover:bg-orange-600">Get My Free Checklist</button>
        </form>
        
        <div class="mt-8 text-sm text-slate-500">
            <p>Already downloaded? <a href="/download.php?file=email-marketing-checklist" class="text-orange-500 underline">Download again</a></p>
        </div>
    </div>';
    
    $stmt = $pdo->prepare("UPDATE landing_pages SET custom_html = ? WHERE slug = 'free-email-checklist'");
    $stmt->execute([$html]);
    
    // Create a sample blog post
    $blogHtml = <<<HTML
<p>Email marketing remains one of the most effective ways to reach your audience. But getting started can be overwhelming.</p>
<p>In this post, I'll share the essential checklist I use for every email campaign:</p>
<h2>1. Define Your Goal</h2>
<p>Before writing, know what you want to achieve. Sales? Brand awareness? Website traffic?</p>
<h2>2. Build Your List Right</h2>
<p>Never buy email lists. Grow organically through opt-ins, content, and referrals.</p>
<h2>3. Segment Your Audience</h2>
<p>Send the right message to the right people. One-size-fits-all doesn't work.</p>
<h2>4. Write Compelling Subject Lines</h2>
<p>Keep it under 50 characters. Personalize when possible. No spam words!</p>
<h2>5. Focus on ONE Call to Action</h2>
<p>Don't confuse readers. One clear action per email.</p>
<p>Want the complete checklist? Download my free Email Marketing Checklist!</p>
HTML;
    
    $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, excerpt, body, is_published, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW(), NOW())");
    $stmt->execute([
        'Email Marketing Checklist: Complete Guide for 2026',
        'email-marketing-checklist-guide',
        'The ultimate checklist for successful email marketing campaigns',
        $blogHtml
    ]);
    
    echo "<h2>✅ Updated!</h2>";
    echo "<p>Landing page updated with download link</p>";
    echo "<p>Blog post created</p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}