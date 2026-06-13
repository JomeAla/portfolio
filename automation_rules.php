<?php
error_reporting(0);

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$result = $conn->query("SHOW TABLES LIKE 'automation_rules'");
if ($result->num_rows == 0) {
    $conn->query("CREATE TABLE automation_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        trigger_type VARCHAR(50) NOT NULL,
        trigger_value VARCHAR(255),
        action_type VARCHAR(50) NOT NULL,
        action_sequence_id INT,
        action_step_id INT,
        is_active BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Created automation_rules table<br>";
} else {
    echo "✓ automation_rules table exists<br>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $stmt = $conn->prepare("INSERT INTO automation_rules (name, trigger_type, trigger_value, action_type, action_sequence_id, action_step_id) VALUES (?, ?, ?, ?, ?, ?)");
        $seqId = $_POST['action_sequence_id'] ? intval($_POST['action_sequence_id']) : null;
        $stepId = $_POST['action_step_id'] ? intval($_POST['action_step_id']) : null;
        $stmt->bind_param("sssiii", $_POST['name'], $_POST['trigger_type'], $_POST['trigger_value'], $_POST['action_type'], $seqId, $stepId);
        $stmt->execute();
        echo "✅ Rule created<br>";
    } elseif ($_POST['action'] === 'delete') {
        $conn->query("DELETE FROM automation_rules WHERE id = " . intval($_POST['id']));
        echo "✅ Rule deleted<br>";
    } elseif ($_POST['action'] === 'toggle') {
        $conn->query("UPDATE automation_rules SET is_active = NOT is_active WHERE id = " . intval($_POST['id']));
        echo "✅ Rule toggled<br>";
    }
}

$rules = $conn->query("SELECT * FROM automation_rules ORDER BY created_at DESC");
$sequences = $conn->query("SELECT * FROM email_sequences WHERE is_active = 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automation Rules - Joala Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4">
        <div class="flex items-center justify-between max-w-5xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="/admin/marketing" class="text-slate-600 hover:text-slate-800"><i class="fas fa-arrow-left"></i></a>
                <h1 class="text-xl font-bold text-slate-800">Automation Rules</h1>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto mt-6 px-6 pb-12">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="font-bold text-yellow-800 mb-2"><i class="fas fa-info-circle mr-2"></i>How It Works</h3>
            <p class="text-sm text-yellow-700">Create rules like: "If lead opens email X, then enroll in sequence Y"</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Create New Rule</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Rule Name</label>
                    <input type="text" name="name" required placeholder="e.g., Follow up after welcome email" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Trigger (If...) </label>
                        <select name="trigger_type" id="triggerType" onchange="updateTriggerValue()" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                            <option value="email_opened">Email Opened</option>
                            <option value="email_clicked">Email Clicked</option>
                            <option value="score_reached">Score Reached</option>
                            <option value="tag_added">Tag Added</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Value</label>
                        <input type="text" name="trigger_value" id="triggerValue" placeholder="e.g., welcome email subject or 50" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    </div>
                </div>
                
                <div class="border-t border-slate-200 pt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Then...</label>
                    <select name="action_type" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                        <option value="enroll_sequence">Enroll in Sequence</option>
                        <option value="add_tag">Add Tag</option>
                        <option value="send_email">Send Specific Email</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Sequence</label>
                    <select name="action_sequence_id" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                        <option value="">-- Select Sequence --</option>
                        <?php while ($seq = $sequences->fetch_assoc()): ?>
                        <option value="<?= $seq['id'] ?>"><?= htmlspecialchars($seq['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    Create Rule
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-slate-200">
                <h3 class="font-bold text-slate-800">Active Rules</h3>
            </div>
            <?php if ($rules->num_rows === 0): ?>
            <div class="p-6 text-center text-slate-500">No rules yet</div>
            <?php else: ?>
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Rule</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Trigger</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php while ($rule = $rules->fetch_assoc()): ?>
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800"><?= htmlspecialchars($rule['name']) ?></td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded">If <?= htmlspecialchars($rule['trigger_type']) ?></span>
                            <span class="text-slate-500"><?= htmlspecialchars($rule['trigger_value'] ?? '') ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded"><?= htmlspecialchars($rule['action_type']) ?></span>
                            <?php if ($rule['action_sequence_id']): ?>
                            <span class="ml-2">Seq #<?= $rule['action_sequence_id'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $rule['id'] ?>">
                                <button type="submit" class="text-xs <?= $rule['is_active'] ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $rule['is_active'] ? 'Active' : 'Inactive' ?>
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $rule['id'] ?>">
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