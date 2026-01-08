<?php
// insert_users.php - Insert users into your database
session_start();

echo "<h3>Inserting Users into Database</h3>";
echo "<pre>";

try {
    // Connect to database
    require_once 'includes/config.php';
    require_once 'includes/database.php';
    require_once 'includes/security.php';
    
    $db = Database::getConnection();
    
    echo "✓ Connected to database\n";
    
    // Check current users
    $currentUsers = $db->query("SELECT COUNT(*) as count FROM users")->fetch();
    echo "Current users in database: {$currentUsers['count']}\n\n";
    
    // Delete existing users to start fresh
    $db->exec("DELETE FROM users");
    echo "✓ Cleared existing users\n";
    
    // Define sample users with hashed passwords
    $sampleUsers = [
        [
            'username' => 'admin_daz',
            'password' => 'password123',
            'role' => 'admin',
            'description' => 'System Administrator'
        ],
        [
            'username' => 'it_pro',
            'password' => 'password123', 
            'role' => 'it_manager',
            'description' => 'IT Manager'
        ],
        [
            'username' => 'cashier_01',
            'password' => 'password123',
            'role' => 'cashier',
            'description' => 'Cashier Account'
        ],
        [
            'username' => 'customer_lee',
            'password' => 'password123',
            'role' => 'customer',
            'description' => 'Customer Account'
        ]
    ];
    
    echo "\n=== Creating Users ===\n";
    
    // Prepare insert statement
    $stmt = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    
    $createdCount = 0;
    foreach ($sampleUsers as $user) {
        // Hash the password
        $hashedPassword = hashPassword($user['password']);
        
        // Insert user
        $result = $stmt->execute([
            $user['username'],
            $hashedPassword,
            $user['role']
        ]);
        
        if ($result) {
            echo "✓ Created: {$user['username']} ({$user['role']})\n";
            $createdCount++;
            
            // Log the creation
            logSecurityEvent($user['username'], 'USER_CREATED', 'Sample user created', '127.0.0.1');
        } else {
            echo "✗ Failed: {$user['username']}\n";
        }
    }
    
    echo "\n=== Verification ===\n";
    echo "Total users created: {$createdCount}\n\n";
    
    // Verify users were inserted
    $finalCount = $db->query("SELECT COUNT(*) as count FROM users")->fetch();
    echo "Users in database now: {$finalCount['count']}\n\n";
    
    // Show all users
    $allUsers = $db->query("SELECT username, role, created_at FROM users ORDER BY role")->fetchAll();
    
    echo "=== User List ===\n";
    echo "Username          Role          Created At\n";
    echo "-------------------------------------------\n";
    
    foreach ($allUsers as $user) {
        echo sprintf("%-16s %-12s %s\n", 
            $user['username'], 
            $user['role'], 
            $user['created_at']
        );
    }
    
    echo "\n=== Testing Login ===\n";
    
    // Test the getUserByUsername function
    $testUser = getUserByUsername('admin_daz');
    if ($testUser) {
        echo "✓ getUserByUsername() works - Found: admin_daz\n";
        
        // Test password verification
        if (verifyPassword('password123', $testUser['password_hash'])) {
            echo "✓ Password verification works!\n";
            echo "   You can now login with:\n";
            echo "   Username: admin_daz\n";
            echo "   Password: password123\n";
        } else {
            echo "✗ Password verification failed!\n";
            echo "   Hash stored: " . substr($testUser['password_hash'], 0, 30) . "...\n";
            echo "   Hash length: " . strlen($testUser['password_hash']) . "\n";
        }
    } else {
        echo "✗ Could not find admin_daz user!\n";
    }
    
    echo "\n=== Login Credentials ===\n";
    echo "1. admin_daz / password123 (Administrator)\n";
    echo "2. it_pro / password123 (IT Manager)\n"; 
    echo "3. cashier_01 / password123 (Cashier)\n";
    echo "4. customer_lee / password123 (Customer)\n";
    
    echo "\n=== Next Steps ===\n";
    echo "1. <a href='index.php'>Go to Login Page</a>\n";
    echo "2. Use the credentials above\n";
    echo "3. Test the login\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    
    // Try direct connection
    echo "\n=== Direct Connection Test ===\n";
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        echo "✓ Direct connection successful\n";
    } catch (PDOException $ex) {
        echo "✗ Direct connection failed: " . $ex->getMessage() . "\n";
    }
}

echo "</pre>";
?>