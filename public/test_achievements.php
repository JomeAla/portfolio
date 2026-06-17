<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
    
    echo "<h2>Achievements Debug</h2>";
    
    // Check achievements table
    $ach = $pdo->query("SELECT COUNT(*) FROM achievements")->fetchColumn();
    echo "<p>achievements table: $ach rows</p>";
    
    // Check customer_achievements
    $ca = $pdo->query("SELECT COUNT(*) FROM customer_achievements")->fetchColumn();
    echo "<p>customer_achievements table: $ca rows</p>";
    
    // Test AchievementService
    try {
        $svc = app(\App\Services\AchievementService::class);
        echo "<p>AchievementService: resolvable ✅</p>";
    } catch (\Exception $e) {
        echo "<p style='color:red'>AchievementService error: " . $e->getMessage() . "</p>";
    }
    
    // Test Achievement model
    try {
        $all = \App\Models\Achievement::where('is_active', true)->get();
        echo "<p>Achievement model: " . $all->count() . " active achievements ✅</p>";
    } catch (\Exception $e) {
        echo "<p style='color:red'>Achievement model error: " . $e->getMessage() . "</p>";
    }
    
} catch (\Exception $e) {
    echo "<h2 style='color:red'>Error: " . $e->getMessage() . "</h2>";
}
