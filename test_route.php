<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Route;
use App\Models\EmailSequence;

$routes = Route::getRoutes();
$path = '/admin/marketing/sequences/1/edit';

foreach ($routes as $route) {
    if ($route->uri() === 'admin/marketing/sequences/{sequence}/edit') {
        echo "Route found: " . $route->uri() . "<br>";
        echo "Controller: " . get_class($route->getController()) . "<br>";
        echo "Method: sequencesEdit<br>";
        break;
    }
}

echo "<h2>Testing Route</h2>";
$sequence = EmailSequence::find(1);
if ($sequence) {
    echo "Sequence ID: " . $sequence->id . "<br>";
    echo "Sequence Name: " . $sequence->name . "<br>";
    $sequence->load('steps');
    echo "Steps: " . $sequence->steps->count() . "<br>";
    echo "<h3>View test</h3>";
    
    try {
        ob_start();
        view('admin.marketing.sequences.edit', compact('sequence'));
        $output = ob_get_clean();
        echo "View rendered successfully!<br>";
    } catch (Exception $e) {
        echo "View Error: " . $e->getMessage() . "<br>";
    }
}
