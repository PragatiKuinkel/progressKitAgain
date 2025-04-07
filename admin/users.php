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
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'edit':
                $stmt = $dbh->prepare("
                    UPDATE users 
                    SET full_name = ?, email = ?, phone = ?, role = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['full_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['role'],
                    $_POST['id']
                ]);
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
                exit();
                break;

            case 'delete':
                $stmt = $dbh->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                exit();
                break;

            case 'toggle_status':
                $stmt = $dbh->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->execute([$_POST['status'], $_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'User status updated successfully']);
                exit();
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                exit();
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        exit();
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

if ($status_filter !== '') {
    $query .= " AND status = ?";
    $params[] = (int)$status_filter;
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

$stmt = $dbh->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Progress Kit</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body class="light-mode">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/images/progress-kit-logo.png" alt="Progress Kit">
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="active">
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li>
                    <a href="events.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Event Management</span>
                    </a>
                </li>
                <li>
                    <a href="registrations.php">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Registrations</span>
                    </a>
                </li>
                <li>
                    <a href="announcements.php">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports & Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <button id="theme-toggle" class="btn btn-icon">
                <i class="fas fa-moon"></i>
                <span>Dark Mode</span>
            </button>
            <a href="../logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <header class="top-nav">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search users...">
            </div>
            <div class="user-info">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>
                <div class="user-profile">
                    <img src="../assets/images/admin-avatar.jpg" alt="Admin">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
            </div>
        </header>

        <!-- Users Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <h1>User Management</h1>
                <a href="add_user.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            </div>

            <!-- Filters -->
            <div class="filters">
                <select id="roleFilter" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="super_user">Super User</option>
                    <option value="user">User</option>
                </select>
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
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
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $user['status'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $user['status'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info edit-user" data-id="<?php echo $user['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo $user['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" id="editUserId" name="id">
                        <input type="hidden" name="action" value="edit">
                        
                        <div class="mb-3">
                            <label for="editFullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editFullName" name="full_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editPhone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="editPhone" name="phone" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editRole" class="form-label">Role</label>
                            <select class="form-select" id="editRole" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="super_user">Super User</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveEdit">Save Changes</button>
                </div>
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
                order: [[5, 'desc']] // Sort by created_at descending
            });

            // Search functionality
            $('#searchInput').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Role filter
            $('#roleFilter').on('change', function() {
                table.column(3).search(this.value).draw();
            });

            // Status filter
            $('#statusFilter').on('change', function() {
                table.column(4).search(this.value).draw();
            });

            // Edit user
            $('.edit-user').click(function() {
                const userId = $(this).data('id');
                const row = $(this).closest('tr');
                
                // Populate form with user data
                $('#editUserId').val(userId);
                $('#editFullName').val(row.find('td:eq(0)').text());
                $('#editEmail').val(row.find('td:eq(1)').text());
                $('#editPhone').val(row.find('td:eq(2)').text());
                $('#editRole').val(row.find('td:eq(3) .badge').text().toLowerCase().replace(' ', '_'));
                
                // Show modal
                $('#editUserModal').modal('show');
            });

            // Save edit
            $('#saveEdit').click(function() {
                const formData = new FormData($('#editUserForm')[0]);
                
                $.ajax({
                    url: 'users.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
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

            // Theme toggle
            $('#theme-toggle').click(function() {
                $('body').toggleClass('dark-mode');
                const isDarkMode = $('body').hasClass('dark-mode');
                localStorage.setItem('darkMode', isDarkMode);
                
                const icon = $(this).find('i');
                icon.toggleClass('fa-moon fa-sun');
            });

            // Check for saved theme preference
            if (localStorage.getItem('darkMode') === 'true') {
                $('body').addClass('dark-mode');
                $('#theme-toggle i').removeClass('fa-moon').addClass('fa-sun');
            }
        });
    </script>
</body>
</html> 