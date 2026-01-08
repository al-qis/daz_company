<?php
session_start();
require_once __DIR__ . '/includes/database.php';

// Initialize variables
$isLocked = false;
$remainingTime = 0;

// Check if there's a username in POST or GET to check lock status
$usernameToCheck = '';
if (isset($_POST['username'])) {
    $usernameToCheck = $_POST['username'];
} elseif (isset($_SESSION['last_attempt_username'])) {
    $usernameToCheck = $_SESSION['last_attempt_username'];
}

// Check lock status for this user
if ($usernameToCheck) {
    $user = getUserByUsername($usernameToCheck);
    if ($user) {
        if ($user['is_locked'] && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $isLocked = true;
            $remainingTime = strtotime($user['locked_until']) - time();
        }
    }
}

// Store last attempted username for lock checking
if (isset($_POST['username'])) {
    $_SESSION['last_attempt_username'] = $_POST['username'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAZ Security Control Terminal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .security-layout {
            width: 100%;
            max-width: 440px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            position: relative;
        }
        
        .security-layout::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        }
        
        .card-header {
            background: #0f172a;
            padding: 40px;
            text-align: center;
            border-bottom: 1px solid #1e293b;
            position: relative;
        }
        
        /* DAZ Logo Text - Clean and Big */
        .daz-logo {
            font-size: 52px;
            font-weight: 900;
            color: #3b82f6;
            margin: 0 auto 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 0 2px 15px rgba(59, 130, 246, 0.4);
        }
        
        /* Optional: If you want glowing effect */
        .daz-logo-glow {
            font-size: 56px;
            font-weight: 900;
            color: white;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 
                0 0 10px rgba(59, 130, 246, 0.8),
                0 0 20px rgba(59, 130, 246, 0.6),
                0 0 30px rgba(59, 130, 246, 0.4);
            display: inline-block;
            padding: 0 20px;
        }
        
        .daz-logo-glow span:nth-child(1) { color: #60a5fa; }
        .daz-logo-glow span:nth-child(2) { color: #93c5fd; }
        .daz-logo-glow span:nth-child(3) { color: #3b82f6; }
        
        .card-title {
            font-size: 24px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.025em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .card-subtitle {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .card-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 6px;
            margin-left: 4px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 16px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            transition: all 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            background-color: #0f172a;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: background-color 0.2s;
            position: relative;
        }
        
        .btn:hover {
            background-color: #1e293b;
        }
        
        .btn:disabled {
            background-color: #cbd5e1;
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .btn-locked {
            background-color: #dc2626;
            cursor: not-allowed;
            animation: pulse 2s infinite;
        }
        
        .countdown-display {
            font-size: 10px;
            font-weight: bold;
            color: #dc2626;
            text-align: center;
            margin-top: 8px;
        }
        
        .error-message {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: fadeIn 0.3s ease-out;
        }
        
        .error-lockout {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { 
                opacity: 1;
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4);
            }
            50% { 
                opacity: 0.8;
                box-shadow: 0 0 0 10px rgba(220, 38, 38, 0);
            }
        }
        
        .error-text {
            color: #dc2626;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 0.05em;
        }
        
        .demo-credentials {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }
        
        .demo-btn {
            font-size: 10px;
            color: #64748b;
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-weight: 700;
            text-transform: uppercase;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .demo-btn:hover {
            border-color: #93c5fd;
        }
        
        .demo-role {
            color: #3b82f6;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 24px;
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        /* NEW: Registration link styling */
        .registration-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }
        
        .registration-link p {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 12px;
        }
        
        .register-btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            transition: all 0.2s;
        }
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="security-layout">
        <div class="card-header">
            <!-- Option 1: Simple DAZ Logo (Choose this one) -->
            <div class="daz-logo">
                DAZ
            </div>
            
            <!-- Option 2: Glowing DAZ Logo (Uncomment to use) -->
            <!--
            <div class="daz-logo-glow">
                <span>D</span>
                <span>A</span>
                <span>Z</span>
            </div>
            -->
            
            <h1 class="card-title">DAZ COMPANY</h1>
            <p class="card-subtitle">Retailer Management System</p>
        </div>
        
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message <?php echo strpos($_SESSION['error'], 'Lockout') !== false ? 'error-lockout' : ''; ?>">
                    <p class="error-text"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="POST" id="login-form" onsubmit="return checkLockStatus()">
                <div class="form-group">
                    <label class="form-label">Staff / User ID</label>
                    <input type="text" name="username" id="username" class="form-input" required placeholder="Enter ID" 
                           oninput="checkUserLockStatus()">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required placeholder="Enter Email">
                </div>
                <div class="form-group">
                    <label class="form-label">Security Key</label>
                    <input type="password" name="password" class="form-input" required placeholder="••••••••">
                </div>

               
                
                <div id="lockout-message" class="error-message" style="display: none;">
                    <p class="error-text" id="lockout-text"></p>
                </div>
                
                <button type="submit" id="login-btn" class="btn">
                    <span id="btn-text">LOGIN</span>
                </button>
                <div id="countdown" class="countdown-display" style="display: none;"></div>
            </form>
            
            
            <!-- NEW: Registration Link Section -->
            <div class="registration-link">
                <p>New to DAZ Security System?</p>
                <a href="register.php" class="register-btn">
                    CREATE NEW ACCOUNT
                </a>
            </div>
            
        </div>
    </div>
    
    <script>
        // Lock status variables
        let isLocked = <?php echo $isLocked ? 'true' : 'false'; ?>;
        let remainingTime = <?php echo $remainingTime; ?>;
        let countdownInterval = null;
        
        function setCredentials(username) {
            const form = document.getElementById('login-form');
            form.username.value = username;
            form.password.value = 'password123';
            
            // Check lock status for this user
            checkUserLockStatus();
        }
        
        function checkUserLockStatus() {
            const username = document.getElementById('username').value;
            const loginBtn = document.getElementById('login-btn');
            const lockoutMessage = document.getElementById('lockout-message');
            const lockoutText = document.getElementById('lockout-text');
            const countdownDiv = document.getElementById('countdown');
            const btnText = document.getElementById('btn-text');
            
            // Clear previous interval
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            
            // For demo purposes, we'll simulate checking lock status
            // In a real application, you would make an AJAX request to check the server
            if (username) {
                // Simulate checking if this user is locked
                // This is where you would make an AJAX call to check the actual lock status
                
                // For now, we'll just check if the user was recently locked
                const lockedUsers = JSON.parse(localStorage.getItem('locked_users') || '{}');
                if (lockedUsers[username] && Date.now() < lockedUsers[username]) {
                    const remaining = Math.ceil((lockedUsers[username] - Date.now()) / 1000);
                    updateLockoutDisplay(remaining, username);
                    return false;
                }
            }
            
            // Enable button if not locked
            if (lockoutMessage) lockoutMessage.style.display = 'none';
            if (countdownDiv) countdownDiv.style.display = 'none';
            if (loginBtn) {
                loginBtn.disabled = false;
                loginBtn.classList.remove('btn-locked');
            }
            if (btnText) btnText.textContent = 'LOGIN';
            
            return true;
        }
        
        function updateLockoutDisplay(remainingSeconds, username) {
            const loginBtn = document.getElementById('login-btn');
            const lockoutMessage = document.getElementById('lockout-message');
            const lockoutText = document.getElementById('lockout-text');
            const countdownDiv = document.getElementById('countdown');
            const btnText = document.getElementById('btn-text');
            
            // Disable button
            if (loginBtn) {
                loginBtn.disabled = true;
                loginBtn.classList.add('btn-locked');
            }
            
            // Update button text
            if (btnText) btnText.textContent = 'ACCOUNT LOCKED';
            
            // Show lockout message
            if (lockoutMessage && lockoutText) {
                lockoutMessage.style.display = 'block';
                lockoutText.textContent = `Account locked. Too many failed attempts.`;
            }
            
            // Show countdown
            if (countdownDiv) {
                countdownDiv.style.display = 'block';
                countdownDiv.textContent = `Try again in: ${remainingSeconds}s`;
            }
            
            // Start countdown
            countdownInterval = setInterval(() => {
                remainingSeconds--;
                
                if (remainingSeconds <= 0) {
                    clearInterval(countdownInterval);
                    if (lockoutMessage) lockoutMessage.style.display = 'none';
                    if (countdownDiv) countdownDiv.style.display = 'none';
                    if (loginBtn) {
                        loginBtn.disabled = false;
                        loginBtn.classList.remove('btn-locked');
                    }
                    if (btnText) btnText.textContent = 'LOGIN';
                    
                    // Remove from local storage
                    const lockedUsers = JSON.parse(localStorage.getItem('locked_users') || '{}');
                    delete lockedUsers[username];
                    localStorage.setItem('locked_users', JSON.stringify(lockedUsers));
                } else {
                    if (countdownDiv) {
                        countdownDiv.textContent = `Try again in: ${remainingSeconds}s`;
                    }
                }
            }, 1000);
        }
        
        function checkLockStatus() {
            const username = document.getElementById('username').value;
            const lockedUsers = JSON.parse(localStorage.getItem('locked_users') || '{}');
            
            if (lockedUsers[username] && Date.now() < lockedUsers[username]) {
                const remaining = Math.ceil((lockedUsers[username] - Date.now()) / 1000);
                updateLockoutDisplay(remaining, username);
                return false;
            }
            
            return true;
        }
        
        // Lock user function (to be called from login.php or other pages)
        function lockUser(username, durationSeconds = 60) {
            const lockedUsers = JSON.parse(localStorage.getItem('locked_users') || '{}');
            lockedUsers[username] = Date.now() + (durationSeconds * 1000);
            localStorage.setItem('locked_users', JSON.stringify(lockedUsers));
            checkUserLockStatus();
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkUserLockStatus();
            
            // Check if there's a stored lock status for the current user
            const usernameInput = document.getElementById('username');
            if (usernameInput && usernameInput.value) {
                checkUserLockStatus();
            }
            
            // If PHP detected a lock, update the display
            if (isLocked && remainingTime > 0) {
                const username = document.getElementById('username').value;
                if (username) {
                    updateLockoutDisplay(remainingTime, username);
                }
            }
        });
    </script>
</body>
</html>