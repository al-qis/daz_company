<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}

// Helper functions
function getUserByUsername($username) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function incrementFailedAttempts($username) {
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE username = ?");
    $stmt->execute([$username]);
}

function resetFailedAttempts($username) {
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE users SET failed_attempts = 0, is_locked = 0, locked_until = NULL WHERE username = ?");
    $stmt->execute([$username]);
}

function lockAccount($username, $duration) {
    $db = Database::getConnection();
    $lockedUntil = date('Y-m-d H:i:s', time() + $duration);
    $stmt = $db->prepare("UPDATE users SET is_locked = 1, locked_until = ? WHERE username = ?");
    $stmt->execute([$lockedUntil, $username]);
}

function getSecurityLogs($limit = 50) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM security_logs ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Add these functions to your existing database.php

function getRegisteredUserByUsername($username) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM registered_users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function getRegisteredUserByEmail($email) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM registered_users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function incrementRegisteredFailedAttempts($username) {
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE registered_users SET failed_attempts = failed_attempts + 1 WHERE username = ?");
    $stmt->execute([$username]);
}

function resetRegisteredFailedAttempts($username) {
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE registered_users SET failed_attempts = 0, is_locked = 0, locked_until = NULL WHERE username = ?");
    $stmt->execute([$username]);
}

function lockRegisteredAccount($username, $duration) {
    $db = Database::getConnection();
    $lockedUntil = date('Y-m-d H:i:s', time() + $duration);
    $stmt = $db->prepare("UPDATE registered_users SET is_locked = 1, locked_until = ? WHERE username = ?");
    $stmt->execute([$lockedUntil, $username]);
}

function countRegisteredUsers() {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM registered_users");
    $stmt->execute();
    return $stmt->fetch()['count'];
}
?>