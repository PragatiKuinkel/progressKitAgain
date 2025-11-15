document.addEventListener('DOMContentLoaded', function() {
    // Theme Toggle
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    const themeIcon = themeToggle.querySelector('i');

    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        themeIcon.classList.replace('fa-moon', 'fa-sun');
    }

    themeToggle.addEventListener('click', function() {
        body.classList.toggle('dark-mode');
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        } else {
            localStorage.setItem('theme', 'light');
            themeIcon.classList.replace('fa-sun', 'fa-moon');
        }
    });

    // Mobile Menu Toggle
    const mobileMenuBtn = document.createElement('button');
    mobileMenuBtn.className = 'mobile-menu-btn';
    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('.top-nav').prepend(mobileMenuBtn);

    mobileMenuBtn.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('show');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 && 
            !e.target.closest('.sidebar') && 
            !e.target.closest('.mobile-menu-btn')) {
            document.querySelector('.sidebar').classList.remove('show');
        }
    });

    // Search Functionality
    const searchInput = document.querySelector('.search-bar input');
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        // Implement search functionality based on the current page
        console.log('Searching for:', searchTerm);
    });

    // Notifications
    const notifications = document.querySelector('.notifications');
    notifications.addEventListener('click', function() {
        // Implement notifications dropdown or modal
        console.log('Notifications clicked');
    });

    // Initialize Charts
    if (document.querySelector('#userDistributionChart')) {
        const userCtx = document.getElementById('userDistributionChart').getContext('2d');
        new Chart(userCtx, {
            type: 'doughnut',
            data: {
                labels: ['Regular Users', 'Super Users'],
                datasets: [{
                    data: [70, 30],
                    backgroundColor: [
                        'rgba(74, 144, 226, 0.8)',
                        'rgba(46, 204, 113, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    if (document.querySelector('#eventTimelineChart')) {
        const eventCtx = document.getElementById('eventTimelineChart').getContext('2d');
        new Chart(eventCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Events',
                    data: [12, 19, 15, 25, 22, 30],
                    borderColor: 'rgba(74, 144, 226, 1)',
                    backgroundColor: 'rgba(74, 144, 226, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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

    // Event Status Toggle
    document.querySelectorAll('.status-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = this.dataset.eventId;
            const currentStatus = this.dataset.status;
            const newStatus = currentStatus === 'active' ? 'cancelled' : 'active';
            
            if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'cancel'} this event?`)) {
                // Send AJAX request to update status
                fetch('update_event_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        event_id: eventId,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.dataset.status = newStatus;
                        this.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                        this.className = `status-badge ${newStatus}`;
                    } else {
                        alert('Error updating event status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating event status');
                });
            }
        });
    });

    // User Role Update
    document.querySelectorAll('.role-select').forEach(select => {
        select.addEventListener('change', function() {
            const userId = this.dataset.userId;
            const newRole = this.value;
            
            if (confirm(`Are you sure you want to change this user's role to ${newRole}?`)) {
                // Send AJAX request to update role
                fetch('update_user_role.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        role: newRole
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Error updating user role');
                        this.value = this.dataset.originalValue;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating user role');
                    this.value = this.dataset.originalValue;
                });
            } else {
                this.value = this.dataset.originalValue;
            }
        });
    });

    // Delete User
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                // Send AJAX request to delete user
                fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closest('tr').remove();
                    } else {
                        alert('Error deleting user');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting user');
                });
            }
        });
    });

    // Responsive Adjustments
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            document.querySelector('.sidebar').classList.remove('show');
        }
    });
}); 