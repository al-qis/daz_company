<?php
require_once __DIR__ . '/database.php';

function logSecurityEvent($username, $event, $details, $ip) {
    $db = Database::getConnection();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $stmt = $db->prepare("INSERT INTO security_logs (username, event, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $event, $details, $ip, $userAgent]);
    
    return $stmt->rowCount() > 0;
}

function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// FIXED: Proper password verification - SIMPLIFIED
function verifyPassword($password, $hash) {
    // Always use password_verify for bcrypt hashes
    $result = password_verify($password, $hash);
    
    // DEBUG logging
    error_log("Password verification: password='$password', hash starts with: " . substr($hash, 0, 20) . ", result: " . ($result ? 'true' : 'false'));
    
    return $result;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function getClientIp() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    
    return $ip;
}
?>