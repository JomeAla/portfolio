<?php
/**
 * Set up Facebook/Google Analytics pixels on funnels
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Add pixel columns to funnels table
    try { $pdo->exec("ALTER TABLE funnels ADD COLUMN facebook_pixel TEXT"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE funnels ADD COLUMN google_pixel TEXT"); } catch (Exception $e) {}
    
    // Sample Facebook Pixel (replace with real pixel ID)
    $fbPixel = <<<'HTML'
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', 'YOUR_PIXEL_ID_HERE');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=YOUR_PIXEL_ID_HERE&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
HTML;

    // Sample Google Analytics Tag
    $gaTag = <<<'HTML'
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>
<!-- End Google Analytics -->
HTML;

    // Update the WordPress Starter Kit funnel with pixels
    $stmt = $pdo->prepare("UPDATE funnels SET facebook_pixel = ?, google_pixel = ? WHERE slug = 'wordpress-starter-kit'");
    $stmt->execute([$fbPixel, $gaTag]);
    
    echo "<h2>✅ Pixels Added!</h2>";
    echo "<p>Facebook & Google Analytics pixels configured for WordPress Starter Kit funnel</p>";
    echo "<div style='background:#f1f5f9;padding:10px;border-radius:5px;margin:10px 0;'>";
    echo "<p><strong>Note:</strong> Replace these placeholders with real IDs:</p>";
    echo "<ul>";
    echo "<li>facebook: 'YOUR_PIXEL_ID_HERE'</li>";
    echo "<li>google: 'GA_MEASUREMENT_ID'</li>";
    echo "</ul>";
    echo "</div>";
    echo "<p>Update the funnel in admin panel for real tracking IDs</p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}