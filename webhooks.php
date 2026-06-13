<?php
error_reporting(0);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$result = $conn->query("SHOW TABLES LIKE 'webhooks'");
if ($result->num_rows == 0) {
    $conn->query("CREATE TABLE webhooks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        url VARCHAR(500) NOT NULL,
        events JSON,
        is_active BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Created webhooks table<br>";
} else {
    echo "✓ webhooks table exists<br>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $stmt = $conn->prepare("INSERT INTO webhooks (name, url, events) VALUES (?, ?, ?)");
        $events = json_encode($_POST['events'] ?? ['lead_created']);
        $stmt->bind_param("sss", $_POST['name'], $_POST['url'], $events);
        $stmt->execute();
        echo "✅ Webhook created<br>";
    } elseif ($_POST['action'] === 'delete') {
        $conn->query("DELETE FROM webhooks WHERE id = " . intval($_POST['id']));
        echo "✅ Webhook deleted<br>";
    } elseif ($_POST['action'] === 'toggle') {
        $conn->query("UPDATE webhooks SET is_active = NOT is_active WHERE id = " . intval($_POST['id']));
        echo "✅ Webhook toggled<br>";
    }
}

$webhooks = $conn->query("SELECT * FROM webhooks ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webhooks - Joala Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4">
        <div class="flex items-center justify-between max-w-5xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="/admin/marketing" class="text-slate-600 hover:text-slate-800"><i class="fas fa-arrow-left"></i></a>
                <h1 class="text-xl font-bold text-slate-800">Webhooks</h1>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto mt-6 px-6 pb-12">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Create New Webhook</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Name</label>
                    <input type="text" name="name" required placeholder="e.g., Notify CRM" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Webhook URL</label>
                    <input type="url" name="url" required placeholder="https://your-server.com/webhook" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Trigger Events</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="events[]" value="lead_created" checked class="rounded">
                            <span class="text-sm">Lead Created</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="events[]" value="lead_converted" class="rounded">
                            <span class="text-sm">Lead Converted</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="events[]" value="email_opened" class="rounded">
                            <span class="text-sm">Email Opened</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="events[]" value="email_clicked" class="rounded">
                            <span class="text-sm">Email Clicked</span>
                        </label>
                    </div>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    Create Webhook
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-800">Active Webhooks</h3>
            </div>
            <?php if ($webhooks->num_rows === 0): ?>
            <div class="p-6 text-center text-slate-500">No webhooks created yet</div>
            <?php else: ?>
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">URL</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Events</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php while ($webhook = $webhooks->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800"><?= htmlspecialchars($webhook['name']) ?></td>
                        <td class="px-4 py-3 text-sm text-slate-500"><?= htmlspecialchars($webhook['url']) ?></td>
                        <td class="px-4 py-3">
                            <?php foreach (json_decode($webhook['events']) as $event): ?>
                            <span class="text-xs bg-slate-100 px-2 py-1 rounded mr-1"><?= htmlspecialchars($event) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $webhook['id'] ?>">
                                <button type="submit" class="text-xs <?= $webhook['is_active'] ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $webhook['is_active'] ? 'Active' : 'Inactive' ?>
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $webhook['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>