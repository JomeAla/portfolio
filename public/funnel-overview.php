<?php
// Legacy URL redirect - sends to Laravel route
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$qs = $id > 0 ? '?id=' . $id : '';
header('Location: /funnel-overview' . $qs, true, 301);
exit;