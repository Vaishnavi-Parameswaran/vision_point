<?php
session_start();
include 'db_connect.php'; // Ensure this path is correct for your database connection

// Security Check: Only allow logged-in patients to access this page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

// Initialize variables for doctors and services
$doctors = [];
$services = [];
$error_message = '';
$success_message = '';

// Check for messages from make_appointment.php
if (isset($_SESSION['error_message'])) {
    $error_message = htmlspecialchars($_SESSION['error_message']);
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    $success_message = htmlspecialchars($_SESSION['success_message']);
    unset($_SESSION['success_message']);
}

// Fetch doctor list
// Using prepared statement for better security, even for simple SELECTs
$stmt_doctors = $conn->prepare("SELECT id, name FROM vision_doctors ORDER BY name ASC");
if ($stmt_doctors) {
    $stmt_doctors->execute();
    $result_doctors = $stmt_doctors->get_result();
    while ($row = $result_doctors->fetch_assoc()) {
        $doctors[] = $row;
    }
    $stmt_doctors->close();
} else {
    $error_message .= "Failed to fetch doctors: " . $conn->error . "<br>";
}

// Fetch service list from the new vision_services table
$stmt_services = $conn->prepare("SELECT id, name, duration_minutes, cost FROM vision_services ORDER BY name ASC");
if ($stmt_services) {
    $stmt_services->execute();
    $result_services = $stmt_services->get_result();
    while ($row = $result_services->fetch_assoc()) {
        $services[] = $row;
    }
    $stmt_services->close();
} else {
    $error_message .= "Failed to fetch services: " . $conn->error . "<br>";
}

// Get current date for default min value in date picker
$currentDate = date('Y-m-d');

// Close connection if it's not persistent
// $conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book New Appointment - Vision Point</title>
    <link rel="stylesheet" href="../css/appointment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
   <!-- <a href="index.php" class="back-button">← Back to Home</a>  -->
    <div class="appointment-wrapper">
        <div class="appointment-form-container">
            <h2><i class="fas fa-calendar-plus"></i> Book a New Appointment</h2>
            <p class="form-description">Please fill out the details below to schedule your appointment with a Vision Point professional.</p>

            <?php if ($error_message): ?>
                <div class="message error-message">
                    <?= $error_message; ?>
                    <button class="close-message" onclick="this.parentElement.style.display='none';">&times;</button>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="message success-message">
                    <?= $success_message; ?>
                    <button class="close-message" onclick="this.parentElement.style.display='none';">&times;</button>
                </div>
            <?php endif; ?>

            <form action="make_appointment.php" method="POST">
                <!-- Hidden input for patient_id from session -->
                <input type="hidden" name="patient_id" value="<?= htmlspecialchars($_SESSION['user_id']); ?>">

                <div class="input-group">
                    <label for="doctor_id"><i class="fas fa-user-md"></i> Choose Doctor</label>
                    <select name="doctor_id" id="doctor_id" required>
                        <option value="">-- Select Doctor --</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?= htmlspecialchars($doctor['id']); ?>"><?= htmlspecialchars($doctor['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="input-group">
                    <label for="service_id"><i class="fas fa-eye"></i> Select Service</label>
                    <select name="service_id" id="service_id" required>
                        <option value="">-- Select Service --</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?= htmlspecialchars($service['id']); ?>" 
                                data-duration="<?= htmlspecialchars($service['duration_minutes']); ?>">
                                <?= htmlspecialchars($service['name']); ?> (<?= htmlspecialchars($service['duration_minutes']); ?> mins)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label for="appointment_date"><i class="fas fa-calendar-alt"></i> Date</label>
                    <input type="date" name="appointment_date" id="appointment_date" required min="<?= $currentDate; ?>">
                </div>
                
                <div class="input-group">
                    <label for="appointment_time"><i class="fas fa-clock"></i> Time</label>
                    <input type="time" name="appointment_time" id="appointment_time" required>
                </div>

                <div class="input-group">
                    <label for="notes"><i class="fas fa-clipboard-list"></i> Notes (Optional)</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Any specific requests or conditions?"></textarea>
                </div>

                <button type="submit" class="submit-button"><i class="fas fa-calendar-check"></i> Book Appointment</button>
            </form>
        </div>
    </div>
</body>
</html>