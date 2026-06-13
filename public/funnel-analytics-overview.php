<?php
/**
 * Funnel Analytics Overview
 * Shows all funnels performance in one view
 */

require __DIR__ . '/bootstrap/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Funnel;
use App\Models\FunnelLead;
use App\Models\Product;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Funnel Analytics Overview</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; padding: 20px; }
        h1 { color: #1a1a2e; margin-bottom: 10px; }
        h2 { color: #16213e; margin: 25px 0 15px; font-size: 1.3em; }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; border-radius: 10px; margin-bottom: 25px; }
        .header p { opacity: 0.9; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { color: #666; font-size: 0.85em; font-weight: normal; margin-bottom: 5px; }
        .stat-card .value { font-size: 2em; font-weight: bold; color: #1a1a2e; }
        
        .funnel-table { width: 100%; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .funnel-table th, .funnel-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .funnel-table th { background: #1a1a2e; color: white; }
        .funnel-table tr:hover { background: #f8f9fa; }
        .funnel-table a { color: #667eea; text-decoration: none; }
        .funnel-table a:hover { text-decoration: underline; }
        
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 0.8em; }
        .badge-active { background: #d4edda; color: #155724; }
        .badge-inactive { background: #f8d7da; color: #721c24; }
        
        .conversion-rate { font-weight: bold; color: #667eea; }
        .revenue { font-weight: bold; color: #28a745; }
        .leads { color: #666; }
        
        .empty { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>Funnel Analytics Overview</h1>
        <p>All your funnels performance at a glance</p>
    </div>
    
    <div class='stats-grid'>";

$totalFunnels = Funnel::where('is_active', 1)->count();
$totalLeads = FunnelLead::count();
$totalConverted = FunnelLead::where('converted', true)->count();
$totalRevenue = FunnelLead::where('converted', true)->count() * 5000; // Estimate

$conversionRate = $totalLeads > 0 ? round(($totalConverted / $totalLeads) * 100, 1) : 0;

echo "
        <div class='stat-card'>
            <h3>Active Funnels</h3>
            <div class='value'>$totalFunnels</div>
        </div>
        <div class='stat-card'>
            <h3>Total Leads</h3>
            <div class='value'>$totalLeads</div>
        </div>
        <div class='stat-card'>
            <h3>Conversions</h3>
            <div class='value'>$totalConverted</div>
        </div>
        <div class='stat-card'>
            <h3>Conversion Rate</h3>
            <div class='value'>$conversionRate%</div>
        </div>
    </div>
    
    <h2>All Funnels Performance</h2>
    <table class='funnel-table'>
        <thead>
            <tr>
                <th>Funnel</th>
                <th>Type</th>
                <th>Leads</th>
                <th>Hot</th>
                <th>Converted</th>
                <th>Conv. Rate</th>
                <th>Revenue (Est.)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>";

$funnels = Funnel::where('is_active', 1)->orderBy('name')->get();

if ($funnels->isEmpty()) {
    echo "<tr><td colspan='8' class='empty'>No funnels found. Create your first funnel!</td></tr>";
} else {
    foreach ($funnels as $funnel) {
        $leads = FunnelLead::where('funnel_id', $funnel->id)->count();
        $hot = FunnelLead::where('funnel_id', $funnel->id)->where('is_tagged_hot', true)->count();
        $converted = FunnelLead::where('funnel_id', $funnel->id)->where('converted', true)->count();
        $convRate = $leads > 0 ? round(($converted / $leads) * 100, 1) : 0;
        
        $product = $funnel->product;
        $price = $product ? ($product->sale_price ?? $product->price) : 0;
        $revenue = $converted * $price;
        
        $type = $funnel->funnel_type ?? 'direct';
        
        echo "<tr>
            <td><strong>{$funnel->name}</strong></td>
            <td><span class='badge badge-active'>$type</span></td>
            <td class='leads'>$leads</td>
            <td>$hot</td>
            <td>$converted</td>
            <td class='conversion-rate'>$convRate%</td>
            <td class='revenue'>N" . number_format($revenue) . "</td>
            <td><a href='/admin/marketing/funnels/{$funnel->id}/analytics'>View Analytics</a></td>
        </tr>";
    }
}

echo "        </tbody>
    </table>
    
    <h2>Top Converting Funnels</h2>
    <table class='funnel-table'>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Funnel</th>
                <th>Leads</th>
                <th>Converted</th>
                <th>Conversion Rate</th>
            </tr>
        </thead>
        <tbody>";

$topFunnels = Funnel::where('is_active', 1)
    ->get()
    ->map(function($f) {
        $leads = FunnelLead::where('funnel_id', $f->id)->count();
        $converted = FunnelLead::where('funnel_id', $f->id)->where('converted', true)->count();
        $f->leads_count = $leads;
        $f->converted_count = $converted;
        $f->conv_rate = $leads > 0 ? ($converted / $leads) * 100 : 0;
        return $f;
    })
    ->sortByDesc('conv_rate')
    ->take(5);

$rank = 1;
foreach ($topFunnels as $f) {
    $rate = round($f->conv_rate, 1);
    echo "<tr>
        <td>#$rank</td>
        <td><strong>{$f->name}</strong></td>
        <td>{$f->leads_count}</td>
        <td>{$f->converted_count}</td>
        <td class='conversion-rate'>$rate%</td>
    </tr>";
    $rank++;
}

echo "        </tbody>
    </table>
    
    <p style='margin-top: 30px; color: #666; font-size: 0.9em;'>
        Generated: " . date('Y-m-d H:i:s') . " | 
        <a href='/admin/marketing/funnels' style='color: #667eea;'>Manage Funnels</a>
    </p>
</body>
</html>";