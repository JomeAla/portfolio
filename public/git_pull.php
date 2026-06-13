<?php
// Git pull script - run from browser to update live server from GitHub

$output = [];
$returnVar = 0;

// Change to portfolio directory
chdir('/home/joalacom/public_html');

// Git commands
exec('git pull origin master 2>&1', $output, $returnVar);

echo "<h1>Git Pull Result</h1>";
echo "<pre>";
echo implode("\n", $output);
echo "</pre>";

echo "<h2>Pull Complete!</h2>";
echo "<p>Test the new features:</p>";
echo "<ul>";
echo "<li><a href='/customer/login'>Customer Login</a></li>";
echo "<li><a href='/customer/register'>Customer Register</a></li>";
echo "<li><a href='/customer/dashboard'>Customer Dashboard</a></li>";
echo "</ul>";