<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/dbconnection.php';

// Initialize variables
$errors = [];
$success_message = '';
$users = [];
$total_users = 0;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    try {
        $stmt = $dbh->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$_POST['role'], $_POST['user_id']]);
        $success_message = "User role updated successfully";
    } catch (PDOException $e) {
        error_log("Update role error: " . $e->getMessage());
        $errors[] = "Error updating user role";
    }
}

// Build query for user list
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (full_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter) {
    $query .= " AND role = ?";
    $params[] = $role_filter;
}

// Get total users count
$count_query = "SELECT COUNT(*) FROM (" . $query . ") as total";
$stmt = $dbh->prepare($count_query);
$stmt->execute($params);
$total_users = $stmt->fetchColumn();

// Calculate pagination
$total_pages = ceil($total_users / $per_page);
$offset = ($current_page - 1) * $per_page;

// Get users with pagination
$query .= " ORDER BY created_at DESC LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;

try {
    $stmt = $dbh->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Check if roles are being fetched correctly
    error_log("Users fetched: " . print_r($users, true));
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $errors[] = "Error fetching users from database";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - EventPro Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body class="light-mode">
    <!-- Sidebar -->
    <div class="sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-wrapper">
            <div class="content-header">
                <h1>User Management</h1>
                <a href="add_user.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="filters">
                <select id="roleFilter" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="super_user">Super User</option>
                    <option value="user">User</option>
                </select>
            </div>

            <!-- Users Table -->
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? ''); ?></td>
                                <td>
                                    <form method="POST" class="role-form">
                                        <input type="hidden" name="action" value="update_role">
                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id'] ?? ''); ?>">
                                        <select name="role" class="form-select">
                                            <option value="admin" <?php echo (isset($user['role']) && $user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                            <option value="super_user" <?php echo (isset($user['role']) && $user['role'] === 'super_user') ? 'selected' : ''; ?>>Super User</option>
                                            <option value="user" <?php echo (isset($user['role']) && $user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo htmlspecialchars($user['id'] ?? ''); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#usersTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[3, 'desc']], // Sort by role
                language: {
                    search: "Search users:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ users",
                    infoEmpty: "Showing 0 to 0 of 0 users",
                    infoFiltered: "(filtered from _MAX_ total users)"
                }
            });

            // Role filter
            $('#roleFilter').on('change', function() {
                table.column(3).search(this.value).draw();
            });

            // Delete user
            $('.delete-user').click(function() {
                if (confirm('Are you sure you want to delete this user?')) {
                    const userId = $(this).data('id');
                    $.ajax({
                        url: 'users.php',
                        method: 'POST',
                        data: {
                            action: 'delete',
                            id: userId
                        },
                        success: function(response) {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Error: ' + error);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 