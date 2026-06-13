<?php
error_reporting(0);

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$result = $conn->query("SHOW TABLES LIKE 'ab_tests'");
if ($result->num_rows == 0) {
    $conn->query("CREATE TABLE ab_tests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        subject_a VARCHAR(500),
        subject_b VARCHAR(500),
        body_a TEXT,
        body_b TEXT,
        sequence_step_id INT,
        status VARCHAR(20) DEFAULT 'draft',
        winner VARCHAR(10),
        opens_a INT DEFAULT 0,
        opens_b INT DEFAULT 0,
        clicks_a INT DEFAULT 0,
        clicks_b INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Created ab_tests table<br>";
} else {
    echo "✓ ab_tests table exists<br>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $stmt = $conn->prepare("INSERT INTO ab_tests (name, subject_a, subject_b, body_a, body_b, sequence_step_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stepId = $_POST['sequence_step_id'] ? intval($_POST['sequence_step_id']) : null;
        $stmt->bind_param("sssssi", $_POST['name'], $_POST['subject_a'], $_POST['subject_b'], $_POST['body_a'], $_POST['body_b'], $stepId);
        $stmt->execute();
        echo "✅ A/B Test created<br>";
    } elseif ($_POST['action'] === 'start') {
        $conn->query("UPDATE ab_tests SET status = 'running' WHERE id = " . intval($_POST['id']));
        echo "✅ Test started<br>";
    } elseif ($_POST['action'] === 'stop') {
        $conn->query("UPDATE ab_tests SET status = 'completed' WHERE id = " . intval($_POST['id']));
        echo "✅ Test stopped<br>";
    } elseif ($_POST['action'] === 'delete') {
        $conn->query("DELETE FROM ab_tests WHERE id = " . intval($_POST['id']));
        echo "✅ Test deleted<br>";
    } elseif ($_POST['action'] === 'record_open') {
        $testId = intval($_POST['id']);
        $variant = $_POST['variant'];
        $conn->query("UPDATE ab_tests SET {$variant} = {$variant} + 1 WHERE id = $testId");
    } elseif ($_POST['action'] === 'record_click') {
        $testId = intval($_POST['id']);
        $variant = $_POST['variant'];
        $conn->query("UPDATE ab_tests SET clicks_{$variant} = clicks_{$variant} + 1 WHERE id = $testId");
    }
}

$tests = $conn->query("SELECT * FROM ab_tests ORDER BY created_at DESC");
$sequences = $conn->query("SELECT * FROM email_sequences WHERE is_active = 1");
$steps = $conn->query("SELECT ss.*, es.name as seq_name FROM sequence_steps ss JOIN email_sequences es ON ss.sequence_id = es.id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A/B Testing - Joala Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="/admin/marketing" class="text-slate-600 hover:text-slate-800"><i class="fas fa-arrow-left"></i></a>
                <h1 class="text-xl font-bold text-slate-800">A/B Testing</h1>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto mt-6 px-6 pb-12">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-bold text-blue-800 mb-2"><i class="fas fa-info-circle mr-2"></i>How A/B Testing Works</h3>
            <p class="text-sm text-blue-700">Create tests for email subject lines or content. Leads receive random variations (A or B). Track opens and clicks to find the winner.</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Create New Test</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Test Name</label>
                    <input type="text" name="name" required placeholder="e.g., Welcome Email Subject Test" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Subject Line A</label>
                        <input type="text" name="subject_a" required placeholder="Version A subject" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Subject Line B</label>
                        <input type="text" name="subject_b" required placeholder="Version B subject" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email Body A (optional)</label>
                        <textarea name="body_a" rows="3" placeholder="Version A content" class="w-full border border-slate-300 rounded-lg px-4 py-2"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email Body B (optional)</label>
                        <textarea name="body_b" rows="3" placeholder="Version B content" class="w-full border border-slate-300 rounded-lg px-4 py-2"></textarea>
                    </div>
                </div>
                
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    Create Test
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-800">Active Tests</h3>
            </div>
            <?php if ($tests->num_rows === 0): ?>
            <div class="p-6 text-center text-slate-500">No tests yet</div>
            <?php else: ?>
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Test</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Subject A vs B</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Results</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php while ($test = $tests->fetch_assoc()): 
                        $totalA = $test['opens_a'] + $test['clicks_a'];
                        $totalB = $test['opens_b'] + $test['clicks_b'];
                        $winner = '';
                        if ($totalA > $totalB) $winner = 'A';
                        elseif ($totalB > $totalA) $winner = 'B';
                    ?>
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800"><?= htmlspecialchars($test['name']) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <div class="text-slate-600">A: <?= htmlspecialchars($test['subject_a']) ?></div>
                            <div class="text-slate-600">B: <?= htmlspecialchars($test['subject_b']) ?></div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-4">
                                <div class="text-center">
                                    <div class="text-lg font-bold <?= $winner === 'A' ? 'text-green-600' : 'text-slate-800' ?>"><?= $test['opens_a'] ?></div>
                                    <div class="text-xs text-slate-500">A Opens</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold <?= $winner === 'B' ? 'text-green-600' : 'text-slate-800' ?>"><?= $test['opens_b'] ?></div>
                                    <div class="text-xs text-slate-500">B Opens</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-blue-600"><?= $test['clicks_a'] ?></div>
                                    <div class="text-xs text-slate-500">A Clicks</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-blue-600"><?= $test['clicks_b'] ?></div>
                                    <div class="text-xs text-slate-500">B Clicks</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded 
                                <?= $test['status'] === 'running' ? 'bg-green-100 text-green-700' : 
                                   ($test['status'] === 'completed' ? 'bg-slate-100 text-slate-700' : 'bg-yellow-100 text-yellow-700') ?>">
                                <?= ucfirst($test['status']) ?>
                            </span>
                            <?php if ($winner): ?>
                            <span class="ml-2 text-xs font-bold text-green-600">Winner: <?= $winner ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <?php if ($test['status'] === 'draft'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="start">
                                <input type="hidden" name="id" value="<?= $test['id'] ?>">
                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm mr-2">Start</button>
                            </form>
                            <?php elseif ($test['status'] === 'running'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="stop">
                                <input type="hidden" name="id" value="<?= $test['id'] ?>">
                                <button type="submit" class="text-yellow-600 hover:text-yellow-800 text-sm mr-2">Stop</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $test['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete?')">Delete</button>
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