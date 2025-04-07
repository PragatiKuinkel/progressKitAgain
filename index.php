<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventPro - Professional Event Management System</title>
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <img src="assets/images/progress-kit-logo.png" alt="Progress Kit" class="logo-img">
            </div>
            <div class="nav-links">
                <a href="#about">About</a>
                <a href="#events">Events</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#testimonials">Testimonials</a>
                <a href="#contact">Contact</a>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-outline">Sign In</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                </div>
            </div>
            <div class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Transform Your Event Experience</h1>
            <p>Discover, register, and manage events with ease. Your gateway to unforgettable experiences.</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-primary">Get Started</a>
                <a href="#about" class="btn btn-outline">Learn More</a>
            </div>
        </div>
        <div class="hero-overlay"></div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2 class="section-title">About EventPro</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>EventPro is a comprehensive event management platform designed to simplify the way you discover, register, and manage events. Whether you're an event organizer or attendee, our platform provides the tools you need for a seamless experience.</p>
                    <p>Our mission is to connect people through meaningful events and create unforgettable experiences.</p>
                </div>
                <div class="about-stats">
                    <div class="stat-item">
                        <h3>500+</h3>
                        <p>Events Hosted</p>
                    </div>
                    <div class="stat-item">
                        <h3>10K+</h3>
                        <p>Happy Users</p>
                    </div>
                    <div class="stat-item">
                        <h3>50+</h3>
                        <p>Partners</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section id="events" class="events">
        <div class="container">
            <h2 class="section-title">Upcoming Events</h2>
            <div class="swiper events-slider">
                <div class="swiper-wrapper">
                    <!-- Event Card 1 -->
                    <div class="swiper-slide">
                        <div class="event-card">
                            <div class="event-image">
                                <img src="assets/images/event1.jpg" alt="Tech Conference">
                            </div>
                            <div class="event-details">
                                <span class="event-date">May 15, 2024</span>
                                <h3>Tech Conference 2024</h3>
                                <p>Join industry leaders for a day of innovation and networking.</p>
                                <a href="#" class="btn btn-outline">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <!-- Event Card 2 -->
                    <div class="swiper-slide">
                        <div class="event-card">
                            <div class="event-image">
                                <img src="assets/images/event2.jpg" alt="Business Summit">
                            </div>
                            <div class="event-details">
                                <span class="event-date">June 20, 2024</span>
                                <h3>Business Summit</h3>
                                <p>Connect with entrepreneurs and business leaders.</p>
                                <a href="#" class="btn btn-outline">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <!-- Add more event cards as needed -->
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Browse Events</h3>
                    <p>Discover upcoming events in your area or field of interest.</p>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>Register</h3>
                    <p>Create an account and register for events with ease.</p>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h3>Get Tickets</h3>
                    <p>Receive your tickets and event details instantly.</p>
                </div>
                <div class="step">
                    <div class="step-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Attend</h3>
                    <p>Show up and enjoy your event experience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Users Say</h2>
            <div class="swiper testimonials-slider">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                <p>"EventPro made organizing our conference a breeze. The platform is intuitive and user-friendly."</p>
                            </div>
                            <div class="testimonial-author">
                                <img src="assets/images/user1.jpg" alt="User">
                                <div>
                                    <h4>Sarah Johnson</h4>
                                    <p>Event Organizer</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add more testimonials as needed -->
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <!-- Sponsors Section -->
    <section class="sponsors">
        <div class="container">
            <h2 class="section-title">Our Partners</h2>
            <div class="sponsors-grid">
                <img src="assets/images/sponsor1.png" alt="Sponsor 1">
                <img src="assets/images/sponsor2.png" alt="Sponsor 2">
                <img src="assets/images/sponsor3.png" alt="Sponsor 3">
                <!-- Add more sponsor logos as needed -->
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <p>info@progresskit.com</p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <p>+977 9841000011</p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>07 Kathmandu Nepal</p>
                    </div>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <form class="contact-form">
                    <div class="form-group">
                        <input type="text" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <textarea placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>EventPro</h3>
                    <p>Your gateway to unforgettable event experiences.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#about">About</a></li>
                        <li><a href="#events">Events</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 EventPro. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="assets/js/landing.js"></script>
</body>
</html> 