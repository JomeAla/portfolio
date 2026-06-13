<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Funnel;

echo "<h1>Calculate Health Score</h1>";

$funnel = Funnel::find(26);
if ($funnel) {
    $result = $funnel->calculateHealthScore();
    echo "<p>Funnel: {$funnel->name}</p>";
    echo "<p><strong>Health Score: {$result['score']}</strong></p>";
    echo "<h3>Issues:</h3><ul>";
    foreach ($result['issues'] as $issue) {
        echo "<li>{$issue}</li>";
    }
    echo "</ul>";
    
    // Verify in DB
    $updated = Funnel::find(26);
    echo "<p>Stored health_score: {$updated->health_score}</p>";
} else {
    echo "<p>Funnel not found</p>";
}