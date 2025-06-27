<?php
session_start(); // Must be the very first line of the script

// Enable error reporting for debugging. IMPORTANT: Disable this in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone for date/time functions.
// This is crucial for accurate 'CURDATE()' and 'NOW()' comparisons in database queries
// and for consistent display. Adjust to your specific timezone (e.g., 'America/New_York').
date_default_timezone_set('Asia/Colombo'); 

// --- Database Connection ---
// This file (patient_dashboard.php) is assumed to be in the 'php/' folder.
// So, 'db_connect.php' should be in the same 'php/' folder.
include 'db_connect.php'; 

// --- Security Check ---
// Only allow logged-in patients to access this page.
// It checks if 'user_id' and 'role' are set in the session, and if the role is 'patient'.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    // If not authorized, alert the user and redirect to the login page (assumed to be in the root).
    echo "<script>alert('Please login to access the patient dashboard.'); window.location.href='../login.php';</script>";
    exit(); // Always exit after a redirect to prevent further script execution
}

// Get the patient's ID and full name from the session for display and queries
$patient_id = $_SESSION['user_id'];
$fullName = htmlspecialchars($_SESSION['fullname'] ?? 'Patient User'); 
$shortName = strtoupper(substr($fullName, 0, 1)); // First letter for avatar display

// Initialize counts for dashboard cards. These will be fetched from the database.
$upcomingAppointmentsCount = 0;
$completedAppointmentsCount = 0;
$feedbackGivenCount = 0;

// --- Handle Session Messages ---
// Display and then clear any success or error messages from previous actions (e.g., appointment booking)
$message = '';
if (isset($_SESSION['success_message'])) {
    $message = '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']); // Clear the message after displaying
}
if (isset($_SESSION['error_message'])) {
    $message = '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']); // Clear the message after displaying
}


// --- Fetch Dynamic Data from Database ---
// Ensure the database connection ($conn) is valid before performing queries
if (isset($conn) && $conn) {
    // Fetch Upcoming Appointments Count
    // Appointments scheduled for today or in the future, with status 'scheduled'
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vision_appointments WHERE patient_id = ? AND appointment_date >= CURDATE() AND status = 'scheduled'");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id); // 'i' indicates integer type for patient_id
        if ($stmt->execute()) {
            $stmt->bind_result($upcomingAppointmentsCount); // Bind the result to the variable
            $stmt->fetch(); // Fetch the single result
        } else {
            error_log("Error fetching upcoming appointments: " . $stmt->error);
            // Optionally, set message for user here
        }
        $stmt->close(); // Close the statement
    } else {
        error_log("Error preparing statement for upcoming appointments: " . $conn->error);
    }

    // Fetch Completed Appointments Count
    // Appointments with 'completed' status and date in the past
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vision_appointments WHERE patient_id = ? AND status = 'completed' AND appointment_date < CURDATE()");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id);
        if ($stmt->execute()) {
            $stmt->bind_result($completedAppointmentsCount);
            $stmt->fetch();
        } else {
            error_log("Error fetching completed appointments: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Error preparing statement for completed appointments: " . $conn->error);
    }

    // Fetch Feedback Given Count
    // Counts the number of feedback entries submitted by this patient
    // Assumes 'vision_feedbacks' table has a 'patient_id' column
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vision_feedbacks WHERE patient_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $patient_id);
        if ($stmt->execute()) {
            $stmt->bind_result($feedbackGivenCount);
            $stmt->fetch();
        } else {
            error_log("Error fetching feedback given count: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Error preparing statement for feedback count: " . $conn->error);
    }

    // It's generally good practice to close the connection after all queries are done
    // if the connection is not persistent. However, db_connect.php might manage this.
    // if (isset($conn)) { $conn->close(); }
} else {
    // If database connection itself failed
    error_log("Database connection failed for patient dashboard. Check db_connect.php.");
    // Provide fallback counts if DB connection fails for graceful degradation
    $upcomingAppointmentsCount = 'N/A';
    $completedAppointmentsCount = 'N/A';
    $feedbackGivenCount = 'N/A';
    $message = '<div class="alert alert-danger">Database connection error. Please try again later.</div>';
}

