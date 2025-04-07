<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Database connection
require_once '../includes/dbconnection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $dbh->prepare("
                    INSERT INTO events (event_name, description, event_date, location, organizer_id, status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['event_name'],
                    $_POST['description'],
                    $_POST['event_date'],
                    $_POST['location'],
                    $_POST['organizer_id'],
                    $_POST['status']
                ]);
                echo json_encode(['success' => true, 'message' => 'Event added successfully']);
                exit();
                break;

            case 'edit':
                $stmt = $dbh->prepare("
                    UPDATE events
                    SET event_name = ?, description = ?, event_date = ?, location = ?, organizer_id = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['event_name'],
                    $_POST['description'],
                    $_POST['event_date'],
                    $_POST['location'],
                    $_POST['organizer_id'],
                    $_POST['status'],
                    $_POST['id']
                ]);
                echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
                exit();
                break;

            case 'delete':
                $stmt = $dbh->prepare("DELETE FROM events WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
                exit();
                break;

            case 'change_status':
                $stmt = $dbh->prepare("UPDATE events SET status = ? WHERE id = ?");
                $stmt->execute([$_POST['status'], $_POST['id']]);
                echo json_encode(['success' => true, 'message' => 'Event status updated successfully']);
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

// Fetch all events with organizer details
try {
    $stmt = $dbh->query("
        SELECT e.*, u.full_name as organizer_name, u.role as organizer_role
        FROM events e
        LEFT JOIN users u ON e.organizer_id = u.id
        ORDER BY e.event_date DESC
    ");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
    $error = "Error fetching events: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - EventPro</title>
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
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li class="active">
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
                <input type="text" id="searchInput" placeholder="Search events...">
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

        <!-- Events Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <h1>Event Management</h1>
                <a href="add_event.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Event
                </a>
            </div>

            <!-- Filters -->
            <div class="filters">
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
                <select id="organizerFilter" class="form-select">
                    <option value="">All Organizers</option>
                    <?php
                    // Fetch unique organizers
                    $organizers = array_unique(array_column($events, 'organizer_name'));
                    foreach ($organizers as $organizer) {
                        echo "<option value='$organizer'>$organizer</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Events Table -->
            <div class="table-responsive">
                <table id="eventsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Organizer</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($event['organizer_name']); ?>
                                <span class="badge bg-secondary"><?php echo ucfirst($event['organizer_role']); ?></span>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($event['event_date'])); ?></td>
                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $event['status']; ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info edit-event" data-id="<?php echo $event['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-event" data-id="<?php echo $event['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-warning change-status" data-id="<?php echo $event['id']; ?>">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1">
        <!-- Similar structure to Add Event Modal, will be populated dynamically -->
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this event? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div class="modal fade" id="statusChangeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Event Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select class="form-select" id="newStatus">
                        <option value="upcoming">Upcoming</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStatusChange">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#eventsTable').DataTable({
                responsive: true,
                order: [[2, 'desc']] // Sort by date by default
            });

            // Search functionality
            $('#searchInput').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Status filter
            $('#statusFilter').on('change', function() {
                table.column(4).search(this.value).draw();
            });

            // Organizer filter
            $('#organizerFilter').on('change', function() {
                table.column(1).search(this.value).draw();
            });

            // Edit Event
            $('.edit-event').on('click', function() {
                var eventId = $(this).data('id');
                // Load event data and show edit modal
            });

            // Delete Event
            $('.delete-event').on('click', function() {
                var eventId = $(this).data('id');
                $('#deleteEventModal').modal('show');
                $('#confirmDelete').data('id', eventId);
            });

            // Change Status
            $('.change-status').on('click', function() {
                var eventId = $(this).data('id');
                $('#statusChangeModal').modal('show');
                $('#confirmStatusChange').data('id', eventId);
            });

            // Confirm Delete
            $('#confirmDelete').on('click', function() {
                var eventId = $(this).data('id');
                // Handle delete request
            });

            // Confirm Status Change
            $('#confirmStatusChange').on('click', function() {
                var eventId = $(this).data('id');
                var newStatus = $('#newStatus').val();
                // Handle status change request
            });
        });
    </script>
</body>
</html> 