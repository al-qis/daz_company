<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$user = $_SESSION['user'];

// Get security logs
function getSecurityLogsFromDB($limit = 50) {
    require_once __DIR__ . '/includes/database.php';
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM security_logs ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

$logs = getSecurityLogsFromDB(50);

// Handle logout
if (isset($_GET['logout'])) {
    logSecurityEvent($user['username'], 'LOGOUT', 'User logged out', getClientIp());
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Control Terminal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .dashboard-header {
            flex-shrink: 0;
        }
        
        .banner {
            height: 96px;
            width: 100%;
            position: relative;
            overflow: hidden;
            background: linear-gradient(90deg, #0f172a, rgba(15, 23, 42, 0.9));
        }
        
        .banner-content {
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 40px;
            gap: 24px;
        }
        
        .banner-icon {
            padding: 8px;
            background-color: #2563eb;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: white;
        }
        
        .banner-title {
            font-size: 20px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.025em;
            text-transform: uppercase;
            line-height: 1;
        }
        
        .banner-subtitle {
            font-size: 11px;
            color: #60a5fa;
            font-weight: 700;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-style: italic;
        }
        
        .main-header {
            height: 64px;
            background-color: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        
        .session-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .session-dot {
            width: 4px;
            height: 4px;
            background-color: #cbd5e1;
            border-radius: 50%;
        }
        
        .session-encrypted {
            color: #10b981;
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background-color: #fef2f2;
            color: #dc2626;
        }
        
        .dashboard-main {
            flex: 1;
            padding: 32px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            animation: fadeIn 0.5s ease-out;
        }
        
        .dashboard-footer {
            background-color: white;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3em;
            padding: 16px;
            text-align: center;
        }
        
        /* Admin Dashboard */
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .metric-card {
            background-color: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            padding: 24px;
        }
        
        .metric-label {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 8px;
        }
        
        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
        }
        
        .metric-success {
            color: #10b981;
        }
        
        .metric-primary {
            color: #3b82f6;
        }
        
        .log-table-container {
            background-color: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .log-table-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            background-color: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .log-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .log-table thead {
            background-color: #f8fafc;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .log-table th {
            padding: 16px 24px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .log-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }
        
        .log-table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .log-table td {
            padding: 16px 24px;
            font-size: 12px;
            color: #475569;
        }
        
        .log-event-failed {
            color: #dc2626;
            font-weight: 600;
        }
        
        /* Cashier Dashboard */
        .cashier-dashboard {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .cashier-card {
            background-color: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            padding: 48px;
            text-align: center;
        }
        
        .cashier-icon {
            width: 80px;
            height: 80px;
            background-color: #dbeafe;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            border: 1px solid #bfdbfe;
            color: #2563eb;
        }
        
        .cashier-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .cashier-subtitle {
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.1em;
        }
        
        .cashier-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 40px;
        }
        
        .cashier-metric {
            background-color: #f8fafc;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        /* IT Dashboard */
        .it-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 32px;
        }
        
        .it-dark-card {
            background-color: #0f172a;
            color: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        }
        
        .it-light-card {
            background-color: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .it-events {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .it-event-item {
            padding: 12px;
            background-color: #f8fafc;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            border: 1px solid #f1f5f9;
        }
        
        /* Customer Dashboard */
        .customer-profile-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            min-height: 400px;
            display: flex;
            flex-direction: column;
        }
        
        @media (min-width: 768px) {
            .customer-profile-card {
                flex-direction: row;
            }
        }
        
        .customer-sidebar {
            background-color: #0f172a;
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        @media (min-width: 768px) {
            .customer-sidebar {
                width: 33.333%;
            }
        }
        
        .customer-avatar {
            width: 80px;
            height: 80px;
            background-color: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #60a5fa;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 24px;
        }
        
        .customer-content {
            flex: 1;
            padding: 40px;
            background-color: rgba(248, 250, 252, 0.3);
        }
        
        .customer-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        
        .customer-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .action-btn {
            padding: 10px 20px;
            background-color: #0f172a;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            cursor: pointer;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        
        .action-btn-outline {
            background-color: white;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="banner">
            <div class="banner-content">
                <div class="banner-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 20.944a11.955 11.955 0 01-8.618-3.04m17.236 0a11.955 11.955 0 01-8.618 3.04" />
                    </svg>
                </div>
                <div>
                    <h2 class="banner-title">Security Control Terminal</h2>
                    <p class="banner-subtitle">Identity: <?php echo htmlspecialchars($user['role']); ?> (<?php echo htmlspecialchars($user['username']); ?>)</p>
                </div>
            </div>
        </div>
        
        <div class="main-header">
            <div class="session-info">
                <span>Session Verified</span>
                <span class="session-dot"></span>
                <span class="session-encrypted">Encrypted</span>
            </div>
            <a href="?logout=true" class="logout-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7" />
                </svg>
                LOGOUT
            </a>
        </div>
    </div>
    
    <main class="dashboard-main">
        <?php if ($user['role'] === 'admin'): ?>
            <!-- Admin Dashboard -->
            <div>
                <div class="admin-grid">
                    <div class="metric-card">
                        <h5 class="metric-label">Audit Entries</h5>
                        <p class="metric-value"><?php echo count($logs); ?></p>
                    </div>
                    <div class="metric-card">
                        <h5 class="metric-label">System Health</h5>
                        <p class="metric-value metric-success">STABLE</p>
                    </div>
                    <div class="metric-card">
                        <h5 class="metric-label">Auth Protocols</h5>
                        <p class="metric-value metric-primary">MFA Active</p>
                    </div>
                </div>
                
                <div class="log-table-container">
                    <div class="log-table-header">
                        <h3>Global Security Log</h3>
                        <span style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; background: #e2e8f0; padding: 4px 8px; border-radius: 4px;">Read-Only</span>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="log-table">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Identity</th>
                                    <th>Event Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td style="font-weight: 700; text-transform: uppercase;"><?php echo htmlspecialchars($log['username']); ?></td>
                                    <td class="<?php echo strpos($log['event'], 'FAILED') !== false ? 'log-event-failed' : ''; ?>">
                                        <?php echo str_replace('_', ' ', htmlspecialchars($log['event'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        <?php elseif ($user['role'] === 'cashier'): ?>
            <!-- Cashier Dashboard -->
            <div class="cashier-dashboard">
                <div class="cashier-card">
                    <div class="cashier-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="cashier-title">Retail Transaction Node</h3>
                    <p class="cashier-subtitle">Ready for secure checkout</p>
                    
                    <div class="cashier-metrics">
                        <div class="cashier-metric">
                            <p style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Shift Total</p>
                            <p style="font-size: 24px; font-weight: 700; color: #0f172a;">$2,145.00</p>
                        </div>
                        <div class="cashier-metric">
                            <p style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Vault Link</p>
                            <p style="font-size: 24px; font-weight: 700; color: #10b981;">SECURE</p>
                        </div>
                    </div>
                    
                    <button style="margin-top: 32px; width: 100%; padding: 16px; background-color: #0f172a; color: white; border: none; border-radius: 10px; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer;">
                        Open New Sale Terminal
                    </button>
                </div>
            </div>
            
        <?php elseif ($user['role'] === 'it_manager'): ?>
            <!-- IT Manager Dashboard -->
            <div>
                <div class="it-grid">
                    <div class="it-dark-card">
                        <h4 style="font-size: 11px; font-weight: 700; color: #60a5fa; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 24px;">Network Architecture Status</h4>
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <span style="opacity: 0.7;">Primary Auth Server</span>
                                <span style="color: #10b981; font-weight: 700; text-transform: uppercase;">Online</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <span style="opacity: 0.7;">Log Storage Unit</span>
                                <span style="color: #10b981; font-weight: 700; text-transform: uppercase;">Stable</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="opacity: 0.7;">MFA Bridge</span>
                                <span style="color: #60a5fa; font-weight: 700; text-transform: uppercase;">Operational</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="it-light-card">
                        <h4 style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 16px;">System Intrusions Today</h4>
                        <p style="font-size: 48px; font-weight: 700; color: #0f172a;">0</p>
                        <p style="font-size: 10px; color: #10b981; font-weight: 700; text-transform: uppercase; margin-top: 8px;">All defensive modules active</p>
                    </div>
                </div>
                
                <div style="background-color: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    <h4 style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 16px;">Technical Event Stream</h4>
                    <div class="it-events">
                        <?php foreach (array_slice($logs, 0, 4) as $log): ?>
                        <div class="it-event-item">
                            <span style="opacity: 0.6;"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></span>
                            <span style="text-transform: uppercase; color: #0f172a;"><?php echo htmlspecialchars($log['username']); ?></span>
                            <span style="color: #3b82f6; text-transform: uppercase;"><?php echo htmlspecialchars($log['event']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
        <?php elseif ($user['role'] === 'customer'): ?>
            <!-- Customer Dashboard -->
            <div>
                <div class="customer-profile-card">
                    <div class="customer-sidebar">
                        <div class="customer-avatar">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <h3 style="font-size: 20px; font-weight: 700; text-transform: uppercase; letter-spacing: -0.025em;"><?php echo htmlspecialchars($user['username']); ?></h3>
                        <p style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; margin-top: 8px;">Verified Member Profile</p>
                    </div>
                    
                    <div class="customer-content">
                        <div>
                            <h4 style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 24px;">Account Metrics</h4>
                            <div class="customer-metrics">
                                <div>
                                    <p style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Loyalty Status</p>
                                    <p style="font-size: 24px; font-weight: 700; color: #0f172a; text-transform: uppercase;">Standard</p>
                                </div>
                                <div>
                                    <p style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Purchase Points</p>
                                    <p style="font-size: 24px; font-weight: 700; color: #0f172a;">1,240</p>
                                </div>
                            </div>
                        </div>
                        
                        <div style="padding-top: 32px; border-top: 1px solid #e2e8f0; margin-top: 32px;">
                            <h4 style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 16px;">Security Center</h4>
                            <div class="customer-actions">
                                <button class="action-btn">Reset Password</button>
                                <button class="action-btn action-btn-outline">Update 2FA Email</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    
    <footer class="dashboard-footer">
        Restricted Access Platform • Session Node <?php echo rand(1000, 9999); ?> • BIS30703 Security Framework
    </footer>
</body>
</html>