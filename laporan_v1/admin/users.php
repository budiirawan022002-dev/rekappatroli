<?php
/**
 * User Management Page (Admin Only)
 */
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../config/auth.php');
require_once(__DIR__ . '/../auth_check.php');

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        
        if (empty($username) || empty($password)) {
            $message = 'Username dan password harus diisi!';
            $messageType = 'danger';
        } else {
            $result = createUser($username, $password, $fullName, $email, $role);
            if ($result) {
                $message = 'User berhasil dibuat!';
                $messageType = 'success';
            } else {
                $message = 'Gagal membuat user. Username mungkin sudah ada.';
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'change_password') {
        $username = $_POST['username'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        if (empty($newPassword)) {
            $message = 'Password baru harus diisi!';
            $messageType = 'danger';
        } else {
            if (updateUserPassword($username, $newPassword)) {
                $message = 'Password berhasil diubah!';
                $messageType = 'success';
            } else {
                $message = 'Gagal mengubah password!';
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'update') {
        $userId = intval($_POST['user_id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($username)) {
            $message = 'Username harus diisi!';
            $messageType = 'danger';
        } else {
            if (updateUser($userId, $username, $fullName, $email, $role, $isActive)) {
                $message = 'User berhasil diperbarui!';
                $messageType = 'success';
            } else {
                $message = 'Gagal memperbarui user!';
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'delete') {
        $userId = intval($_POST['user_id'] ?? 0);
        
        // Prevent deleting yourself
        $currentUserId = $_SESSION['user_id'] ?? null;
        if ($currentUserId && $userId == $currentUserId) {
            $message = 'Anda tidak dapat menonaktifkan akun sendiri!';
            $messageType = 'danger';
        } else {
            if (deleteUser($userId)) {
                $message = 'User berhasil dinonaktifkan!';
                $messageType = 'success';
            } else {
                $message = 'Gagal menonaktifkan user!';
                $messageType = 'danger';
            }
        }
    }
}

// Get filter parameters
$searchQuery = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';
$roleFilter = $_GET['role'] ?? 'all';

// Get all users
$allUsers = getAllUsers();

// Filter users
$users = array_filter($allUsers, function($user) use ($searchQuery, $statusFilter, $roleFilter) {
    // Search filter
    if (!empty($searchQuery)) {
        $searchLower = strtolower($searchQuery);
        $usernameMatch = strpos(strtolower($user['username']), $searchLower) !== false;
        $emailMatch = $user['email'] && strpos(strtolower($user['email']), $searchLower) !== false;
        $nameMatch = $user['full_name'] && strpos(strtolower($user['full_name']), $searchLower) !== false;
        if (!$usernameMatch && !$emailMatch && !$nameMatch) {
            return false;
        }
    }
    
    // Status filter
    if ($statusFilter !== 'all') {
        $isActive = $statusFilter === 'active' ? 1 : 0;
        if ($user['is_active'] != $isActive) {
            return false;
        }
    }
    
    // Role filter
    if ($roleFilter !== 'all') {
        if ($user['role'] !== $roleFilter) {
            return false;
        }
    }
    
    return true;
});

$users = array_values($users); // Re-index array
$totalUsers = count($allUsers);

// Format date helper
function formatDate($date) {
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

function formatDateTime($date) {
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    return date('d/m/Y, H.i.s', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Users - Rekap Hastag</title>
    <link href="<?php echo dirname(__DIR__); ?>/template_web/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?php echo dirname(__DIR__); ?>/node_modules/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        
        .main-wrapper {
            min-height: 100vh;
            padding: 2rem;
        }
        
        .container-custom {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Header */
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            color: var(--gray-600);
            font-size: 1rem;
        }
        
        .header-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            justify-content: flex-end;
        }
        
        /* Buttons */
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9375rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        
        .btn-outline {
            background: white;
            color: var(--gray-700);
            border: 1.5px solid var(--gray-300);
        }
        
        .btn-outline:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group:last-child {
            margin-bottom: 0;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            display: block;
        }
        
        .form-control, .form-select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1.5px solid var(--gray-300);
            border-radius: 8px;
            font-size: 0.9375rem;
            transition: all 0.2s;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        /* Table Card */
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        
        .table-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: var(--gray-50);
        }
        
        .table-card-title {
            font-weight: 600;
            font-size: 1.125rem;
            color: var(--gray-900);
            margin: 0;
            white-space: nowrap;
        }
        
        /* Table */
        .table {
            margin: 0;
        }
        
        .table thead th {
            background: var(--gray-50);
            color: var(--gray-700);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
            border-bottom: 2px solid var(--gray-200);
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--gray-100);
        }
        
        .table tbody tr {
            transition: background 0.2s;
        }
        
        .table tbody tr:hover {
            background: var(--gray-50);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }
        
        .user-email {
            font-size: 0.875rem;
            color: var(--gray-500);
        }
        
        /* Badge */
        .badge {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-block;
        }
        
        .badge-primary {
            background: #e0e7ff;
            color: #4338ca;
        }
        
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .btn-icon.view {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .btn-icon.edit {
            background: #fef3c7;
            color: #92400e;
        }
        
        .btn-icon.delete {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .btn-icon:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        /* Modal */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1.5rem;
            border: none;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-header .modal-title {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            background: var(--gray-50);
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group:last-child {
            margin-bottom: 0;
        }
        
        .required {
            color: var(--danger);
        }
        
        /* Alert */
        .alert-custom {
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-500);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .table-card-header {
                flex-wrap: wrap;
            }
            
            .table-card-header > div {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }
            
            .table-card-header form {
                max-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .main-wrapper {
                padding: 1rem;
            }
            
            .page-header {
                margin-bottom: 1.5rem;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .header-actions {
                flex-direction: column;
                margin-top: 1rem;
            }
            
            .header-actions .btn {
                width: 100%;
            }
            
            .table-card-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .table-card-header > div {
                flex-direction: column;
            }
            
            .table-card-header form {
                flex-direction: column;
                max-width: 100%;
            }
            
            .table-card-header form .form-control,
            .table-card-header form .form-select {
                width: 100%;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="container-custom">
            <!-- Header -->
            <div class="page-header">
                <h1>Manage Users</h1>
                <p>Manage system users and their permissions</p>
                <div class="header-actions">
                    <button type="button" class="btn btn-outline">
                        <i class="bi bi-funnel"></i>
                        Filters
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-lg"></i>
                        Add User
                    </button>
                </div>
            </div>
            
            <!-- Alert Message -->
            <?php if ($message): ?>
                <div class="alert-custom alert-<?php echo $messageType; ?>">
                    <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Users Table Card -->
            <div class="table-card">
                <div class="table-card-header">
                    <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                        <h2 class="table-card-title">Users List</h2>
                        <form method="GET" action="" style="display: flex; gap: 0.75rem; align-items: center; flex: 1; max-width: 600px;">
                            <input type="text" class="form-control" name="search" 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>" 
                                   placeholder="Search by name or email..." 
                                   style="flex: 1; min-width: 200px;">
                            <select class="form-select" name="status" style="width: 140px;">
                                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <select class="form-select" name="role" style="width: 120px;">
                                <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                                <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="user" <?php echo $roleFilter === 'user' ? 'selected' : ''; ?>>User</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                                <i class="bi bi-search"></i>
                                Search
                            </button>
                        </form>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-lg"></i>
                        Add User
                    </button>
                </div>
                <div class="table-card-body">
                    <?php if (empty($users)): ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>No users found</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                    </div>
                                                    <div class="user-details">
                                                        <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                                        <div class="user-email"><?php echo htmlspecialchars($user['email'] ?: '-'); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $user['is_active'] ? 'ACTIVE' : 'INACTIVE'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDateTime($user['last_login'] ?? null); ?></td>
                                            <td><?php echo formatDate($user['created_at'] ?? null); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-icon view" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewUserModal<?php echo $user['id']; ?>"
                                                            title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-icon edit" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editUserModal<?php echo $user['id']; ?>"
                                                            title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php 
                                                    $currentUserId = $_SESSION['user_id'] ?? null;
                                                    if (!$currentUserId || $user['id'] != $currentUserId): 
                                                    ?>
                                                        <button type="button" class="btn btn-icon delete" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteUserModal<?php echo $user['id']; ?>"
                                                                title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- View User Modal -->
                                        <div class="modal fade" id="viewUserModal<?php echo $user['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-person-fill"></i>
                                                            User Details - <?php echo htmlspecialchars($user['username']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="form-label">Username</label>
                                                            <div style="padding: 0.625rem 0.875rem; background: var(--gray-50); border-radius: 8px;">
                                                                <?php echo htmlspecialchars($user['username']); ?>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Full Name</label>
                                                            <div style="padding: 0.625rem 0.875rem; background: var(--gray-50); border-radius: 8px;">
                                                                <?php echo htmlspecialchars($user['full_name'] ?: '-'); ?>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Email</label>
                                                            <div style="padding: 0.625rem 0.875rem; background: var(--gray-50); border-radius: 8px;">
                                                                <?php echo htmlspecialchars($user['email'] ?: '-'); ?>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Role</label>
                                                            <div style="padding: 0.625rem 0.875rem;">
                                                                <span class="badge badge-primary"><?php echo ucfirst($user['role']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Status</label>
                                                            <div style="padding: 0.625rem 0.875rem;">
                                                                <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                                                    <?php echo $user['is_active'] ? 'ACTIVE' : 'INACTIVE'; ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Last Login</label>
                                                            <div style="padding: 0.625rem 0.875rem; background: var(--gray-50); border-radius: 8px;">
                                                                <?php echo formatDateTime($user['last_login'] ?? null); ?>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Created</label>
                                                            <div style="padding: 0.625rem 0.875rem; background: var(--gray-50); border-radius: 8px;">
                                                                <?php echo formatDateTime($user['created_at'] ?? null); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Edit User Modal -->
                                        <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-pencil-fill"></i>
                                                            Edit User - <?php echo htmlspecialchars($user['username']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="update">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            
                                                            <div class="form-group">
                                                                <label class="form-label">Username <span class="required">*</span></label>
                                                                <input type="text" class="form-control" name="username" 
                                                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="form-label">Full Name</label>
                                                                <input type="text" class="form-control" name="full_name" 
                                                                       value="<?php echo htmlspecialchars($user['full_name'] ?: ''); ?>">
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="form-label">Email</label>
                                                                <input type="email" class="form-control" name="email" 
                                                                       value="<?php echo htmlspecialchars($user['email'] ?: ''); ?>">
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="form-label">Role</label>
                                                                <select class="form-select" name="role">
                                                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active<?php echo $user['id']; ?>" 
                                                                           value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="is_active<?php echo $user['id']; ?>">
                                                                        User Aktif
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="bi bi-check-circle-fill"></i> Save Changes
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Change Password Modal -->
                                        <div class="modal fade" id="changePasswordModal<?php echo $user['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-key-fill"></i>
                                                            Change Password - <?php echo htmlspecialchars($user['username']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="change_password">
                                                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                                            
                                                            <div class="form-group">
                                                                <label class="form-label">New Password <span class="required">*</span></label>
                                                                <input type="password" class="form-control" name="new_password" required 
                                                                       placeholder="Enter new password">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="bi bi-check-circle-fill"></i> Save Password
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete User Modal -->
                                        <?php 
                                        $currentUserId = $_SESSION['user_id'] ?? null;
                                        if (!$currentUserId || $user['id'] != $currentUserId): 
                                        ?>
                                            <div class="modal fade" id="deleteUserModal<?php echo $user['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);">
                                                            <h5 class="modal-title">
                                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                                Confirm Delete User
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <p style="margin-bottom: 0.5rem;">Are you sure you want to deactivate user <strong><?php echo htmlspecialchars($user['username']); ?></strong>?</p>
                                                                <p class="text-muted" style="font-size: 0.875rem; margin: 0;">User will be deactivated and cannot login anymore.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">
                                                                    <i class="bi bi-trash-fill"></i> Yes, Deactivate
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Add User Modal -->
            <div class="modal fade" id="addUserModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-person-plus-fill"></i>
                                Add New User
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="create">
                                
                                <div class="form-group">
                                    <label class="form-label">Username <span class="required">*</span></label>
                                    <input type="text" class="form-control" name="username" required placeholder="Enter username">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Password <span class="required">*</span></label>
                                    <input type="password" class="form-control" name="password" required placeholder="Enter password">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="full_name" placeholder="Enter full name">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" placeholder="email@example.com">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Role</label>
                                    <select class="form-select" name="role">
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle-fill"></i> Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo dirname(__DIR__); ?>/template_web/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Quick search functionality
        document.getElementById('quickSearch')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
