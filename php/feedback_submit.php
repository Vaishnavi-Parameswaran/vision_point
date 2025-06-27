<?php
session_start();

// Enable error reporting for debugging. IMPORTANT: Disable this in production!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Database Connection ---
// This file is in the 'php' folder, so db_connect.php should be in the same folder.
include 'db_connect.php';

// --- Security Check ---
// IMPORTANT: Use $_SESSION['user_id'] consistently (matching what your login sets)
// Also, add the role check for robustness, similar to patient_dashboard.php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    echo "<script>alert('Unauthorized access. Please login.'); window.location.href='../login.php';</script>"; // Redirect to login in root
    exit();
}

// Check if the request method is POST (prevent direct access)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get patient_id from session (using 'user_id' for consistency)
    $patient_id = $_SESSION['user_id'];

    // Sanitize and validate input
    $feedback_text = $_POST['feedback_text'] ?? ''; // Get feedback text, default to empty string if not set
    // Validate rating as an integer and ensure it's within 1-5 range
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);

    // Basic validation for required fields and valid rating
    if (empty($feedback_text)) {
        echo "<script>alert('Feedback text cannot be empty.'); window.history.back();</script>";
        exit();
    }
    if ($rating === false || $rating === null) { // filter_input returns false for failure, null if not set
        echo "<script>alert('Please select a valid rating between 1 and 5 stars.'); window.history.back();</script>";
        exit();
    }

    // --- Prepare SQL Statement (CRUCIAL for Security against SQL Injection) ---
    // Corrected column name from 'submission_date' to 'submitted_at' as per your database schema
    $sql = "INSERT INTO vision_feedbacks (patient_id, feedback_text, rating, submitted_at) VALUES (?, ?, ?, NOW())";

    if (isset($conn) && $conn) { // Check if connection object exists and is valid
        $stmt = $conn->prepare($sql); // Prepare the SQL statement

        if ($stmt) {
            // Bind parameters: 'isi' for integer (patient_id), string (feedback_text), integer (rating)
            $stmt->bind_param("isi", $patient_id, $feedback_text, $rating);

            // Execute the prepared statement
            if ($stmt->execute()) {
                // Success: Alert and redirect to the main index page (assuming it's in the root folder)
                echo "<script>alert('Thank you for your feedback!'); window.location.href='../index.php';</script>";
                exit(); // Important to exit after redirect
            } else {
                // Error during execution: Log the error, show generic message to user
                error_log("Error inserting feedback: " . $stmt->error);
                echo "<script>alert('Something went wrong during submission. Please try again later.'); window.history.back();</script>";
                exit();
            }
            $stmt->close(); // Close the prepared statement
        } else {
            // Error during statement preparation: Log the error
            error_log("Error preparing feedback statement: " . $conn->error);
            echo "<script>alert('Database error. Could not prepare statement. Please contact support.'); window.history.back();</script>";
            exit();
        }
    } else {
        // Database connection failed
        error_log("Database connection failed for feedback submission in feedback_submit.php.");
        echo "<script>alert('Database connection not available. Please try again later.'); window.history.back();</script>";
        exit();
    }

} else {
    // If someone tries to access this script directly without a POST request, redirect them
    header("Location: ../feedback.php"); // Redirect back to the feedback form in the root folder
    exit();
}
?>