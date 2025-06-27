<?php
session_start();
include 'php/db_connect.php'; // Correct path to your database connection

// Security Check: Only allow logged-in admins to access this page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Access Denied. Please login as Admin.'); window.location.href='login.php';</script>";
    exit();
}

$appointments = [];
// Join with vision_patients and vision_doctors tables to get names
$sql = "SELECT a.id, p.full_name AS patient_name, d.name AS doctor_name, 
               s.name AS service_name, a.appointment_date, a.appointment_time, 
               a.notes, a.status
        FROM vision_appointments a
        JOIN vision_patients p ON a.patient_id = p.id
        JOIN vision_doctors d ON a.doctor_id = d.id
        JOIN vision_services s ON a.service_id = s.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments - Vision Point Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
      
        /* Specific styles for appointment table */
        .appointments-table-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            overflow-x: auto; /* For responsive tables */
        }
        .appointments-table-container h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 28px;
        }
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .appointments-table th, .appointments-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }
        .appointments-table th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: 600;
            white-space: nowrap;
        }
        .appointments-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .appointments-table tr:hover {
            background-color: #eef;
        }
       
/* Top bar container */
.top-bar {
  display: flex;
  justify-content: space-between; /* Space out heading and time */
  align-items: center; /* Vertically align items */
  margin-bottom: 30px;
  padding-bottom: 15px; /* Add padding at the bottom */
  border-bottom: 1px solid #e0e0e0; /* Subtle line below the header area */
}

/* Main Heading within the top bar */
.main-content h2 {
  font-size: 28px; /* Slightly larger for prominence */
  font-weight: 700; /* Bolder */
  color: white; 
  margin: 0; /* Reset default margin */
  padding: 0; /* Reset default padding */
  letter-spacing: -0.5px; /* Slightly tighter letter spacing for modern feel */
}

/* Current time display */
.time-display {
  color: #666; /* Slightly darker grey for better readability */
  font-size: 16px; /* A bit larger */
  font-weight: 500; /* Medium weight */
  background-color: #f5f5f5; /* Light background for the time box */
  padding: 8px 15px; /* Padding around the text */
  border-radius: 8px; /* Rounded corners */
  box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* Subtle shadow */
  white-space: nowrap; /* Prevent wrapping on smaller screens */
}

/* Responsive adjustments for smaller screens */
@media (max-width: 768px) {
  .top-bar {
    flex-direction: column; /* Stack heading and time on smaller screens */
    align-items: flex-start; /* Align stacked items to the start */
    padding-bottom: 10px;
  }

  .main-content h2 {
    font-size: 24px; /* Adjust font size for mobile */
    margin-bottom: 10px; /* Add space between heading and time */
  }

  .time-display {
    font-size: 14px; /* Adjust font size for mobile */
    padding: 6px 12px;
  }
}


    </style>
</head>
<body>
 
     
      

        <main class="main-content">
            <header class="top-bar">
                <h1>Patient Appointments</h1>
                <div class="time-display" id="current-time"></div>
            </header>

            <div class="appointments-table-container">
                <?php if (empty($appointments)): ?>
                    <p style="text-align: center;">No appointments found.</p>
                <?php else: ?>
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Notes</th>
                                <th>Status</th>
                                
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($appointment['id']); ?></td>
                                    <td><?= htmlspecialchars($appointment['patient_name']); ?></td>
                                    <td><?= htmlspecialchars($appointment['doctor_name']); ?></td>
                                    <td><?= htmlspecialchars($appointment['service_name']); ?></td>
                                    <td><?= htmlspecialchars($appointment['appointment_date']); ?></td>
                                    <td><?= htmlspecialchars(date('h:i A', strtotime($appointment['appointment_time']))); ?></td>
                                    <td><?= nl2br(htmlspecialchars($appointment['notes'] ?: 'N/A')); ?></td>
                                    <td class="status-<?= strtolower(htmlspecialchars($appointment['status'])); ?>">
                                        <?= htmlspecialchars(ucfirst($appointment['status'])); ?>
                                    </td>
                                    
                                    </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="js/dashboard.js"></script>
</body>
</html>