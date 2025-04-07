<?php
session_start();

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/dbconnection.php';

// Initialize variables
$errors = [];
$success_message = '';
$user_id = $_SESSION['user_id'];

// Get user profile information
try {
    $stmt = $dbh->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch user error: " . $e->getMessage());
    $errors[] = "Error fetching user information";
}

// Get upcoming registered events
try {
    $stmt = $dbh->prepare("
        SELECT e.*, r.status as registration_status
        FROM events e
        JOIN registrations r ON e.id = r.event_id
        WHERE r.user_id = ? AND e.event_date > NOW()
        ORDER BY e.event_date ASC
    ");
    $stmt->execute([$user_id]);
    $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch upcoming events error: " . $e->getMessage());
    $errors[] = "Error fetching upcoming events";
}

// Get past events
try {
    $stmt = $dbh->prepare("
        SELECT e.*, r.status as registration_status, r.rating, r.feedback
        FROM events e
        JOIN registrations r ON e.id = r.event_id
        WHERE r.user_id = ? AND e.event_date <= NOW()
        ORDER BY e.event_date DESC
    ");
    $stmt->execute([$user_id]);
    $past_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch past events error: " . $e->getMessage());
    $errors[] = "Error fetching past events";
}

// Get notifications
try {
    $stmt = $dbh->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch notifications error: " . $e->getMessage());
    $errors[] = "Error fetching notifications";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - EventPro</title>
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="light-mode">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/images/progress-kit-logo.png" alt="Progress Kit">
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="events.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Events</span>
                    </a>
                </li>
                <li>
                    <a href="notifications.php">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
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
                    <span class="badge"><?php echo count($notifications); ?></span>
                </div>
                <div class="user-profile">
                    <img src="../assets/images/user-avatar.jpg" alt="User">
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="content">
            <div class="content-header">
                <h1>Dashboard Overview</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Profile Summary -->
            <div class="profile-summary">
                <div class="profile-card">
                    <div class="profile-header">
                        <img src="../assets/images/user-avatar.jpg" alt="User">
                        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="profile-details">
                        <div class="detail-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <span>Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <a href="profile.php" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="section">
                <h2>Upcoming Events</h2>
                <div class="events-grid">
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="event-card">
                            <div class="event-header">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <span class="status-badge <?php echo $event['registration_status']; ?>">
                                    <?php echo ucfirst($event['registration_status']); ?>
                                </span>
                            </div>
                            <div class="event-details">
                                <div class="detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('M d, Y', strtotime($event['event_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo date('h:i A', strtotime($event['event_time'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                            </div>
                            <div class="event-actions">
                                <a href="view_event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if ($event['registration_status'] === 'pending'): ?>
                                    <button class="btn btn-danger cancel-registration" 
                                            data-event-id="<?php echo $event['id']; ?>">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Past Events -->
            <div class="section">
                <h2>Past Events</h2>
                <div class="events-grid">
                    <?php foreach ($past_events as $event): ?>
                        <div class="event-card">
                            <div class="event-header">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <div class="event-rating">
                                    <?php if ($event['rating']): ?>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $event['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-primary rate-event" 
                                                data-event-id="<?php echo $event['id']; ?>">
                                            <i class="fas fa-star"></i> Rate Event
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="event-details">
                                <div class="detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('M d, Y', strtotime($event['event_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                            </div>
                            <div class="event-actions">
                                <a href="view_event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if (!$event['feedback']): ?>
                                    <button class="btn btn-info leave-feedback" 
                                            data-event-id="<?php echo $event['id']; ?>">
                                        <i class="fas fa-comment"></i> Leave Feedback
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Notifications -->
            <div class="section">
                <h2>Recent Notifications</h2>
                <div class="notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="notification-content">
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <small><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Rate Event Modal -->
    <div id="rateEventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Rate Event</h2>
                <button class="close-modal">&times;</button>
            </div>
            <form id="rateEventForm" method="POST">
                <input type="hidden" name="action" value="rate_event">
                <input type="hidden" name="event_id" id="rate_event_id">
                <div class="form-group">
                    <label>Rating</label>
                    <div class="rating-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star" data-rating="<?php echo $i; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="rating_value">
                </div>
                <div class="form-group">
                    <label for="feedback">Feedback</label>
                    <textarea id="feedback" name="feedback" rows="4"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeRateEventModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/user.js"></script>
    <script>
        // Rating stars functionality
        document.querySelectorAll('.rating-stars i').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                const stars = this.parentElement.querySelectorAll('i');
                
                stars.forEach(s => {
                    if (s.dataset.rating <= rating) {
                        s.classList.add('selected');
                    } else {
                        s.classList.remove('selected');
                    }
                });
                
                document.getElementById('rating_value').value = rating;
            });
        });

        // Rate event button click handler
        document.querySelectorAll('.rate-event').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('rate_event_id').value = this.dataset.eventId;
                showRateEventModal();
            });
        });

        // Leave feedback button click handler
        document.querySelectorAll('.leave-feedback').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('rate_event_id').value = this.dataset.eventId;
                showRateEventModal();
            });
        });

        // Cancel registration button click handler
        document.querySelectorAll('.cancel-registration').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel your registration for this event?')) {
                    const eventId = this.dataset.eventId;
                    // Add AJAX call to cancel registration
                    fetch('cancel_registration.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            event_id: eventId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error canceling registration');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error canceling registration');
                    });
                }
            });
        });

        // Modal functions
        function showRateEventModal() {
            document.getElementById('rateEventModal').style.display = 'block';
        }

        function closeRateEventModal() {
            document.getElementById('rateEventModal').style.display = 'none';
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