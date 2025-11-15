<?php
global $dbh;
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Database connection
require_once '../includes/dbconnection.php';

$stmt = $dbh->query("SELECT * FROM events LEFT JOIN users ON events.user_id = users.id");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - EventPro Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .table-responsive {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 20px;
            padding: 20px;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1rem;
        }

        .table th {
            background-color: var(--light-bg);
            color: var(--text-color);
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid var(--border-color);
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
            border-radius: 4px;
        }

        .btn-info {
            background-color: var(--info-color);
            color: white;
            border: none;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
            border: none;
        }

        .btn-info:hover, .btn-danger:hover {
            opacity: 0.9;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-upcoming {
            background-color: var(--info-color);
            color: white;
        }

        .status-in-progress {
            background-color: var(--warning-color);
            color: white;
        }

        .status-completed {
            background-color: var(--success-color);
            color: white;
        }

        .status-cancelled {
            background-color: var(--danger-color);
            color: white;
        }

        @media (max-width: 768px) {
            .table-responsive {
                padding: 10px;
            }
            
            .table th,
            .table td {
                padding: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }
        }
    </style>
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
                            <th>Description</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($event['description']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($event['start_date'])); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($event['end_date'])); ?></td>
                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($event['status']); ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($event['full_name']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-info edit-event" data-id="<?php echo $event['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-event" data-id="<?php echo $event['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
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
                const eventId = $(this).data('id');
                window.location.href = `edit_event.php?id=${eventId}`;
            });

            // Delete Event
            $('.delete-event').on('click', function() {
                if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                    const eventId = $(this).data('id');
                    $.post('delete_event.php', { id: eventId }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error deleting event');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html> 