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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // Validate required fields
        $required_fields = ['event_name', 'start_date', 'end_date', 'location', 'status'];
        $errors = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }

        // Validate dates
        if (strtotime($_POST['end_date']) < strtotime($_POST['start_date'])) {
            $errors[] = 'End date must be after start date';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit();
        }

        $assigned_users_csv = isset($_POST['assigned_users']) ? implode(',', $_POST['assigned_users']) : null;

        $stmt = $dbh->prepare("
            INSERT INTO events (
                event_name, description, start_date, end_date, location, status, is_public, user_id, assigned_users
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['event_name'],
            $_POST['description'],
            $_POST['start_date'],
            $_POST['end_date'],
            $_POST['location'],
            $_POST['status'],
            $_POST['is_public'] ?? 0,
            $_SESSION['user_id'],
            $assigned_users_csv
        ]);

        echo json_encode(['success' => true, 'message' => 'Event created successfully']);
    } catch (PDOException $e) {
        error_log("Error creating event: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error creating event', 'error' => $e->getMessage()]);
    }
    exit();
}

// Fetch users for the autocomplete dropdown
try {
    $stmt = $dbh->query("
        SELECT id, full_name, email, role
        FROM users
        WHERE role IN ('user', 'super_user')
        ORDER BY full_name
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event - EventPro</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
</head>
<body class="light-mode">
<!-- Sidebar -->
<div class="sidebar">
    <?php include 'includes/sidebar.php'; ?>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navigation -->
    <header class="top-nav">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search...">
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

    <!-- Add Event Content -->
    <div class="content-wrapper">
        <div class="content-header">
            <h1>Add New Event</h1>
            <a href="events.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Events
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="addEventForm" class="needs-validation" novalidate>
                    <!-- Basic Information Section -->
                    <div class="form-section mb-4">
                        <h3 class="section-title">Basic Information</h3>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="event_name" class="form-label">Event Name *</label>
                                <input type="text" class="form-control form-control-lg" id="event_name"
                                       name="event_name" required>
                                <div class="invalid-feedback">Please enter the event name</div>
                            </div>

                            <div class="col-md-6">
                                <label for="event_status" class="form-label">Status *</label>
                                <select class="form-select form-select-lg" id="event_status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="upcoming">Upcoming</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <div class="invalid-feedback">Please select the event status</div>
                            </div>

                            <div class="col-12">
                                <label for="event_description" class="form-label">Description</label>
                                <textarea class="form-control" id="event_description" name="description"
                                          rows="4"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Date & Time Section -->
                    <div class="form-section mb-4">
                        <h3 class="section-title">Date & Time</h3>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="event_start_date" class="form-label">Start Date & Time *</label>
                                <input type="datetime-local" class="form-control form-control-lg" id="event_start_date"
                                       name="start_date" required>
                                <div class="invalid-feedback">Please select the start date and time</div>
                            </div>

                            <div class="col-md-6">
                                <label for="event_end_date" class="form-label">End Date & Time *</label>
                                <input type="datetime-local" class="form-control form-control-lg" id="event_end_date"
                                       name="end_date" required>
                                <div class="invalid-feedback">Please select the end date and time</div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div class="form-section mb-4">
                        <h3 class="section-title">Location</h3>
                        <div class="row g-4">
                            <div class="col-12">
                                <label for="event_location" class="form-label">Location *</label>
                                <input type="text" class="form-control form-control-lg" id="event_location"
                                       name="location" required>
                                <div class="invalid-feedback">Please enter the event location</div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Users Section -->
                    <div class="form-section mb-4">
                        <h3 class="section-title">Assigned Users</h3>
                        <div class="row g-4">
                            <div class="col-12">
                                <label for="event_assigned_users" class="form-label">Select Users to Assign</label>
                                <select class="form-select form-select-lg" id="event_assigned_users"
                                        name="assigned_users[]" multiple>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>">
                                            <?php echo htmlspecialchars($user['full_name']); ?>
                                            (<?php echo htmlspecialchars($user['email']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Is Public Section -->
                    <div class="form-section mb-4">
                        <h3 class="section-title">Visibility</h3>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1">
                            <label class="form-check-label" for="is_public">Make this event public</label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions mt-4">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="events.php" class="btn btn-lg btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-lg btn-primary">Add Event</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="../assets/js/admin.js"></script>
<script>
    $(document).ready(function () {
        // Initialize Select2 for user selection
        $('#event_assigned_users').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select users',
            allowClear: true,
            width: '100%'
        });

        // Initialize Flatpickr for date inputs
        flatpickr("#event_start_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today"
        });
        flatpickr("#event_end_date", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today"
        });

        // Form submission
        $('#addEventForm').on('submit', function (event) {
            event.preventDefault();

            if (!this.checkValidity()) {
                event.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }

            // Validate dates
            const startDate = new Date($('#event_start_date').val());
            const endDate = new Date($('#event_end_date').val());

            if (endDate < startDate) {
                $('#event_end_date').addClass('is-invalid');
                $('#event_end_date').next('.invalid-feedback').text('End date must be after start date');
                return;
            }

            const formData = new FormData(this);

            $.ajax({
                url: 'add_event.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.success) {
                            // Show success message
                            const successAlert = `
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        Event created successfully!
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                `;
                            $('.content-wrapper').prepend(successAlert);

                            // Redirect after 2 seconds
                            setTimeout(() => {
                                window.location.href = 'events.php?success=1';
                            }, 2000);
                        } else {
                            // Show error message
                            const errorMessage = data.errors ? data.errors.join('<br>') : data.message;
                            const errorAlert = `
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        ${errorMessage}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                `;
                            $('.content-wrapper').prepend(errorAlert);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Error processing server response');
                    }
                },
                error: function (xhr, status, error) {
                    const errorAlert = `
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                An error occurred while creating the event. Please try again.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                    $('.content-wrapper').prepend(errorAlert);
                }
            });
        });
    });
</script>
</body>
</html> 