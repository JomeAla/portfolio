<?php
error_reporting(0);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Campaign;
use App\Models\EmailSequence;

$campaigns = Campaign::orderBy('created_at', 'desc')->get();
$sequences = EmailSequence::where('is_active', true)->get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $sequence_ids = $_POST['sequence_ids'] ?? [];
    
    Campaign::create([
        'name' => $name,
        'description' => $description,
        'status' => $status,
        'sequence_ids' => json_encode($sequence_ids),
    ]);
    
    echo "<script>alert('Campaign created!'); window.location.href = '/campaigns.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns - Joala Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="/admin/marketing" class="text-slate-600 hover:text-slate-800"><i class="fas fa-arrow-left"></i></a>
                <h1 class="text-xl font-bold text-slate-800">Campaigns</h1>
            </div>
            <a href="/admin/marketing/campaigns/create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i>New Campaign
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto mt-6 px-6">
        <?php if ($campaigns->count() === 0): ?>
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-bullhorn text-4xl text-slate-300 mb-4"></i>
            <h2 class="text-xl font-bold text-slate-800 mb-2">No campaigns yet</h2>
            <p class="text-slate-600 mb-6">Create your first campaign to group multiple sequences</p>
            <a href="/admin/marketing/campaigns/create" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">Create Campaign</a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($campaigns as $campaign): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="font-bold text-slate-800"><?= htmlspecialchars($campaign->name) ?></h3>
                    <span class="px-2 py-1 text-xs rounded <?= $campaign->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700' ?>">
                        <?= ucfirst($campaign->status) ?>
                    </span>
                </div>
                <p class="text-sm text-slate-600 mb-4"><?= htmlspecialchars($campaign->description ?? 'No description') ?></p>
                
                <?php if ($campaign->sequence_ids): ?>
                <div class="flex flex-wrap gap-1 mb-4">
                    <?php foreach (json_decode($campaign->sequence_ids) as $seqId): ?>
                        <?php $seq = $sequences->firstWhere('id', $seqId); ?>
                        <?php if ($seq): ?>
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded"><?= htmlspecialchars($seq->name) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="text-xs text-slate-500 mb-4">
                    <i class="fas fa-users mr-1"></i> <?= $campaign->campaignLeads()->count() ?> leads
                </div>
                
                <div class="flex gap-2">
                    <button onclick="alert('Edit not available in standalone mode')" class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="max-w-7xl mx-auto mt-12 px-6 pb-12">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Quick Stats</h2>
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-slate-800"><?= $campaigns->count() ?></div>
                <div class="text-sm text-slate-600">Total Campaigns</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-green-600"><?= $campaigns->where('status', 'active')->count() ?></div>
                <div class="text-sm text-slate-600">Active</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-indigo-600"><?= $sequences->count() ?></div>
                <div class="text-sm text-slate-600">Available Sequences</div>
            </div>
        </div>
    </div>
</body>
</html>