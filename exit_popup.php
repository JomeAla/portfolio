<?php
/**
 * Exit Intent Popup Script
 * Add this to landing pages for exit intent functionality
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Update landing page with exit intent enabled
    $popupContent = <<<'HTML'
<div id="exit-popup" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-8 max-w-md mx-4 relative shadow-2xl">
        <button onclick="document.getElementById('exit-popup').classList.add('hidden')" class="absolute top-2 right-4 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        
        <h3 class="text-2xl font-bold mb-2">Wait! Don't miss this...</h3>
        
        <p class="text-gray-600 mb-4">Get my <strong>Email Templates Pack</strong> (worth ₦15,000) for just <strong class="text-green-600">₦12,000</strong></p>
        
        <ul class="text-sm mb-4 space-y-2">
            <li>✓ 6 ready-to-use sequences</li>
            <li>✓ 24 tested templates</li>
            <li>✓ Launch, welcome, follow-up sequences</li>
        </ul>
        
        <a href="/email-templates" class="block bg-green-600 text-white text-center py-3 rounded-lg font-bold hover:bg-green-700">Get The Templates Pack</a>
        
        <p class="text-center text-sm text-gray-500 mt-3">
            <button onclick="document.getElementById('exit-popup').classList.add('hidden')" class="underline">No thanks, I'll pay full price</button>
        </p>
    </div>
</div>

<script>
let exitShown = false;
document.addEventListener('mouseleave', function(e) {
    if (e.clientY <= 0 && !exitShown) {
        exitShown = true;
        document.getElementById('exit-popup').classList.remove('hidden');
    }
});
</script>
HTML;
    
    $stmt = $pdo->prepare("UPDATE landing_pages SET popup_enabled = 1, popup_content = ?, popup_delay = 30 WHERE slug = 'free-email-checklist'");
    $stmt->execute([$popupContent]);
    
    echo "<h2>✅ Exit Intent Enabled!</h2>";
    echo "<p>Popup will show when user's mouse leaves the page (upward movement)</p>";
    echo "<p>Offer: Email Templates Pack at ₦12,000</p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}