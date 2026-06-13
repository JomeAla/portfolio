<?php
// Redirect old funnel-view.php?id=26 to new Laravel route /f/26
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    header('Location: /f/' . $id, true, 301);
    exit;
}

// If no id, redirect to home
header('Location: /', true, 301);
exit;