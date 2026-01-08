<?php
session_start();
echo "<h2>Session Debug Info</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Variables:\n";
print_r($_SESSION);
echo "</pre>";

// Check if register.php exists
echo "<h2>File Check</h2>";
echo "register.php exists: " . (file_exists(__DIR__ . '/register.php') ? 'YES' : 'NO') . "<br>";
echo "register.php path: " . __DIR__ . '/register.php';
?>