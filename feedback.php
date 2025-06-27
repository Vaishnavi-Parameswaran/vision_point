<?php
session_start();

// Enable error reporting for debugging. IMPORTANT: Disable this in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Database Connection ---
// Ensure this path is correct relative to feedback.php
// If feedback.php is in 'vision_point_eye_clinic/' (root) and db_connect.php is in 'vision_point_eye_clinic/php/'
include 'php/db_connect.php'; 

// --- Security Check ---
// Only allow logged-in patients to access this page
// Check for user_id AND role, and ensure role is 'patient' (lowercase)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    // Alert and redirect to login page if not authorized
    echo "<script>alert('Please login to submit feedback.'); window.location.href='login.php';</script>";
    exit(); // Always exit after a header redirect or script-based redirect
}

// Get the user's full name securely from session for display in sidebar
$fullName = htmlspecialchars($_SESSION['fullname'] ?? 'Patient User'); 
$shortName = strtoupper(substr($fullName, 0, 1)); // First letter for avatar

// Initialize variables for the form (useful if you want to pre-fill, though not needed here)
$feedback_text = '';
$rating = '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Give Feedback - Vision Point Eye Clinic</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Your custom CSS for the feedback form and general layout, assuming it's in a shared CSS or you want to keep it here */
        /* It's highly recommended to move this into a separate CSS file like `css/feedback_form.css`
           and link it in the head section: `<link rel="stylesheet" href="css/feedback_form.css">` */
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            /* Removed padding from body, dashboard-wrapper will handle it */
            margin: 0;
            overflow-x: hidden; /* Prevent horizontal scroll on small screens */
        }

        /* Dashboard Layout - ensures sidebar and main content work together */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh; /* Full viewport height */
            background-color: #f4f7f6;
        }

        .sidebar {
            width: 250px;
            background-color: #003366; /* Dark blue */
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transition: width 0.3s ease-in-out; /* For responsive sidebar */
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative; /* For menu toggle positioning */
        }

        .sidebar-header .logo {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
        }

        .menu-toggle {
            display: none; /* Hidden by default for desktop */
            position: absolute;
            right: 0;
            top: 0;
            font-size: 24px;
            cursor: pointer;
            color: #fff;
        }

        .user-profile {
            text-align: center;
            margin-bottom: 30px;
        }

        .user-profile .avatar {
            width: 60px;
            height: 60px;
            background-color: #007bff; /* Light blue */
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            font-weight: bold;
            color: #fff;
            margin: 0 auto 10px;
        }

        .user-profile .user-name {
            font-size: 18px;
            font-weight: bold;
            color: #fff;
        }

        .user-profile .user-role {
            font-size: 14px;
            color: #e0e0e0;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin-bottom: 10px;
        }

        .sidebar-nav a {
            display: block;
            color: #e0e0e0;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: flex;
            align-items: center;
        }

        .sidebar-nav a i {
            margin-right: 10px;
            font-size: 18px;
        }

        .sidebar-nav a:hover,
        .sidebar-nav .nav-item.active a {
            background-color: #0059b3; /* Slightly lighter blue */
            color: #fff;
        }

        .sidebar-nav .logout-item {
            margin-top: auto; /* Pushes logout to the bottom */
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .main-content {
            flex-grow: 1; /* Takes remaining space */
            padding: 30px;
            background-color: #f4f7f6;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .top-bar h2 {
            margin: 0;
            color: #333;
            font-size: 28px;
            display: flex;
            align-items: center;
        }

        .top-bar .emoji {
            margin-left: 10px;
            font-size: 24px;
        }

        .time-display {
            font-size: 16px;
            color: #666;
            display: flex;
            align-items: center;
        }

        .time-display i {
            margin-right: 8px;
            color: #007bff;
        }

        /* Feedback Form Specific Styles */
        .feedback-container {
            max-width: 700px;
            margin: 30px auto; /* Center the form in the main content area */
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }

        .feedback-container h2 {
            text-align: center;
            color: #003366;
            margin-bottom: 25px;
        }

        textarea {
            width: 100%;
            height: 120px;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: vertical; /* Allow vertical resizing only */
            box-sizing: border-box; /* Include padding and border in element's total width */
        }

        .rating {
            margin: 20px 0;
            text-align: center;
            direction: rtl; /* For right-to-left stars for easier selection */
        }

        .rating input {
            display: none;
        }

        .rating label {
            font-size: 30px;
            color: #ccc;
            cursor: pointer;
            padding: 0 6px;
            transition: color 0.2s ease-in-out;
        }

        .rating input:checked ~ label,
        .rating label:hover,
        .rating label:hover ~ label {
            color: gold;
        }
        /* Ensure the selected star stays gold */
        .rating input:checked + label {
            color: gold;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 10px; /* Space between buttons */
        }

        .btn-group button {
            flex-grow: 1;
            padding: 12px 20px;
            font-size: 17px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn {
            background-color: #222831;
            color: white;
        }
        .submit-btn:hover {
            background-color:#948979;
        }

        .cancel-btn {
            background-color: #222831;
            color: white;
        }
        .cancel-btn:hover {
            background-color: #948979;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 16px;
            background: #222831;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color:#948979;
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
                position: fixed;
                height: 100%;
                z-index: 1000;
            }

            .sidebar.active {
                width: 250px;
            }

            .menu-toggle {
                display: block;
            }

            .main-content {
                margin-left: 0; /* No margin when sidebar is hidden */
                padding: 20px;
                width: 100%;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .top-bar .time-display {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">Vision Point</div>
                <div class="menu-toggle" id="menuToggle">
                    <span>&#9776;</span>
                </div>
            </div>

            <div class="user-profile">
                <div class="avatar"><?php echo $shortName; ?></div>
                <div class="user-info">
                    <div class="user-name"><?php echo $fullName; ?></div>
                    <div class="user-role">Patient</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item">
                        <a href="index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="php/appointment.php"><i class="fas fa-calendar-plus"></i> Make Appointment</a>
                    </li>
                    
                    <li class="nav-item active"> <a href="feedback.php"><i class="fas fa-comment-alt"></i> Give Feedback</a>
                    </li>
                    
                    <li class="nav-item logout-item">
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h2>Give Feedback <span class="emoji">💬</span></h2>
                <div class="time-display">
                    <i class="far fa-calendar-alt"></i> <span id="current-time"></span>
                </div>
            </div>

            <div class="feedback-container">
                <a href="index.php" class="back-button">← Back to Home</a>
                <h2>Your Opinion Matters!</h2>

                <form action="php/feedback_submit.php" method="POST">
                    <label for="feedback_text">Your Feedback:</label>
                    <textarea name="feedback_text" id="feedback_text" required placeholder="Tell us about your experience..."></textarea>

                    <div class="rating">
                        <input type="radio" name="rating" id="star5" value="5" required><label for="star5">★</label>
                        <input type="radio" name="rating" id="star4" value="4"><label for="star4">★</label>
                        <input type="radio" name="rating" id="star3" value="3"><label for="star3">★</label>
                        <input type="radio" name="rating" id="star2" value="2"><label for="star2">★</label>
                        <input type="radio" name="rating" id="star1" value="1"><label for="star1">★</label>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="submit-btn">Submit Feedback</button>
                        <button type="reset" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="js/dashboard.js"></script>
    <script>
        // Update current time every second
        function updateTime() {
            const timeDisplay = document.getElementById('current-time');
            if (timeDisplay) {
                const now = new Date();
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: true
                };
                timeDisplay.textContent = now.toLocaleString('en-US', options);
            }
        }
        setInterval(updateTime, 1000); // Update every second
        updateTime(); // Call once immediately when page loads

        // Basic sidebar toggle logic (if dashboard.js doesn't already handle it effectively)
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');

        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }
    </script>
</body>
</html>