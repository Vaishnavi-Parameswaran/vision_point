<?php
session_start();

// Enable error reporting for debugging. IMPORTANT: Disable this in production!
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

$feedback_entries = [];
// Join with vision_patients to get patient's full name for display
$sql = "SELECT 
            f.id, 
            f.patient_id, 
            p.full_name AS patient_name, 
            f.rating, 
            f.feedback_text, 
            f.submitted_at 
        FROM vision_feedbacks f 
        JOIN vision_patients p ON f.patient_id = p.id 
        ORDER BY f.submitted_at DESC"; // Order by most recent feedback

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // Fetch all feedback entries
    while ($row = $result->fetch_assoc()) {
        $feedback_entries[] = $row;
    }
} else {
    // Handle database query error if any
    $error = "Error fetching feedback: " . $conn->error;
}
$conn->close(); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient Feedback - Vision Point Admin</title>
    <!-- Link to your main style.css for general site-wide styling (from root's css folder) -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Link to admin-specific styles (from root's css folder) -->
    <link rel="stylesheet" href="../css/admin.css">
    <!-- Link to dashboard-specific styles (from root's css folder), for sidebar and top-bar -->
    <link rel="stylesheet" href="../css/dashboard.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Specific styles for the feedback table and container */
        .feedback-table-container {
            max-width: 1200px;
            margin: 30px auto; /* Center the container */
            padding: 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            overflow-x: auto; /* Enable horizontal scrolling on small screens if needed */
        }
        
        .feedback-table-container h2 {
            text-align: center;
            color: #2c3e50; /* Dark blue/grey */
            margin-bottom: 25px;
            font-size: 28px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .feedback-table {
            width: 100%;
            border-collapse: collapse; /* Remove space between borders */
            margin-top: 20px;
            font-size: 14px;
            border: 1px solid #e0e0e0; /* Light border around the whole table */
            border-radius: 8px; /* Slightly rounded corners for the table */
            overflow: hidden; /* Ensures rounded corners are applied */
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); /* Subtle shadow for depth */
        }

        .feedback-table th, .feedback-table td {
            border: 1px solid #f0f0f0; /* Lighter border for cells */
            padding: 12px 15px; /* More padding */
            text-align: left;
            vertical-align: top; /* Align content to the top */
        }

        .feedback-table th {
            background-color: #393E46; /* Darker header background, consistent with other admin tables */
            color: #ffffff; /* White text for headers */
            font-weight: 600;
            white-space: nowrap; /* Prevent text wrapping in headers */
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .feedback-table tr:nth-child(even) {
            background-color: #f8f8f8; /* Light grey for even rows */
        }

        .feedback-table tr:hover {
            background-color: #eef5f9; /* Lighter blue/grey on hover */
        }

        .feedback-table .rating {
            font-weight: bold;
            color: #f39c12; /* Orange color for ratings, simulating stars */
        }

        .no-feedback {
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
            color: black; /* Make the heading text white if main-content has a dark gradient */
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
       
            


        <main class="main-content">
            <header class="top-bar">
                <h2>Patient Feedback</h2>
                <div class="time-display" id="current-time"></div>
            </header>

            <div class="feedback-table-container">
                <?php if (isset($error)): ?>
                    <div class="message-box message-error"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (empty($feedback_entries)): ?>
                    <p class="no-feedback">No feedback submitted yet.</p>
                <?php else: ?>
                    <table class="feedback-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Rating</th>
                                <th>Feedback Text</th>
                                <th>Submitted On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedback_entries as $feedback): ?>
                                <tr>
                                    <td><?= htmlspecialchars($feedback['id']); ?></td>
                                    <td><?= htmlspecialchars($feedback['patient_name']); ?></td>
                                    <td class="rating"><?= htmlspecialchars($feedback['rating']); ?> / 5</td>
                                    <td><?= nl2br(htmlspecialchars($feedback['feedback_text'])); ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($feedback['submitted_at']))); ?></td>
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