// Initial display of current date/time. This will be updated by JavaScript for real-time effect.
$currentDateTimeDisplay = date("F j, Y, g:i a");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Vision Point Eye Clinic</title>
    <!-- Link to your dashboard CSS file. patient_dashboard.php is in 'php/', css is in 'css/'. -->
    <link rel="stylesheet" href="../css/dashboard.css">
    <!-- Link to Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General body styles for consistent layout */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Prevent horizontal scroll */
            background-color: #f4f7f6; /* Overall background */
        }

        /* Dashboard Wrapper - main container for sidebar and content */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh; /* Takes full viewport height */
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background-color:#948979; /* Dark blue background */
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transition: width 0.3s ease-in-out; /* Smooth toggle for responsiveness */
            flex-shrink: 0; /* Prevent sidebar from shrinking when content is wide */
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .sidebar-header .logo {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
        }

        .menu-toggle {
            display: none; /* Hidden on desktop, shown on mobile */
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
            background-color: #222831; /* Accent color for avatar */
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
            background-color: #948979; /* Slightly lighter blue on hover/active */
            color: #fff;
        }

        .sidebar-nav .logout-item {
            margin-top: auto; /* Pushes logout to the bottom of the sidebar */
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* Main Content Area */
        .main-content {
            flex-grow: 1; /* Takes up remaining space */
            padding: 30px;
            background-color: #f4f7f6; /* Background color for main content */
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0; /* Separator line */
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
            color:  #222831; /* Accent color for time icon */
        }

        /* Dashboard Cards Grid */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Responsive grid */
            gap: 25px; /* Space between cards */
            margin-bottom: 40px;
        }

        .card {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05); /* Soft shadow */
            text-align: center;
            transition: transform 0.3s ease; /* Hover effect */
        }
        .card:hover {
            transform: translateY(-5px); /* Lift card on hover */
        }
        .card-icon {
            font-size: 48px;
            color: #948979; /* Icon color */
            margin-bottom: 15px;
        }
        .card-title {
            font-size: 18px;
            color: white;
            margin-bottom: 10px;
        }
        .card-value {
            font-size: 36px;
            font-weight: bold;
            color: white;
        }

        /* Quick Actions Section */
        .quick-actions-section {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            margin-top: 30px;
        }
        .quick-actions-section h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .quick-actions-section p {
            color: #666;
            line-height: 1.6;
        }

        /* Styles for alert messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            animation: fadeIn 0.5s ease-out; /* Simple fade-in effect */
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

   
      
      

        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            .sidebar {
                width: 0; /* Hide sidebar by default */
                overflow: hidden;
                position: fixed;
                height: 100%;
                z-index: 1000;
            }

            .sidebar.active {
                width: 250px; /* Show sidebar when active */
            }

            .menu-toggle {
                display: block; /* Show menu toggle button */
            }

            .main-content {
                margin-left: 0; /* No margin from sidebar when hidden */
                padding: 20px;
                width: 100%;
            }

            .top-bar {
                flex-direction: column; /* Stack elements vertically */
                align-items: flex-start;
            }

            .top-bar .time-display {
                margin-top: 15px;
            }

            .dashboard-cards {
                grid-template-columns: 1fr; /* Stack cards on top of each other */
            }

           
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar HTML Structure -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">Vision Point</div>
                <!-- Menu Toggle for smaller screens -->
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
                    <!-- Adjusted paths for links based on patient_dashboard.php being in 'php/' folder -->
                    <li class="nav-item active">
                        <a href="../index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="appointment.php"><i class="fas fa-calendar-plus"></i> Make Appointment</a>
                    </li>
                   
                    </li>
                    <li class="nav-item">
                        <a href="../feedback.php"><i class="fas fa-comment-alt"></i> Give Feedback</a>
                    </li>
                   
                    <li class="nav-item logout-item">
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="top-bar">
                <h2>Welcome back, <?php echo $fullName; ?> <span class="emoji">👋</span></h2>
                <div class="time-display">
                    <i class="far fa-calendar-alt"></i> <span id="current-time"></span>
                </div>
            </div>

            <?php echo $message; // Display any session messages ?>

            <div class="dashboard-cards">
                <div class="card card-upcoming">
                    <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="card-content">
                        <div class="card-title">Upcoming Appointments</div>
                        <div class="card-value"><?php echo $upcomingAppointmentsCount; ?></div>
                    </div>
                </div>


                <div class="card card-feedback">
                    <div class="card-icon"><i class="fas fa-comments"></i></div>
                    <div class="card-content">
                        <div class="card-title">Feedback Given</div>
                        <div class="card-value"><?php echo $feedbackGivenCount; ?></div>
                    </div>
                </div>
            </div>

            <section class="dashboard-section quick-actions-section">
                <h3>Quick Actions & Information</h3>
                <p>Use the navigation links on the left to manage your appointments, provide feedback.
                        Your vision, our priority. Committed to compassionate care and exceptional service.
    </p>
            </section>
        </main>
    </div>

    <!-- Link to your dashboard JavaScript file. patient_dashboard.php is in 'php/', js is in 'js/'. -->
    <script src="../js/dashboard.js"></script>
    <script>
        // Update current time every second for a real-time clock display
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
                    hour12: true // For AM/PM format
                };
                timeDisplay.textContent = now.toLocaleString('en-US', options);
            }
        }
        setInterval(updateTime, 1000); // Call updateTime every 1000 milliseconds (1 second)
        updateTime(); // Call once immediately to set the time on page load

        // Basic sidebar toggle logic for smaller screens
        // This assumes dashboard.js might not fully handle a menu toggle, or you want simple inline logic.
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');

        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active'); // Toggles a class that controls sidebar visibility/width
            });
        }

        // Optional: Close sidebar when clicking outside (on overlay, if one exists)
        // or when content area is clicked (for simple toggles)
        document.addEventListener('click', (event) => {
            if (sidebar && sidebar.classList.contains('active') && !sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Hide alerts after a few seconds (optional for better UX)
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease-out';
                    setTimeout(() => alert.remove(), 500); // Remove element after fade out
                }, 5000); // Hide after 5 seconds
            });
        });
    </script>
</body>
</html>
