<?php
/**
 * Authentication Functions
 */

require_once(__DIR__ . '/database.php');

/**
 * Authenticate user
 */
function authenticate($username, $password) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}

/**
 * Get user by username
 */
function getUserByUsername($username) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT id, username, full_name, email, role FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

/**
 * Create new user
 */
function createUser($username, $password, $fullName = '', $email = '', $role = 'user') {
    $db = getDB();
    
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, $fullName, $email, $role]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user password
 */
function updateUserPassword($username, $newPassword) {
    $db = getDB();
    
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE username = ?");
    return $stmt->execute([$hashedPassword, $username]);
}

/**
 * Get all users (for admin)
 */
function getAllUsers() {
    $db = getDB();
    
    $stmt = $db->query("SELECT id, username, full_name, email, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Get user by ID
 */
function getUserById($userId) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT id, username, full_name, email, role, is_active FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Update user
 */
function updateUser($userId, $username, $fullName = '', $email = '', $role = 'user', $isActive = 1) {
    $db = getDB();
    
    try {
        $stmt = $db->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, role = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$username, $fullName, $email, $role, $isActive, $userId]);
    } catch (PDOException $e) {
        error_log("Error updating user: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete user
 */
function deleteUser($userId) {
    $db = getDB();
    
    try {
        // Don't actually delete, just deactivate
        $stmt = $db->prepare("UPDATE users SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$userId]);
    } catch (PDOException $e) {
        error_log("Error deleting user: " . $e->getMessage());
        return false;
    }
}
?>

