<?php
function isLoggedIn() {
    return isset($_SESSION['user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function getUserRole() {
    return $_SESSION['user']['role'] ?? null;
}

function hasPermission($requiredRole) {
    $userRole = getUserRole();
    
    // Define role hierarchy
    $roleHierarchy = [
        'admin' => 4,
        'it_manager' => 3,
        'cashier' => 2,
        'customer' => 1
    ];
    
    if (!isset($roleHierarchy[$userRole]) || !isset($roleHierarchy[$requiredRole])) {
        return false;
    }
    
    return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}
?>