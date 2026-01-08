<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';

// Check if user is in MFA phase
if (!isset($_SESSION['mfa_username']) || !isset($_SESSION['mfa_code'])) {
    header('Location: index.php');
    exit();
}

// Check if OTP expired
if (time() > $_SESSION['mfa_expires']) {
    unset($_SESSION['mfa_username'], $_SESSION['mfa_code'], $_SESSION['mfa_expires'], $_SESSION['pending_user']);
    $_SESSION['error'] = 'Verification session expired.';
    header('Location: index.php');
    exit();
}

// Handle MFA verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_input = trim($_POST['otp'] ?? '');
    
    if ($otp_input === $_SESSION['mfa_code']) {
        // Successful MFA verification
        $username = $_SESSION['mfa_username'];
        $_SESSION['user'] = $_SESSION['pending_user'];
        
        // Log the event
        require_once __DIR__ . '/includes/security.php'; // Ensure security functions are loaded
        logSecurityEvent($username, 'MFA_VERIFIED', 'Full session authorized', getClientIp());
        
        // Clean up MFA session
        unset($_SESSION['mfa_username'], $_SESSION['mfa_code'], $_SESSION['mfa_expires'], $_SESSION['pending_user']);
        
        header('Location: dashboard.php');
        exit();
    } else {
        $_SESSION['mfa_error'] = 'Invalid security token.';
        
        // Log the failed attempt
        require_once __DIR__ . '/includes/security.php';
        logSecurityEvent($_SESSION['mfa_username'], 'LOGIN_FAILED', 'Incorrect MFA code provided', getClientIp());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Required - DAZ Security</title>
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
        
        .mfa-layout {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            position: relative;
        }
        
        .mfa-layout::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        }
        
        .mfa-header {
            background: #0f172a;
            padding: 40px;
            text-align: center;
            border-bottom: 1px solid #1e293b;
        }
        
        .mfa-title {
            font-size: 24px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.025em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .mfa-subtitle {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .mfa-body {
            padding: 40px;
            text-align: center;
        }
        
        .mfa-icon {
            width: 80px;
            height: 80px;
            background-color: #dbeafe;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 1px solid #bfdbfe;
            color: #3b82f6;
            font-size: 32px;
        }
        
        .otp-display {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 32px;
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.2em;
        }
        
        .otp-input {
            width: 100%;
            padding: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 32px;
            text-align: center;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.4em;
            margin-bottom: 16px;
            transition: all 0.2s;
        }
        
        .otp-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .otp-instruction {
            font-size: 11px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 32px;
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
            margin-bottom: 16px;
        }
        
        .btn:hover {
            background-color: #1e293b;
        }
        
        .btn-back {
            background-color: transparent;
            color: #64748b;
            font-size: 11px;
            text-decoration: underline;
            text-decoration-color: #cbd5e1;
        }
        
        .error-message {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .error-text {
            color: #dc2626;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>
    <div class="mfa-layout">
        <div class="mfa-header">
            <h1 class="mfa-title">Token Required</h1>
            <p class="mfa-subtitle">Verify identity via secondary factor</p>
        </div>
        
        <div class="mfa-body">
            <div class="mfa-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 0012 21a10.003 10.003 0 008.381-4.562l.054.09c1.744-2.772 2.753-6.054 2.753-9.571V7a1 1 0 00-1-1h-2a1 1 0 00-1 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.707.293H10" />
                </svg>
            </div>
            
            <div class="otp-display">
                <?php echo isset($_SESSION['mfa_code']) ? $_SESSION['mfa_code'] : '000000'; ?>
            </div>
            
            <?php if (isset($_SESSION['mfa_error'])): ?>
                <div class="error-message">
                    <p class="error-text"><?php echo htmlspecialchars($_SESSION['mfa_error']); unset($_SESSION['mfa_error']); ?></p>
                </div>
            <?php endif; ?>
            
            <form action="mfa.php" method="POST">
                <input type="text" name="otp" class="otp-input" required maxlength="6" placeholder="000000" autofocus 
                       oninput="this.value = this.value.replace(/\D/g, '')">
                <p class="otp-instruction">Enter 6-digit identification code</p>
                
                <button type="submit" class="btn">VERIFY</button>
                <a href="logout.php" class="btn btn-back">BACK TO LOGIN</a>
            </form>
        </div>
    </div>
</body>
</html>