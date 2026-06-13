<?php
/**
 * Create WhatsApp Marketing Bundle Product
 * Run: https://www.joala.com.ng/create_whatsapp_product.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\Log;

header('Content-Type: text/plain');
echo "=== Creating WhatsApp Marketing Bundle Product ===\n\n";

try {
    $product = Product::updateOrCreate(
        ['slug' => 'whatsapp-marketing-bundle'],
        [
            'title' => 'WhatsApp Marketing Bundle',
            'slug' => 'whatsapp-marketing-bundle',
            'description' => '48 ready-to-send WhatsApp templates for business. Includes broadcast sequences, auto-replies, status templates, chatbot flows, and order fulfillment sequences.',
            'price' => 15000,
            'sale_price' => 8000,
            'file_path' => 'uploads/products/files/whatsapp-marketing-bundle.html',
            'image' => '/uploads/products/whatsapp-marketing-bundle-cover.svg',
            'is_active' => true,
            'is_featured' => true,
        ]
    );
    echo "Created Product: WhatsApp Marketing Bundle (ID: {$product->id})\n";
    echo "Price: ₦15,000 | Sale Price: ₦8,000\n";

    echo "\n=== DONE ===\n";
    echo "Product ID: {$product->id}\n";
    echo "URL: https://www.joala.com.ng/whatsapp-marketing-bundle\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}