<?php
error_reporting(0);

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

if ($_POST) {
    $name = $conn->real_escape_string($_POST['name']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $body = $conn->real_escape_string($_POST['body']);
    $description = $conn->real_escape_string($_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if ($action === 'edit' && $id) {
        $conn->query("UPDATE email_templates SET name='$name', subject='$subject', body='$body', description='$description', is_active=$is_active WHERE id=$id");
    } else {
        $conn->query("INSERT INTO email_templates (name, subject, body, description, is_active, created_at, updated_at) VALUES ('$name', '$subject', '$body', '$description', $is_active, NOW(), NOW())");
    }
    header("Location: /email_templates.php");
    exit;
}

if ($action === 'delete' && $id) {
    $conn->query("DELETE FROM email_templates WHERE id=$id");
    header("Location: /email_templates.php");
    exit;
}

$templates = $conn->query("SELECT * FROM email_templates ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Templates - Joala Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Email Templates</h1>
                <p class="text-slate-600 mt-2">Create and manage reusable email templates</p>
            </div>
            <a href="?action=create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i>New Template
            </a>
        </div>

        <?php if ($action === 'create' || $action === 'edit'): ?>
            <?php
            $template = ['name'=>'','subject'=>'','body'=>'','description'=>'','is_active'=>1];
            if ($action === 'edit' && $id) {
                $result = $conn->query("SELECT * FROM email_templates WHERE id=$id");
                $template = $result->fetch_assoc();
            }
            ?>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-4"><?= $action === 'edit' ? 'Edit' : 'Create' ?> Template</h2>
                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Template Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($template['name']) ?>" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Subject Line</label>
                        <input type="text" name="subject" value="<?= htmlspecialchars($template['subject']) ?>" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email Body (HTML)</label>
                        <textarea name="body" rows="12" required class="w-full border border-slate-300 rounded-lg px-4 py-2 font-mono text-sm"><?= htmlspecialchars($template['body']) ?></textarea>
                        <p class="text-xs text-slate-500 mt-1">Use {{name}} for recipient name, {{unsubscribe_url}} for unsubscribe link</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                        <textarea name="description" rows="2" class="w-full border border-slate-300 rounded-lg px-4 py-2"><?= htmlspecialchars($template['description']) ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" <?= $template['is_active'] ? 'checked' : '' ?> class="rounded border-slate-300 text-indigo-600">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">Save Template</button>
                        <a href="/email_templates.php" class="px-6 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($t = $templates->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($t['name']) ?></h3>
                        <span class="px-2 py-1 text-xs rounded <?= $t['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>">
                            <?= $t['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 mb-3"><?= htmlspecialchars($t['description'] ?? 'No description') ?></p>
                    <div class="bg-slate-50 rounded p-3 mb-3">
                        <p class="text-xs text-slate-500 mb-1"><strong>Subject:</strong></p>
                        <p class="text-sm text-slate-700"><?= htmlspecialchars($t['subject']) ?></p>
                    </div>
                    <div class="flex gap-3">
                        <a href="?action=edit&id=<?= $t['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
                        <a href="?action=delete&id=<?= $t['id'] ?>" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete this template?')">Delete</a>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if ($templates->num_rows == 0): ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-slate-500 mb-4">No templates yet. Create your first email template!</p>
                    <a href="?action=create" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        Create Template
                    </a>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
