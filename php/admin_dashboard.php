<?php
session_start(); // Must be the very first line!

// Enable error reporting for debugging. IMPORTANT: Disable this in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone for date/time functions.
// This is crucial for accurate 'CURDATE()' and 'NOW()' comparisons in database queries
// and for consistent display. Adjust to your specific timezone (e.g., 'Asia/Colombo').
date_default_timezone_set('Asia/Colombo'); 

// Include database connection (only if needed immediately, otherwise load on demand)
// Assuming db_connect.php is in the same 'php' folder as admin_dashboard.php.
include 'db_connect.php'; 

// --- Security Check for Admin Access ---
// Check if 'user_id' and 'role' are set in session AND if the 'role' is exactly 'admin'.
// This prevents unauthorized access to the admin dashboard.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not authorized as admin, alert the user and redirect to the admin login page.
    // Assumes 'login_admin.php' is in the ROOT of 'vision_point_eye_clinic'.
    echo "<script>alert('Access Denied. Please login as Admin.'); window.location.href='../login_admin.php';</script>";
    exit(); // Crucial: Stop script execution immediately after redirection
}

// If execution reaches here, the user is logged in as an admin.
// Safely retrieve admin details from session using the consistent 'user_id'.
$adminId = htmlspecialchars($_SESSION['user_id'] ?? ''); // Using 'user_id' for consistency
$adminUsername = htmlspecialchars($_SESSION['username'] ?? 'Admin');
// For admin, if a 'fullname' isn't explicitly set in session, use username as fallback.
$adminFullName = htmlspecialchars($_SESSION['fullname'] ?? $adminUsername); 
$shortName = strtoupper(substr($adminFullName, 0, 1)); // First letter for avatar display

// Initialize counts for dashboard cards. These will be fetched from the database.
$totalDoctorsCount = 0;
$totalPatientsCount = 0;
$upcomingAppointmentsAdminCount = 0;
$newFeedbackCount = 0;

