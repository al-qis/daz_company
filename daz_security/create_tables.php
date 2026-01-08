<?php
// create_tables.php - Create database tables
session_start();
echo "<h3>Creating Database Tables</h3>";
echo "<pre>";

try {
    // Include config
    require_once 'includes/config.php';
    
    // Connect to MySQL (without database)
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);
    
    echo "✓ Database '" . DB_NAME . "' created/selected\n\n";
    
    // Drop tables if they exist
    $pdo->exec("DROP TABLE IF EXISTS security_logs");
    $pdo->exec("DROP TABLE IF EXISTS users");
    
    echo "✓ Dropped existing tables\n\n";
    
    // Create users table
    $pdo->exec("CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'cashier', 'it_manager', 'customer') NOT NULL,
        failed_attempts INT DEFAULT 0,
        is_locked BOOLEAN DEFAULT FALSE,
        locked_until TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "✓ Created 'users' table\n";
    
    // Create security_logs table
    $pdo->exec("CREATE TABLE security_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        event VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "✓ Created 'security_logs' table\n\n";
    
    // Create indexes
    $pdo->exec("CREATE INDEX idx_username ON security_logs(username)");
    $pdo->exec("CREATE INDEX idx_event ON security_logs(event)");
    $pdo->exec("CREATE INDEX idx_created_at ON security_logs(created_at DESC)");
    
    echo "✓ Created indexes\n\n";
    
    // Insert sample users
    echo "=== INSERTING SAMPLE USERS ===\n";
    
    $users = [
        ['admin_daz', 'password123', 'admin'],
        ['it_pro', 'password123', 'it_manager'],
        ['cashier_01', 'password123', 'cashier'],
        ['customer_lee', 'password123', 'customer']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    
    foreach ($users as $user) {
        $hashedPassword = password_hash($user[1], PASSWORD_DEFAULT);
        $stmt->execute([$user[0], $hashedPassword, $user[2]]);
        echo "✓ Created: {$user[0]} ({$user[2]})\n";
    }
    
    echo "\n=== VERIFICATION ===\n";
    
    // Verify tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database: " . implode(', ', $tables) . "\n\n";
    
    // Count users
    $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch();
    echo "Users in 'users' table: {$userCount['count']}\n\n";
    
    // Show all users
    $allUsers = $pdo->query("SELECT id, username, role FROM users ORDER BY id")->fetchAll();
    
    echo "=== USER LIST ===\n";
    echo "ID  Username        Role\n";
    echo "----------------------------\n";
    
    foreach ($allUsers as $user) {
        echo sprintf("%-3d %-15s %-15s\n", 
            $user['id'], 
            $user['username'], 
            $user['role']
        );
    }
    
    // Test password verification
    echo "\n=== PASSWORD TEST ===\n";
    $admin = $pdo->query("SELECT username, password_hash FROM users WHERE username = 'admin_daz'")->fetch();
    
    if ($admin && password_verify('password123', $admin['password_hash'])) {
        echo "✓ Password verification works for admin_daz\n";
    } else {
        echo "✗ Password verification failed\n";
    }
    
    echo "\n=== LOGIN CREDENTIALS ===\n";
    echo "1. admin_daz / password123\n";
    echo "2. it_pro / password123\n";
    echo "3. cashier_01 / password123\n";
    echo "4. customer_lee / password123\n";
    
    echo "\n=== NEXT STEPS ===\n";
    echo "1. <a href='index.php'>Go to Login Page</a>\n";
    echo "2. Test login with credentials above\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    
    // Show manual SQL
    echo "\n=== MANUAL SQL TO RUN IN phpMyAdmin ===\n";
    echo "CREATE DATABASE IF NOT EXISTS daz_security;\n";
    echo "USE daz_security;\n\n";
    
    echo "CREATE TABLE users (\n";
    echo "    id INT PRIMARY KEY AUTO_INCREMENT,\n";
    echo "    username VARCHAR(50) UNIQUE NOT NULL,\n";
    echo "    password_hash VARCHAR(255) NOT NULL,\n";
    echo "    role ENUM('admin', 'cashier', 'it_manager', 'customer') NOT NULL,\n";
    echo "    failed_attempts INT DEFAULT 0,\n";
    echo "    is_locked BOOLEAN DEFAULT FALSE,\n";
    echo "    locked_until TIMESTAMP NULL,\n";
    echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";
    
    echo "CREATE TABLE security_logs (\n";
    echo "    id INT PRIMARY KEY AUTO_INCREMENT,\n";
    echo "    username VARCHAR(50) NOT NULL,\n";
    echo "    event VARCHAR(100) NOT NULL,\n";
    echo "    details TEXT,\n";
    echo "    ip_address VARCHAR(45),\n";
    echo "    user_agent TEXT,\n";
    echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
    echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";
    
    echo "-- Insert users\n";
    echo "INSERT INTO users (username, password_hash, role) VALUES\n";
    echo "('admin_daz', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),\n";
    echo "('it_pro', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'it_manager'),\n";
    echo "('cashier_01', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier'),\n";
    echo "('customer_lee', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');\n";
}

echo "</pre>";
?>