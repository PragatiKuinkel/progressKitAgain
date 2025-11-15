<?php
session_start();

// Check if user is logged in and is a superuser
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_user') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/dbconnection.php';

// Initialize variables
$errors = [];
$success_message = '';
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_registration':
            try {
                $stmt = $dbh->prepare("
                    UPDATE registrations r
                    JOIN events e ON r.event_id = e.id
                    SET r.status = ?
                    WHERE r.id = ? AND e.created_by = ?
                ");
                
                $stmt->execute([
                    $_POST['status'],
                    $_POST['registration_id'],
                    $user_id
                ]);
                
                $success_message = "Registration status updated successfully!";
            } catch (PDOException $e) {
                error_log("Update registration error: " . $e->getMessage());
                $errors[] = "Error updating registration status";
            }
            break;
    }
}

// Get registrations for events created by the superuser
try {
    $stmt = $dbh->prepare("
        SELECT r.*, 
               e.title as event_title,
               u.full_name as user_name,
               u.email as user_email
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        JOIN users u ON r.user_id = u.id
        WHERE e.created_by = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch registrations error: " . $e->getMessage());
    $errors[] = "Error fetching registrations";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrations - EventPro</title>
    <link rel="stylesheet" href="../assets/css/superuser.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="light-mode">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>EventPro</h2>
            <p>Superuser Dashboard</p>
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
                    <a href="events.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Event Management</span>
                    </a>
                </li>
                <li class="active">
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
                <input type="text" placeholder="Search registrations...">
            </div>
            <div class="user-info">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>
                <div class="user-profile">
                    <img src="../assets/images/user-avatar.jpg" alt="Superuser">
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
            </div>
        </header>

        <!-- Registrations Content -->
        <div class="content">
            <div class="content-header">
                <h1>Registrations Management</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <p><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="filters">
                <select id="status-filter" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select id="event-filter" class="form-control">
                    <option value="">All Events</option>
                    <?php
                    $stmt = $dbh->prepare("SELECT id, title FROM events WHERE created_by = ? ORDER BY title");
                    $stmt->execute([$user_id]);
                    while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value=\"{$event['id']}\">" . htmlspecialchars($event['title']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Registrations Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Event</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Rating</th>
                            <th>Feedback</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $registration): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <strong><?php echo htmlspecialchars($registration['user_name']); ?></strong>
                                        <small><?php echo htmlspecialchars($registration['user_email']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($registration['event_title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($registration['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $registration['status']; ?>">
                                        <?php echo ucfirst($registration['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($registration['rating']): ?>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $registration['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No rating</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($registration['feedback']): ?>
                                        <button class="btn btn-sm btn-info view-feedback" 
                                                data-feedback="<?php echo htmlspecialchars($registration['feedback']); ?>">
                                            <i class="fas fa-comment"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">No feedback</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-primary edit-registration" 
                                                data-registration-id="<?php echo $registration['id']; ?>"
                                                data-status="<?php echo $registration['status']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="view_registration.php?id=<?php echo $registration['id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Registration Modal -->
    <div id="editRegistrationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Registration Status</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form id="editRegistrationForm" method="POST">
                <input type="hidden" name="action" value="update_registration">
                <input type="hidden" name="registration_id" id="edit_registration_id">
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditRegistrationModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Feedback Modal -->
    <div id="viewFeedbackModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>User Feedback</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="feedback-content"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeViewFeedbackModal()">Close</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/superuser.js"></script>
    <script>
        // Filter functionality
        document.getElementById('status-filter').addEventListener('change', function() {
            filterRegistrations();
        });

        document.getElementById('event-filter').addEventListener('change', function() {
            filterRegistrations();
        });

        function filterRegistrations() {
            const statusFilter = document.getElementById('status-filter').value;
            const eventFilter = document.getElementById('event-filter').value;
            
            document.querySelectorAll('tbody tr').forEach(row => {
                const status = row.querySelector('.status-badge').textContent.toLowerCase();
                const eventId = row.querySelector('.edit-registration').dataset.eventId;
                
                const statusMatch = !statusFilter || status === statusFilter;
                const eventMatch = !eventFilter || eventId === eventFilter;
                
                row.style.display = statusMatch && eventMatch ? '' : 'none';
            });
        }

        // Edit registration button click handler
        document.querySelectorAll('.edit-registration').forEach(button => {
            button.addEventListener('click', function() {
                const registrationId = this.dataset.registrationId;
                document.getElementById('edit_registration_id').value = registrationId;
                document.getElementById('edit_status').value = this.dataset.status;
                showEditRegistrationModal();
            });
        });

        // View feedback button click handler
        document.querySelectorAll('.view-feedback').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('feedback-content').textContent = this.dataset.feedback;
                showViewFeedbackModal();
            });
        });

        // Modal functions
        function showEditRegistrationModal() {
            document.getElementById('editRegistrationModal').style.display = 'block';
        }

        function closeEditRegistrationModal() {
            document.getElementById('editRegistrationModal').style.display = 'none';
        }

        function showViewFeedbackModal() {
            document.getElementById('viewFeedbackModal').style.display = 'block';
        }

        function closeViewFeedbackModal() {
            document.getElementById('viewFeedbackModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Close modals when clicking close button
        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });
    </script>
</body>
</html> 