// --- Fetch Dynamic Data for Admin Dashboard ---
// Ensure the database connection ($conn) is valid before performing queries.
if (isset($conn) && $conn) {
    // Fetch Total Doctors Count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vision_doctors");
    if ($stmt) {
        if ($stmt->execute()) {
            $stmt->bind_result($totalDoctorsCount);
            $stmt->fetch();
        } else {
            error_log("Admin Dashboard: Error fetching total doctors: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Admin Dashboard: Error preparing statement for total doctors: " . $conn->error);
    }

    // Fetch Total Patients Count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vision_patients");
    if ($stmt) {
        if ($stmt->execute()) {
            $stmt->bind_result($totalPatientsCount);
            $stmt->fetch();
        } else {
            error_log("Admin Dashboard: Error fetching total patients: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Admin Dashboard: Error preparing statement for total patients: " . $conn->error);
    }

    // Fetch Upcoming Appointments Count (Admin View - all upcoming appointments)
    // You might want to filter by status like 'scheduled' and date >= CURDATE()
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vision_appointments WHERE appointment_date >= CURDATE() AND status = 'scheduled'");
    if ($stmt) {
        if ($stmt->execute()) {
            $stmt->bind_result($upcomingAppointmentsAdminCount);
            $stmt->fetch();
        } else {
            error_log("Admin Dashboard: Error fetching upcoming appointments (admin): " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Admin Dashboard: Error preparing statement for upcoming appointments (admin): " . $conn->error);
    }

    // Fetch New Feedback Count (e.g., feedback not yet reviewed, if you have a 'reviewed' column)
    // For simplicity, let's just fetch total feedback count for now.
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vision_feedbacks");
    if ($stmt) {
        if ($stmt->execute()) {
            $stmt->bind_result($newFeedbackCount);
            $stmt->fetch();
        } else {
            error_log("Admin Dashboard: Error fetching new feedback count: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Admin Dashboard: Error preparing statement for new feedback count: " . $conn->error);
    }

    // Close database connection if not persistent (handled by db_connect.php or not explicitly closed here)
    // if (isset($conn)) { $conn->close(); }
} else {
    // If database connection itself failed
    error_log("Admin Dashboard: Database connection failed. Check db_connect.php.");
    // Provide fallback counts if DB connection fails for graceful degradation
    $totalDoctorsCount = 'N/A';
    $totalPatientsCount = 'N/A';
    $upcomingAppointmentsAdminCount = 'N/A';
    $newFeedbackCount = 'N/A';
}

// Initial display of current date/time. This will be updated by JavaScript for a real-time clock.
$currentDateTimeDisplay = date("F j, Y, g:i a");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Vision Point Eye Clinic</title>
    <!-- Link to your dashboard CSS file. admin_dashboard.php is in 'php/', css is in 'css/'. -->
    <link rel="stylesheet" href="../css/dashboard.css"> 
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      

        body { /* Ensure no external padding/margin affecting dashboard-wrapper */
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Prevent horizontal scroll */
        }

        /* Dashboard Wrapper - main flex container for layout */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh; /* Full viewport height */
            background-color: white
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background-color:white;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transition: width 0.3s ease-in-out; /* For responsive toggle effect */
            flex-shrink: 0; /* Prevent sidebar from shrinking */
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
            background-color: #222831;
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
            background-color:#948979;
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
            background-color: #f4f7f6; /* Background color for main content area */
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
            color: var(--primary-admin-color); /* Admin specific heading color */
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
            color: #222831;
        }

        /* Dashboard Grid for cards */
        .dashboard-grid {
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
        .card h3 {
            color:white ;
            margin-bottom: 10px;
            font-size: 20px;
        }
        .card p {
            font-size: 36px;
            font-weight: bold;
            color: white
            
        }

        /* Quick Actions Section */
        .quick-actions {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            margin-top: 30px;
        }
        .quick-actions h3 {
            color: var(--primary-admin-color);
            margin-bottom: 20px;
            font-size: 20px;
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
                margin-left: 0; /* No margin when sidebar is hidden */
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

            .dashboard-grid {
                grid-template-columns: 1fr; /* Stack cards on small screens */
            }

            .quick-actions .action-button {
                width: 100%; /* Full width buttons on small screens */
                margin-right: 0;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar HTML Structure -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">Admin Panel</div>
                <!-- Menu Toggle for smaller screens -->
                <div class="menu-toggle" id="menuToggle">
                    <span>&#9776;</span>
                </div>
            </div>

            <div class="user-profile">
                <div class="avatar"><?php echo $shortName; ?></div>
                <div class="user-info">
                    <div class="user-name"><?php echo $adminFullName; ?></div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active">
                        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="../add_doctor.php"><i class="fas fa-user-md"></i> Doctors</a>
                    </li>
                    <li class="nav-item">
                        <a href="../view_patients.php"><i class="fas fa-users"></i> Patients</a>
                    </li>
                    <li class="nav-item">
                        <a href="../manage_appointments.php"><i class="fas fa-calendar-alt"></i> Appointments</a>
                    </li>
                     <li class="nav-item">
                        <a href="../view_feedback.php"><i class="fas fa-comments"></i> Patient Feedback</a>
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
                <h2>Admin Dashboard <span class="emoji">📊</span></h2>
                <div class="time-display">
                    <i class="far fa-calendar-alt"></i> <span id="current-time"></span>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Dashboard cards/sections would go here -->
                <div class="card">
                    <h3>Total Doctors</h3>
                    <p><?php echo $totalDoctorsCount; ?></p> 
                </div>
                <div class="card">
                    <h3>Total Patients</h3>
                    <p><?php echo $totalPatientsCount; ?></p> 
                </div>
                <div class="card">
                    <h3>Upcoming Appointments</h3>
                    <p><?php echo $upcomingAppointmentsAdminCount; ?></p> 
                </div>
                <div class="card">
                    <h3>Total Feedback</h3>
                    <p><?php echo $newFeedbackCount; ?></p> 
                </div>
            </div>
            <!-- Example of a section for quick actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
             
            </div>
        </main>
    </div>

    <!-- JavaScript for menu toggle and current time -->
    <script src="../js/dashboard.js"></script>
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
        updateTime(); // Call once immediately

        // Basic sidebar toggle logic (if dashboard.js doesn't handle it)
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
