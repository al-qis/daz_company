<?php
session_start();

// For debugging - enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug logging
error_log("=== LOGIN.PHP STARTED ===");

// Check if includes exist
$includePath = __DIR__ . '/includes/';
if (!file_exists($includePath . 'config.php')) {
    die("config.php not found at: " . $includePath . 'config.php');
}

require_once $includePath . 'config.php';
require_once $includePath . 'database.php';
require_once $includePath . 'security.php';

error_log("All includes loaded successfully");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    error_log("Login attempt - Username: '$username', Password provided: " . (!empty($password) ? 'YES' : 'NO'));
    
    // Security: Basic input validation
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please enter both username and password';
        error_log("Empty username or password");
        header('Location: index.php');
        exit();
    }
    
    // Check for lockout
    error_log("Calling getUserByUsername for: $username");
    $user = getUserByUsername($username);
    
    if ($user) {
        error_log("User found: " . print_r($user, true));
        
        if ($user['is_locked'] && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $remaining = strtotime($user['locked_until']) - time();
            $_SESSION['error'] = "Security Lockout active. Try again in {$remaining}s.";
            logSecurityEvent($username, 'LOGIN_FAILED', 'Account locked', getClientIp());
            error_log("Account is locked");
            header('Location: index.php');
            exit();
        }
        
        // Verify password
        error_log("Calling verifyPassword for user: $username");
        error_log("Password hash from DB: " . substr($user['password_hash'], 0, 30) . "...");
        
        if (verifyPassword($password, $user['password_hash'])) {
            // Successful login - reset failed attempts
            error_log("Password verification SUCCESSFUL!");
            resetFailedAttempts($username);
            logSecurityEvent($username, 'LOGIN_SUCCESS', 'Phase 1 verification passed', getClientIp());
            
            // Generate OTP and send to MFA phase
            $otp = generateOTP();
            $_SESSION['mfa_username'] = $username;
            $_SESSION['mfa_code'] = $otp;
            $_SESSION['mfa_expires'] = time() + OTP_EXPIRY; // 5 minutes
            logSecurityEvent($username, 'MFA_SENT', 'MFA token dispatched', getClientIp());
            
            // Store user info for MFA phase
            $_SESSION['pending_user'] = $user;
            
            error_log("Redirecting to mfa.php");
            header('Location: mfa.php');
            exit();
        } else {
            // Failed login
            error_log("Password verification FAILED!");
            error_log("Input password: '$password'");
            error_log("Stored hash: " . $user['password_hash']);
            error_log("Hash length: " . strlen($user['password_hash']));
            
            // Try direct password_verify for debugging
            $directVerify = password_verify($password, $user['password_hash']);
            error_log("Direct password_verify result: " . ($directVerify ? 'TRUE' : 'FALSE'));
            
            incrementFailedAttempts($username);
            $attempts = $user['failed_attempts'] + 1;
            
            if ($attempts >= LOCKOUT_THRESHOLD) {
                lockAccount($username, LOCKOUT_DURATION);
                $_SESSION['error'] = "Account locked due to multiple failed attempts.";
                logSecurityEvent($username, 'ACCOUNT_LOCKED', "Max failures reached: {$attempts}", getClientIp());
            } else {
                $_SESSION['error'] = "Incorrect credentials (Attempt {$attempts} of " . LOCKOUT_THRESHOLD . ").";
                logSecurityEvent($username, 'LOGIN_FAILED', "Password mismatch (Attempt {$attempts})", getClientIp());
            }
            
            header('Location: index.php');
            exit();
        }
    } else {
        // User not found
        error_log("User NOT FOUND in database for username: $username");
        
        // Debug: Check what users exist
        try {
            $db = Database::getConnection();
            $allUsers = $db->query("SELECT username FROM users")->fetchAll(PDO::FETCH_COLUMN);
            error_log("All users in database: " . implode(', ', $allUsers));
            
            // Check case sensitivity
            $stmt = $db->prepare("SELECT username FROM users WHERE LOWER(username) = LOWER(?)");
            $stmt->execute([$username]);
            $caseInsensitiveUser = $stmt->fetch();
            if ($caseInsensitiveUser) {
                error_log("Found user with case-insensitive search: " . $caseInsensitiveUser['username']);
            }
        } catch (Exception $e) {
            error_log("Error checking users: " . $e->getMessage());
        }
        
        $_SESSION['error'] = 'Identity verification failed.';
        logSecurityEvent($username, 'LOGIN_FAILED', 'Username not found', getClientIp());
        header('Location: index.php');
        exit();
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    header('Location: index.php');
    exit();
}
?>