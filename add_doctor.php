<?php
session_start();

// Enable error reporting for debugging (Disable in production!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the default timezone for consistent date/time handling
date_default_timezone_set('Asia/Colombo'); // Adjust to your specific timezone

// Include database connection. This script is in the 'php/' folder.
include 'php/db_connect.php'; 

// --- Security Check for Admin Access ---
// Check if 'user_id' and 'role' are set in session AND if the 'role' is exactly 'admin'.
// This prevents unauthorized access to this page.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not authorized as admin, alert the user and redirect to the login page (assuming login.php is in root).
    echo "<script>alert('Access Denied. Please login as Admin.'); window.location.href='../login.php';</script>";
    exit(); // Crucial: Stop script execution immediately after redirection
}

$doctors = [];
// Fetch all doctors from the vision_doctors table
// UPDATED: Selecting individual schedule columns instead of 'schedule_json'
$sql = "SELECT 
            id, 
            name, 
            specialty, 
            email, 
            phone ,
            bio, 
            image_path, 
            monday_schedule, 
            tuesday_schedule, 
            wednesday_schedule, 
            thursday_schedule, 
            friday_schedule, 
            saturday_schedule, 
            sunday_schedule 
        FROM vision_doctors ORDER BY name ASC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // Fetch all doctor entries
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
} else {
    // Handle database query error if any
    $error = "Error fetching doctors: " . $conn->error;
}
$conn->close(); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Doctors - Vision Point Admin</title>
    <!-- Link to your main style.css for general site-wide styling (from root's css folder) -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Link to admin-specific styles (from root's css folder) -->
    <link rel="stylesheet" href="../css/admin.css">
    <!-- Link to dashboard-specific styles (from root's css folder), for sidebar and top-bar -->
    <link rel="stylesheet" href="../css/dashboard.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Specific styles for the doctors table and container */
        .doctors-table-container {
            max-width: 1200px;
            margin: 30px auto; /* Center the container */
            padding: 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            overflow-x: auto; /* Enable horizontal scrolling on small screens if needed */
        }
        
        .doctors-table-container h2 {
            text-align: center;
            color: #2c3e50; /* Dark blue/grey */
            margin-bottom: 25px;
            font-size: 28px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .doctors-table {
            width: 100%;
            border-collapse: collapse; /* Remove space between borders */
            margin-top: 20px;
            font-size: 14px;
            border: 1px solid #e0e0e0; /* Light border around the whole table */
            border-radius: 8px; /* Slightly rounded corners for the table */
            overflow: hidden; /* Ensures rounded corners are applied */
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); /* Subtle shadow for depth */
        }

        .doctors-table th, .doctors-table td {
            border: 1px solid #f0f0f0; /* Lighter border for cells */
            padding: 12px 15px; /* More padding */
            text-align: left;
            vertical-align: top; /* Align content to the top */
        }

        .doctors-table th {
            background-color: #393E46; /* Darker header background, consistent with other admin tables */
            color: #ffffff; /* White text for headers */
            font-weight: 600;
            white-space: nowrap; /* Prevent text wrapping in headers */
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doctors-table tr:nth-child(even) {
            background-color: #f8f8f8; /* Light grey for even rows */
        }

        .doctors-table tr:hover {
            background-color: #eef5f9; /* Lighter blue/grey on hover */
        }

        .doctors-table .doctor-image {
            width: 60px; /* Smaller image size for table */
            height: 60px;
            border-radius: 50%; /* Circular image */
            object-fit: cover; /* Cover the area, crop if needed */
            border: 2px solid #eee;
            vertical-align: middle;
        }

        .no-doctors {
            text-align: center;
            color: #777;
            padding: 40px;
            font-size: 1.2em;
            background-color: #fdfdfd;
            border: 1px dashed #ddd;
            border-radius: 8px;
            margin-top: 20px;
        }

        /* Adjust top-bar h2 for consistency with dashboard.css gradient background */
        .top-bar h2 {
            color: #222831; /* Make the heading text white if main-content has a dark gradient */
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
    
        <main class="main-content">
            <header class="top-bar">
                <h2>All Registered Doctors</h2>
                <div class="time-display" id="current-time"></div>
            </header>

            <div class="doctors-table-container">
                <?php if (isset($error)): ?>
                    <div class="message-box message-error"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (empty($doctors)): ?>
                    <p class="no-doctors">No doctors registered yet.</p>
                <?php else: ?>
                    <table class="doctors-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Speciality</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Bio</th>
                                <th>Schedule</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doctor): ?>
                                <tr>
                                    <td><?= htmlspecialchars($doctor['id']); ?></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($doctor['image_path'] ?: '../images/default_doctor.png'); ?>" 
                                             alt="Dr. <?= htmlspecialchars($doctor['name']); ?>" 
                                             class="doctor-image"
                                             onerror="this.onerror=null;this.src='../images/default_doctor.png';">
                                    </td>
                                    <td><?= htmlspecialchars($doctor['name']); ?></td>
                                    <td><?= htmlspecialchars($doctor['specialty']); ?></td>
                                    <td><?= htmlspecialchars($doctor['email']); ?></td>
                                    <td><?= htmlspecialchars($doctor['phone']); ?></td> <!-- Changed from phone_number to phone to match alias -->
                                    <td><?= nl2br(htmlspecialchars($doctor['bio'] ?: 'N/A')); ?></td>
                                    <td>
                                        <?php 
                                        // UPDATED: Displaying individual schedule fields
                                        $schedule_display = [];
                                        if (!empty($doctor['monday_schedule'])) $schedule_display[] = "Mon: " . htmlspecialchars($doctor['monday_schedule']);
                                        if (!empty($doctor['tuesday_schedule'])) $schedule_display[] = "Tue: " . htmlspecialchars($doctor['tuesday_schedule']);
                                        if (!empty($doctor['wednesday_schedule'])) $schedule_display[] = "Wed: " . htmlspecialchars($doctor['wednesday_schedule']);
                                        if (!empty($doctor['thursday_schedule'])) $schedule_display[] = "Thu: " . htmlspecialchars($doctor['thursday_schedule']);
                                        if (!empty($doctor['friday_schedule'])) $schedule_display[] = "Fri: " . htmlspecialchars($doctor['friday_schedule']);
                                        if (!empty($doctor['saturday_schedule'])) $schedule_display[] = "Sat: " . htmlspecialchars($doctor['saturday_schedule']);
                                        if (!empty($doctor['sunday_schedule'])) $schedule_display[] = "Sun: " . htmlspecialchars($doctor['sunday_schedule']);
                                        
                                        echo empty($schedule_display) ? 'N/A' : implode('<br>', $schedule_display);
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Include dashboard.js for current time display and sidebar functionality -->
    <script src="../js/dashboard.js"></script>
    <script>
        // Update current time every second (redundant if dashboard.js already handles this)
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
    </script>
</body>
</html>


