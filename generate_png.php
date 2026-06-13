<?php
error_reporting(0);

// Original size, tighter spacing (before sharp version)
$logo = imagecreatetruecolor(420, 60);

imagealphablending($logo, true);
imagesavealpha($logo, true);
$transparent = imagecolorallocatealpha($logo, 0, 0, 0, 127);
imagefill($logo, 0, 0, $transparent);

$darkSlate = imagecolorallocate($logo, 30, 41, 59);
$cyan = imagecolorallocate($logo, 6, 182, 212);
$gray = imagecolorallocate($logo, 100, 116, 139);

imagestring($logo, 5, 5, 18, "</", $cyan);
imageline($logo, 40, 12, 40, 48, $gray);
imagestring($logo, 5, 48, 16, "JOALA", $darkSlate);
imagestring($logo, 3, 48, 38, "VENTURES", $gray);

imagepng($logo, __DIR__ . '/joala-logo.png');
imagedestroy($logo);

$favicon = imagecreatetruecolor(32, 32);
imagealphablending($favicon, true);
imagesavealpha($favicon, true);
$transp = imagecolorallocatealpha($favicon, 0, 0, 0, 127);
imagefill($favicon, 0, 0, $transp);

$favCyan = imagecolorallocate($favicon, 6, 182, 212);
imagestring($favicon, 3, 2, 10, "</", $favCyan);

imagepng($favicon, __DIR__ . '/favicon.png');
imagedestroy($favicon);

echo "<h2>Done!</h2>";