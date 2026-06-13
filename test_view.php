<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmailSequence;
use Illuminate\Support\Facades\View;

$sequence = EmailSequence::with('steps')->find(1);

if (!$sequence) {
    die("Sequence not found!");
}

try {
    $html = View::make('admin.marketing.sequences.edit', compact('sequence'))->render();
    echo "<h1>SUCCESS!</h1>";
    echo "<p>Sequence: " . htmlspecialchars($sequence->name) . "</p>";
    echo "<p>Steps: " . $sequence->steps->count() . "</p>";
    echo "<p>View rendered without error!</p>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
