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
        case 'create_event':
            try {
                $stmt = $dbh->prepare("
                    INSERT INTO events (
                        title, description, event_date, location, 
                        capacity, status, created_by
                    ) VALUES (?, ?, ?, ?, ?, 'pending', ?)
                ");
                
                $stmt->execute([
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['event_date'],
                    $_POST['location'],
                    $_POST['capacity'],
                    $user_id
                ]);
                
                $success_message = "Event created successfully!";
            } catch (PDOException $e) {
                error_log("Create event error: " . $e->getMessage());
                $errors[] = "Error creating event";
            }
            break;

        case 'update_event':
            try {
                $stmt = $dbh->prepare("
                    UPDATE events 
                    SET title = ?, description = ?, event_date = ?, 
                        location = ?, capacity = ?, status = ?
                    WHERE id = ? AND created_by = ?
                ");
                
                $stmt->execute([
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['event_date'],
                    $_POST['location'],
                    $_POST['capacity'],
                    $_POST['status'],
                    $_POST['event_id'],
                    $user_id
                ]);
                
                $success_message = "Event updated successfully!";
            } catch (PDOException $e) {
                error_log("Update event error: " . $e->getMessage());
                $errors[] = "Error updating event";
            }
            break;
    }
}

// Get events created by the superuser
try {
    $stmt = $dbh->prepare("
        SELECT e.*, 
               COUNT(r.id) as registration_count
        FROM events e
        LEFT JOIN registrations r ON e.id = r.event_id
        WHERE e.created_by = ?
        GROUP BY e.id
        ORDER BY e.event_date DESC
    ");
    $stmt->execute([$user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch events error: " . $e->getMessage());
    $errors[] = "Error fetching events";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - EventPro</title>
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
                <input type="text" placeholder="Search events...">
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

        <!-- Events Content -->
        <div class="content">
            <div class="content-header">
                <h1>Event Management</h1>
                <button class="btn btn-primary" onclick="showCreateEventModal()">
                    <i class="fas fa-plus"></i> Create New Event
                </button>
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

            <!-- Events Table -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Registrations</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                <td><?php echo $event['capacity']; ?></td>
                                <td><?php echo $event['registration_count']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $event['status']; ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-event" 
                                            data-event-id="<?php echo $event['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($event['title']); ?>"
                                            data-description="<?php echo htmlspecialchars($event['description']); ?>"
                                            data-date="<?php echo $event['event_date']; ?>"
                                            data-location="<?php echo htmlspecialchars($event['location']); ?>"
                                            data-capacity="<?php echo $event['capacity']; ?>"
                                            data-status="<?php echo $event['status']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="view_event.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div id="createEventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Event</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form id="createEventForm" method="POST">
                <input type="hidden" name="action" value="create_event">
                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="datetime-local" id="event_date" name="event_date" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" required>
                </div>
                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" id="capacity" name="capacity" min="1" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateEventModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div id="editEventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Event</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form id="editEventForm" method="POST">
                <input type="hidden" name="action" value="update_event">
                <input type="hidden" name="event_id" id="edit_event_id">
                <div class="form-group">
                    <label for="edit_title">Event Title</label>
                    <input type="text" id="edit_title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_event_date">Event Date</label>
                    <input type="datetime-local" id="edit_event_date" name="event_date" required>
                </div>
                <div class="form-group">
                    <label for="edit_location">Location</label>
                    <input type="text" id="edit_location" name="location" required>
                </div>
                <div class="form-group">
                    <label for="edit_capacity">Capacity</label>
                    <input type="number" id="edit_capacity" name="capacity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditEventModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/superuser.js"></script>
    <script>
        // Modal functions
        function showCreateEventModal() {
            document.getElementById('createEventModal').style.display = 'block';
        }

        function closeCreateEventModal() {
            document.getElementById('createEventModal').style.display = 'none';
        }

        function showEditEventModal() {
            document.getElementById('editEventModal').style.display = 'block';
        }

        function closeEditEventModal() {
            document.getElementById('editEventModal').style.display = 'none';
        }

        // Edit event button click handler
        document.querySelectorAll('.edit-event').forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.dataset.eventId;
                document.getElementById('edit_event_id').value = eventId;
                document.getElementById('edit_title').value = this.dataset.title;
                document.getElementById('edit_description').value = this.dataset.description;
                document.getElementById('edit_event_date').value = this.dataset.date;
                document.getElementById('edit_location').value = this.dataset.location;
                document.getElementById('edit_capacity').value = this.dataset.capacity;
                document.getElementById('edit_status').value = this.dataset.status;
                showEditEventModal();
            });
        });

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