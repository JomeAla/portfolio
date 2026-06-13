<?php
error_reporting(0);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;
use App\Models\EmailSequence;

$step = $_GET['step'] ?? 1;
$previewData = [];
$mappedColumns = $_SESSION['csv_columns'] ?? [];
$csvFile = $_SESSION['csv_file'] ?? '';
$hasHeader = $_SESSION['csv_has_header'] ?? true;
$errors = [];
$success = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    if ($file['error'] === 0 && $file['type'] === 'text/csv') {
        $_SESSION['csv_file'] = $file['tmp_name'];
        $hasHeader = isset($_POST['has_header']) ? true : false;
        $_SESSION['csv_has_header'] = $hasHeader;
        
        $handle = fopen($file['tmp_name'], 'r');
        $firstRow = fgetcsv($handle);
        fclose($handle);
        
        $_SESSION['csv_columns'] = $firstRow;
        header('Location: /csv_import.php?step=2');
        exit;
    }
    $errors[] = 'Please upload a valid CSV file';
}

if ($step == 2 && !empty($_POST['mapping'])) {
    $mapping = $_POST['mapping'];
    
    $handle = fopen($_SESSION['csv_file'], 'r');
    if ($_SESSION['csv_has_header']) {
        fgetcsv($handle);
    }
    
    while (($row = fgetcsv($handle)) !== false) {
        $leadData = [];
        foreach ($mapping as $csvCol => $leadField) {
            if ($leadField && isset($row[$csvCol])) {
                $leadData[$leadField] = trim($row[$csvCol]);
            }
        }
        
        if (!empty($leadData['email'])) {
            $lead = Lead::firstOrCreate(
                ['email' => $leadData['email']],
                $leadData
            );
            $success++;
        }
    }
    fclose($handle);
    
    unset($_SESSION['csv_file'], $_SESSION['csv_columns'], $_SESSION['csv_has_header']);
    $message = "Successfully imported $success leads!";
}

$availableFields = ['email', 'name', 'status', 'sequence_id', 'source', 'is_newsletter'];
$sequences = EmailSequence::where('is_active', true)->get();
$csvColumns = $_SESSION['csv_columns'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Import - Joala Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4">
        <div class="flex items-center justify-between max-w-5xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="/admin/marketing" class="text-slate-600 hover:text-slate-800"><i class="fas fa-arrow-left"></i></a>
                <h1 class="text-xl font-bold text-slate-800">Import Leads from CSV</h1>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto mt-6 px-6 pb-12">
        <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i><?= $message ?>
        </div>
        <?php endif; ?>

        <?php if ($errors): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php foreach ($errors as $err): ?>
            <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Step 1: Upload CSV File</h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Choose CSV File</label>
                    <input type="file" name="csv_file" accept=".csv" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="has_header" checked class="rounded">
                        <span class="text-sm text-slate-700">First row contains headers</span>
                    </label>
                </div>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    Next Step
                </button>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($step == 2): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Step 2: Map Columns</h2>
            <form method="POST" class="space-y-4">
                <p class="text-sm text-slate-600 mb-4">Map your CSV columns to lead fields:</p>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">CSV Column</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Leads Field</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Preview (first row)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php foreach ($csvColumns as $idx => $colName): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-slate-800"><?= htmlspecialchars($colName) ?></td>
                                <td class="px-4 py-3">
                                    <select name="mapping[<?= $idx ?>]" class="border border-slate-300 rounded px-3 py-1 text-sm">
                                        <option value="">-- Skip --</option>
                                        <option value="email">Email (required)</option>
                                        <option value="name">Name</option>
                                        <option value="status">Status</option>
                                        <option value="source">Source</option>
                                        <option value="is_newsletter">Newsletter</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500">
                                    <?php 
                                    $handle = fopen($_SESSION['csv_file'], 'r');
                                    if ($_SESSION['csv_has_header']) fgetcsv($handle);
                                    $row = fgetcsv($handle);
                                    echo htmlspecialchars($row[$idx] ?? '');
                                    fclose($handle);
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Enroll in Sequence (optional)</label>
                    <select name="sequence_id" class="border border-slate-300 rounded-lg px-4 py-2 w-full">
                        <option value="">-- No sequence --</option>
                        <?php foreach ($sequences as $seq): ?>
                        <option value="<?= $seq->id ?>"><?= htmlspecialchars($seq->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-upload mr-2"></i>Import Leads
                    </button>
                    <a href="/csv_import.php" class="px-6 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">Start Over</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>