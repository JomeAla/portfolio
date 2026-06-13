<?php
// Test script to check if form is posting correctly
header('Content-Type: text/plain');
echo "Test script running...\n";
echo "POST data:\n";
print_r($_POST);
echo "\n\nMethod: " . $_SERVER['REQUEST_METHOD'] . "\n";