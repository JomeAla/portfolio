<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmailSequence;

$sequence = EmailSequence::with('steps')->find(1);

if (!$sequence) {
    die("Sequence not found!");
}

echo "<h1>Edit Sequence: " . htmlspecialchars($sequence->name) . "</h1>";
echo "<p>Steps count: " . $sequence->steps->count() . "</p>";

foreach ($sequence->steps as $step) {
    echo "<hr>";
    echo "<h3>Step " . $step->step_number . ": " . htmlspecialchars($step->subject) . "</h3>";
    echo "<p>Delay: " . $step->delay_days . " days</p>";
}
