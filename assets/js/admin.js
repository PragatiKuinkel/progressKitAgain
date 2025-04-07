document.addEventListener('DOMContentLoaded', function() {
    // Theme Toggle
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        body.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
    }

    themeToggle.addEventListener('click', function() {
        const currentTheme = body.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        body.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
        const icon = themeToggle.querySelector('i');
        const text = themeToggle.querySelector('span');
        
        if (theme === 'dark') {
            icon.className = 'fas fa-sun';
            text.textContent = 'Light Mode';
        } else {
            icon.className = 'fas fa-moon';
            text.textContent = 'Dark Mode';
        }
    }

    // Mobile Menu Toggle
    const mobileMenuBtn = document.createElement('button');
    mobileMenuBtn.className = 'mobile-menu-btn';
    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('.top-nav').prepend(mobileMenuBtn);

    const sidebar = document.querySelector('.sidebar');
    mobileMenuBtn.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !mobileMenuBtn.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });

    // Initialize Charts
    const userDistributionCtx = document.getElementById('userDistributionChart');
    const eventTimelineCtx = document.getElementById('eventTimelineChart');

    if (userDistributionCtx) {
        new Chart(userDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Admins', 'Super Users', 'Users'],
                datasets: [{
                    data: [1, 5, 20], // Replace with actual data
                    backgroundColor: [
                        '#4a6bff',
                        '#28a745',
                        '#ffc107'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    if (eventTimelineCtx) {
        new Chart(eventTimelineCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Events',
                    data: [12, 19, 15, 25, 22, 30],
                    borderColor: '#4a6bff',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(74, 107, 255, 0.1)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Load Recent Activity
    const activityList = document.querySelector('.activity-list');
    if (activityList) {
        // Simulated activity data - replace with actual API call
        const activities = [
            {
                icon: 'fa-user-plus',
                text: 'New user registered',
                time: '2 minutes ago'
            },
            {
                icon: 'fa-calendar-plus',
                text: 'New event created',
                time: '1 hour ago'
            },
            {
                icon: 'fa-check-circle',
                text: 'Event registration completed',
                time: '3 hours ago'
            }
        ];

        activities.forEach(activity => {
            const activityItem = document.createElement('div');
            activityItem.className = 'activity-item';
            activityItem.innerHTML = `
                <i class="fas ${activity.icon}"></i>
                <div>
                    <p>${activity.text}</p>
                    <small>${activity.time}</small>
                </div>
            `;
            activityList.appendChild(activityItem);
        });
    }

    // Search Functionality
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            // Implement search functionality here
            console.log('Searching for:', searchTerm);
        });
    }

    // Notifications
    const notifications = document.querySelector('.notifications');
    if (notifications) {
        notifications.addEventListener('click', function() {
            // Implement notifications dropdown or modal
            console.log('Show notifications');
        });
    }

    // Responsive Adjustments
    function handleResize() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
        }
    }

    window.addEventListener('resize', handleResize);

    // Event Management Functions
    function handleAddEvent() {
        const formData = {
            event_name: $('#eventName').val(),
            description: $('#eventDescription').val(),
            event_date: $('#eventDate').val(),
            location: $('#eventLocation').val(),
            organizer_id: $('#eventOrganizer').val(),
            status: $('#eventStatus').val(),
            action: 'add'
        };

        $.ajax({
            url: 'events.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#addEventModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error adding event: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error adding event: ' + error);
            }
        });
    }

    function handleEditEvent(eventId) {
        $.ajax({
            url: 'get_event.php',
            method: 'GET',
            data: { id: eventId },
            success: function(event) {
                $('#editEventName').val(event.event_name);
                $('#editEventDescription').val(event.description);
                $('#editEventDate').val(event.event_date);
                $('#editEventLocation').val(event.location);
                $('#editEventOrganizer').val(event.organizer_id);
                $('#editEventStatus').val(event.status);
                $('#editEventModal').modal('show');
            },
            error: function(xhr, status, error) {
                alert('Error loading event: ' + error);
            }
        });
    }

    function handleUpdateEvent(eventId) {
        const formData = {
            id: eventId,
            event_name: $('#editEventName').val(),
            description: $('#editEventDescription').val(),
            event_date: $('#editEventDate').val(),
            location: $('#editEventLocation').val(),
            organizer_id: $('#editEventOrganizer').val(),
            status: $('#editEventStatus').val(),
            action: 'edit'
        };

        $.ajax({
            url: 'events.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editEventModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error updating event: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error updating event: ' + error);
            }
        });
    }

    function handleDeleteEvent(eventId) {
        $.ajax({
            url: 'events.php',
            method: 'POST',
            data: {
                id: eventId,
                action: 'delete'
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteEventModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error deleting event: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error deleting event: ' + error);
            }
        });
    }

    function handleStatusChange(eventId) {
        const newStatus = $('#newStatus').val();
        $.ajax({
            url: 'events.php',
            method: 'POST',
            data: {
                id: eventId,
                status: newStatus,
                action: 'change_status'
            },
            success: function(response) {
                if (response.success) {
                    $('#statusChangeModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error changing status: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error changing status: ' + error);
            }
        });
    }

    // Event Listeners
    $(document).ready(function() {
        // Add Event
        $('#saveEvent').on('click', handleAddEvent);

        // Edit Event
        $('.edit-event').on('click', function() {
            const eventId = $(this).data('id');
            handleEditEvent(eventId);
        });

        // Delete Event
        $('.delete-event').on('click', function() {
            const eventId = $(this).data('id');
            $('#deleteEventModal').modal('show');
            $('#confirmDelete').data('id', eventId);
        });

        // Confirm Delete
        $('#confirmDelete').on('click', function() {
            const eventId = $(this).data('id');
            handleDeleteEvent(eventId);
        });

        // Change Status
        $('.change-status').on('click', function() {
            const eventId = $(this).data('id');
            $('#statusChangeModal').modal('show');
            $('#confirmStatusChange').data('id', eventId);
        });

        // Confirm Status Change
        $('#confirmStatusChange').on('click', function() {
            const eventId = $(this).data('id');
            handleStatusChange(eventId);
        });
    });
}); 