<?php
session_start();
// Enable error reporting for debugging. IMPORTANT: Disable this in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php'; // Ensure this path is correct for your database connection file

// --- Database Connection Check ---
// Verify that the database connection ($conn) was successfully established by db_connect.php
if (!isset($conn) || $conn->connect_error) {
    // Set an error message in the session and redirect back to the form
    $_SESSION['error_message'] = "Database connection failed: " . ($conn->connect_error ?? 'Connection object not found.');
    header("Location: appointment.php");
    exit();
}

// --- Security Check ---
// Ensure the user is logged in and has the 'patient' role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    $_SESSION['error_message'] = "You must be logged in as a patient to book an appointment.";
    header("Location: ../login.php"); // Redirect to login page if not authorized
    exit();
}

// --- Process Form Submission ---
// Check if the request method is POST (meaning the form was submitted)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data using filter_input for security
    $patient_id = $_SESSION['user_id']; // Patient ID is taken from the session (securely)
    $doctor_id = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
    $service_id = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
    $appointment_date = filter_input(INPUT_POST, 'appointment_date', FILTER_SANITIZE_STRING);
    $appointment_time = filter_input(INPUT_POST, 'appointment_time', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING); // Notes are optional

    // --- Basic Server-Side Validation ---
    // Check if all required fields have valid values
    if (!$patient_id || !$doctor_id || !$service_id || empty($appointment_date) || empty($appointment_time)) {
        $_SESSION['error_message'] = "Please fill in all required fields. Debug Info: Patient ID: " . $patient_id . ", Doctor ID: " . $doctor_id . ", Service ID: " . $service_id . ", Date: " . $appointment_date . ", Time: " . $appointment_time;
        header("Location: appointment.php"); // Redirect back to the form with an error
        exit();
    }

    // Validate if the appointment date is not in the past
    // Using UTC date for comparison to avoid timezone issues, though it's best to handle timezones consistently
    if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        $_SESSION['error_message'] = "Appointment date cannot be in the past. Please select a future date.";
        header("Location: appointment.php");
        exit();
    }
    
    // You could add more advanced validation here, such as:
    // - Checking if the selected time slot is within the doctor's working hours.
    // - Checking if the time slot is still available (race condition check).

    // --- Prepare and Execute SQL INSERT Statement ---
    // SQL query to insert a new appointment into the 'vision_appointments' table
    // The 'status' is set to 'scheduled' by default
    $stmt = $conn->prepare("INSERT INTO vision_appointments (patient_id, doctor_id, service_id, appointment_date, appointment_time, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'scheduled')");

    // Check if the prepared statement was successful
    if ($stmt) {
        // Bind parameters to the prepared statement.
        // 'iiisss' specifies the data types:
        // i: integer (for patient_id, doctor_id, service_id)
        // s: string (for appointment_date, appointment_time, notes)
        $bind_success = $stmt->bind_param("iiisss", $patient_id, $doctor_id, $service_id, $appointment_date, $appointment_time, $notes);
        
        // Check if parameter binding was successful
        if (!$bind_success) {
            $_SESSION['error_message'] = "Error binding parameters for appointment: " . $stmt->error;
            header("Location: appointment.php");
            exit();
        }

        // Execute the prepared statement to insert the data
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Appointment booked successfully!";
            header("Location: ../php/patient_dashboard.php"); // Redirect to the patient dashboard on success
            exit();
        } else {
            // --- Error Handling for Database Execution ---
            // Check for specific MySQL error codes to provide more helpful messages
            if ($conn->errno == 1062) { // Error code 1062 is for duplicate entry (e.g., if a UNIQUE constraint is violated)
                $_SESSION['error_message'] = "The selected doctor is already booked at this exact time. Please choose a different time or doctor. (MySQL Error: " . $conn->error . ")";
            } else if ($conn->errno == 1452) { // Error code 1452 is for foreign key constraint failure
                $_SESSION['error_message'] = "Failed to book appointment due to invalid Doctor, Service, or Patient ID. Please check your selections. (MySQL Error: " . $conn->error . ")";
            } else {
                // General database execution error
                $_SESSION['error_message'] = "Error booking appointment: " . $stmt->error . " (MySQL Error Code: " . $conn->errno . ")";
            }
            header("Location: appointment.php"); // Redirect back to the form with an error
            exit();
        }
        $stmt->close(); // Close the statement
    } else {
        // Error if the prepared statement itself failed (e.g., SQL syntax error in the query)
        $_SESSION['error_message'] = "Database error: Could not prepare statement. " . $conn->error;
        header("Location: appointment.php"); // Redirect back to the form with an error
        exit();
    }
} else {
    // If the request method is not POST (e.g., direct access), redirect back to the form
    $_SESSION['error_message'] = "Invalid request method. Please submit the form.";
    header("Location: appointment.php");
    exit();
}

// Close database connection if it's not persistent (e.g., if you opened it explicitly in db_connect.php and not using a persistent connection)
// $conn->close();
?>