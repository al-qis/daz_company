<?php
// view_users.php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';

// Only admins can view this page
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Get all registered users
function getAllRegisteredUsers() {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM registered_users ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

$users = getAllRegisteredUsers();
$totalUsers = count($users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - DAZ Security Control</title>
    <style>
        /* Add the same styles from dashboard.php or use your existing styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        body {
            background-color: #f8fafc;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(90deg, #0f172a, rgba(15, 23, 42, 0.9));
            color: white;
            padding: 32px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .header p {
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th {
            background: #f8fafc;
            padding: 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .users-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #475569;
        }
        
        .users-table tr:hover {
            background-color: #f8fafc;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .role-admin { background: #fef2f2; color: #dc2626; }
        .role-it_manager { background: #f0f9ff; color: #0ea5e9; }
        .role-cashier { background: #f0fdf4; color: #10b981; }
        .role-customer { background: #f8fafc; color: #64748b; }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-active { background: #f0fdf4; color: #10b981; }
        .status-locked { background: #fef2f2; color: #dc2626; }
        .status-pending { background: #fefce8; color: #eab308; }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-edit { background: #dbeafe; color: #1d4ed8; }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-unlock { background: #f0fdf4; color: #10b981; }
        
        .btn-edit:hover { background: #bfdbfe; }
        .btn-delete:hover { background: #fecaca; }
        .btn-unlock:hover { background: #bbf7d0; }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: #0f172a;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .back-link:hover {
            background: #1e293b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registered Users Management</h1>
            <p>Admin Control Panel</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($users, fn($u) => $u['is_verified'])); ?></div>
                <div class="stat-label">Verified Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($users, fn($u) => $u['is_locked'])); ?></div>
                <div class="stat-label">Locked Accounts</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'customer')); ?></div>
                <div class="stat-label">Customers</div>
            </div>
        </div>
        
        <div style="padding: 24px; overflow-x: auto;">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['is_locked']): ?>
                                <span class="status-badge status-locked">Locked</span>
                            <?php elseif (!$user['is_verified']): ?>
                                <span class="status-badge status-pending">Pending</span>
                            <?php else: ?>
                                <span class="status-badge status-active">Active</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="action-btn btn-edit" onclick="editUser(<?php echo $user['id']; ?>)">Edit</button>
                            <?php if ($user['is_locked']): ?>
                                <button class="action-btn btn-unlock" onclick="unlockUser(<?php echo $user['id']; ?>)">Unlock</button>
                            <?php endif; ?>
                            <button class="action-btn btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="padding: 24px; text-align: center;">
            <a href="dashboard.php" class="back-link">Back to Dashboard</a>
        </div>
    </div>
    
    <script>
        function editUser(userId) {
            alert('Edit user ' + userId + ' - This feature would open an edit form in a real application.');
        }
        
        function unlockUser(userId) {
            if (confirm('Are you sure you want to unlock this user?')) {
                // In a real application, this would make an AJAX call to unlock the user
                alert('User ' + userId + ' unlocked. In a real app, this would be handled via AJAX.');
                location.reload();
            }
        }
        
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                // In a real application, this would make an AJAX call to delete the user
                alert('User ' + userId + ' deleted. In a real app, this would be handled via AJAX.');
                location.reload();
            }
        }
    </script>
</body>
</html>