<?php
// register.php
session_start();

// DEBUG: Uncomment to see what's in session
// echo "<pre>"; print_r($_SESSION); echo "</pre>";

// Clear any old session data for registration
unset($_SESSION['mfa_username'], $_SESSION['mfa_code'], $_SESSION['mfa_expires'], $_SESSION['pending_user']);

// Only redirect to dashboard if user is actually logged in (has valid user data)
if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['username'])) {
    // Check if this is a valid user session (not just leftover data)
    require_once __DIR__ . '/includes/database.php';
    $userCheck = getUserByUsername($_SESSION['user']['username']);
    
    if ($userCheck) {
        // Valid logged in user, redirect to dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        // Invalid session data, clear it
        unset($_SESSION['user']);
    }
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = 'customer'; // Default role for new registrations

    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } else {
        // Password strength validation
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }

    if (empty($full_name)) {
        $errors[] = 'Full name is required';
    }

    // Check if username or email already exists
    if (empty($errors)) {
        try {
            $db = Database::getConnection();
            
            // Check username
            $stmt = $db->prepare("SELECT id FROM registered_users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = 'Username already exists';
            }

            // Check email
            $stmt = $db->prepare("SELECT id FROM registered_users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    // If no errors, register the user
    if (empty($errors)) {
        try {
            $db = Database::getConnection();
            
            // Hash the password
            $hashedPassword = hashPassword($password);
            
            // Generate verification token
            $verification_token = bin2hex(random_bytes(32));
            
            // Insert new user
            $stmt = $db->prepare("
                INSERT INTO registered_users 
                (username, email, password_hash, full_name, phone, address, role, verification_token)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $username,
                $email,
                $hashedPassword,
                $full_name,
                $phone,
                $address,
                $role,
                $verification_token
            ]);
            
            // Log the registration event
            logSecurityEvent($username, 'USER_REGISTERED', 'New user registered via registration form', getClientIp());
            
            $success = true;
            
        } catch (Exception $e) {
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DAZ Security Control</title>
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
        
        .register-layout {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            position: relative;
        }
        
        .register-layout::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #10b981);
        }
        
        .register-header {
            background: #0f172a;
            padding: 32px;
            text-align: center;
            border-bottom: 1px solid #1e293b;
        }
        
        .register-title {
            font-size: 24px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.025em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .register-subtitle {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .register-body {
            padding: 32px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
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
            padding: 12px 16px;
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
        
        .password-strength {
            margin-top: 8px;
            font-size: 11px;
            color: #64748b;
        }
        
        .strength-meter {
            height: 4px;
            background-color: #e2e8f0;
            border-radius: 2px;
            margin-top: 4px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            background-color: #ef4444;
            transition: all 0.3s ease;
        }
        
        .password-requirements {
            margin-top: 8px;
            padding: 12px;
            background-color: #f8fafc;
            border-radius: 8px;
            font-size: 11px;
            color: #64748b;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
        }
        
        .requirement.valid {
            color: #10b981;
        }
        
        .requirement.invalid {
            color: #ef4444;
        }
        
        .requirement-check {
            margin-right: 8px;
            font-size: 14px;
        }
        
        .error-message {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: fadeIn 0.3s ease-out;
        }
        
        .success-message {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 24px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-text {
            color: #dc2626;
            font-size: 12px;
            font-weight: 600;
        }
        
        .success-text {
            color: #10b981;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 16px;
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
        }
        
        .btn:hover {
            background-color: #1e293b;
        }
        
        .btn-secondary {
            background-color: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
            margin-top: 12px;
        }
        
        .btn-secondary:hover {
            background-color: #f1f5f9;
        }
        
        .login-link {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #64748b;
        }
        
        .login-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="register-layout">
        <div class="register-header">
            <h1 class="register-title">Create Account</h1>
            <p class="register-subtitle">Join DAZ Security Control</p>
        </div>
        
        <div class="register-body">
            <?php if ($success): ?>
                <div class="success-message">
                    <p class="success-text">🎉 Registration Successful!</p>
                    <p style="font-size: 12px; color: #475569; margin-bottom: 20px;">
                        Your account has been created successfully. You can now login with your credentials.
                    </p>
                    <a href="index.php" class="btn">Go to Login</a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <p class="error-text">Please fix the following errors:</p>
                        <ul style="margin-top: 8px; padding-left: 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li class="error-text" style="margin-bottom: 4px;"><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="register.php" method="POST" id="register-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-input" required 
                                   placeholder="johndoe" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                   pattern="[a-zA-Z0-9_]+" title="Letters, numbers, and underscores only">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-input" required 
                                   placeholder="john@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" id="password" class="form-input" required 
                                   placeholder="••••••••" minlength="8">
                            <div class="password-strength">
                                <span id="strength-text">Strength: </span>
                                <div class="strength-meter">
                                    <div class="strength-fill" id="strength-fill"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-input" required 
                                   placeholder="••••••••">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-input" required 
                               placeholder="John Doe" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                    
                    
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-input" 
                                   placeholder="+1234567890" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                    
                    
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-input" rows="3" 
                                  placeholder="Enter your address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="password-requirements" id="password-requirements">
                        <div class="requirement invalid" id="req-length">
                            <span class="requirement-check">✗</span>
                            At least 8 characters
                        </div>
                        <div class="requirement invalid" id="req-uppercase">
                            <span class="requirement-check">✗</span>
                            One uppercase letter
                        </div>
                        <div class="requirement invalid" id="req-lowercase">
                            <span class="requirement-check">✗</span>
                            One lowercase letter
                        </div>
                        <div class="requirement invalid" id="req-number">
                            <span class="requirement-check">✗</span>
                            One number
                        </div>
                        <div class="requirement invalid" id="req-special">
                            <span class="requirement-check">✗</span>
                            One special character
                        </div>
                    </div>
                    
                    <button type="submit" class="btn" id="submit-btn">CREATE ACCOUNT</button>
                    
                    <div class="login-link">
                        Already have an account? <a href="index.php">Sign in here</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');
        const submitBtn = document.getElementById('submit-btn');
        
        // Password validation functions
        function checkPasswordStrength(password) {
            let score = 0;
            
            // Length check
            if (password.length >= 8) score++;
            
            // Uppercase check
            if (/[A-Z]/.test(password)) score++;
            
            // Lowercase check
            if (/[a-z]/.test(password)) score++;
            
            // Number check
            if (/[0-9]/.test(password)) score++;
            
            // Special character check
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            // Update strength meter
            const percentages = ['0%', '20%', '40%', '60%', '80%', '100%'];
            const colors = ['#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16', '#10b981'];
            const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
            
            strengthFill.style.width = percentages[score];
            strengthFill.style.backgroundColor = colors[score];
            strengthText.textContent = `Strength: ${texts[score]}`;
            
            return score;
        }
        
        function validatePassword(password) {
            // Update requirement checks
            document.getElementById('req-length').className = password.length >= 8 ? 'requirement valid' : 'requirement invalid';
            document.getElementById('req-uppercase').className = /[A-Z]/.test(password) ? 'requirement valid' : 'requirement invalid';
            document.getElementById('req-lowercase').className = /[a-z]/.test(password) ? 'requirement valid' : 'requirement invalid';
            document.getElementById('req-number').className = /[0-9]/.test(password) ? 'requirement valid' : 'requirement invalid';
            document.getElementById('req-special').className = /[^A-Za-z0-9]/.test(password) ? 'requirement valid' : 'requirement invalid';
            
            return password.length >= 8 && 
                   /[A-Z]/.test(password) && 
                   /[a-z]/.test(password) && 
                   /[0-9]/.test(password) && 
                   /[^A-Za-z0-9]/.test(password);
        }
        
        function validateForm() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            const isPasswordValid = validatePassword(password);
            const passwordsMatch = password === confirmPassword;
            
            // Enable/disable submit button
            submitBtn.disabled = !(isPasswordValid && passwordsMatch);
            
            // Visual feedback for password match
            if (confirmPassword) {
                confirmPasswordInput.style.borderColor = passwordsMatch ? '#10b981' : '#ef4444';
                confirmPasswordInput.style.boxShadow = passwordsMatch ? '0 0 0 3px rgba(16, 185, 129, 0.1)' : '0 0 0 3px rgba(239, 68, 68, 0.1)';
            } else {
                confirmPasswordInput.style.borderColor = '#e2e8f0';
                confirmPasswordInput.style.boxShadow = 'none';
            }
        }
        
        // Event listeners
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validateForm();
        });
        
        confirmPasswordInput.addEventListener('input', validateForm);
        
        // Form submission validation
        document.getElementById('register-form').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (!validatePassword(password)) {
                e.preventDefault();
                alert('Please meet all password requirements.');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                return;
            }
        });
        
        // Initialize
        validateForm();
    </script>
</body>
</html>