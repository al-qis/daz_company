<?php
// reset_passwords.php
session_start();
echo "<h3>Resetting User Passwords</h3>";
echo "<pre>";

try {
    require_once 'includes/config.php';
    require_once 'includes/database.php';
    
    $db = Database::getConnection();
    
    // All users will have password: password123
    $users = ['admin_daz', 'it_pro', 'cashier_01', 'customer_lee'];
    
    foreach ($users as $username) {
        // Hash the password properly
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
        
        echo "✓ Reset password for: {$username}\n";
        echo "   New hash: " . substr($hashedPassword, 0, 30) . "...\n";
    }
    
    echo "\n=== TEST LOGIN ===\n";
    echo "Username: admin_daz\n";
    echo "Password: password123\n";
    echo "\n<a href='index.php'>Go to Login Page</a>\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>