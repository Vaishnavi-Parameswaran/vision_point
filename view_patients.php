<?php
session_start();

// Enable error reporting for debugging (Disable in production!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Colombo'); // Set your timezone

// Include database connection
include 'php/db_connect.php';

// --- Security Check for Admin Access ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Access Denied. Please login as Admin.'); window.location.href='../login.php';</script>";
    exit();
}

$patients = [];
// Fetch all patients from the vision_patients table
$sql = "SELECT id, full_name,nic_num, email_id, mobile_number, created_at 
        FROM vision_patients 
        ORDER BY created_at DESC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
} else {
    $error = "Error fetching patients: " . $conn->error;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Patients - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css"> 
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Specific styles for patient table */
        .patients-table-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            overflow-x: auto; /* For responsive tables */
        }
        .patients-table-container h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 28px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .patients-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        .patients-table th, .patients-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }
        .patients-table th {
            background-color: #393E46; /* Darker header background */
            color: #ffffff; /* White text for headers */
            font-weight: 600;
            white-space: nowrap; /* Prevent wrapping for headers */
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .patients-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .patients-table tr:hover {
            background-color: #eef5f9; /* Light blue/grey on hover */
            cursor: pointer;
        }
        .no-patients {
            text-align: center;
            color: #777;
            padding: 40px;
            font-size: 1.2em;
            background-color: #fdfdfd;
            border: 1px dashed #ddd;
            border-radius: 8px;
            margin-top: 20px;
        }
        .top-bar .main-content h2 { /* Targeting specific h2 within main content for top bar */
            color: white; /* Make heading white against the gradient background */
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        
            
            <div class="user-profile">
               
          
        

        <main class="main-content">
            <header class="top-bar">
                <h2>All Registered Patients</h2>
                <div class="time-display" id="current-time"></div>
            </header>

            <div class="patients-table-container">
                <h2><i class="fas fa-users"></i> Patient Records</h2>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if (empty($patients)): ?>
                    <p class="no-patients">No patient records found.</p>
                <?php else: ?>
                    <table class="patients-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>NIC</th>
                                <th>Email</th>
                                <th>Phone</th>
                                
                                
                                <th>Reg. Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><?= htmlspecialchars($patient['id']) ?></td>
                                <td><?= htmlspecialchars($patient['full_name']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($patient['nic_num'])) ?></td>
                                <td><?= htmlspecialchars($patient['email_id']) ?></td>
                                <td><?= htmlspecialchars($patient['mobile_number']) ?></td>
                                
                               
                                <td><?= htmlspecialchars($patient['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../js/dashboard.js"></script>
    <script>
        // Update current time every second (redundant if dashboard.js already does this)
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
    </script>
</body>
</html>